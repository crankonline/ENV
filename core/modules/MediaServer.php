<?php

namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class MediaServer extends \Environment\Core\Module
{
    const ROWS_PER_PAGE = 50;

    protected $config = [
        'template' => 'layouts/MediaServer/Default.html',
        'listen' => 'action',
		'plugins'  => [
			'paginator' => Plugins\Paginator::class
        ]
    ];

    private function getFiles($idService, array $filters, $limit = null, $offset = null   )
    {
    try{

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

        if ($filters){
            $params = [];

            if ($filters['from'] && strlen($filters['from']) > 0 && $filters['file_size_max'] && strlen($filters['file_size_max']) > 0) {

                $params[] = 'AND ("f"."time_stamp" BETWEEN :f_d_min AND :f_d_max) AND ("f"."file_size" BETWEEN :f_s_min AND :f_s_max)';

            } elseif ($filters['from']) {

                $params[] = 'AND ("f"."time_stamp" BETWEEN :f_d_min AND :f_d_max)';

            }  elseif ($filters['file_size_max']) {

                $params[] = 'AND ("f"."file_size" BETWEEN :f_s_min AND :f_s_max)';

            }

            else {
                $params[0] = NULL;
            }

            $sql = <<<SQL
SELECT
    COUNT ("f"."id")

FROM
    "mediaserver"."files" AS "f"
WHERE "f"."service_id" = {$idService}
     AND "f"."file_name" like '%{$filters['fileName']}%'
    {$params[0]}


SQL;

            $stmt = Connections::getConnection('MediaServer')->prepare($sql);

            if ($filters['from'] && $filters['file_size_max']) {
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
                    'f_d_max'   => $premium_date


                ]);

            }  elseif ($filters['file_size_max']) {

                $stmt->execute([
                    'f_s_min'   => ($filters['file_size_min']  * 1000000),
                    'f_s_max'   => ($filters['file_size_max']  * 1000000)

                ]);
            }

            else {
                $stmt->execute();

            }


            $count = $stmt->fetchColumn();

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
  {$limits};
SQL;
            $stmt = Connections::getConnection('MediaServer')->prepare($sql);
            if ($filters['from'] && $filters['file_size_max']) {
                $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['to'])));
                $stmt->execute([
                    'f_d_min' => $filters['from'],
                    'f_d_max'   => $premium_date,
                    'f_s_min'   => ($filters['file_size_min']  * 1000000),
                    'f_s_max'   => ($filters['file_size_max']  * 1000000),
                    'limit'            => 50,
                    'offset'           => $offset

                ]);

            } elseif ($filters['from']) {
                $premium_date = date("Y-m-d", strtotime("+1 days", strtotime($filters['to'])));
                $stmt->execute([

                    'f_d_min' => $filters['from'],
                    'f_d_max'   => $premium_date,
                    'limit'            => 50,
                    'offset'           => $offset

                ]);

            }  elseif ($filters['file_size_max']) {
                $stmt->execute([
                    'f_s_min'   => ($filters['file_size_min']  * 1000000),
                    'f_s_max'   => ($filters['file_size_max']  * 1000000),
                    'limit'            => 50,
                    'offset'           => $offset

                ]);
            }

            else {
                $stmt->execute([
                    'limit'            => 50,
                    'offset'           => $offset
                ] );

            }

        } else {

            $sql = <<<SQL
SELECT
    COUNT ("f"."id")

FROM
    "mediaserver"."files" AS "f"
WHERE "f"."service_id" = {$idService}


SQL;

            $stmt = Connections::getConnection('MediaServer')->prepare($sql);

            $stmt->execute();

            $count = $stmt->fetchColumn();


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
  {$limits};
SQL;

        $stmt = Connections::getConnection('MediaServer')->prepare($sql);

            $stmt->execute([
                'limit'            => 50,
                'offset'           => $offset
            ] );
        }

        } catch (\SoapFault $e) {
            \Sentry\captureException( $e );
            exit;
        }

        return [&$count, $stmt->fetchAll()];

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
        $pstTru          = $_GET['pstTru'] ?? null;


        $arr = [ 'files' => 'file' ];

        try {

            $this->variables->serviceName      = $this->getServiceName();
            $this->variables->FileSize         = $this->getSumFileSize()[0]['sum_files_size'];
            $this->variables->FileSum          = $this->getSumFileSize()[0]['sum_files'];
            $this->variables->idService        = $idService;
            $this->variables->link             = $link;
            $this->variables->link2            = $link2;
            $this->variables->сhkAnalize       =$сhkAnalize;
            $this->variables->file_name        = $_GET['file_name'] ?? null;
            $this->variables->file_size_min    = $_GET['$file_size_min'] ?? null;
            $this->variables->file_size_max    = $_GET['$file_size_max'] ?? null;
            $this->variables->from             = $_GET['$from'] ?? null;
            $this->variables->to               = $_GET['$to'] ?? null;
            $this->variables->pstTru               = $_GET['pstTru'] ?? null;

            $page   = isset( $_GET['page'] ) ? ( abs( (int) $_GET['page'] ) ?: 1 ) : 1;
            $limit  = self::ROWS_PER_PAGE;
            $offset = ( $page - 1 ) * $limit;


            if ( $сhkAnalize )
            {
                $resFil = $this->makeRequest($link2, $arr);
                $json=json_decode($resFil, true);
                $this->variables->jsonfile = $json;
            }
            if ( $idService ) {

                    if ( $pstTru ) {
                        list($count, $files) = $this->getFiles($idService, [
                            'fileName' => $_GET['file_name'],
                            'file_size_min' => $_GET['file_zie_min'],
                            'file_size_max' => $_GET['file_zie_max'],
                            'from' => $_GET['from'],
                            'to' => $_GET['to'],
                        ],  $limit, $offset);

                        $this->context->paginator['count'] = (int)ceil($count / $limit);

                        $this->variables->count = $count;
                        $this->variables->files = &$files;

                    } else {

                        list($count, $files) = $this->getFiles($idService, [], $limit, $offset);

                        $this->context->paginator['count'] = (int)ceil($count / $limit);

                        $this->variables->count = $count;
                        $this->variables->files = &$files;
                    }
            }


        } catch ( \Exception $e ) {
            \Sentry\captureException( $e );
            $this->variables->errors[] = $e->getMessage();
        }
    }

}