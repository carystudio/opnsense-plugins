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

class Portal extends Csbackend
{
    protected static $ERRORCODE = array(
        'Portal_100'=>'开启参数不正确',
        'Portal_101'=>'服务类型不正确',
        'Portal_101'=>'服务类型不正确',
        'Portal_102'=>'空闲断开时间不正确（1-300）',
        'Portal_103'=>'登录页面上传失败',
        'Portal_104'=>'IP白名单不正确',
        'Portal_105'=>'MAC白名单不正确',
        'Portal_106'=>'热点ID不能为空',
        'Portal_200'=>'密码不正确（6-15）',
        'Portal_201'=>'登录有效截止时间不正确',
        'Portal_202'=>'可登录时间不正确',
        'Portal_203'=>'可重复登录参数不正确',
        'Portal_204'=>'用户已存在',
        'Portal_205'=>'创建用户失败',
        'Portal_300'=>'用户不存在',
        'Portal_301'=>'删除用户失败',
        'Portal_302'=>'微信用户不能删除',
        'Portal_400'=>'用户不存在',
        'Portal_401'=>'更新用户失败',
        'Portal_500'=>'登录用户不存在',
        'Portal_501'=>'下线失败'
    );

    private static function getLogger($ident = "api")
    {
        $logger = new Syslog($ident, array(
            'option' => LOG_PID,
            'facility' => LOG_LOCAL4
        ));

        return $logger;
    }

    public static function reconfigureAction(){
        $backend = new Backend();
        // the ipfw rules need to know about all the zones, so we need to reload ipfw for the portal to work
        $backend->configdRun('template reload OPNsense/IPFW');
        $bckresult = trim($backend->configdRun("ipfw reload"));
        if ($bckresult == "OK") {
            // generate captive portal config
            $bckresult = trim($backend->configdRun('template reload OPNsense/Captiveportal'));
            if ($bckresult == "OK") {
                $mdlCP = new CaptivePortal();
                if ($mdlCP->isEnabled()) {
                    $bckresult = trim($backend->configdRun("captiveportal restart"));
                    if ($bckresult == "OK") {
                        $status = "ok";
                    } else {
                        $status = "error reloading captive portal:".$bckresult;
                    }
                } else {
                    $backend->configdRun("captiveportal stop");
                    $status = "ok";
                }
            } else {
                $status = "error reloading captive portal template";
            }
        } else {
            $status = "error reloading captive portal rules (" . $bckresult . ")";
        }

        return $status;
    }

    private static function getPortal(){
        global $config;

        if(isset($config['OPNsense']['captiveportal']['zones']['zone']['@attributes']['uuid']) &&
            '154b5da3-b1d2-48e8-86d6-1f055cdb8efa'==$config['OPNsense']['captiveportal']['zones']['zone']['@attributes']['uuid']){
            return $config['OPNsense']['captiveportal']['zones']['zone'];
        }else{
            $portal = array();
            $portal['@attributes'] = array('version'=>'1.0.0');
            $zone = array('@attributes'=>array('uuid'=>'154b5da3-b1d2-48e8-86d6-1f055cdb8efa'));
            $zone['enabled'] = '0';
            $zone['zoneid'] = 0;
            $zone['interfaces'] = 'lan';
            $zone['authservers'] = 'Local Database';
            $zone['idletimeout'] = 10;
            $zone['hardtimeout'] = 600;
            $zone['concurrentlogins'] = 1;
            $zone['certificate'] = '592fb0cf2dd36';
            $zone['servername'] = '';
            $zone['allowedAddresses'] = '';
            $zone['allowedMACAddresses'] = '';
            $zone['transparentHTTPProxy'] = 0;
            $zone['transparentHTTPSProxy'] = 0;
            $zone['template'] = '';
            $zone['description'] = 'GatewayID';
            $zone['type'] = 'local';
            $portal['zones'] = array('zone'=>$zone);
            $config['OPNsense']['captiveportal'] = $portal;
            write_config();

            return $portal['zones']['zones'];
        }
    }

