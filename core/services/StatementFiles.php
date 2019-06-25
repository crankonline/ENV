<?php
/**
 * Reregister
 */
namespace Environment\Services;

use Environment\DataLayers\FileStore\Files as FilesSchema;

class StatementFiles extends \Unikum\Core\Module {
    protected $config = [
        'render' => false
    ];

    protected function main(){
        if(empty($_GET['id'])){
            return http_response_code(404);
        }

        try {
            $id = $_GET['id'];

            $dlStore = new FilesSchema\Store();

            $file = $dlStore->getById($id);

            if(!$file){
                return http_response_code(404);
            }

            $alias = addslashes($file['name']);

            header('Content-Length: ' . $file['size']);
            header('Accept-Ranges: bytes');
            header('Connection: close');
            header('Content-Type: image/jpeg');
            header('Content-Disposition: inline; filename="' . $alias . '"');
            //header('Content-Type: application/force-download');
            //header('Content-Disposition: attachment; filename="' . $alias . '"');

            echo base64_decode($file['content']);
        } catch(\Exception $e) {
            return http_response_code(500);
        }
    }
}
?>