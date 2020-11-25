<?php


namespace Unikum\Core;

use PDO;
use Unikum\Core\Dbms\ConnectionManager as Connections;

abstract class Migration {

    /**
     * @param PDO $dbms
     */
    abstract protected static function up(PDO $dbms):void;

    /**
     * @param PDO $dbms
     */
    abstract protected static function down(PDO $dbms):void;

    /**
     * @return array
     */
    private static function getUnresolved():array {
        $sql = <<<SQL
			SELECT "Version" FROM "Core"."Migration" ORDER BY 1;
SQL;
        $dbms = Connections::getConnection('Environment');

        $stmt = $dbms->prepare($sql);
        $stmt->execute([]);
        $data = $stmt->fetchAll();
        $migrations = [];

        foreach ($data as $record) {
            $migrations[] = 'Migration' . $record['Version'] . '.php';
        }

        $migrationsFiles = glob( PATH_MIGRATIONS . "Migration*.php" );
        $result = [];

        foreach ($migrationsFiles as $file) {
            if(in_array(substr($file, -27), $migrations)) continue;

            $class = substr($file, 0, -4);
            $class = substr($class, -23);
            $result[] = $class;
        }
        asort($result);

        return $result;
    }

    /**
     * @return string|null
     */
    private static function getLastResolved() {
        $sql = <<<SQL
			SELECT "Version" FROM "Core"."Migration" ORDER BY 1 DESC LIMIT 1;
SQL;
        $dbms = Connections::getConnection('Environment');

        $stmt = $dbms->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return (count($data) === 0) ? null : $data[0]['Version'];
    }

    /**
     *
     */
    public static function migrate():void {

        $unresolved = static::getUnresolved();

        if(count($unresolved) == 0) {
            echo "\e[91mDB migrations not found" . "\e[39m" . PHP_EOL;
            exit;
        }

        echo "Start migrations" . PHP_EOL;

        $dbms = Connections::getConnection('Environment');

        $sql = <<<SQL
			INSERT INTO "Core"."Migration" ("Version") VALUES (?);
SQL;
        $stmt = $dbms->prepare($sql);

        $dbms->beginTransaction();

        foreach ($unresolved as $index => $migration) {
            try {
                $version = substr($migration, -14);
                $className = "\\Environment\\Migrations\\" . $migration;
                $className::up($dbms);
                $stmt->execute([ $version ]);
                echo "\e[92m".($index+1) . ': ' . $migration . ' - success' . "\e[39m" . PHP_EOL;
            }
            catch(\Exception $e) {
                echo "\e[91m".($index+1) . ': ' . $migration . ' - failure' . PHP_EOL;
                echo $e->getMessage() . "\e[39m" . PHP_EOL;
                $dbms->rollBack();
                exit;
            }
        }

        echo "\e[92mMigrations has been resolved\e[39m" . PHP_EOL;
        $dbms->commit();

    }

    /**
     *
     */
    public static function downgrade():void {
        $last = static::getLastResolved();
        if(is_null($last)) {
            echo "\e[91mNot found previous resolved migrations"  . "\e[39m" . PHP_EOL;
            return;
        }
        $className = '\\Environment\\Migrations\\Migration' . $last;

        $sql = <<<SQL
			DELETE FROM "Core"."Migration" WHERE "Version" = ?;
SQL;

        $dbms = Connections::getConnection('Environment');
        $dbms->beginTransaction();
        try {
            if(!class_exists($className)) {
                throw new \Exception("Class " . $className . " not exists");

            }
            $className::down($dbms);
            $stmt = $dbms->prepare($sql);
            $stmt->execute([ $last ]);
        }
        catch(\Exception $e) {
            echo "Migration " . $last . " not downgraded" . PHP_EOL;
            echo "\e[91m" . $e->getMessage()  . "\e[39m" . PHP_EOL;
            $dbms->rollBack();
            return;
        }

        echo "\e[92mMigration " . $last . " has been downgraded"  . "\e[39m" . PHP_EOL;
        $dbms->commit();
    }

    /**
     *
     */
    public static function generate() {
        $version = date('YmdHis');
        $fileName = PATH_MIGRATIONS . 'Migration' . $version . '.php';

        $template = sprintf(file_get_contents(PATH_DOCS . 'migration.php.tpl'), $version);
        file_put_contents($fileName, $template);
        echo "\e[92mMigration " . $version . ' generated' . "\e[39m" . PHP_EOL;
    }

}
