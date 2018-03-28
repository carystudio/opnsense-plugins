<?php
//获取到临时文件
$file=$_FILES['file'];
//获取文件名
$fileName=$file['name'];

$result = array("result"=>"Success","msg"=>"success");

$uploaddir = "/var/upload";
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
    chmod($uploaddir, 0777);
}
$gz="/var/upload/csgateway.tar.gz";
if(file_exists($gz)) {
    unlink($gz);
}
try {
//移动文件到当前目录
    if ( move_uploaded_file($file['tmp_name'], $gz)) {
        //解压
        exec("tar -xzvf ".$gz." -C /var/upload");
//        $handle = popen("tar -xzvf csgateway.tar.gz -C /var/upload", 'r');
//        pclose($handle);
        //还原数据库
        if(file_exists("/var/upload/data/csgateway.sql")){
            $user = "root";
            $dbname = "csgateway";
            $exec = "mysql -u" . $user . " " . $dbname . " </var/upload/data/csgateway.sql";
            exec($exec);
            if(file_exists($gz)){
                unlink($gz);
            }
            if(is_dir("/var/upload/data")){
                deldir("/var/upload/data");
            }

        }else{
            $result['result'] = "Fail";
            $result['msg'] = "文件更新失败";
        }
    } else {
        $result['result'] = "Fail";
        $result['msg'] = "文件上传失败";
    }
}catch (Exception $ex) {
    $result['result'] = "Fail";
    $result['msg']  = $aex->getMessage();
}
echo json_encode($result);

//删除整个文件
function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}