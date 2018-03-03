<?php
//获取到临时文件
$file=$_FILES['file'];
//获取文件名
$fileName=$file['name'];
$gz="csgateway.tar.gz ";
if(file_exists($gz)) {
    unlink($gz);
}
//移动文件到当前目录
if(move_uploaded_file($file['tmp_name'],$fileName)){
    $uploaddir="/var/upload";
    if(is_dir($uploaddir)) {
        deldir($uploaddir);
    }
    mkdir($uploaddir);
    chmod($uploaddir, 0777);
    //解压
    $handle = popen("tar -xzvf csgateway.tar.gz -C /var/upload", 'r');
    pclose($handle);
    //还原数据库
    $user="root";
    $dbname ="csgateway";
    $exec="mysql -u".$user." ".$dbname." </var/upload/data/csgateway.sql";
    exec($exec);
    echo "更新数据库文件成功\n";
} else {
    echo "文件上传失败\n";
}

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