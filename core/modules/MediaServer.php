<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class MediaServer extends \Environment\Core\Module
{
    protected $config = [
        'template' => 'layouts/MediaServer/Default.html',
        'listen' => 'action'
    ];

    private function getFiles( $idService)
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
    "public"."files" AS "f"
WHERE "f"."service_id" = {$idService}
ORDER BY "f"."file_name" DESC 


SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
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
    "public"."service_name" AS "sn"
left join "public"."files" AS "f" ON "sn"."id" = "f"."service_id"

GROUP BY  "sn"."name", "sn"."id"; 
SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

        $stmt->execute();

        } catch (\SoapFault $e) {

            return $e;

        }
        return $stmt->fetchAll();
    }

    private function getSumFileSize()
    {
        $sql = <<<SQL
SELECT
    SUM("sn"."file_size" * 0.0000010)  AS sum_files
FROM
    "public"."files" AS "sn"

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
    "public"."service_name"
SET
    "name" = :name

WHERE
    ("id" = :id);
SQL;

        $stmt = Connections::getConnection( 'MediaServer' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'id' => $id,
            'name' => $name
        ]));exit;

    }


    function addName(){
        $name = $_POST['name'];
        $sql = <<<SQL
INSERT INTO
    "public"."service_name"
    ("name")
VALUES
    (
        :name
    ); 

SQL;

        $stmt = Connections::getConnection( 'MediaServer' )->prepare( $sql );

        echo json_encode($stmt->execute([
            'name' => $name
        ]));exit;

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
        $this->variables->errors = [];
        $idService =  $_GET['idService'] ?? null;
        $ChkAnalize =  $_GET['ChkAnalize'] ?? null;
        $arr = [ 'files' => 'file' ];
        try {

           $this->variables->serviceName   = $this->getServiceName();
            $this->variables->FileSize     = $this->getSumFileSize()[0]['sum_files'];
            $this->variables->idService    = $idService;
            $this->variables->link         = $link;
            $this->variables->link2        = $link2;
            if ( $ChkAnalize )
            {
                $resFil = $this->makeRequest($link2, $arr);
                $json=json_decode($resFil, true);
                $this->variables->jsonfile = $json;
            }
            if ( $idService ) {
                $this->variables->files    = $this->getFiles( $idService );
             }

        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }

}