    public static function getPortalStatus(){
        global $config;

        $portal = self::getPortal();
        $portal_status = array();
        $portal_status['Enable'] = $portal['enabled'];
        $portal_status['Type'] = $portal['type'];
        if('local'==$portal_status['Type']){
            $portal_status['IdleTimeout'] = $portal['idletimeout'];
            $portal_status['SessionTimeout'] = $portal['hardtimeout'];
            $portal_status['AllowedIp'] = $portal['allowedAddresses'];
            $portal_status['AllowedMac'] = $portal['allowedMACAddresses'];
            $portal_status['attention'] = $portal['attention'];
            if(is_dir(PortalHelper::$CUSTOM_TPL_DIR)){
                $portal_status['LoginPageStatus'] = '1';
            }else{
                $portal_status['LoginPageStatus'] = '0';
            }
            $portal_status['wc_enable'] = isset($portal['wc_enable'])?'yes'==$portal['wc_enable']?'yes':'no':'no';
            $portal_status['appId'] = $portal['appId'];
            $portal_status['shop_id'] = $portal['shop_id'];
            $portal_status['ssid'] = $portal['ssid'];
            $portal_status['secretkey'] = $portal['secretkey'];
        }else{
            $portal_status['GatewayId'] = $portal['description'];
            $portal_status['LoginPageStatus'] = '0';
        }

        if(is_array($portal_status)){
            $portal_status['Mcode'] = file_get_contents('/usr/local/opnsense/cs/tmp/mcode.txt');
        }

        return $portal_status;
    }

    public function updatePortalLanAlias($portal){
        global $config;

        $ifname = '';
        $ifinfo = false;
        foreach($config['interfaces'] as $ifname_tmp=>$ifinfo_tmp){
            if($ifname_tmp == $portal['interfaces']){
                $ifname = $ifname_tmp;
                $ifinfo = $ifinfo_tmp;
                break;
            }
        }
        if(!$ifinfo){
            return false;
        }
        $alias_name = strtoupper($ifinfo['descr']).'_TO_MULTIWAN';
        foreach($config['aliases']['alias'] as $idx=>$alias){
            if($alias['name']==$alias_name && $alias['descr']==$alias_name){
                unset($config['aliases']['alias'][$idx]);
            }
        }
        $address = $ifinfo['ipaddr'].'/'.$ifinfo['subnet'];
        if(isset($config['OPNsense']['captiveportal']['zones']['zone']['enabled']) &&
            '1'==$config['OPNsense']['captiveportal']['zones']['zone']['enabled']){
            $address = $ifinfo['ipaddr'].'/32';
        }
        $alias = array(
            'name'=>$alias_name,
            'type'=>'network',
            'descr'=>$alias_name,
            'address'=> $address,
            'detail'=>'Entry added '.date("Y-m-d H:i:s")
        );
        $config['aliases']['alias'][] = $alias;
    }

    private static function setDnsStrict(){
        global $config;

        $natrule = array(
            'disabled'=>'1',
            'protocol'=>'tcp/udp',
            'interface'=>'lan',
            'ipprotocol'=>'inet',
            'descr'=>'PORTAL_STRICT_DNS_TO_GW',
            'tag'=>'',
            'tagged'=>'',
            'poolopts'=>'',
            'associated-rule-id'=>'pass',
            'target'=>'127.0.0.1',
            'local-port'=>'53',
            'source'=>array('any'=>'1'),
            'destination'=>array('any'=>'1','port'=>'53'),
            'natreflection'=>'disable'
        );
        if('1'==$config['OPNsense']['captiveportal']['zones']['zone']['enabled']){
            unset($natrule['disabled']);
        }
        if(isset($config['nat']['rule']) && is_array($config['nat']['rule'])){
            foreach($config['nat']['rule'] as $idx=>$rule){
                if('PORTAL_STRICT_DNS_TO_GW'==$rule['descr']){
                    unset($config['nat']['rule'][$idx]);
                }
            }
        }else{
            $config['nat']['rule'] = array();
        }
        $config['nat']['rule'][] = $natrule;

    }

