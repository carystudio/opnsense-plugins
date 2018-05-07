<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");

use \OPNsense\TrafficShaper\TrafficShaper;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Backend;
use \OPNsense\Core\Config;
use \OPNsense\Freeradius\User;
use \OPNsense\Base\ApiMutableModelControllerBase;

class Freeradius extends Csbackend
{
    protected static $ERRORCODE = array(
        'RADIUS_100'=>'开启参数不正确'
    );

    const default_eap_type = array("md5","mschapv2","peap","ttls");

    public static function getGeneralStatus(){
        global $config;

        $freeRadiusConf = array();
        if(isset($config['OPNsense']['freeradius']['general'])){
            $freeRadiusConf = $config['OPNsense']['freeradius']['general'];
            unset($freeRadiusConf['@attributes']);
        }
        $freeRadiusConf['default_eap_type'] = $config['OPNsense']['freeradius']['eap']['default_eap_type'];
        return $freeRadiusConf;
    }

    public static function setGeneral($data){
        global $config;

        $result = 0;
        try{
            $general = array();
            if(!isset($data['enabled'])){
                throw new AppException("RADIUS_100");
            }
            $general['enabled'] = $data['enabled'];
            if(!isset($data['vlanassign'])){
                throw new AppException("RADIUS_101");   //VLAN参数不正确
            }
            $general['vlanassign'] = $data['vlanassign'];
            if(!isset($data['wispr'])){
                throw new AppException("RADIUS_102");   //WISPr参数不正确
            }
            $general['wispr'] = $data['wispr'];
            if(!isset($data['chillispot'])){
                throw new AppException("RADIUS_103");   //ChilliSpot参数不正确
            }
            $general['chillispot'] = $data['chillispot'];
            if(!isset($data['sessionlimit'])){
                throw new AppException("RADIUS_104");   //sessionlimit参数不正确
            }
            $general['sessionlimit'] = $data['sessionlimit'];
            if(!isset($data['log_destination']) && !in_array($data['log_destination'],array("file","syslog"))){
                throw new AppException("RADIUS_105");   //log_destination参数不正确
            }
            $general['log_destination'] = $data['log_destination'];
            if(!isset($data['log_authentication_request'])){
                throw new AppException("RADIUS_106");   //log_authentication_request参数不正确
            }
            $general['log_authentication_request'] = $data['log_authentication_request'];
            if(!isset($data['log_authbadpass'])){
                throw new AppException("RADIUS_107");   //Log Authentication Bad Password参数不正确
            }
            $general['log_authbadpass'] = $data['log_authbadpass'];
            if(!isset($data['log_authgoodpass'])){
                throw new AppException("RADIUS_108");   //Log Authentication Good Password参数不正确
            }
            $general['log_authgoodpass'] = $data['log_authgoodpass'];

            if(!isset($data['default_eap_type']) && !in_array($data['default_eap_type'],Freeradius::default_eap_type)){
                throw new AppException("RADIUS_109");   //default_eap_type参数不正确
            }
            $config['OPNsense']['freeradius']['eap']['default_eap_type'] = $data['default_eap_type'];

            if(isset($config['OPNsense']['freeradius']['general'])){
                foreach ($config['OPNsense']['freeradius']['general'] as $key=>$val){
                    foreach ($general as $k=>$v){
                        if($k == $key){
                            $config['OPNsense']['freeradius']['general'][$key] = $v;
                        }
                    }
                }
            }
            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getFrUser(){
        global $config;

        $freeRadiusUser = array();
        if(isset($config['OPNsense']['freeradius']['user'])){
            $users = $config['OPNsense']['freeradius']['user'][0];
            foreach ($users as $key=>$val){
                if('users' == $key){
                    if(isset($val['user'])){
                        $freeRadiusUser = $val['user'];
                    }
                }
            }
        }
        foreach ($freeRadiusUser as $key=>$val){
            foreach ($val as $k=>$v){
                if('@attributes' == $k){
                    $freeRadiusUser[$key]['uuid'] = $v['uuid'];
                    unset($freeRadiusUser[$key][$k]);
                }
            }
        }
        return $freeRadiusUser;
    }

    public static function setFrUser($data){
        global $config;

        $result = 0;
        try{
            $userInfo = array();
            if(!isset($data['enabled'])){
                throw new AppException("RADIUS_200");
            }
            $userInfo['enabled'] = $data['enabled'];
            if(!isset($data['username']) || '' == trim($data['username'])){
                throw new AppException("RADIUS_201");
            }
            $userInfo['username'] = $data['username'];
            if(!isset($data['password']) || '' == trim($data['password']) ){
                throw new AppException("RADIUS_202");
            }
            $userInfo['password'] = $data['password'];
            if(!isset($data['description'])){
                throw new AppException("RADIUS_203");
            }
            $userInfo['description'] = $data['description'];
            if(!isset($data['ip']) || ('' != $data['ip'] && !is_ipaddr($data['ip'])) ){
                throw new AppException("RADIUS_204");
            }
            $userInfo['ip'] = $data['ip'];
            if(!isset($data['subnet'])){
                throw new AppException("RADIUS_205");
            }
            if('' != $data['subnet']){
                $netmask = Util::maskip2bit($data['subnet']);
                if (false === $netmask) {
                    throw new AppException('RADIUS_205');
                }
            }
            $userInfo['subnet'] = $data['subnet'];

            if(!isset($data['vlan']) || ('' != $data['vlan'] && !is_numeric($data['vlan']))){
                throw new AppException("RADIUS_206");
            }
            if('' != $data['vlan']){
                $vlan = intval($data['vlan']);
                if($vlan < 1 || $vlan > 4096){
                    throw new AppException("RADIUS_206");
                }
            }
            $userInfo['vlan'] = $data['vlan'];
            if(!isset($data['wispr_bw_min_up']) || ('' != $data['wispr_bw_min_up'] && !is_numeric($data['wispr_bw_min_up']))){
                throw new AppException("RADIUS_207");
            }
            $userInfo['wispr_bw_min_up'] = $data['wispr_bw_min_up'];
            if(!isset($data['wispr_bw_max_up']) || ('' != $data['wispr_bw_max_up'] && !is_numeric($data['wispr_bw_max_up'])) ){
                throw new AppException("RADIUS_208");
            }
            $userInfo['wispr_bw_max_up'] = $data['wispr_bw_max_up'];
            if(!isset($data['wispr_bw_min_down']) || ('' != $data['wispr_bw_min_down'] && !is_numeric($data['wispr_bw_min_down'])) ){
                throw new AppException("RADIUS_209");
            }
            $userInfo['wispr_bw_min_down'] = $data['wispr_bw_min_down'];
            if(!isset($data['wispr_bw_max_down']) || ('' != $data['wispr_bw_max_down'] && !is_numeric($data['wispr_bw_max_down'])) ){
                throw new AppException("RADIUS_210");
            }
            $userInfo['wispr_bw_max_down'] = $data['wispr_bw_max_down'];
            if(!isset($data['chillispot_bw_max_up']) || ('' != $data['chillispot_bw_max_up'] && !is_numeric($data['chillispot_bw_max_up'])) ){
                throw new AppException("RADIUS_211");
            }
            $userInfo['chillispot_bw_max_up'] = $data['chillispot_bw_max_up'];
            if(!isset($data['chillispot_bw_max_down']) || ('' != $data['chillispot_bw_max_down'] && !is_numeric($data['chillispot_bw_max_down']))){
                throw new AppException("RADIUS_212");
            }
            $userInfo['chillispot_bw_max_down'] = $data['chillispot_bw_max_down'];
            if(!isset($data['sessionlimit_max_session_limit']) || ('' != $data['sessionlimit_max_session_limit'] && !is_numeric($data['sessionlimit_max_session_limit'])) ){
                throw new AppException("RADIUS_213");
            }
            $userInfo['sessionlimit_max_session_limit'] = $data['sessionlimit_max_session_limit'];


            if(isset($data['uuid']) && '' != $data['uuid']){    //updata user
                $frUser = $config['OPNsense']['freeradius']['user'][0]['users'];
                if(!$frUser){
                    throw new AppException("RADIUS_214");   //用户不存在
                }
                $updataFlag = false;
                foreach ($frUser['user'] as $key=>$val){
                    if($val["@attributes"]['uuid'] != $data['uuid']){
                        continue;
                    }
                    foreach ($userInfo as $k=>$v){
                        if(array_key_exists($k,$val)){
                            $config['OPNsense']['freeradius']['user'][0]['users']['user'][$key][$k] = $v;
                            $updataFlag = true;
                        }
                    }
                    if($updataFlag){
                        break;
                    }
                }
                if(!$updataFlag){
                    throw new AppException("RADIUS_214");   //用户不存在
                }
            }else{  //add user
                $frUser = $config['OPNsense']['freeradius']['user'][0]['users'];
                $uuid = self::getUuid();
                $userInfo['@attributes']['uuid'] = $uuid;
                if($frUser){
                    foreach ($frUser['user'] as $key=>$val){
                        foreach ($val as $k=>$v){
                            if('username' == $k){
                                if($v == $userInfo['username']){
                                    throw new AppException("RADIUS_215");   //用户已存在
                                }
                            }
                        }
                    }
                    array_push($frUser['user'],$userInfo);
                }else{
                    $frUser = array("user"=>array($userInfo));
                }
                $config['OPNsense']['freeradius']['user'][0]['users'] = $frUser;
            }
            write_config();

        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delFrUser($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['uuid'])){
                throw new AppException("RADIUS_300");
            }

            $frUser = $config['OPNsense']['freeradius']['user'][0]['users'];
            if(!$frUser){
                throw new AppException("RADIUS_301");   //用户不存在
            }

            foreach ($frUser['user'] as $key=>$val){
                if($val["@attributes"]['uuid'] == $data['uuid']){
                    unset($config['OPNsense']['freeradius']['user'][0]['users']['user'][$key]);
                }
            }


            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;

    }

    public static function getUuid(){
        $uuid1 = self::getRandStr(8,3);
        $uuid2 = self::getRandStr(4,3);
        $uuid3 = self::getRandStr(4,3);
        $uuid4 = self::getRandStr(4,3);
        $uuid5 = self::getRandStr(12,3);
        $uuid = $uuid1.'-'.$uuid2.'-'.$uuid3.'-'.$uuid4.'-'.$uuid5;
        return $uuid;
    }

    //mode 0:数字和字母 1:数字 2:字母
    public static function getRandStr($strLen,$mode=0){
        $mode = intval($mode);
        if(0>$mode || 3<$mode){
            $mode = 0;
        }
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));

        $number = '';
        $number_len = $strLen;
        if($mode==1){//数字
            $stuff = '1234567890';
        }elseif($mode==2){//字母
            $stuff = 'abcdefghijklmnopqrstuvwxyz';
        }else if($mode==3){
            $stuff = '1234567890abcdef';
        }else{
            $stuff = '1234567890abcdefghijklmnopqrstuvwxyz';//附加码显示范围ABCDEFGHIJKLMNOPQRSTUVWXYZ
        }
        $stuff_len = strlen($stuff) - 1;
        for ($i = 0; $i < $number_len; $i++) {
            $number .= substr($stuff, mt_rand(0, $stuff_len), 1);
        }

        return $number;
    }

