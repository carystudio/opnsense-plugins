<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/openvpn.inc');

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

class Openvpn extends Csbackend
{
    private  static $cipherlist = false;
    protected static $ERRORCODE = array(
        'OVPN_100'=>'开启参数不正确'
    );



    private static function confInit(){
        global $config;

        if(!isset($config['openvpn']) ||
            !is_array($config['openvpn']) ||
            !isset($config['openvpn']['openvpn-server']) ||
            !is_array($config['openvpn']['openvpn-server']) ||
            count($config['openvpn']['openvpn-server'])<=0
        ){
            $config['openvpn'] = array();
            $openvpnsvr = array();
            $openvpnsvr['mode'] = 'server_tls_user';
            $openvpnsvr['protocol'] = 'TCP';
            $openvpnsvr['dev_mode'] = 'tun';
            $openvpnsvr['local_port'] = 1194;
            $openvpnsvr['description'] = 'openvpnsvr1';
            $openvpnsvr['crypto'] = 'AES-128-CBC';
            $openvpnsvr['digest'] = 'SHA1';
            $openvpnsvr['engine'] = 'none';
            $openvpnsvr['tunnel_network'] = '10.0.8.0/24';
            $openvpnsvr['remote_network'] = '';
            $openvpnsvr['local_network'] = '';
            $openvpnsvr['maxclients'] = 100;
            $openvpnsvr['client2client'] = 'yes';
            $openvpnsvr['pool_enable'] = 'yes';
            $openvpnsvr['local_group'] = 'openvpn';
            $openvpnsvr['dns_domain'] = 'vpn.csg200p.me';
            $openvpnsvr['dns_server1'] = '114.114.114.114';
            $openvpnsvr['push_register_dns'] = 'yes';
            $openvpnsvr['netbios_ntype'] = '0';
            $openvpnsvr['no_tun_ipv6'] = 'yes';
            $openvpnsvr['verbosity_level'] = 1;
            $openvpnsvr['vpnid'] = 1;
            $openvpnsvr['disable'] = 1;
            $openvpnsvr['authmode'] = 'Local Database';
            $openvpnsvr['interface'] = 'wan';
            $openvpnsvr['custom_options'] = '';
            $openvpnsvr['tls'] = 'Iw0KIyAyMDQ4IGJpdCBPcGVuVlBOIHN0YXRpYyBrZXkNCiMNCi0tLS0tQkVHSU4gT3BlblZQTiBTdGF0aWMga2V5IFYxLS0tLS0NCjA1MjExNTYzN2MyY2M1N2ZjMWFhMjc4ZGY0NDk4NzhiDQozNmFmYTY5MzVlNmNjNGNkYWViZTQ0YzI3OTFiYjA2OA0KMWQ0MTI3NmZkZGEzYjc3NTM1MjFkZDc2YWUyNjM0NmMNCmM5YmE2ZDI1ZGIyY2JkOGQwNDk3YmFlZTM3ODdmMzViDQo0Mzc0ZGMxZWViOTliMzFlOGY0ZGEwODg3MTg5ZDFjZQ0KMGE5YzNlYjUzZTY1N2ZmYzQ4NzZkN2ZkNjdiNDQ0NTgNCjZhMWZmMTI4NzkzOWIzMjFhNjI4MTIwYWZlMzdiOThhDQozZGM2ZmY5ZTRjNjQ0NTI1YmRkZTFiYjRlNjUzNDYyYg0KNjFhMDI5MTJjMWUzMDg2NTIxNjcwNGI2MzBhNzBlMGENCmI4YjNiMTU5MjJkMWJjN2FiZmYyNGZlMmEyOTFmYmVhDQpmN2VhZmM4NTBlNDI1ZWZkNTA5MTFmMGVkMTI0NDYzZQ0KZWExZDhkZTAzYWEyYWI1ZGMwYmUzYTkzZjg0YmQ1MzINCmI1ZTU3ZDRmNWE0NmFkZjA1YTk1YzliZjVjYTJlNDQ4DQo5ZTUzNzU5ZTI0MGZjMDFhZDNhMzkxYWE5MTQ1Zjc1MA0KOTZlNGM3OWRhMjA2MWMxMTdlYWUyOTYyYmI1ZTk0NjENCjlhN2Y0ZDE1MGM1NGVmMGIyNzE4ODExNmQzM2U0N2RmDQotLS0tLUVORCBPcGVuVlBOIFN0YXRpYyBrZXkgVjEtLS0tLQ0K';
            $openvpnsvr['caref'] = '592fa6d7bf05a';
            $openvpnsvr['certref'] = '592fb0cf2dd36';
            $openvpnsvr['dh_length'] = 1024;
            $openvpnsvr['cert_depth'] = 1;
            $openvpnsvr['duplicate_cn'] = 1;

            $config['openvpn']['openvpn-server'][] = $openvpnsvr;
        }
    }

    public static function getCipherlist(){
        if(false == self::$cipherlist){
            self::$cipherlist = openvpn_get_cipherlist();
        }

        return self::$cipherlist;
    }

    public static function getStatus(){
        global $config;

        $openvpnStatus = array('Enable'=>'0');
        self::confInit();

        $openvpn = $config['openvpn']['openvpn-server'][0];
        if(!isset($openvpn['disable']) && '1'!=$openvpn['disable']){
            $openvpnStatus['Enable'] = '1';
        }
        $openvpn['Enable'] = $openvpnStatus['Enable'];
        unset($openvpn['caref']);
        unset($openvpn['certref']);

        return $openvpn;
    }

    private static function setFirewall(){
        global $config;

        foreach($config['filter']['rule'] as $idx=>$rule){
            if('' == $rule['descr']){

            }
        }
    }

