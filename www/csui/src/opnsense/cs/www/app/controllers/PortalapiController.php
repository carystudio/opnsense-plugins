<?php
require_once("config.inc");
require_once('util.inc');
require_once('interfaces.inc');

use \OPNsense\Core\Backend;
use OPNsense\Auth\PortalLocal;
use OPNsense\Auth\PortalCenter;
use OPNsense\Auth\AuthenticationFactory;

class PortalapiController extends BaseController
{
    private $db = false;

    private function getDbConn(){
        if(false === $this->db){
            $this->db = new \SQLite3('/var/captiveportal/captiveportal.sqlite');
        }

        return $this->db;
    }

    private function getAccessKey(){
        $signkey='wifiap';
        $ip = Util::getClientIp($this->request);

        return md5($signkey.$ip);
    }

    private function getClientInfo(){
        $ip = Util::getClientIp($this->request);
        $mac = Util::getLanMacByIp($ip);

        $result = array();
        $result['ipAddress'] = $ip;
        if($mac){
            $result['macAddress'] = $mac;
        }else{
            $result['macAddress'] = '';
        }
        $db = $this->getDbConn();
        $res = $db->query("select  c.* from cp_clients c,session_info s where c.sessionid=s.sessionid and c.ip_address='$ip' and c.deleted=0 limit 1");
        $session = $res->fetchArray(SQLITE3_ASSOC);
        if($session) {
            $result['sessionId'] = $session['sessionid'];
            $result['userName']=$session['username'];
            $result['ipAddress']=$session['ip_address'];
            $result['macAddress']=$session['mac_address'];
            $result['total_bytes']=$session['total_bytes'];
            $result['idletime']=$session['idletime'];
            $result['totaltime']=$session['totaltime'];
            $result['acc_timeout']=$session['acc_session_timeout'];
            $result['authenticated_via']=$session['authenticated_via'];
            $result['clientState'] = 'AUTHORIZED';
        }else{
            $result['clientState'] = 'NOT_AUTHORIZED';
        }

        return $result;
    }

    private function getSession($sessionid){
        $result = false;
        $db = $this->getDbConn();
        $res = $db->query("select  c.* from cp_clients c,session_info s where c.sessionid=s.sessionid and c.sessionid='$sessionid' and c.deleted=0 limit 1");
        $session = $res->fetchArray(SQLITE3_ASSOC);
        if($session) {
            $result['sessionId'] = $session['sessionid'];
            $result['userName']=$session['username'];
            $result['ipAddress']=$session['ip_address'];
            $result['macAddress']=$session['mac_address'];
            $result['total_bytes']=$session['total_bytes'];
            $result['idletime']=$session['idletime'];
            $result['totaltime']=$session['totaltime'];
            $result['acc_timeout']=$session['acc_session_timeout'];
            $result['authenticated_via']=$session['authenticated_via'];
            $result['clientState'] = 'AUTHORIZED';
        }
        return $result;
    }

    public function clientstatusAction(){

        $result = $this->getClientInfo();

        echo json_encode($result);
    }