    public static function setPortalStatus($data){
        global $config;
        $result = 0;
        try{
            if(!isset($data['Enable']) || ('1'!=$data['Enable'] && '0'!=$data['Enable'])){
                throw new AppException('Portal_100');
            }

            $portal = self::getPortal();
            $portal['enabled'] = $data['Enable'];
            if('1'==$portal['enabled'] && (!isset($data['Type']) || ('local'!=$data['Type'] && 'server'!=$data['Type']))){
                throw new AppException('Portal_101');
            }
            if('1'==$portal['enabled'] && 'local'==$data['Type']){
                $idletimeout = isset($data['IdleTimeout'])?intval($data['IdleTimeout']):0;
                if($idletimeout<1 || $idletimeout>300){
                    throw new AppException('Portal_102');
                }
                if('1'==$data['LoginPageStatus']){
                    if(!is_dir(PortalHelper::$CUSTOM_TPL_DIR)){
                        throw new AppException('Portal_103');
                    }
                }else{
                    exec('/bin/rm -rf '.PortalHelper::$CUSTOM_TPL_DIR);
                }
                $sessiontimeout = isset($data['SessionTimeout'])?intval($data['SessionTimeout']):0;
                $allowed_ips = array();
                $allowed_macs = array();
                if(isset($data['AllowedIp']) && strlen(trim($data['AllowedIp']))>0){
                    $allowed_ips = explode(',', trim($data['AllowedIp']));
                    foreach($allowed_ips as $ip){
                        if(!is_ipaddr($ip)){
                            throw new AppException('Portal_104');
                        }
                    }
                }
                if(isset($data['AllowedMac']) && strlen(trim($data['AllowedMac']))>0){
                    $allowed_macs = explode(',', trim($data['AllowedMac']));
                    foreach($allowed_macs as $idx=>$mac){
                        $allowed_macs[$idx] = strtolower($mac);
                        if(!is_macaddr($allowed_macs[$idx])){
                            throw new AppException('Portal_105');
                        }
                    }
                }
                if(isset($data['wc_enable']) && 'yes' == $data['wc_enable']){
                    if(strlen(trim($data['appId']))<=0){
                        throw new AppException('appId不能为空');
                    }
                    if(strlen(trim($data['shop_id']))<=0){
                        throw new AppException('shop_id不能为空');
                    }
                    if(strlen(trim($data['ssid']))<=0){
                        throw new AppException('ssid不能为空');
                    }
                    if(strlen(trim($data['secretkey']))<=0){
                        throw new AppException('secretkey不能为空');
                    }
                    $portal['wc_enable'] = 'yes';
                    $portal['appId'] = trim($data['appId']);
                    $portal['shop_id'] = trim($data['shop_id']);
                    $portal['ssid'] = trim($data['ssid']);
                    $portal['secretkey'] = trim($data['secretkey']);
                    if ($data['attention'] ==  "0"){
                        $portal['attention'] = "yes";
                    }else{
                        $portal['attention'] =  "no";
                    }
                }else{
                    $portal['wc_enable'] = 'no';
                }

                
                $portal['authservers'] = 'Portal Local';
                $portal['idletimeout'] = $idletimeout;
                $portal['hardtimeout'] = $sessiontimeout;
                $portal['type'] = 'local';
                $portal['allowedAddresses'] = implode(',', $allowed_ips);
                $portal['allowedMACAddresses'] = implode(',', $allowed_macs);
                file_put_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_loginpage.txt','/index.html');
                if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt')){
                    unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt');
                }
                if(file_exists('/usr/local/opnsense/cs/tmp/portal_gatewayid')){
                    unlink('/usr/local/opnsense/cs/tmp/portal_gatewayid');
                }
                if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_server.txt')){
                    unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_server.txt');
                }
                if(file_exists('/usr/local/opnsense/cs/tmp/portal_server')){
                    unlink('/usr/local/opnsense/cs/tmp/portal_server');
                }
                if('yes' == $portal['wc_enable']){
                    file_put_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_wc_enable.txt', "1");
                }else{
                    if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_wc_enable.txt')){
                        unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_wc_enable.txt');
                    }
                }
            }else if('1'==$portal['enabled'] && 'server'==$data['Type']){
                if(strlen(trim($data['GatewayId']))<=0){
                    throw new AppException('Portal_106');
                }
                if(is_dir(PortalHelper::$CUSTOM_TPL_DIR)){
                    exec('/bin/rm -rf '.PortalHelper::$CUSTOM_TPL_DIR);
                }
                $portal['authservers'] = 'Portal Center';
                $portal['type'] = 'server';
                $portal['description'] = trim($data['GatewayId']);
                $portal['allowedAddresses'] = '';
                $portal['allowedMACAddresses'] = '';
                $portal['idletimeout'] = 30;
                $portal['hardtimeout'] = 0;
                $loginpage = 'http://totolink.carystudio.com/c/api/v3/login/?gw_id='.$portal['description'].
                    '&gw_address='.$config['interfaces']['lan']['ipaddr'].'&gw_port=8000';
                file_put_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_loginpage.txt',$loginpage);
                file_put_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt',$portal['description']);
                file_put_contents('/usr/local/opnsense/cs/conf/captiveportal/portal_server.txt',PortalHelper::$PORTAL_SERVER);
                unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_wc_enable.txt');
                if(PortalHelper::getConf()){
                    $center_conf = parse_ini_file('/usr/local/opnsense/cs/tmp/portal_server_config');
                    if(is_array($center_conf)){
                        if(isset($center_conf['CheckInterval']) && isset($center_conf['ClientTimeout'])){
                            $portal['idletimeout'] = intval(intval($center_conf['CheckInterval']) * intval($center_conf['ClientTimeout']) / 60)+2;
                        }
                        if(isset($center_conf['TrustedMACList'])){
                            $portal['allowedMACAddresses'] = $center_conf['TrustedMACList'];
                        }
                        if(isset($center_conf['TrustedIPList'])){
                            $portal['allowedAddresses'] = $center_conf['TrustedIPList'];
                        }
                        if(isset($center_conf['TrustedWANHOSTList'])){
                            $portal['allowedWANAddresses'] = $center_conf['TrustedWANHOSTList'];
                        }
                        if(isset($center_conf['wanwhiteset1'])){
                            $portal['wanWhiteSet1'] = $center_conf['wanwhiteset1'];
                        }else{
                            $portal['wanWhiteSet1'] = '';
                        }
                        if(isset($center_conf['wanwhiteset2'])){
                            $portal['wanWhiteSet2'] = $center_conf['wanwhiteset2'];
                        }else{
                            $portal['wanWhiteSet2'] = '';
                        }
                        if(isset($center_conf['wanwhiteset3'])){
                            $portal['wanWhiteSet3'] = $center_conf['wanwhiteset3'];
                        }else{
                            $portal['wanWhiteSet3'] = '';
                        }
                        
                    }
                }
                PortalHelper::getCmdForStart();
            }else{
                if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_loginpage.txt')){
                    unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_loginpage.txt');
                }
                if(file_exists('/usr/local/opnsense/cs/tmp/portal_loginpage')){
                    unlink('/usr/local/opnsense/cs/tmp/portal_loginpage');
                }

                if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt')){
                    unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_gatewayid.txt');
                }
                if(file_exists('/usr/local/opnsense/cs/tmp/portal_gatewayid')){
                    unlink('/usr/local/opnsense/cs/tmp/portal_gatewayid');
                }
                if(file_exists('/usr/local/opnsense/cs/conf/captiveportal/portal_server.txt')){
                    unlink('/usr/local/opnsense/cs/conf/captiveportal/portal_server.txt');
                }
                if(file_exists('/usr/local/opnsense/cs/tmp/portal_server')){
                    unlink('/usr/local/opnsense/cs/tmp/portal_server');
                }
            }
            $config['OPNsense']['captiveportal']['zones']['zone'] = $portal;
            self::updatePortalLanAlias($portal);
            //self::setDnsStrict();

            if('1'==$portal['enabled']){
                $db = PortalHelper::getDbConn();
            }
            write_config();

            $res = self::reconfigureAction();
            filter_configure();
            if('ok'!=$res){
                $result = '1150'.$res;
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function setPortalStatusUp($request){
        global $config;

        if ($_FILES && strlen($_FILES["loginPage"]["error"])>0 && strlen($_FILES["loginPage"]["tmp_name"])>0) {
            if (is_dir(PortalHelper::$CUSTOM_TPL_DIR)){
                exec("/bin/rm -rf ".PortalHelper::$CUSTOM_TPL_DIR);
            }
            @mkdir(PortalHelper::$CUSTOM_TPL_DIR);
            $tmp_name = $_FILES["loginPage"]["tmp_name"];
            $name = $_FILES [ "loginPage" ][ "name" ];
            exec("unzip $tmp_name -d ".PortalHelper::$CUSTOM_TPL_DIR."/");
            if(!file_exists(PortalHelper::$CUSTOM_TPL_DIR.'/index.html')){
                exec('/bin/rm -rf '.PortalHelper::$CUSTOM_TPL_DIR);
                echo 'error';
            }else{
                echo "success";
            }
        }
        exit(0);
    }

    public static function getPortalUsers($data){
        global $config;

        $result = array();
        $users = array();
        $alluser = array();
        $pagesize = 10;
        $pagebegin = ($data['pagenum'] - 1) * $pagesize;
        try{
            $db = PortalHelper::getDbConn();
            $total_result = $db->query('select COUNT(*) from users');
            $totals = $total_result->fetchArray(SQLITE3_ASSOC);
            $total = $totals['COUNT(*)'];
            $pagecnt = ceil($total/$pagesize);
            $res = $db->query('select * from users limit '.$pagebegin.','.$pagesize);
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                $users[] = array('Username'=>$row['username'],
                    'CreateTime'=>date('Y-m-d H:i:s',$row['created']),
                    'ExpireTime'=>date('Y-m-d H:i:s',$row['expire_time']),
                    'RemainTime'=>intval($row['remain_time']/60),
                    'MutilLogins'=>$row['concurrent_logins']);
            }
            $allres = $db->query('select * from users');
            while ($allrow = $allres->fetchArray(SQLITE3_ASSOC)) {
                $alluser[] = array('Username'=>$allrow['username'],
                    'CreateTime'=>date('Y-m-d H:i:s',$allrow['created']),
                    'ExpireTime'=>date('Y-m-d H:i:s',$allrow['expire_time']),
                    'RemainTime'=>intval($allrow['remain_time']/60),
                    'MutilLogins'=>$allrow['concurrent_logins']);
            }
        }catch(Exception $ex){
            $result = '1151';
        }
        $result[] = array('pagebegin'=>$pagebegin, 'pagenum'=>$data['pagenum'], 'pagecnt'=>$pagecnt, 'users'=>$users, 'alluser'=>$alluser);

        return $result;
    }

    public static function addPortalUser($data){
        global $config;

        $result = 0;
        $t = time();
        try{
            $username = SQLite3::escapeString(trim($data['Username']));
            $password = SQLite3::escapeString(trim($data['Password']));
            $expire_time = strtotime(trim($data['ExpireTime']));
            $remain_time = intval($data['RemainTime']);
            $concurrent = $data['MutilLogins'];
            if(strlen($password) <6 || strlen($password)>15){
                throw new AppException('Portal_200');
            }
            if(false === $expire_time){
                throw new AppException('Portal_201');
            }
            if($remain_time<0){
                throw new AppException('Portal_202');
            }
            $remain_time = $remain_time * 60;
            if('0'!=$concurrent && '1'!=$concurrent){
                throw new AppException('Portal_203');
            }
            $db = PortalHelper::getDbConn();
            $res = $db->query("select * from users where username='".SQLite3::escapeString($username)."' limit 1");
            $user = $res->fetchArray(SQLITE3_ASSOC);
            if($user) {
                throw new AppException('Portal_204');
            }
            $res = $db->exec("insert into users (username, password, expire_time, remain_time, concurrent_logins, created, deleted) values (
'".$username."',
'".$password."',
'".$expire_time."',
'".$remain_time."',
'".$concurrent."',
'".$t."',
'0')");
            if(!$res){
                throw new AppException('Portal_205');
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delPortalUser($data){
        global $config;

        $result = 0;
        $t = time();
        try{
            $username = SQLite3::escapeString(trim($data['Username']));
            if('WeChatUser'==$username){
                throw new AppException('Portal_302');
            }
            $db = PortalHelper::getDbConn();
            $res = $db->query("select * from users where username='".SQLite3::escapeString($username)."' limit 1");
            $user = $res->fetchArray(SQLITE3_ASSOC);
            if(!$user) {
                throw new AppException('Portal_300');
            }
            $res = $db->exec("delete from users where username='$username'");
            if(!$res){
                throw new AppException('Portal_301');
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function updatePortalUser($data){
        global $config;

        $result = 0;
        $t = time();
        try{
            $username = SQLite3::escapeString(trim($data['Username']));
            $password = SQLite3::escapeString(trim($data['Password']));
            $expire_time = strtotime(trim($data['ExpireTime']));
            $remain_time = intval($data['RemainTime']);
            $concurrent = $data['MutilLogins'];
            if(strlen($password)>0 && (strlen($password) <6 || strlen($password)>15)){
                throw new AppException('Portal_200');
            }
            if(false === $expire_time){
                throw new AppException('Portal_201');
            }
            if($remain_time<0){
                throw new AppException('Portal_202');
            }
            $remain_time = $remain_time * 60;
            if('0'!=$concurrent && '1'!=$concurrent){
                throw new AppException('Portal_203');
            }
            $db = PortalHelper::getDbConn();
            $res = $db->query("select * from users where username='".SQLite3::escapeString($username)."' limit 1");
            $user = $res->fetchArray(SQLITE3_ASSOC);
            if(!$user) {
                throw new AppException('Portal_400');
            }

            if(strlen($password)==0){
                $password = $user['password'];
            }
            $res = $db->exec("update users set password='".$password."', expire_time='".$expire_time."',remain_time='".$remain_time.
                "', concurrent_logins='".$concurrent."', created=".$t.", deleted=0 where username='".$username."'");
            if(!$res){
                throw new AppException('Portal_401');
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getSessions(){
        global $config;

        $result = array();
        try{
            $db = PortalHelper::getDbConn();
            $res = $db->query('select c.sessionid,c.username,c.ip_address,c.mac_address,s.bytes_in,s.bytes_out,s.last_accessed from cp_clients c,session_info s where c.sessionid=s.sessionid and c.deleted=0');
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                if(!empty($row['username'])){
                    $result[] = array('Sessionid'=>$row['sessionid'],
                        'Username'=>$row['username'],
                        'Ip'=>$row['ip_address'],
                        'Mac'=>$row['mac_address'],
                        'UpBytes'=>$row['bytes_in'],
                        'DownBytes'=>$row['bytes_out'],
                        'LastAccess'=>date("Y-m-d H:i:s",$row['last_accessed']));
                }

            }
        }catch(Exception $ex){
            $result = '1151';
        }

        return $result;
    }

    public static function delSession($data){
        global $config;

        $result = 0;
        try{
            $sessionid = SQLite3::escapeString(trim($data['Sessionid']));
            $db = PortalHelper::getDbConn();
            $res = $db->query("select * from cp_clients where sessionid='".$sessionid."'");
            $session = $res->fetchArray(SQLITE3_ASSOC);
            if(!$session) {
                throw new AppException('Portal_500');
            }
            $res = $db->exec("update cp_clients set deleted=1 where sessionid='$sessionid'");
            if(!$res){
                throw new AppException('Portal_501');
            }
            $backend = new Backend();
            $statusRAW = $backend->configdpRun(
                "captiveportal disconnect",
                array('0', $session['sessionId'], 'json')
            );
            $status = json_decode($statusRAW, true);
            if ($status != null) {
                self::getLogger("captiveportal")->info(
                    "LOGOUT " . $session['username'] .  " (".$session['ip_address'].") zone 0"
                );
            }else{
                $result =1;
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

}