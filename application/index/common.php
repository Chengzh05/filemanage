<?php
/**
 * Created by PhpStorm.
 * User: 28982
 * Date: 2019/6/27
 * Time: 10:24
 */
function  isImg($fileName)
{
    $file     = fopen($fileName, "rb");
    $bin      = fread($file, 2);  // 只读2字节

    fclose($file);
    $strInfo  = @unpack("C2chars", $bin);
    $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
    $fileType = '';

    if($typeCode == 255216 /*jpg*/ || $typeCode == 7173 /*gif*/ || $typeCode == 13780 /*png*/)
    {
        return $typeCode;
    }
    else
    {
        // echo '"仅允许上传jpg/jpeg/gif/png格式的图片！';
        return false;
    }
}
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