    public function logonAction(){
        $username = trim($this->request->getPost('user',null, ''));
        $password = trim($this->request->getPost('password',null, ''));

        $ip = Util::getClientIp($this->request);
        $mac = Util::getLanMacByIp($ip);
        $t = time();

        $result = array('clientState' => 'NOT_AUTHORIZED','ipAddress'=>$ip);
        try{
            $session = $this->getClientInfo();
            if('AUTHORIZED'!=$session['clientState']){
                $authFactory = new AuthenticationFactory();
                $auth = $authFactory->get('Portal Local');
                $check_user = $auth->authenticate($username, $password);
                if(!$check_user){
                    throw new AppException('user or password not correct');
                }
                $user = $auth->getLastAuthProperties();
                if($user['expire_time']>$t || $user['remain_time']>0){
                    $backend = new Backend();
                    $CPsession = $backend->configdpRun(
                        "captiveportal allow",
                        array(
                            '0',
                            $username,
                            $ip,
                            'Portal Local',
                            'json',
                            '0'
                        )
                    );
                    $CPsession = json_decode($CPsession, true);
                    if ($CPsession != null && array_key_exists('sessionId', $CPsession)) {
                        $backend->configdpRun(
                            "captiveportal set session_restrictions",
                            array('0',
                                $CPsession['sessionId'],
                                $user['session_timeout']
                            )
                        );
                    }

                    if(is_array($CPsession)){
                        $result = $CPsession;
                    }
                }else{
                    $result['clientState'] = 'NO_QUOTA';
                }

            }

        }catch (AppException $aex){

        }catch (Exception $ex){

        }

        echo json_encode($result);
    }
    public function weixinlogonAction(){
        $username = $_POST['openId'];
        $password = "111111";

        $ip = Util::getClientIp($this->request);
        $mac = Util::getLanMacByIp($ip);
        $t = time();
        $result = array('clientState' => 'NOT_AUTHORIZED','ipAddress'=>$ip);
        try{
            $db = PortalHelper::getDbConn();
            $res = $db->query("select * from users where username='".SQLite3::escapeString($username)."' limit 1");
            $user = $res->fetchArray(SQLITE3_ASSOC);
            if($user) {
                $delweixin = $db->exec("delete from users where username='$username'");
                if(!$delweixin){
                    throw new AppException('删除用户失败');
                }
            }

                $expire_time = strtotime('2099-12-12');
                $remain_time = "800000";
                $concurrent = "1";
                $res = $db->exec("insert into users (username, password, expire_time, remain_time, concurrent_logins, created, deleted) values (
'".$username."',
'".$password."',
'".$expire_time."',
'".$remain_time."',
'".$concurrent."',
'".$t."',
'0')");
                if(!$res){
                    throw new AppException('添加用户失败');
                }
                $session = $this->getClientInfo();
                if('AUTHORIZED'!=$session['clientState']){
                    $authFactory = new AuthenticationFactory();
                    $auth = $authFactory->get('Portal Local');
                      $check_user = $auth->authenticate($username, $password);
                      if(!$check_user){
                          throw new AppException('user or password not correct');
                      }
                    $user = $auth->getLastAuthProperties();
                    if($user['expire_time']>$t || $user['remain_time']>0){
                        $backend = new Backend();
                        $CPsession = $backend->configdpRun(
                            "captiveportal allow",
                            array(
                                '0',
                                $username,
                                $ip,
                                'Portal Local',
                                'json',
                                '0'
                            )
                        );
                        $CPsession = json_decode($CPsession, true);
                        $CPsession['clientState123'] = $CPsession['ip_address'];
                        if ($CPsession != null && array_key_exists('sessionId', $CPsession)) {
                            $backend->configdpRun(
                                "captiveportal set session_restrictions",
                                array('0',
                                    $CPsession['sessionId'],
                                    $user['session_timeout']
                                )
                            );
                        }

                        if(is_array($CPsession)){
                            $result = $CPsession;
                        }
                    }else{
                        $result['clientState'] = 'NO_QUOTA';
                    }
                }
            
        }catch (AppException $aex){
        }catch (Exception $ex){
        }
        echo json_encode($result);

    }
    //随机生成六位数密码
    function randStr($len=6,$format='ALL') {
        switch($format) {
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~'; break;
            case 'NUMBER':
                $chars='0123456789'; break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        }
        mt_srand((double)microtime()*1000000*getmypid());
        $password="";
        while(strlen($password)<$len)
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
        return $password;
    }

    public function logoutAction()
    {
        $zoneid='0';
        if(isset($_GET['sessionid'])){
            $sessionid = $_GET['sessionid'];
        }else{
            $sessionid = '';
        }
        $result = -1;
        $clientSession = $this->getClientInfo();
        if(''==$sessionid){
            if ($clientSession['clientState'] == 'AUTHORIZED' &&
                $clientSession['authenticated_via'] != '---ip---' &&
                $clientSession['authenticated_via'] != '---mac---'
            ) {
                $result = Portal::delSession(array('Sessionid'=>$clientSession['sessionId']));
            }
        }else{
            $result = Portal::delSession(array('Sessionid'=>$sessionid));
        }

        if(-1 == $result){
            echo json_encode(array("clientState" => "UNKNOWN", "ipAddress" => $clientSession['ipAddress']));
        }else{
            echo json_encode(array("clientState" => "NOT_AUTHORIZED", "ipAddress" => $clientSession['ipAddress']));
        }
    }


    public function portalType(){
        global $config;

        if($config['']){

        }
    }

    public function indexAction()
    {

    }

    public function authAction()
    {
        $username = $_GET['token'];
        $json = isset($_GET['api'])?true:false;
        $jsonp = isset($_GET['callback'])?$_GET['callback']:false;
        $auth = new PortalCenter();
        $auth->setProperties(array());
        $check_user = $auth->authenticate($username, '');
        $result = array('res'=>'SUCCESS');
        try{
            if (false === $check_user) {
                throw new AppException('1');
            }
            $info = $auth->getLastAuthProperties();
            if (1 == $check_user[1]) {
                $backend = new Backend();
                $CPsession = $backend->configdpRun(
                    "captiveportal allow",
                    array(
                        '0',
                        $check_user[8],
                        $info['client_ip'],
                        'Portal Center',
                        'json',
                        $username
                    )
                );
                $CPsession = json_decode($CPsession, true);
                $backend->configdpRun(
                    "captiveportal set session_restrictions",
                    array('0',
                        $CPsession['sessionId'],
                        $check_user[7]
                    )
                );
            } else {
                throw new AppException('auth fail');
            }
        }catch(AppException $aex){
            $result['res'] = 'ERROR';
            $result['content'] = $aex->getMessage();
        }catch(Exception $ex){
            $result['res'] = 'ERROR';
            $result['content'] = 'unknown error';
        }

        if($jsonp){
            echo $jsonp.'('.json_encode($result).')';
        }else if($json){
            echo json_encode($result);
        }else{
            if('SUCCESS'==$result['res']){
                $this->response->redirect('http://totolink.carystudio.com/c/api/v3/portal/?gw_id=' . $info['gatewayid']);
            }else{
                $this->response->redirect('http://totolink.carystudio.com/c/api/v3/message.php?error=1&msg='.$result['content']);
            }
        }
    }

