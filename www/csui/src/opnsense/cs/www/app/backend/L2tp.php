<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/if_l2tp.inc');

function l2tpusercmp($a, $b)
{
    return  strcasecmp($a['name'], $b['name']);
}

function l2tp_users_sort()
{
    global  $config;

    if (!is_array($config['l2tp']['user'])) {
        return;
    }

    usort($config['l2tp']['user'], "l2tpusercmp");
}

class L2tp extends Csbackend
{
    protected static $ERRORCODE = array(
        'L2tp_100'=>'开启参数不正确',
        'L2tp_101'=>'服务器IP不正确',
        'L2tp_102'=>'客户端开始IP不正确',
        'L2tp_103'=>'客户端结束IP不正确',
        'L2tp_104'=>'Wins服务器IP不正确',
        'L2tp_105'=>'DNS1不正确',
        'L2tp_106'=>'DNS2不正确',
        'L2tp_107'=>'认证类型不正确',
        'L2tp_200'=>'用户名不正确',
        'L2tp_201'=>'密码不正确',
        'L2tp_202'=>'用户IP不正确',
        'L2tp_203'=>'用户已存在',
        'L2tp_300'=>'用户不存在'
    );

    private static function setFirewall(){
        global $config;
        $t = time();

        foreach($config['filter']['rule'] as $idx=>$rule){
            if('L2TP_SUBNET'==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }else if('L2TP_SERVER_ACCESS'==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }
        }
        if(isset($config['l2tp']['mode']) && 'server'==$config['l2tp']['mode']){
            $newrule1 = array(
                'type' => 'pass',
                'interface' => 'l2tp',
                'ipprotocol' => 'inet',
                'statetype' => 'keep state',
                'descr' => 'L2TP_SUBNET',
                'source' => Array('any' => '1'),
                'destination' => Array('any' => '1'),
                'updated' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend L2tp made changes'),
                'created' => Array('username' => 'root@'.$_SERVER['REMOTE_ADDR'],
                    'time' => time(),
                    'description' => 'Backend L2tp made changes')
            );
            $newrule2 = array(
                'type'=>'pass',
                'ipprotocol'=>'inet',
                'statetype'=>'keep state',
                'descr'=>'L2TP_SERVER_ACCESS',
                'direction'=>'in',
                'quick'=>'yes',
                'floating'=>'yes',
                'protocol'=>'udp',
                'source'=>array('any'=>'1'),
                'destination'=>array('network'=>'(self)','port'=>'1701'),
                'updated'=>array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>$t, 'description'=>'backend L2tp made changes'),
                'created'=>array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>$t, 'description'=>'backend L2tp made changes')
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

    public static function getStatus(){
        global $config;

        $l2tpStatus = array('Enable'=>'0');
        if(!isset($config['l2tp'])) {
            return $l2tpStatus;
        }
        $l2tp = $config['l2tp'];
        if('server'==$l2tp['mode']){
            $l2tpStatus['Enable'] = '1';
        }
        $l2tpStatus['LocalIp'] = $l2tp['localip'];
        $l2tpStatus['ClientIpStart'] = $l2tp['remoteip'];
        $l2tpStatus['ClientIpEnd'] = long2ip(ip2long($l2tp['remoteip']) + $l2tp['n_l2tp_units'] - 1);
        $l2tpStatus['Dns1'] = isset($l2tp['dns1'])?$l2tp['dns1']:'';
        $l2tpStatus['Dns2'] = isset($l2tp['dns2'])?$l2tp['dns2']:'';
        $l2tpStatus['Wins'] = isset($l2tp['wins'])?$l2tp['wins']:'';
        $l2tpStatus['AuthType'] = $l2tp['paporchap'];


        return $l2tpStatus;
    }

    public static function setStatus($data){
        global $config;
        $result = 0;
        try {
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            if(!isset($data['Enable']) || ('1'!=$data['Enable'] && '0'!=$data['Enable'])){
                throw new AppException('L2tp_100');
            }
            if('1'==$data['Enable']){
                if(!isset($data['LocalIp']) || !is_ipaddr($data['LocalIp'])){
                    throw new AppException('L2tp_101');
                }
                if(!isset($data['ClientIpStart']) || !is_ipaddr($data['ClientIpStart'])){
                    throw new AppException('L2tp_102');
                }
                if(!isset($data['ClientIpEnd']) || !is_ipaddr($data['ClientIpEnd'])){
                    throw new AppException('L2tp_103');
                }
                if(!isset($data['Wins']) || (strlen($data['Wins'])>0 && !is_ipaddr($data['Wins']))){
                    throw new AppException('L2tp_104');
                }
                if(isset($data['Dns1']) && (strlen($data['Dns1'])>0) &&!is_ipaddr($data['Dns1'])){
                    throw new AppException('L2tp_105');
                }
                if(isset($data['Dns2']) && strlen($data['Dns2'])>0 && !is_ipaddr($data['Dns2'])){
                    throw new AppException('L2tp_106');
                }
                if(!isset($data['AuthType']) || ('chap'!=$data['AuthType'] && 'pap'!=$data['AuthType'])){
                    throw new AppException('L2tp_107');
                }
            }

            if('1'==$data['Enable']){
                $config['l2tp']['mode'] = 'server';
                $config['l2tp']['localip'] = $data['LocalIp'];
                $config['l2tp']['remoteip'] = $data['ClientIpStart'];
                $config['l2tp']['n_l2tp_units'] = ip2long($data['ClientIpEnd']) - ip2long($data['ClientIpStart']) + 1;
                $config['l2tp']['wins'] = $data['Wins'];
                $config['l2tp']['dns1'] = $data['Dns1'];
                if(isset($data['Dns2'])){
                    $config['l2tp']['dns2'] = $data['Dns2'];
                }
                $config['l2tp']['paporchap'] = $data['AuthType'];
            }else{
                $config['l2tp']['mode'] = 'off';
                if(isset($config['interfaces']['l2tp'])){
                    unset($config['interfaces']['l2tp']);
                }
            }
            self::setFirewall();

            write_config();
            if_l2tp_configure_do();
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

        $l2tpUsers = array();
        if(isset($config['l2tp']['user']) && is_array($config['l2tp']['user'])) {
            foreach ($config['l2tp']['user'] as $user){
                $user_info = array();
                $user_info['Username'] = $user['name'];
                $user_info['Password'] = $user['password'];
                $user_info['Ip'] = $user['ip'];
                $l2tpUsers[] = $user_info;
            }
        }


        return $l2tpUsers;
    }

    public static function addUser($data){
        global $config;
        $result = 0;
        try {
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            if(!isset($data['Username']) || strlen($data['Username'])<1){
                throw new AppException('L2tp_200');
            }
            if(!isset($data['Password']) || strlen($data['Password'])<1){
                throw new AppException('L2tp_201');
            }
            if(isset($data['Ip']) && strlen($data['Ip'])>0 && !is_ipaddr($data['Ip'])){
                throw new AppException('L2tp_202');
            }
            $new_user = array('name'=>$data['Username'], 'password'=>$data['Password'], 'ip'=>$data['Ip']);
            if(isset($config['l2tp']['user']) && is_array($config['l2tp']['user'])) {//检查是否已存在
                foreach ($config['l2tp']['user'] as $user){
                    if($user['name'] == $data['Username']){
                        throw new AppException('L2tp_203');
                    }
                }
                $config['l2tp']['user'][] = $new_user;
            }else{
                $config['l2tp']['user'] = array();
                $config['l2tp']['user'][] = $new_user;
            }

            l2tp_users_sort();
            write_config();
            if_l2tp_configure_do();
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
            if(isset($config['l2tp']['user']) && is_array($config['l2tp']['user'])) {//检查是否已存在
                foreach ($config['l2tp']['user'] as $idx=>$user){
                    if($user['name'] == $data['Username']){
                        unset($config['l2tp']['user'][$idx]);
                        $deleted = true;
                    }
                }
            }
            if(!$deleted){
                throw new AppException('L2tp_300');
            }

            l2tp_users_sort();
            write_config();
            if_l2tp_configure_do();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }
}