    public static function getClientCfg(){
        global $config;

        $frClient = array();
        $client = $config['OPNsense']['freeradius']['client']['clients'];
        $cuntFlag = false;
        if(isset($client) && '' != $client){
            foreach ($client['client'] as $key=>$val){
                if(is_numeric($key)){  //多条数据
                    $cuntFlag = true;
                    foreach ($val as $k=>$v){
                        $client['client'][$key]['uuid'] = $val['@attributes']['uuid'];
                        if('@attributes' == $k){
                            unset($client['client'][$key]['@attributes']);
                        }
                    }
                }else{   //一条数据
                    if('@attributes' == $key){
                        $client['client']['uuid'] = $client['client']['@attributes']['uuid'];
                        unset($client['client']['@attributes']);
                        break;
                    }
                }
            }

            if($cuntFlag){
                $frClient = $client['client'];
            }else{
                $frClient = array($client['client']);
            }
        }

        return $frClient;
    }

    public static function delClientCfg($data){
        global $config;

        $result = 0;
        try{
            if(isset($data['uuid']) && '' == $data['uuid']){
                throw new AppException("RADIUS_400");   //参数不正确
            }
            $client = $config['OPNsense']['freeradius']['client']['clients'];
            if(!isset($client) && '' == $client){
                throw new AppException("RADIUS_401");   //用户不存在
            }
            $delFlag = false;
            foreach ($client['client'] as $key=>$val){
                if(is_numeric($key)){  //多条数据
                    foreach ($val as $k=>$v){
                        if($val['@attributes']['uuid'] == $data['uuid']){
                            unset($config['OPNsense']['freeradius']['client']['clients']['client'][$key]);
                            $delFlag = true;
                            break;
                        }
                    }
                }else{   //一条数据
                    if($client['client']){
                        if($client['client']['@attributes']['uuid'] == $data['uuid']){
                            $config['OPNsense']['freeradius']['client']['clients'] = '';
                            $delFlag = true;
                            break;
                        }
                    }
                }
            }
            if(!$delFlag){
                throw new AppException("RADIUS_401");   //用户不存在
            }
            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function setClient($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['enabled'])){
                throw new AppException("RADIUS_500");   //开启参数不正确
            }
            if(!isset($data['name'])){
                throw new AppException("RADIUS_502");   //名称参数不正确
            }
            if(!isset($data['secret'])){
                throw new AppException("RADIUS_503");   //安全参数不正确
            }
            if(isset($data['ip']) ){
                if(('' != $data['ip'] && !is_ipaddr($data['ip']))){
                    $ip = explode("/",$data['ip']);
                    if(!is_ipaddr($ip[0]) || !is_numeric($ip[1])){
                        throw new AppException("RADIUS_501");   //IP地址参数不正确
                    }
                }
            }
            $editFlag = false;
            if(isset($data['uuid']) && '' != $data['uuid']){
                $editFlag = true;
            }
            $client = $config['OPNsense']['freeradius']['client']['clients'];
            if(isset($client) && '' != $client){

                $uuid = self::getUuid();
                $addClient = array();
                $addClient['@attributes']['uuid'] = $uuid;
                $addClient['enabled'] = intval($data['enabled']);
                $addClient['name'] = $data['name'];
                $addClient['secret'] = $data['secret'];
                $addClient['ip'] = $data['ip'];

                foreach ($client['client'] as $key=>$val){
                    if(is_numeric($key)){  //多条数据
                        foreach ($val as $k=>$v){
                            if($editFlag){  //更新数据
                                if($val['@attributes']['uuid'] == $data['uuid']){
                                    $client['client'][$key]['enabled'] = intval($data['enabled']);
//                                    $client['client'][$key]['name'] = $data['name'];
                                    $client['client'][$key]['secret'] = $data['secret'];
                                    $client['client'][$key]['ip'] = $data['ip'];
                                }
                                break;
                            }
                            if($val['name'] == $data['name'] && !$editFlag){
                                throw new AppException("RADIUS_504");   //用户名已存在
                            }
                        }
                    }else{   //一条数据
                        if($editFlag){  //更新数据
                            if($client['client']['@attributes']['uuid'] == $data['uuid']){
                                $client['client']['enabled'] = intval($data['enabled']);
//                                    $client['client']['name'] = $data['name'];
                                $client['client']['secret'] = $data['secret'];
                                $client['client']['ip'] = $data['ip'];
                            }
                        }else{
                            if($client['client']['name'] == $data['name']){
                                throw new AppException("RADIUS_504");   //用户名已存在
                            }
                            $clientTmp = array("client"=>array($client['client']));
                            $client = $clientTmp;
                        }
                        break;
                    }
                }
                if(!$editFlag){
                    $uuid = self::getUuid();
                    $addClient = array();
                    $addClient['@attributes']['uuid'] = $uuid;
                    $addClient['enabled'] = intval($data['enabled']);
                    $addClient['name'] = $data['name'];
                    $addClient['secret'] = $data['secret'];
                    $addClient['ip'] = $data['ip'];

                    if($client['client']){
                        array_push($client['client'],$addClient);
                    }
                }
            }else{
                $uuid = self::getUuid();
                $addClient = array();
                $addClient['@attributes']['uuid'] = $uuid;
                $addClient['enabled'] = intval($data['enabled']);
                $addClient['name'] = $data['name'];
                $addClient['secret'] = $data['secret'];
                $addClient['ip'] = $data['ip'];
                $client = array("client"=>$addClient);
            }
            $config['OPNsense']['freeradius']['client']['clients'] = $client;
            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }
}