    public function wanwhitesetAction(){
        $action = $_GET['action'];
        $accesskey = $_GET['accesskey'];
        $json = isset($_GET['api'])?true:false;
        $jsonp = isset($_GET['callback'])?$_GET['callback']:false;

        $result = array('res'=>'SUCCESS');
        $t = time();
        try{
            $check_accesskey = $this->getAccessKey();
            if($check_accesskey != $accesskey){
                throw new AppException('accesskey not correct');
            }
            $ip = Util::getClientIp($this->request);
            $db = $this->getDbConn();
            $wanwhiteset_table = array('wanwhiteset0'=>20, 'wanwhiteset1'=>60, 'wanwhiteset2'=>70, 'wanwhiteset3'=>80);
            if(!isset($wanwhiteset_table[$action])){
                throw new AppException('action not correct');
            }
            $fwtable = $wanwhiteset_table[$action];
            $res = $db->query("select ip,fwtable,create_time,expire_time,delete_time from wanwhiteset where ip='$ip' and fwtable=".$fwtable);
            $wanwhiteset = $res->fetchArray(SQLITE3_ASSOC);
            if($wanwhiteset) {
                throw new AppException('exist');
            }
            if('wanwhiteset0' == $action){
                $expire_time = $t + 30;
            }else{
                $expire_time = $t + 60;
            }

            $res = $db->exec("insert into wanwhiteset (ip,fwtable,create_time,expire_time,delete_time) values('$ip',$fwtable,$t, $expire_time, 0)");
            if(!$res){
                throw new AppException('process error');
            }
            exec("/sbin/ipfw table $fwtable add $ip");
        }catch(AppException $aex){
            $result['res'] = 'ERROR';
            $result['content'] = $aex->getMessage();
        }catch(Exception $ex){
            $result['res'] = 'ERROR';
            $result['content'] = 'unknow error';
        }

        if($jsonp){
            echo $jsonp.'('.json_encode($result).')';
        }else{
            echo json_encode($result);
        }

    }

    public function tologinjsAction(){
        $ip = Util::getClientIp($this->request);
        $mac= Util::getLanMacByIp($ip);
        $loginpage = file_get_contents('/usr/local/opnsense/cs/tmp/portal_loginpage');
        if('/index.html'==$loginpage){
            echo 'window.location.href="'.$loginpage.'"+location.search;';
        }else{
            $url = explode('redirurl=',$_SERVER['HTTP_REFERER']);
            if(isset($url[1])){
                $url = 'http://'.$url[1];
            }else{
                $url = 'http://www.baidu.com/';
            }
            echo 'window.location.href="'.$loginpage.'&clientip='.$ip.'&clientmac='.$mac.'&ver=3.1&url='.urlencode($url).'";';
        }
    }

    public function portaltoweixinAction(){
        global $config;
        $portal = self::getPortal();
        $ipaddr = $config['interfaces']['lan']['ipaddr'];

        $result = array('res'=>'Success');
        $result['ip'] = Util::getClientIp($this->request);
        $result['mac'] = Util::getLanMacByIp($result['ip']);
        $result['appId'] = $portal['appId'];
        $result['shop_id'] = $portal['shop_id'];
        $result['ssid'] = $portal['ssid'];
        $result['secretkey'] = $portal['secretkey'];
        $result['timestamp'] = $this->getMillisecond();
        $result['authUrl'] = "http://".$ipaddr."/portalapi/ok/";
        $result['extend'] = $portal['attention'];
        $result['getpassthrouthurl'] = '/wifidog/api?callback=checktoopenwechat&action=wanwhiteset0&accesskey='.md5('wifiap'.$result['ip']).'&clientip='.$result['ip'];

        $result['sign'] = md5($result['appId'].$result['extend'].$result['timestamp'].$result['shop_id'].$result['authUrl'].$result['mac'].$result['ssid'].$result['secretkey']);
//        $result['authUrl'] = urlencode($result['authUrl']);

        echo json_encode($result);
    }
    // 毫秒级时间戳
    function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
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
    public function loginpagedlAction(){
        $filename = '/usr/local/opnsense/cs/tmp/login_page.zip';
        if(!file_exists($filename)){
            chdir('/usr/local/opnsense/cs/conf/captiveportal/login_page/page2');
            exec('/usr/local/bin/zip '.$filename.' *');
        }

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($filename)); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小
        @readfile($filename);
    }


    public function portal_accountsAction()
    {

    }
    public function add_accountAction()
    {

    }
    public function loginuserAction()
    {

    }

    public function portalAction(){

    }
    public function okAction(){

    }
    public function testAction(){

    }
}

