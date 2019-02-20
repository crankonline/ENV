<?php
namespace Unikum\Core;

class FileManager {
    const
        ROOT_DIRECTORY  = PATH_FILESHARE,
        SEND_CHUNK_SIZE = 524288; // 512 * 1024 -> 512 Kb per chunk by default

    public static function getFullPath($path){
        return strpos($path, self::ROOT_DIRECTORY) === 0 ? $path : self::ROOT_DIRECTORY . $path;
    }

    public static function createTmpFile($content){
        $time = microtime(true);
        $sid  = isset($_SESSION) ? session_id() : rand();
        $try  = 1;

        do {
            $file = self::ROOT_DIRECTORY . sprintf(
                'TEMP-%s-%s-%s',
                $time,
                $sid,
                $try++
            );
        } while(is_file($file));

        $handle = @fopen($file, 'wb');

        if(!$handle){
            return false;
        }

        fwrite($handle, $content);
        fclose($handle);

        return $file;
    }

    public static function createTmpDir(){
        $time = microtime(true);
        $sid  = isset($_SESSION) ? session_id() : rand();
        $try  = 1;

        do {
            $dir = self::ROOT_DIRECTORY . sprintf(
                'TEMP-%s-%s-%s/',
                $time,
                $sid,
                $try++
            );
        } while(is_dir($dir));

        return mkdir($dir) ? $dir : false;
    }

    public static function removeDirAndFiles($dir){
        if(!is_dir($dir)){
            return false;
        }

        if(substr($dir, -1) != '/'){
            $dir .= '/';
        }

        $files = glob($dir . '*', GLOB_MARK);

        foreach($files as $file){
            if(is_dir($file)){
                self::removeDirAndFiles($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }

    public static function exists($path){
        return is_file(self::getFullPath($path));
    }

    public static function get($path){
        return self::exists($path) ? file_get_contents(self::getFullPath($path)) : false;
    }

    public static function delete($path){
        return self::exists($path) ? unlink(self::getFullPath($path)) : false;
    }

    public static function put($path, $content){
        return file_put_contents(self::getFullPath($path), $content);
    }

    public static function open($path, $mode){
        return self::exists($path) ? fopen(self::getFullPath($path), $mode) : false;
    }

    public static function upload($temp, $path){
        return move_uploaded_file($temp, self::getFullPath($path));
    }

    public static function flush(
        $path,
        $alias = null,
        $size  = null,
        $hash  = null,
        $chunk = self::SEND_CHUNK_SIZE
    ){
        $path = self::getFullPath($path);

        if(!self::exists($path)){
            return false;
        }

        $handle = fopen($path, 'rb');

        if(!$handle){
            return false;
        }

        $size  = $size === null ? filesize($path) : (int)$size;
        $hash  = $hash === null ? md5_file($path) : $hash;
        $alias = $alias === null ? basename($path) : addslashes($alias);

        header('Content-Length: ' . $size);
        header('Content-MD5: ' . $hash);
        header('Accept-Ranges: bytes');
        header('Connection: close');
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="' . $alias . '"');

        ob_end_clean();
        ob_start();

        while(!feof($handle)){
            set_time_limit(0);

            echo fread($handle, $chunk);

            ob_flush();
            flush();
        }

        fclose($handle);

        return true;
    }
}
?>