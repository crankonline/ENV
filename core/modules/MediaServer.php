<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class MediaServer extends \Environment\Core\Module
{
    const ROWS_PER_PAGE = 30;

    protected $config = [
        'template' => 'layouts/MediaServer/Default.html',
        'listen' => 'action',
		'plugins'  => [
			'paginator' => Plugins\Paginator::class
        ]
    ];

    private function getFiles(  array $filters, $limit = null, $offset = null,$idService )
    {
         $sql = <<<SQL
SELECT
    "f"."id" AS "id",
    "f"."file_name" AS "name",
    ("f"."file_size" * 0.0000010) AS "size",
    "f"."given_name" AS "given_name",
    "f"."time_stamp" AS "time_stamp",
    "f"."service_id" AS "service_id"
FROM
    "mediaserver"."files" AS "f"
WHERE "f"."service_id" = {$idService}
ORDER BY "f"."file_name" DESC 


SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

        $stmt->execute();

        $count = $stmt->fetchColumn();

        $limits = null;
        if ( $limit !== null ) {
            $limits[] = 'LIMIT :limit';

            $values['limit'] = $limit;

            if ( $offset !== null ) {
                $limits[] = 'OFFSET :offset';

                $values['offset'] = $offset;
            }
        }

        $limits = ! empty( $limits ) ? implode( PHP_EOL, $limits ) : '';
        $sql = <<<SQL
SELECT
    "f"."id" AS "id",
    "f"."file_name" AS "name",
    ("f"."file_size" * 0.0000010) AS "size",
    "f"."given_name" AS "given_name",
    "f"."time_stamp" AS "time_stamp",
    "f"."service_id" AS "service_id"
FROM
    "mediaserver"."files" AS "f"
WHERE "f"."service_id" = {$idService}
ORDER BY 
    "f"."file_name" DESC 
    {$limits};  
SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);
        //var_dump($stmt);die();


        if ( $limit !== null ) {
           // var_dump($limit);die();

/*            var_dump($stmt->execute( [
                'service_id'        =>$idService,
                'limit'            => 30,
                'offset'           => $offset
            ] ));die();*/
            $stmt->execute( [
                'limit'            => 30,
                'offset'           => $offset
            ] );
            //var_dump('assa');die();
            $rows = $stmt->fetchAll();
           // var_dump($rows);die();
            return [ &$count, &$rows ];
        } else {
            $stmt->execute( [
            ] );
            var_dump('$stmt');die();
            return $stmt;
        }
    }

    private function getServiceName()
    {
        try {


        $sql = <<<SQL
SELECT
    "sn"."id" AS "id",
    "sn"."name" AS "name",
    COUNT("f".*) as "files_count",
    SUM("f"."file_size" * 0.0000010)  AS sum_files
FROM
    "mediaserver"."service_name" AS "sn"
left join "mediaserver"."files" AS "f" ON "sn"."id" = "f"."service_id"

GROUP BY  "sn"."name", "sn"."id"; 
SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

        $stmt->execute();

        } catch (\SoapFault $e) {
            \Sentry\captureException( $e );
            exit;

        }
        return $stmt->fetchAll();
    }

    private function getSumFileSize()
    {
        $sql = <<<SQL
SELECT
    SUM("sn"."file_size" * 0.0000010)  AS sum_files_size,
    count("sn"."id") AS sum_files
FROM
    "mediaserver"."files" AS "sn"

SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function editName(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $sql = <<<SQL
UPDATE
    "mediaserver"."service_name"
SET
    "name" = :name

WHERE
    ("id" = :id);
SQL;

        $stmt = Connections::getConnection( 'MediaServer' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'id' => $id,
            'name' => $name
        ]));
        exit;

    }


    function addName(){
        $name = $_POST['name'];
        $sql = <<<SQL
INSERT INTO
    "mediaserver"."service_name"
    ("name")
VALUES
    (
        :name
    ); 

SQL;

        $stmt = Connections::getConnection( 'MediaServer' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'name' => $name
        ]));
        exit;

    }

    function makeRequest($url, $data) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        return $response;

    }


    protected function main() {
        $link = $_ENV['core_modules_MediaServer_FileStore'].'file/download/';
        $link2 = $_ENV['core_modules_MediaServer_FileStore'].'analyzer-file/';
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-sochi.css';
        $this->context->css[] = 'resources/css/ui-service-zero-report.css';
        $this->context->css[] = 'resources/css/jquery.dataTables.min.css';
        $this->variables->errors = [];
        $idService =  $_GET['idService'] ?? null;
        $ChkAnalize =  $_GET['ChkAnalize'] ?? null;
        $arr = [ 'files' => 'file' ];
        $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
        $limit  = self::ROWS_PER_PAGE;
        $offset = ( $page - 1 ) * $limit;

        try {

           $this->variables->serviceName   = $this->getServiceName();
            $this->variables->FileSize     = $this->getSumFileSize()[0]['sum_files_size'];
            $this->variables->FileSum      = $this->getSumFileSize()[0]['sum_files'];
            $this->variables->idService    = $idService;
            $this->variables->link         = $link;
            $this->variables->link2        = $link2;
            $this->variables->ChkAnalize        =$ChkAnalize;
            if ( $ChkAnalize )
            {

                $resFil = $this->makeRequest($link2, $arr);
                $json=json_decode($resFil, true);
               // var_dump($json);die();
                $this->variables->jsonfile = $json;
            }
            if ( $idService ) {
                list( $count, $files ) = $this->getFiles( [], $limit, $offset,  $idService);

                $this->context->paginator['count'] = (int) ceil( $count / $limit );

                $this->variables->count   = $count;

                $this->variables->files    = &$files;
             }

        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }

}