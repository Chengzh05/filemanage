<?php
/**
 * Created by PhpStorm.
 * User: 28982
 * Date: 2019/6/27
 * Time: 10:24
 */
function zipDir($zip,$dir,$dirname){
    if(is_dir($dir)) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;

                if (is_dir($fullpath)) {
                    $dirname = $dirname."/".$file;

                    zipDir($zip,$fullpath,$dirname);

                } else {
                    $filename = substr($fullpath,strripos($fullpath,"/")+1);

                    //dump($dirname."/".$filename);
                    $zip->addFile($fullpath,$dirname."/".$filename);
                }
            }
        }
        closedir($dh);

        return true;
    }
}
