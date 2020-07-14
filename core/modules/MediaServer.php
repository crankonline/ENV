<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class MediaServer extends \Environment\Core\Module
{

    protected $config = [
        'template' => 'layouts/MediaServer/Default.html',
        'listen' => 'action',
		'plugins'  => [
			'paginator' => Plugins\Paginator::class
        ]
    ];

    private function getFiles($idService, array $filters )
    {
    try{
        if ($filters){
            $params = [];

            if ($filters['from'] && strlen($filters['from']) > 0 && $filters['file_size_min'] && strlen($filters['file_size_min']) > 0) {

                $params[] = 'AND ("f"."time_stamp" BETWEEN :f_d_min AND :f_d_max) AND ("f"."file_size" BETWEEN :f_s_min AND :f_s_max)';

            } elseif ($filters['from']) {

                $params[] = 'AND ("f"."time_stamp" BETWEEN :f_d_min AND :f_d_max)';

            }  elseif ($filters['file_size_min']) {

                $params[] = 'AND ("f"."file_size" BETWEEN :f_s_min AND :f_s_max)';

            }

            else {
                $params[0] = NULL;
            }

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
     AND "f"."file_name" like '%{$filters['fileName']}%'
    {$params[0]}
  ORDER BY "f"."time_stamp" ASC
SQL;
            $stmt = Connections::getConnection('MediaServer')->prepare($sql);

            if ($filters['from'] && $filters['file_size_min']) {
                $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['to'])));

                $stmt->execute([
                    'f_d_min' => $filters['from'],
                    'f_d_max'   => $premium_date,
                    'f_s_min'   => ($filters['file_size_min']  * 1000000),
                    'f_s_max'   => ($filters['file_size_max']  * 1000000)
                ]);

            } elseif ($filters['from']) {
                $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['to'])));

                $stmt->execute([

                    'f_d_min' => $filters['from'],
                    'f_d_max'   => $premium_date,
                ]);

            }  elseif ($filters['file_size_min']) {


                $stmt->execute([
                    'f_s_min'   => ($filters['file_size_min']  * 1000000),
                    'f_s_max'   => ($filters['file_size_max']  * 1000000)
                ]);
            }

            else {

                $stmt->execute();

            }

        } else {
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
ORDER BY "f"."time_stamp" ASC
SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

        $stmt->execute();
        }

        } catch (\SoapFault $e) {
            \Sentry\captureException( $e );
            exit;
        }

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
    "mediaserver"."service_name" AS "sn"
left join "mediaserver"."files" AS "f" ON "sn"."id" = "f"."service_id"
GROUP BY  "sn"."name", "sn"."id"
ORDER BY "sn"."id"; 
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
        $сhkAnalize =  $_GET['сhkAnalize'] ?? null;
        $chkFilter =  $_GET['chkFilter'] ?? null;
        $pstTru          = $_POST['pst_tru'] ?? null;


        $arr = [ 'files' => 'file' ];

        try {

            $this->variables->serviceName      = $this->getServiceName();
            $this->variables->FileSize         = $this->getSumFileSize()[0]['sum_files_size'];
            $this->variables->FileSum          = $this->getSumFileSize()[0]['sum_files'];
            $this->variables->idService        = $idService;
            $this->variables->link             = $link;
            $this->variables->link2            = $link2;
            $this->variables->сhkAnalize       =$сhkAnalize;
            $this->variables->file_name        = $_POST['file_name'] ?? null;
            $this->variables->file_size_min    = $_POST['$file_size_min'] ?? null;
            $this->variables->file_size_max    = $_POST['$file_size_max'] ?? null;
            $this->variables->from             = $_POST['$from'] ?? null;
            $this->variables->to               = $_POST['$to'] ?? null;


            if ( $сhkAnalize )
            {
                $resFil = $this->makeRequest($link2, $arr);
                $json=json_decode($resFil, true);
                $this->variables->jsonfile = $json;
            }
            if ( $idService ) {

                    if ( $pstTru ) {
                        $sss = $this->getFiles($idService, [
                            'fileName' => $_POST['file_name'],
                            'file_size_min' => $_POST['file_zie_min'],
                            'file_size_max' => $_POST['file_zie_max'],
                            'from' => $_POST['from'],
                            'to' => $_POST['to'],
                        ]);
                        echo json_encode($sss);
                        exit;

                    } else {

                        $this->variables->files = $this->getFiles($idService, []);
                    }
            }


        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }

}