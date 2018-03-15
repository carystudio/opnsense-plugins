<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('auth.inc');

use \OPNsense\TrafficShaper\TrafficShaper;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Backend;
use \OPNsense\Core\Config;

class Firewall extends Csbackend
{
    protected static $ERRORCODE = array(
        'Firewall_100'=>'类型不正确',
        'Firewall_101'=>'协议不正确',
        'Firewall_102'=>'IP不正确',
        'Firewall_103'=>'端口不正确',
        'Firewall_104'=>'规则已存在',
        'Firewall_200'=>'规则不存在',
        'Firewall_300'=>'接口不存在',
        'Firewall_301'=>'转发IP不正确',
        'Firewall_302'=>'转发端口不正确',
        'Firewall_303'=>'规则已存在',
        'Firewall_400'=>'规则不存在',
        'Firewall_500'=>'WEB端口不正确',
        'Firewall_501'=>'SSH端口不正确'
    );

    private static $PROTOCOL = array('tcp/udp', 'tcp', 'udp');

    private static function apply(){
        global $config;

        write_config();
        filter_configure();
        system_login_configure();
        $backend = new Backend();
        if('enabled'==$config['system']['ssh']['enabled']){
            $backend->configdRun('sshd restart');
        }else{
            $backend->configdRun('sshd stop');
        }
        clear_subsystem_dirty('natconf');
        clear_subsystem_dirty('filter');
    }

    public static function getFilterStatus(){
        global $config;

        $filterStatus = array();
        if(isset($config['filter']['rule'])){
            foreach($config['filter']['rule'] as $rule){
                if(0===strpos($rule['descr'], 'block_')){
                    $filter = array();
                    $filter['Id'] = substr($rule['descr'], 6);
                    $filter['Interface'] = $rule['interface'];
                    $filter['Protocol'] = $rule['protocol'];
                    if(isset($rule['source']['address'])){
                        $filter['Type'] = '2';
                        $filter['Ip'] = $rule['source']['address'];
                        $ports = explode('-', $rule['destination']['port']);
                        $filter['PortStart'] = $ports[0];
                        $filter['PortEnd'] = isset($ports[1])?$ports[1]:$ports[0];
                        $filter['Descr'] = isset($rule['comment'])?$rule['comment']:'';
                    }

                    $filterStatus[]=$filter;
                }
            }
        }
        return $filterStatus;
    }

