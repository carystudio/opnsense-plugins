<?php
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");

class Crps extends Csbackend
{
    const URL_FILE = '/usr/local/opnsense/cs/tmp/crpc_url';

    protected static $ERRORCODE = array(
        'DDNS_100'=>'开启参数不正确'
    );

    public static function getCrpcUrl(){
        $url = '';
        if(file_exists(Crps::URL_FILE)){
            $url = @file_get_contents(Crps::URL_FILE);
        }
        if(empty($url)){
            return 1;
        }else{
            $url = trim($url);//删除空格
            $url = str_replace(chr(13), '', $url);//删除回车
            $url = str_replace(chr(10), '', $url);//删除换行
            $url = 'http://www.carystudio.com/router/wechatmanage/routerurl?url='.urlencode($url);
            return array('url'=>$url);
        }

    }
}