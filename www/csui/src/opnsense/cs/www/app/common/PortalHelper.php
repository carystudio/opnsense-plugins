<?php

require_once ('util.inc');
require_once("interfaces.inc");

class PortalHelper
{
    public static $PORTAL_SERVER = 'totolink.carystudio.com';
    public static $PORTAL_SERVER_PORT = 80;
    public static $CUSTOM_TPL_DIR = '/usr/local/opnsense/cs/conf/captiveportal/login_page/custom';

    private static $db = false;

    public static function getDbConn(){
        if(false === self::$db){
            if(file_exists('/var/captiveportal/captiveportal.sqlite')){
                self::$db = new SQLite3('/var/captiveportal/captiveportal.sqlite');
            }else{
                if(!is_dir('/var/captiveportal')){
                    mkdir('/var/captiveportal');
                }
                $res = exec_command('/usr/local/bin/sqlite3 /var/captiveportal/captiveportal.sqlite -init /usr/local/opnsense/scripts/OPNsense/CaptivePortal/sql/init.sql ".quit"');
                self::$db = new SQLite3('/var/captiveportal/captiveportal.sqlite');
            }

        }

        return self::$db;
    }

    public static function getConf(){
        $gatewayid = file_get_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt');
        $mcode = file_get_contents('/usr/local/opnsense/cs/conf/mcode.txt');
        $url = 'http://'.self::$PORTAL_SERVER.':'.self::$PORTAL_SERVER_PORT.'/c/api/v3/conf/?gw_id='.$gatewayid.'&mcode='.$mcode.'&type=ini';
        $result = file_get_contents($url);
        if($result){
            file_put_contents('/usr/local/opnsense/cs/tmp/portal_server_config', $result);
            $result = true;
        }

        return $result;
    }

    public static function getCmdForStart(){
        $gatewayid = file_get_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt');
        $mcode = file_get_contents('/usr/local/opnsense/cs/conf/mcode.txt');
        $url = 'http://'.self::$PORTAL_SERVER.':'.self::$PORTAL_SERVER_PORT.'/c/api/v3/cmd/?gw_id='.$gatewayid.'&mcode='.$mcode.'&dev=CSG2000P&ver=-1';
        $result = file_get_contents($url);
        file_put_contents('/tmp/cmdresult.log',$url.'|||'.$result);

        return $result;
    }

}