    public static function addFilter($data){
        global $config;
        $result = 0;
        try{
            if(!in_array($data['Protocol'], self::$PROTOCOL)){
                throw new AppException('Firewall_101');
            }
            if(!isset($data['Ip']) || !is_ipaddr($data['Ip'])){
                throw new AppException('Firewall_102');
            }
            $port_start = intval($data['PortStart']);
            $port_end = intval($data['PortEnd']);
            if($port_start<1 || $port_end>65535 || $port_end<$port_start){
                throw new AppException('Firewall_103');
            }
            $oldrules = $config['filter']['rule'];
            $passrule = array();
            $config['filter']['rule'] = array();
            foreach($oldrules as $rule){
                if(0===strpos($rule['descr'], 'block_')){
                    $config['filter']['rule'][] = $rule;
                }else{
                    $passrule[] = $rule;
                }
            }
            $rule = array();
            $rule['type'] = 'reject';
            $rule['interface'] = 'lan';
            $rule['ipprotocol'] = 'inet';
            $rule['statetype'] = 'keep state';
            $rule['descr'] = 'block_'.Util::getRandStr(10);
            $rule['protocol'] = $data['Protocol'];
            $rule['comment'] = trim($data['comment']);

            $infinfo = Network::getInfStatus('lan');
            if($data['Ip'] == $infinfo['ipaddr']){
                throw new AppException('');
            }
            $rule['source'] = Array('address' => $data['Ip']);
            if($port_start == $port_end){
                $rule['destination'] = Array('any' => '1','port'=>$port_start);
            }else{
                $rule['destination'] = Array('any' => '1','port'=>$port_start.'-'.$port_end);
            }
            //$rule['updated'] = Array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>time(), 'description'=>'backend Firewall made changes')
            $rule['created'] = Array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>time(), 'description'=>'backend Firewall made changes');
            foreach($config['filter']['rule'] as $a_rule){
                if($a_rule['interface'] == $rule['interface'] &&
                    $a_rule['ipprotocol'] == $rule['ipprotocol'] &&
                    $a_rule['protocol'] == $rule['protocol'] && (
                        (isset($a_rule['source']['address']) && isset($rule['source']['address']) &&
                        $a_rule['source']['address'] == $rule['source']['address'] &&
                        $a_rule['destination']['any'] == $rule['destination']['any'] &&
                        $a_rule['destination']['port'] == $rule['destination']['port'])
                    )){
                    throw new AppException('Firewall_104');
                }
            }
            $config['filter']['rule'][] = $rule;
            foreach ($passrule as $rule){
                $config['filter']['rule'][] = $rule;
            }

            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delFilter($data){
        global $config;
        $result = 0;
        try{
            $deleted = false;
            foreach ($data['Id'] as $id){
                foreach($config['filter']['rule'] as $idx=>$a_rule){
                    if($a_rule['descr'] == 'block_'.$id){
                        unset($config['filter']['rule'][$idx]);
                        $deleted = true;
                        break ;
                    }
                }
            }
            if(!$deleted){
                throw new AppException('Firewall_200');
            }
            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getPatStatus(){
        global $config;

        $filterStatus = array();
        if(isset($config['nat']['rule'])){
            foreach($config['nat']['rule'] as $rule){
                if(0===strpos($rule['descr'], 'pat_')){
                    $pat = array();
                    $pat['Id'] = substr($rule['descr'], 4);
                    if(isset($config['interfaces'][$rule['interface']])){
                        $pat['Interface'] = strtoupper($config['interfaces'][$rule['interface']]['descr']);
                    }else{
                        $pat['Interface'] = '';
                    }
                    
                    $pat['Protocol'] = $rule['protocol'];
                    $pat['LocalPort'] = $rule['destination']['port'];
                    $pat['RemoteIp'] = $rule['target'];
                    $pat['RemotePort'] = $rule['local-port'];
                    $filterStatus[]=$pat;
                }
            }
        }

        return $filterStatus;
    }


    public static function addPatItem($data){
        global $config;
        $result = 0;
        try{
            if(!isset($data['Interface'])){
                throw new AppException('Firewall_300');
            }else {
                $exist = false;
                foreach($config['interfaces'] as $ifname=>$ifinfo){
                    if($ifinfo['descr'] == $data['Interface']){
                        $exist = true;
                        break;
                    }
                }
                if(!$exist){
                    throw new AppException('Firewall_300');
                }
            }
            if(!isset($data['Protocol']) || !in_array($data['Protocol'], self::$PROTOCOL)){
                throw new AppException('Firewall_101');
            }
            $local_port = intval($data['LocalPort']);
            if($local_port>65536 || $local_port<1){
                throw new AppException('Firewall_103');
            }
            if(!isset($data['RemoteIp']) || !is_ipaddr($data['RemoteIp'])){
                throw new AppException('Firewall_301');
            }
            $remote_port = intval($data['RemotePort']);
            if($remote_port>65536 || $remote_port<1){
                throw new AppException('Firewall_302');
            }
            $natrule = array();
            $natrule['protocol'] = $data['Protocol'];
            $natrule['interface'] = $ifname;
            $natrule['ipprotocol'] = 'inet';
            $natrule['descr'] = 'pat_'.Util::getRandStr(10);
            $natrule['tag'] = '';
            $natrule['tagged'] = '';
            $natrule['poolopts'] = '';
            $natrule['associated-rule-id'] = 'pass';
            $natrule['target'] = $data['RemoteIp'];
            $natrule['local-port'] = $data['RemotePort'];
            $natrule['source'] = Array('any' => '1');
            $natrule['destination'] = Array('network'=>$ifname.'ip', 'port'=> $local_port);
            $natrule['created'] = Array('username'=> 'root@'.$_SERVER['REMOTE_ADDR'], 'time'=> time(), 'description' => 'backend Firewall made changes');

            foreach($config['nat']['rule'] as $a_rule){
                if($a_rule['interface'] == $natrule['interface'] &&
                    $a_rule['protocol'] == $natrule['protocol'] &&
                    $a_rule['ipprotocol'] == $natrule['ipprotocol'] &&
                    $a_rule['target'] == $natrule['target'] &&
                    $a_rule['local-port'] == $natrule['local-port'] &&
                    $a_rule['destination']['network'] == $natrule['destination']['network'] &&
                    $a_rule['destination']['port'] == $natrule['destination']['port']){
                    throw new AppException('Firewall_303');
                }
            }
            $config['nat']['rule'][] = $natrule;

            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delPatItem($data){
        global $config;
        $result = 0;
        try{
            $deleted = false;
            foreach($config['nat']['rule'] as $idx=>$a_rule){
                if($a_rule['descr'] == 'pat_'.$data['Id']){
                    unset($config['nat']['rule'][$idx]);
                    $deleted = true;
                    break ;
                }
            }
            if(!$deleted){
                throw new AppException('Firewall_400');
            }
            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    private static function initRemoteWeb($port=8080){
        global $config;

        foreach($config['nat']['rule'] as $idx=>$rule) {
            if ('remote_web' == $rule['descr']) {
                unset($config['nat']['rule'][$idx]);
            }
        }
        foreach($config['filter']['rule'] as $idx=>$rule) {
            if (isset($rule['associated-rule-id']) && 'NAT remote_web' == $rule['descr']) {
                unset($config['filter']['rule'][$idx]);
            }
        }
        foreach($config['interfaces'] as $k=>$v){
            if (substr($config['interfaces'][$k]['descr'],0,3) == 'wan'){
                $t = time();
                $natrule = array();
                $natrule['protocol'] = 'tcp';
                $natrule['interface'] = $k;
                $natrule['ipprotocol'] = 'inet';
                $natrule['descr'] = 'remote_web';
                $natrule['associated-rule-id'] = 'pass';
                $natrule['target'] = $config['interfaces']['lan']['ipaddr'];
                $natrule['local-port'] = '80';
                $natrule['source'] = Array('any' => '1');
                if(0==$port){
                    $natrule['destination'] = Array('network' => $k,'port' => 8080);
                    $natrule['disabled'] = '1';
                }else{
                    $natrule['destination'] = Array('network' => $k,'port' => $port);
                }
                $natrule['created'] = Array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>$t, 'description'=>'Backend Firewall made changes');

                $config['nat']['rule'][] = $natrule;
                if(isset($config['interfaces'][$k]['blockpriv'])){
                    unset($config['interfaces'][$k]['blockpriv']);
                }
            }
        }
    }

    private static function initRemoteSsh($port=10022){
        global $config;

        foreach($config['nat']['rule'] as $idx=>$rule) {
            if ('remote_ssh' == $rule['descr']) {
                unset($config['nat']['rule'][$idx]);
            }
        }
        foreach($config['filter']['rule'] as $idx=>$rule) {
            if (isset($rule['associated-rule-id']) && 'NAT remote_ssh' == $rule['descr']) {
                unset($config['filter']['rule'][$idx]);
            }
        }
        foreach ($config['interfaces'] as $k => $v){
            if (substr($config['interfaces'][$k]['descr'],0,3) == 'wan'){
                $t = time();
                $natrule = array();
                $natrule['protocol'] = 'tcp';
                $natrule['interface'] = $k;
                $natrule['ipprotocol'] = 'inet';
                $natrule['descr'] = 'remote_ssh';
                $natrule['associated-rule-id'] = 'pass';
                $natrule['target'] = $config['interfaces']['lan']['ipaddr'];
                $natrule['local-port'] = '22';
                $natrule['source'] = Array('any' => '1');
                if(0==$port){
                    $natrule['destination'] = Array('network' => $k,'port' => 10022);
                    $natrule['disabled'] = '1';
                    if(isset($config['system']['ssh'])){
                        unset($config['system']['ssh']);
                    }
                }else{
                    $natrule['destination'] = Array('network' => $k,'port' => $port);
                    $config['system']['ssh'] = array('enabled'=>'enabled','passwordauth'=>'1', 'permitrootlogin'=>'1');
                }

                $natrule['created'] = Array('username'=>'root@'.$_SERVER['REMOTE_ADDR'], 'time'=>$t, 'description'=>'Backend Firewall made changes');
                $config['nat']['rule'][] = $natrule;

                if(isset($config['interfaces'][$k]['blockpriv'])){
                    unset($config['interfaces'][$k]['blockpriv']);
                }
            }
        }
    }

    public static function getRemoteAccess(){
        global $config;

        $remoteAccessStatus = array();
        if(isset($config['nat']['rule'])){
            foreach($config['nat']['rule'] as $rule){
                if('remote_web'==$rule['descr']){
                    if('1'==$rule['disabled']){
                        $remoteAccessStatus['Web'] = 0;
                    }else{
                        $remoteAccessStatus['Web'] = $rule['destination']['port'];
                    }
                }else if('remote_ssh'==$rule['descr']){
                    if('1'==$rule['disabled']){
                        $remoteAccessStatus['Ssh'] = 0;
                    }else {
                        $remoteAccessStatus['Ssh'] = $rule['destination']['port'];
                    }
                }
            }
        }
        $needwrite = false;
        if(!isset($remoteAccessStatus['Web'])){
            $remoteAccessStatus['Web'] = '0';
            self::initRemoteWeb();
            $needwrite = true;
        }
        if(!isset($remoteAccessStatus['Ssh'])){
            $remoteAccessStatus['Ssh'] = '0';
            self::initRemoteSsh();
            $needwrite = true;
        }
        if($needwrite){
            self::apply();
        }

        return $remoteAccessStatus;
    }

    public static function setRemoteAccess($data){
        $result = 0;
        try{
            $webport = intval($data['Web']);
            $sshport = intval($data['Ssh']);
            if($webport<0 || $webport>65535){
                throw new AppException('Firewall_500');
            }
            if($sshport<0 || $sshport>65535){
                throw new AppException('Firewall_501');
            }
            self::initRemoteWeb($webport);
            self::initRemoteSsh($sshport);
            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }
}