<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");

use \OPNsense\Core\Backend;
use \OPNsense\CaptivePortal\CaptivePortal;
use \Phalcon\Db\Adapter\Pdo\Sqlite;
use \Phalcon\Logger\Adapter\Syslog;

class Firmware extends Csbackend
{
    protected static $ERRORCODE = array(
        'Firmware_100'=>'版本不正确',
        'Firmware_101'=>'上传的固件信息不存在'
    );

    private static $db=false;

    private static function getDbConn(){
        if(false === self::$db){
            if(file_exists('/var/db/pkg/local.sqlite')){
                self::$db = new SQLite3('/var/db/pkg/local.sqlite');
            }
        }

        return self::$db;
    }

    public static function getFirmwareInfo($cache=false)
    {
        if(false == $cache){
//            echo 'exec script';
            exec('/bin/sh /usr/local/opnsense/cs/script/extract_upgrade_file.sh');
        }

	    $nowres = parse_ini_file('/usr/local/opnsense/cs/www/app/config/config.ini',true);
	    if(file_exists('/usr/local/opnsense/cs/tmp/upgrade_config.ini')){
		    $firmwareres = parse_ini_file('/usr/local/opnsense/cs/tmp/upgrade_config.ini',true);
	    }else{
		    $firmwareres = array('csg2000p'=>array('version'=>'','build_date'=>''));
	    }
	    $res = array('Version'=>$nowres['csg2000p']['version'],
            'Build_date'=>$nowres['csg2000p']['build_date'],
            'Fw_version'=>$firmwareres['csg2000p']['version'],'Fw_build_date'=>$firmwareres['csg2000p']['build_date']);

        return $res;
    }

    public static function upgradeFirmware($data){
        $result = 0;
	    $info = self::getFirmwareInfo(true);
	    try{
            if($data['Fw_version']!=$info['Fw_version']){
                throw new AppException('Firmware_100');
            }
            $db = self::getDbConn();
            $res = $db->query("select name,version from packages");
            $gw_packages_ver = array();
            while($package = $res->fetchArray(SQLITE3_ASSOC)){
                $gw_packages_ver[$package['name']] = $package['version'];
            }
            if(file_exists('/upgrade_tmp/files/packages/+COMPACT_MANIFEST')){
                $text = file_get_contents('/upgrade_tmp/files/packages/+COMPACT_MANIFEST');
                $firmware_packages_ver = json_decode($text,true);
            }else{
                throw new AppException('Firmware_101');
            }
            if(file_exists('/upgrade_tmp/files/packages/packagesite.yaml')){
                $f = fopen('/upgrade_tmp/files/packages/packagesite.yaml', 'r');
                while($str = fgets($f)){
                    $p = json_decode($str, true);
                    if(in_array($p['name'], array('os-pptp','os-l2tp','os-pppoe','os-csui'))){
                        $firmware_packages_ver['deps'][$p['name']] = array('version'=>$p['version']);
                    }
                }
            }else{
                throw new AppException('Firmware_101');
            }
            $to_install_packages = array();
            foreach($firmware_packages_ver['deps'] as $package_name=>$package_info){
                if(isset($gw_packages_ver[$package_name])){
                    if($gw_packages_ver[$package_name] != $package_info['version']){
                        $to_install_packages[]=$package_name;
                    }
                }else{
                    $to_install_packages[]=$package_name;
                }
            }
            $tmp=implode("\n",$to_install_packages);
            if(strlen($tmp)>0){
                $tmp = $tmp."\n";
            }
            file_put_contents('/upgrade_tmp/files/packages/upgrade_package.tmp', $tmp);
            mwexec_bg('/bin/sh /usr/local/opnsense/cs/script/upgrade_firmware.sh');
	    }catch(AppException $aex){
            $result = $aex->getMessage();
	    }catch(Exception $ex){
            $result = 100;
	    }

        return $result;
    }



}


