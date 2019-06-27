<?php
namespace app\index\controller;

use think\Controller;
use ZipArchive;

class Index extends Controller
{
    public $root_dir = "";
    public $sel_dir = "";

    public function initialize()
    {

        //$this->root_dir = "E:/phpstudy/PHPTutorial/WWW/filemanager";
        $this->root_dir = $_SERVER['DOCUMENT_ROOT'].'/root';
        $this->sel_dir = cookie('path');


    }

    public function index()
    {
        $path = $this->root_dir.$this->sel_dir;
        //dump(date("Y-m-d H:i:s",filemtime($path)));

        //dump($this->xm_mkdir('www/czh/111',$this->root_dir,true));
        return $this->fetch();
    }

    public function get_scandir(){

        $sel_dir = input("path");
        if($sel_dir){
            $this->sel_dir = $sel_dir;
        }


        $last = input("last");
        if($last){
            if($this->sel_dir != "/"){
                $this->sel_dir = substr($this->sel_dir,0,strripos($this->sel_dir,"/"));
            }
        }

        if($this->sel_dir == ""){
            $this->sel_dir = "/";
        }

        cookie('path', $this->sel_dir);


        $html = $this->xm_scandir();

        return json(['code'=>0,"data"=>$html,"path"=>$this->sel_dir]);
    }


    public function add_dir(){

        if(input("post.")){
            $this->sel_dir = input("post.path");

            if($this->sel_dir == ""){
                $this->sel_dir = "/";
            }

            $dirname = input("post.dirname");

            if(is_dir($this->root_dir.$this->sel_dir.'/'.$dirname)){
                return json(['code'=>500,"msg"=>"指定目录已存在！"]);
            }


            $res = $this->xm_mkdir('/'.$dirname,$this->root_dir.$this->sel_dir);
            if($res){
                return json(['code'=>200,"msg"=>"目录创建成功！","path"=>$this->sel_dir]);
            }else{
                return json(['code'=>500,"msg"=>"目录创建失败！"]);
            }

        }else{
            return false;
        }
    }

    public function upload_file()
    {
        if(input("post.")){
            $file = request()->file('file');

            $path = input("post.path");

            if($path == ""){
                $path = "/";
            }

            $movepath = $this->root_dir.$path;


            $info = $file->validate(['size'=>1024*1024*50])->move($movepath,'');

            if($info){
                return json(['code'=>0,"msg"=>"文件上传成功！"]);
            }else{
                return json(['code'=>500,"msg"=>"文件上传失败！"]);
            }
        }else{
            return false;
        }
    }

    public function editfile()
    {
        if(input("post.")){

            $path = input("post.path");
            $old_name = input("post.old_name");
            $new_name = input("post.new_name");

            $old_path = $this->root_dir.$path.$old_name;
            $new_path = $this->root_dir.$path.$new_name;
            //dump($old_path);
            //dump($new_path);

            $res = $this->xm_editfile($old_path,$new_path);

            return json($res);
        }
    }

    public function delfile()
    {
        if(input("post.")){

            $path = input("post.path");
            $name = input("post.name");

            $paths = $this->root_dir.$path.$name;

            $res = $this->xm_deldir($paths);

            return json($res);
        }
    }

    /*批量删除*/
    public function delallfile()
    {
        if(input("post.")){

            $paths = input("post.path/a");

            try{
                if(count($paths)){
                    foreach($paths as $v){
                        $res = $this->xm_deldir($this->root_dir.$v);
                    }
                }

                return json($res);
            }catch(\Exception $e){
                return json(['code'=>500,"msg"=>$e->getMessage()]);
            }
        }
    }

    public function download()
    {
        if(input("_m")){
            $_m = input('_m');
            $filename = urldecode(base64_decode($_m));

            if(file_exists($filename)){

                $fname = substr($filename,strripos($filename,'/')+1);

                header( "Content-Disposition:  attachment;  filename=".$fname); //告诉浏览器通过附件形式来处理文件
                header('Content-Length: ' . filesize($filename)); //下载文件大小
                readfile($filename);  //读取文件内容

                exit;
            }
        }

        //abort("404");
    }

    public function download_zip()
    {
        if(input("post.")){

            $path = input("post.path");
            $name = input("post.name");
            try{

                $downname = "打包下载_".date("mHis").".zip";

                if(!$name){
                    exit;
                }

                $name = explode(',',$name);


                $file_name = $this->xm_zipdownload($path,$name,$downname);

                $fp=fopen($file_name,"r");

                $file_size=filesize($file_name);


                Header("Content-type: application/octet-stream");

                Header("Accept-Ranges: bytes");

                Header("Accept-Length:".$file_size);

                Header("Content-Disposition: attachment; filename=".urlencode($downname));

                $buffer=1024;  //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）

                $file_count=0; //读取的总字节数

                //向浏览器返回数据  如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成

                while(!feof($fp) && $file_count<$file_size){
                    $file_con=fread($fp,$buffer);
                    $file_count+=$buffer;
                    echo $file_con;
                }

                fclose($fp);

                //下载完成后删除压缩包，临时文件夹

                if($file_count >= $file_size) {
                    unlink($file_name);
                }

            }catch(\Exception $e){

            }

        }
    }