    public static function setStatus($data){
        global $config;
        global $openvpn_compression_modes;

        $result = 0;
        try {
            self::confInit();
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            if (!isset($data['Enable']) || ('1' != $data['Enable'] && '0' != $data['Enable'])) {
                throw new AppException('OVPN_100');
            }
            if (!in_array($data['mode'], array('server_user'))) {
                throw new AppException('OVPN_101');
            }
            if (!in_array($data['protocol'], array('TCP', 'UDP'))) {
                throw new AppException('OVPN_102');
            }
            $data['local_port'] = intval($data['local_port']);
            if ($data['local_port']>65535 || $data['local_port']<=0) {
                throw new AppException('OVPN_103');
            }
            if (!in_array($data['dev_mode'], array('tun', 'tap'))) {
                throw new AppException('OVPN_104');
            }
            $cipherlist = self::getCipherlist();
            if (!in_array($data['crypto'], $cipherlist)) {
                throw new AppException('OVPN_105');
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('OVPN_106');
            }
            if(!in_array($data['dh_length'], array(1024, 2048, 4096))){
                throw new AppException('OVPN_107');
            }
            $digestlist = openvpn_get_digestlist();
            if (!in_array($data['digest'], $digestlist)) {
                throw new AppException('OVPN_108');
            }
            $engines = openvpn_get_engines();
            if ('none'!=$data['engine'] && !in_array($data['engine'], $engines)) {
                throw new AppException('OVPN_109');
            }
            $data['cert_depth'] = intval($data['cert_depth']);
            if($data['cert_depth']>5 || $data['cert_depth']<1){
                throw new AppException('OVPN_110');
            }
            if(!empty($data['tunnel_network']) && Util::checkCidr($data['tunnel_network'], true, 'ipv4')){
                throw new AppException('OVPN_111');
            }
            $data['tunnel_networkv6'] = '';
            if(!empty($data['local_network']) && Util::checkCidr($data['local_network'], false, 'ipv4')){
                throw new AppException('OVPN_112');
            }
            $data['local_networkv6'] = '';
            if(!empty($data['remote_network']) && Util::checkCidr($data['remote_network'], false, 'ipv4')){
                throw new AppException('OVPN_113');
            }
            $data['remote_networkv6'] = '';
            $data['maxclients'] = intval($data['maxclients']);
            if($data['maxclients']<1 || $data['maxclients']>1024){
                throw new AppException('OVPN_114');
            }
            if(!in_array($data['compression'], $openvpn_compression_modes)){
                throw new AppException('OVPN_115');
            }
            if(isset($data['client2client']) && 'yes'==$data['client2client']){
                $data['client2client'] = 'yes';
            }else{
                $data['client2client'] = 'no';
            }
            if(isset($data['duplicate_cn']) && 'yes'==$data['duplicate_cn']){
                $data['duplicate_cn'] = 'yes';
            }else{
                $data['duplicate_cn'] = 'no';
            }
            $data['no_tun_ipv6'] = 'yes';
            if(isset($data['pool_enable']) && 'yes'==$data['pool_enable']){
                $data['pool_enable'] = 'yes';
            }else{
                $data['pool_enable'] = 'no';
            }
            if(isset($data['dynamic_ip']) && 'yes'==$data['dynamic_ip']){
                $data['dynamic_ip'] = 'yes';
            }else{
                $data['dynamic_ip'] = 'no';
            }
            if(isset($data['topology_subnet']) && 'yes'==$data['topology_subnet']){
                $data['topology_subnet'] = 'yes';
            }else{
                $data['topology_subnet'] = 'no';
            }
            if(isset($data['dns_domain_enable']) && 'yes'==$data['dns_domain_enable']){
                $data['dns_domain_enable'] = 'yes';
            }else{
                $data['dns_domain_enable'] = 'no';
            }
            if(isset($data['dns_server_enable']) && 'yes'==$data['dns_server_enable']){
                $data['dns_server_enable'] = 'yes';
            }else{
                $data['dns_server_enable'] = 'no';
            }
            for($i=1; $i<=4 ;$i++){
                if(empty($data['dns_server'.$i])){
                    for($j=$i; $j<=4; $j++){
                        unset($data['dns_server'.$j]);
                    }
                }
                if(!is_ipaddr($data['dns_server'.$i])){
                    throw new AppException('OVPN_116');
                }
            }
            if(isset($data['push_register_dns']) && 'yes'==$data['push_register_dns']){
                $data['push_register_dns'] = 'yes';
            }else{
                $data['push_register_dns'] = 'no';
            }
            if(empty($data['ntp_server1'])){
                unset($data['ntp_server1']);
                unset($data['ntp_server2']);
            }else{
                if(!is_ipaddr($data['ntp_server1'])){
                    throw new AppException('OVPN_117');
                }
                if(empty($data['ntp_server2'])){
                    unset($data['ntp_server2']);
                }else{
                    if(!is_ipaddr($data['ntp_server2'])){
                        throw new AppException('OVPN_117');
                    }
                }
            }
            $data['verbosity_level'] = intval($data['verbosity_level']);
            if(!isset($openvpn_verbosity_level[$data['verbosity_level']])){
                throw new AppException('OVPN_118');
            }
            $data['reneg-sec'] = intval($data['reneg-sec']);

            if('yes' != $data['disable']){
                $config['interfaces']['openvpn'] = array(
                    'internal_dynamic'=>'1',
                    'enable'=>'1',
                    'if'=>'openvpn',
                    'descr'=>'OpenVPN',
                    'type'=>'group',
                    'virtual'=>'1'
                );
            }else{
                unset($config['interfaces']['openvpn']);
            }
            $config['openvpn']['openvpn-server'][0] = $data;
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