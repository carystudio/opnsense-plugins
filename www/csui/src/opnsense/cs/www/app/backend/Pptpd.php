<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/if_pptp.inc');


class Pptpd extends Csbackend
{
    protected static $ERRORCODE = array(
        'Pptpd_100'=>'开启参数不正确',
        'Pptpd_101'=>'服务器IP不正确',
        'Pptpd_102'=>'客户端开始IP不正确',
        'Pptpd_103'=>'客户端结束IP不正确',
        'Pptpd_104'=>'Wins服务器IP不正确',
        'Pptpd_105'=>'DNS1不正确',
        'Pptpd_106'=>'DNS2不正确',
        'Pptpd_107'=>'加密不正确',
        'Pptpd_200'=>'用户名不能为空',
        'Pptpd_201'=>'密码不能为空',
        'Pptpd_202'=>'IP不正确',
        'Pptpd_203'=>'用户已存在',
        'Pptpd_300'=>'用户不存在'
    );

    private static function setFirewall(){
        global $config;

        foreach($config['filter']['rule'] as $idx=>$rule){
            if('PPTP_SUBNET'==$rule['descr'] || 'PPTP_SERVER_ACCESS'==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }
        }
        if(isset($config['pptpd']['mode']) && 'server'==$config['pptpd']['mode']){
            $newrule1 = array(
                'type' => 'pass',
                'interface' => 'pptp',
                'ipprotocol' => 'inet',
                'statetype' => 'keep state',
                'descr' => 'PPTP_SUBNET',
                'source' => Array('any' => '1'),
                'destination' => Array('any' => '1'),
                'updated' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend PPTP made changes'),
                'created' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend PPTP made changes')
            );
            $newrule2 = array(
                'type' => 'pass',
                'ipprotocol' => 'inet',
                'statetype' => 'keep state',
                'descr' => 'PPTP_SERVER_ACCESS',
                'direction'=>'in',
                'quick'=>'yes',
                'floating'=>'yes',
                'protocol'=>'tcp',
                'source' => Array('any' => '1'),
                'destination' => Array('any' => '(self)','port'=>1723),
                'updated' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend PPTP made changes'),
                'created' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend PPTP made changes')
            );
            if(isset($config['filter']['rule']) && is_array($config['filter']['rule'])){
                $config['filter']['rule'][] = $newrule1;
                $config['filter']['rule'][] = $newrule2;
            }else{
                $config['filter']['rule'] = array();
                $config['filter']['rule'][] = $newrule1;
                $config['filter']['rule'][] = $newrule2;
            }
            if(isset($config['interfaces']['wan']['blockpriv'])){
                unset($config['interfaces']['wan']['blockpriv']);
            }
        }

    }

    private static function pptpusercmp($a, $b)
    {
        return strcasecmp($a['name'], $b['name']);
    }

    private static function pptpd_users_sort()
    {
        global $config;

        if (!is_array($config['ppptpd']['user'])) {
            return;
        }

        usort($config['pptpd']['user'], "pptpusercmp");
    }

    public static function getStatus(){
        global $config;

        $pptpdStatus = array('Enable'=>'0');
        if(!isset($config['pptpd'])) {

        }
        $pptpd = $config['pptpd'];
        if('server'==$pptpd['mode']){
            $pptpdStatus['Enable'] = '1';
        }
        $pptpdStatus['LocalIp'] = $pptpd['localip'];
        $pptpdStatus['ClientIpStart'] = $pptpd['remoteip'];
        $pptpdStatus['ClientIpEnd'] = long2ip(ip2long($pptpd['remoteip']) + $pptpd['n_pptp_units'] - 1);
        $pptpdStatus['Wins'] = $pptpd['wins'];
        $pptpdStatus['Dns1'] = $pptpd['dns1'];
        $pptpdStatus['Dns2'] = $pptpd['dns2'];
        $pptpdStatus['Encrypt'] = $pptpd['req128'];


        return $pptpdStatus;
    }