    /*打包下载*/
    public function xm_zipdownload($file_path,$file_path_name,$downname)
    {
        $file_template = $this->root_dir.'/../zip/empty.zip';
        $file_name = $this->root_dir.'/../zip/'.$downname;//把你打包后zip所存放的目录

        copy( $file_template, $file_name );

        $zip = new ZipArchive();


        if ($zip->open($file_name, ZipArchive::CREATE) === TRUE) {

            if(count($file_path_name)){
                foreach($file_path_name as $v){
                    $l_path = $this->root_dir.$file_path.$v;

                    if (is_dir($l_path)){

                        $zip->addEmptyDir($v);


                        zipDir($zip,$l_path,$v);

                    }else{
                        $zip->addFile($l_path,$v);

                    }
                }
            }


            $zip->close();


            return $file_name;



        }


    }

    /*删除文件，根据路径*/
    public function xm_deldir($dir) {
        //先删除目录下的文件：
        if(is_dir($dir)){
            $dh=opendir($dir);
            while ($file=readdir($dh)) {
                if($file!="." && $file!="..") {
                    $fullpath=$dir."/".$file;
                    if(!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        $this->xm_deldir($fullpath);
                    }
                }
            }
            closedir($dh);
            //删除当前文件夹：
            if(rmdir($dir)) {
                $return['code']='200';
                $return['msg']='删除成功';
            } else {
                $return['code']='500';
                $return['msg']='删除失败';
            }
        }else{
            if(unlink($dir)) {
                $return['code']='200';
                $return['msg']='删除成功';
            } else {
                $return['code']='500';
                $return['msg']='删除失败';
            }
        }


        return $return;
    }

    /*修改文件名*/
    public function xm_editfile($old_path,$new_path)
    {
        if(file_exists($new_path)==false){
            if (rename($old_path, $new_path))//修改目录
            {
                $return['code']='200';
                $return['msg']='修改成功';
            }
            else
            {
                $return['code']='500';
                $return['msg']='修改失败';
            }
        }else{
            $return['code']='500';
            $return['msg']='相同名称已存在';
        }

        return $return;
    }

    /*创建文件夹*/
    public function xm_mkdir($name,$path,$recursive=false){

        if(is_dir($path)){

            return @mkdir($path.$name,644,$recursive);

        }else{

            return false;
        }

    }

    /*查询目录*/
    public function xm_scandir()
    {
        $dir = $this->root_dir.$this->sel_dir;
        if(is_dir($dir)){
            $child_dirs = scandir($dir);
            //$html = "";
            $html = [];
            foreach($child_dirs as $child_dir){
                if($child_dir != '.' && $child_dir != '..'){
                    $sel_dir = $this->sel_dir=="/"?"":$this->sel_dir;
                    /*$html .= "<tr data-path='".$sel_dir."/' data-name='".$child_dir."'>";
                    if(is_dir($dir.'/'.$child_dir)){
                        $html .= "<td><div class='getfile' data-path='".$sel_dir."/".$child_dir."'><img src='/static/images/filedir.png' alt=''> ".$child_dir."</div></td>";
                    }else{
                        $html .= "<td><img src='/static/images/fileico.png' alt=''> ".$child_dir."</td>";
                    }

                    $html .= "<td>".date("Y-m-d H:i:s",filemtime($dir.'/'.$child_dir))."</td>";
                    $html .= "<td>".substr(base_convert(fileperms($dir.'/'.$child_dir), 10, 8), -3)."</td>";
                    $html .= "<td></td>";

                    $html .= "</tr>";*/
                    $tmp['path'] =$sel_dir."/";
                    $tmp['name'] =$child_dir;
                    if(is_dir($dir.'/'.$child_dir)){
                        $tmp['filename']= "<div class='getfile' data-path='".$sel_dir."/".$child_dir."'><img src='/static/images/filedir.png' alt=''> ".$child_dir."</div>";
                        $tmp['operator']= '<div class="opermt"><span class="edname" data-filename="'.$child_dir.'" data-filepath="'.$this->sel_dir.'/">重命名</span> | <span class="deldirfunc" data-filename="'.$child_dir.'" data-filepath="'.$this->sel_dir.'/">删除</span></div>';

                    }else{
                        $tmp['size']= round(filesize($dir.'/'.$child_dir)/1024,2)."KB";

                        if(isImg($dir.'/'.$child_dir)){
                            $tmp['filename']= "<img width='32' onclick='previewImg(this)' src='/root/".$this->sel_dir.'/'.$child_dir."' alt=''> ".$child_dir;
                        }else{
                            $tmp['filename']= "<img src='/static/images/fileico.png' alt=''> ".$child_dir;
                        }
                        $tmp['operator']= '<div class="opermt"><span class="edname" data-filename="'.$child_dir.'" data-filepath="'.$this->sel_dir.'/">重命名</span> | <a target="_blank" href="'.url('download')."?_m=".base64_encode(urlencode($dir.'/'.$child_dir)).'">下载</a> | <span class="deldirfunc" data-filename="'.$child_dir.'" data-filepath="'.$this->sel_dir.'/">删除</span></div>';

                    }

                    $tmp['updatetime']= date("Y-m-d H:i:s",filemtime($dir.'/'.$child_dir));
                    $tmp['role']= substr(base_convert(fileperms($dir.'/'.$child_dir), 10, 8), -3);

                    $html[] = $tmp;
                }
            }
		    return $html;
	    }else{
            return '';
        }
    }
}