    public static function setStatus($data){
        global $config;
        $result = 0;
        try {
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            if(!isset($data['Enable']) || ('1'!=$data['Enable'] && '0'!=$data['Enable'])){
                throw new AppException('Pptpd_100');
            }
            if('1'==$data['Enable']){
                if(!isset($data['LocalIp']) || !is_ipaddr($data['LocalIp'])){
                    throw new AppException('Pptpd_101');
                }
                if(!isset($data['ClientIpStart']) || !is_ipaddr($data['ClientIpStart'])){
                    throw new AppException('Pptpd_102');
                }
                if(!isset($data['ClientIpEnd']) || !is_ipaddr($data['ClientIpEnd'])){
                    throw new AppException('Pptpd_103');
                }
                if(!isset($data['Wins']) || (strlen($data['Wins'])>0 && !is_ipaddr($data['Wins']))){
                    throw new AppException('Pptpd_104');
                }
                if(!isset($data['Dns1']) || !is_ipaddr($data['Dns1'])){
                    throw new AppException('Pptpd_105');
                }
                if(isset($data['Dns2']) && strlen($data['Dns2'])>0 && !is_ipaddr($data['Dns2'])){
                    throw new AppException('Pptpd_106');
                }
                if(!isset($data['Encrypt']) || ('1'!=$data['Encrypt'] && '0'!=$data['Encrypt'])){
                    throw new AppException('Pptpd_107');
                }
            }

            if('1'==$data['Enable']){
                $config['pptpd']['mode'] = 'server';
                $config['pptpd']['localip'] = $data['LocalIp'];
                $config['pptpd']['remoteip'] = $data['ClientIpStart'];
                $config['pptpd']['n_pptp_units'] = ip2long($data['ClientIpEnd']) - ip2long($data['ClientIpStart']) + 1;
                $config['pptpd']['wins'] = $data['Wins'];
                $config['pptpd']['dns1'] = $data['Dns1'];
                if(isset($data['Dns2'])){
                    $config['pptpd']['dns2'] = $data['Dns2'];
                }
                $config['pptpd']['req128'] = $data['Encrypt'];
            }else{
                $config['pptpd']['mode'] = 'off';
            }

            self::setFirewall();
            write_config();
            if_pptp_configure_do();
            filter_configure();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }

    public static function getUsers(){
        global $config;

        $pptpdUsers = array();
        if(isset($config['pptpd']['user']) && is_array($config['pptpd']['user'])) {
            foreach ($config['pptpd']['user'] as $user){
                $user_info = array();
                $user_info['Username'] = $user['name'];
                $user_info['Password'] = $user['password'];
                $user_info['Ip'] = $user['ip'];
                $pptpdUsers[] = $user_info;
            }
        }


        return $pptpdUsers;
    }

    public static function addUser($data){
        global $config;
        $result = 0;
        try {
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            if(!isset($data['Username']) || strlen($data['Username'])<1){
                throw new AppException('Pptpd_200');
            }
            if(!isset($data['Password']) || strlen($data['Password'])<1){
                throw new AppException('Pptpd_201');
            }
            if(isset($data['Ip']) && strlen($data['Ip'])>0 && !is_ipaddr($data['Ip'])){
                throw new AppException('Pptpd_202');
            }
            $new_user = array('name'=>$data['Username'], 'password'=>$data['Password'], 'ip'=>$data['Ip']);
            if(isset($config['pptpd']['user']) && is_array($config['pptpd']['user'])) {//检查是否已存在
                foreach ($config['pptpd']['user'] as $user){
                    if($user['name'] == $data['Username']){
                        throw new AppException('Pptpd_203');
                    }
                }
                $config['pptpd']['user'][] = $new_user;
            }else{
                $config['pptpd']['user'] = array();
                $config['pptpd']['user'][] = $new_user;
            }

            self::pptpd_users_sort();
            write_config();
            if_pptp_configure_do();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }

    public static function delUser($data){
        global $config;
        $result = 0;
        try {
            $deleted = false;
            if(isset($config['pptpd']['user']) && is_array($config['pptpd']['user'])) {//检查是否已存在
                foreach ($config['pptpd']['user'] as $idx=>$user){
                    if($user['name'] == $data['Username']){
                        unset($config['pptpd']['user'][$idx]);
                        $deleted = true;
                    }
                }
            }
            if(!$deleted){
                throw new AppException('Pptpd_300');
            }

            self::pptpd_users_sort();
            write_config();
            if_pptp_configure_do();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }
}