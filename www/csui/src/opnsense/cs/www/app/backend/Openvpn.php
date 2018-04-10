<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/openvpn.inc');
require_once('auth.inc');

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
    private static $digestlist = false;
    private static $enginelist = false;

    protected static $ERRORCODE = array(
        'OVPN_100'=>'开启参数不正确'
    );
    private static $svr_copy_fields = "mode,protocol,authmode,dev_mode,interface,local_port
            ,description,custom_options,crypto,engine,tunnel_network
            ,tunnel_networkv6,remote_network,remote_networkv6,gwredir,local_network
            ,local_networkv6,maxclients,compression,passtos,client2client
            ,dynamic_ip,pool_enable,topology_subnet,serverbridge_dhcp
            ,serverbridge_interface,serverbridge_dhcp_start,serverbridge_dhcp_end
            ,dns_server1,dns_server2,dns_server3,dns_server4,ntp_server1
            ,ntp_server2,netbios_enable,netbios_ntype,netbios_scope,wins_server1
            ,wins_server2,no_tun_ipv6,push_register_dns,dns_domain,local_group
            ,client_mgmt_port,verbosity_level,caref,crlref,certref,dh_length
            ,cert_depth,strictusercn,digest,disable,duplicate_cn,vpnid,reneg-sec,use-common-name";

     private static $client_copy_fields = "auth_user,auth_pass,disable,mode,protocol,interface
            ,local_port,server_addr,server_port,resolve_retry,remote_random,reneg-sec
            ,proxy_addr,proxy_port,proxy_user,proxy_passwd,proxy_authtype
            ,custom_options,ns_cert_type,dev_mode,crypto,digest,engine
            ,tunnel_network,tunnel_networkv6,remote_network,remote_networkv6,use_shaper
            ,compression,passtos,no_tun_ipv6,route_no_pull,route_no_exec,verbosity_level,tls,shared_key,ca,cert,prv";

     const SERVER_MODE = array('server_tls', 'server_user', 'server_tls_user');

     const PROXY_AUTHTYPE = array('none', 'basic', 'ntlm');
     const VPNID_SVR = 1;
     const VPNID_CLIENT = 2;



    private static function clientConfInit(){
        global $config;

        if(!isset($config['openvpn']) ||
            !is_array($config['openvpn'])){
            $config['openvpn'] = array();
        }
        if(!isset($config['openvpn']['openvpn-client']) ||
            !is_array($config['openvpn']['openvpn-client']) ||
            count($config['openvpn']['openvpn-client'])<=0
        ){
            $config['openvpn']['openvpn-client'] = array();
            $openvpnclient = array();
            $openvpnclient['protocol'] = 'TCP';
            $openvpnclient['dev_mode'] = 'tun';
            $openvpnclient['server_addr'] = 'server.openvpn.org';
            $openvpnclient['server_port'] = 1194;
            $openvpnclient['proxy_authtype'] = 'none';
            $openvpnclient['description'] = 'openvpnclient1';
            $openvpnclient['mode'] = 'p2p_tls';
            $openvpnclient['crypto'] = 'AES-128-CBC';
            $openvpnclient['digest'] = 'SHA1';
            $openvpnclient['engine'] = 'rdrand';
            $openvpnclient['compression'] = 'adaptive';
            $openvpnclient['no_tun_ipv6'] = 'yes';
            $openvpnclient['route_no_pull'] = 'yes';
            $openvpnclient['route_no_exec'] = 'yes';
            $openvpnclient['verbosity_level'] = 1;
            $openvpnclient['interface'] = 'wan';
            $openvpnclient['vpnid'] = Openvpn::VPNID_CLIENT;
            $openvpnclient['disable'] = 1;
            $openvpnclient['custom_options'] = '';
            $openvpnclient['caref'] = Cert::OVPN_SERVER_CA_REFID;
            $openvpnclient['certref'] = '5ac9f213dd1fa';

            $config['openvpn']['openvpn-client'][] = $openvpnclient;

            foreach($config['ca'] as $idx=>$a_ca){
                if(Cert::OVPN_SERVER_CA_REFID == $a_ca['refid'] || 'CSG200P_openvpn_client_server_ca' == $a_ca['descr']){
                    unset($config['ca'][$idx]);
                }
            }
            $config['ca'][] = array('refid' => Cert::OVPN_SERVER_CA_REFID,
                'descr' => 'CSG200P_openvpn_client_server_ca',
                'serial' => 3,
                'crt' => '',
                'prv' => '');
            foreach($config['cert'] as $idx=>$a_cert){
                if('5ac9f213dd1fa' == $a_cert['refid'] || 'CSG2000P_openvpn_client' == $a_cert['descr']){
                    unset($config['cert'][$idx]);
                }
            }
            $config['cert'][] = array('refid'=>'5ac9f213dd1fa',
                'descr'=>'CSG2000P_openvpn_client',
                'crt'=>'',
                'prv'=>'',
                'caref'=>Cert::OVPN_SERVER_CA_REFID);
        }

    }

    private static function svrConfInit(){
        global $config;

        if(!isset($config['openvpn']) ||
            !is_array($config['openvpn'])){
            $config['openvpn'] = array();
        }
        if(!isset($config['openvpn']['openvpn-server']) ||
            !is_array($config['openvpn']['openvpn-server']) ||
            count($config['openvpn']['openvpn-server'])<=0
        ){
            $config['openvpn']['openvpn-server'] = array();
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
            $openvpnsvr['vpnid'] = Openvpn::VPNID_SVR;
            $openvpnsvr['disable'] = 1;
            $openvpnsvr['authmode'] = 'Local Database';
            $openvpnsvr['interface'] = 'wan';
            $openvpnsvr['custom_options'] = '';
            $openvpnsvr['tls'] = 'Iw0KIyAyMDQ4IGJpdCBPcGVuVlBOIHN0YXRpYyBrZXkNCiMNCi0tLS0tQkVHSU4gT3BlblZQTiBTdGF0aWMga2V5IFYxLS0tLS0NCjA1MjExNTYzN2MyY2M1N2ZjMWFhMjc4ZGY0NDk4NzhiDQozNmFmYTY5MzVlNmNjNGNkYWViZTQ0YzI3OTFiYjA2OA0KMWQ0MTI3NmZkZGEzYjc3NTM1MjFkZDc2YWUyNjM0NmMNCmM5YmE2ZDI1ZGIyY2JkOGQwNDk3YmFlZTM3ODdmMzViDQo0Mzc0ZGMxZWViOTliMzFlOGY0ZGEwODg3MTg5ZDFjZQ0KMGE5YzNlYjUzZTY1N2ZmYzQ4NzZkN2ZkNjdiNDQ0NTgNCjZhMWZmMTI4NzkzOWIzMjFhNjI4MTIwYWZlMzdiOThhDQozZGM2ZmY5ZTRjNjQ0NTI1YmRkZTFiYjRlNjUzNDYyYg0KNjFhMDI5MTJjMWUzMDg2NTIxNjcwNGI2MzBhNzBlMGENCmI4YjNiMTU5MjJkMWJjN2FiZmYyNGZlMmEyOTFmYmVhDQpmN2VhZmM4NTBlNDI1ZWZkNTA5MTFmMGVkMTI0NDYzZQ0KZWExZDhkZTAzYWEyYWI1ZGMwYmUzYTkzZjg0YmQ1MzINCmI1ZTU3ZDRmNWE0NmFkZjA1YTk1YzliZjVjYTJlNDQ4DQo5ZTUzNzU5ZTI0MGZjMDFhZDNhMzkxYWE5MTQ1Zjc1MA0KOTZlNGM3OWRhMjA2MWMxMTdlYWUyOTYyYmI1ZTk0NjENCjlhN2Y0ZDE1MGM1NGVmMGIyNzE4ODExNmQzM2U0N2RmDQotLS0tLUVORCBPcGVuVlBOIFN0YXRpYyBrZXkgVjEtLS0tLQ0K';
            $openvpnsvr['caref'] = Cert::CSG2000P_CA_REFID;
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

    public static function getDigestlist(){
        if(false == self::$digestlist){
            self::$digestlist = openvpn_get_digestlist();
        }

        return self::$digestlist;
    }

    public static function getEnginelist(){
        if(false == self::$enginelist){
            self::$enginelist = openvpn_get_engines();
        }

        return self::$enginelist;
    }

    public static function getClientStatus(){
        global $config;

        self::clientConfInit();

        $openvpnClient = $config['openvpn']['openvpn-client'][0];
        if(!isset($openvpnClient['disable']) && '1'!=$openvpnClient['disable']){
            $openvpnClient['disable'] = '0';
        }

        unset($openvpnClient['caref']);
        unset($openvpnClient['certref']);

        return $openvpnClient;
    }

    public static function getSvrStatus(){
        global $config;

        self::svrConfInit();

        $openvpnSvr = $config['openvpn']['openvpn-server'][0];
        if(!isset($openvpnSvr['disable']) && '1'!=$openvpnSvr['disable']){
            $openvpnSvr['disable'] = '0';
        }

        unset($openvpnSvr['caref']);
        unset($openvpnSvr['certref']);

        return $openvpnSvr;
    }

    private static function setFirewall(){
        global $config;

        foreach($config['filter']['rule'] as $idx=>$rule){
            if('OpenVPN_SUBNET_ACCEPT' == $rule['descr'] || 'OpenVPN_LISTEN_ACCEPT'==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }
        }
        if(!isset($config['openvpn']['openvpn-server'][0]['disable']) || 'yes'!=$config['openvpn']['openvpn-server'][0]['disable']){
            $config['filter']['rule'][] = array(
                'type'=>'pass',
                'interface'=>$config['openvpn']['openvpn-server'][0]['interface'],
                'ipprotocol'=>'inet',
                'statetype'=>'keep state',
                'descr'=>'OpenVPN_LISTEN_ACCEPT',
                'source'=>array('any'=>1),
                'destination'=>array(
                    'network'=>$config['openvpn']['openvpn-server'][0]['interface'].'ip',
                    'port'=>$config['openvpn']['openvpn-server'][0]['local_port']),
            );
            $config['filter']['rule'][] = array(
                'type'=>'pass',
                'interface'=>'openvpn',
                'ipprotocol'=>'inet',
                'statetype'=>'keep state',
                'desct'=>'OpenVPN_SUBNET_ACCEPT',
                'source'=>array('any'=>'1'),
                'destination'=>array('any'=>1,)
            );
        }
    }

    public static function setClientStatus($data){
        global $config;
        global $openvpn_compression_modes;
        global $openvpn_verbosity_level;

        $result = 0;
        try{
            self::clientConfInit();
            foreach($data as $var=>$val){
                $data[$var] = trim($val);
            }
            $fields = explode(",", self::$client_copy_fields);
            foreach($fields as $var=>$val){
                $fields[$var] = trim($val);
            }
            foreach ($data as $fieldname=>$fieldval) {
                $fieldname = trim($fieldname);
                if(!in_array($fieldname, $fields)){
                    echo $fieldname;
                    throw new AppException('OVPN_1');
                }
            }
            if (isset($data['disable'])){
                if('yes' != $data['disable']) {
                    throw new AppException('OVPN_200');
                }
            }
            if (!in_array($data['mode'], array('p2p_tls'))) {
                throw new AppException('OVPN_201');
            }
            if (!in_array($data['protocol'], array('TCP', 'UDP'))) {
                throw new AppException('OVPN_202');
            }
            if (!in_array($data['dev_mode'], array('tun', 'tap'))) {
                throw new AppException('OVPN_203');
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('OVPN_204');
            }
            if(empty($data['server_addr'])){
                throw new AppException('OVPN_205');
            }
            $data['server_port'] = intval($data['server_port']);
            if($data['server_port']>65535 || $data['server_port']<1){
                throw new AppException('OVPN_206');
            }
            if(isset($data['resolve_retry']) && 'yes' != $data['resolve_retry']){
                throw new AppException('OVPN_207');
            }
            if(isset($data['proxy_addr']) && !empty($data['proxy_addr'])){
                if(!isset($data['proxy_port'])){
                    throw new AppException('OVPN_208');
                }
                $data['proxy_port'] = intval($data['proxy_port']);
                if($data['proxy_port']>65535 || $data['proxy_port']<1){
                    throw new AppException('OVPN_208');
                }
            }else if(!empty($data['proxy_port'])){
                throw new AppException('OVPN_208');
            }
            if(!in_array($data['proxy_authtype'], Openvpn::PROXY_AUTHTYPE)){
                throw new AppException('OVPN_209');
            }
            if('none' != $data['proxy_authtype'] && (empty($data['proxy_user']) || empty($data['proxy_passwd']))){
                throw new AppException('OVPN_210');
            }
            $data['local_port'] = intval($data['local_port']);
            if($data['local_port']>65535 || $data['local_port']<1){
                throw new AppException('OVPN_211');
            }
            if(!is_numeric($data['reneg-sec'])){
                throw new AppException('OVPN_212');
            }else{
                $data['reneg-sec'] = intval($data['reneg-sec']);
                if($data['reneg-sec']<0){
                    throw new AppException('OVPN_212');
                }
            }
            if('p2p_tls' == $data['mode']){
                if(empty($data['ca'])){
                    throw new AppException('OVPN_213');
                }
                $ca = base64_decode($data['ca']);
                if(!$ca || !strstr($ca, "BEGIN CERTIFICATE") || !strstr($ca, "END CERTIFICATE")){
                    throw new AppException('OVPN_213');
                }
                if(empty($data['cert'])){
                    throw new AppException('OVPN_214');
                }
                $cert = base64_decode($data['cert']);
                if(!$cert || empty($cert) || !strstr($cert, "BEGIN CERTIFICATE") || !strstr($cert, "END CERTIFICATE")){
                    throw new AppException('OVPN_214');
                }
                if(empty($data['prv'])){
                    throw new AppException('OVPN_215');
                }
                $prv = base64_decode($data['prv']);
                if(empty($prv) || !strstr($prv, "BEGIN PRIVATE KEY") || !strstr($prv, "END PRIVATE KEY")){
                    throw new AppException('OVPN_215');
                }
                $ca_subject = cert_get_subject($ca, false);
                $subject = cert_get_subject($cert, false);
                $issuer = cert_get_issuer($cert, false);
                if($ca_subject != $issuer){
                    throw new AppException('OVPN_216');
                }
            }else {
                if(empty($data['shared_key'])){
                    throw new AppException('OVPN_217');
                }
            }

            $cipherlist = self::getCipherlist();
            if (!isset($cipherlist[$data['crypto']])) {
                throw new AppException('OVPN_218');
            }
            $digestlist = self::getDigestlist();
            if (!isset($digestlist[$data['digest']])) {
                throw new AppException('OVPN_219');
            }
            $engines = self::getEnginelist();
            if ('none'!=$data['engine'] && !isset($engines[$data['engine']])) {
                throw new AppException('OVPN_220');
            }
            if(!is_numeric($data['use_shaper'])){
                throw new AppException('OVPN_221');
            }else{
                $data['use_shaper'] = intval($data['use_shaper']);
                if($data['use_shaper']<100 || $data['use_shaper']>104857600){
                    throw new AppException('OVPN_221');
                }
            }
            if(!isset($openvpn_compression_modes[$data['compression']])){
                throw new AppException('OVPN_222');
            }
            if (isset($data['passtos'])){
                if('yes' != $data['passtos']) {
                    throw new AppException('OVPN_223');
                }
            }
            if (isset($data['route_no_pull'])){
                if('yes' != $data['route_no_pull']) {
                    throw new AppException('OVPN_224');
                }
            }
            if (isset($data['route_no_exec'])){
                if('yes' != $data['route_no_exec']) {
                    throw new AppException('OVPN_225');
                }
            }
            $data['verbosity_level'] = intval($data['verbosity_level']);
            if(!isset($openvpn_verbosity_level[$data['verbosity_level']])){
                throw new AppException('OVPN_118');
            }
            $data['vpnid'] = Openvpn::VPNID_CLIENT;
            $data['description'] = 'openvpnclient1';
            $data['no_tun_ipv6'] = 'yes';
            foreach($config['ca'] as $idx=>$ca){
                if('CSG200P_openvpn_client_server_ca' == $ca['descr']){
                    unset($config['ca'][$idx]);
                    break ;
                }
            }
            foreach($config['cert'] as $idx=>$cert){
                if('CSG2000P_openvpn_client' == $cert['descr']){
                    unset($config['cert'][$idx]);
                    break;
                }
            }

            $config['ca'][] = array('refid' => Cert::OVPN_SERVER_CA_REFID,
                'descr' => 'CSG200P_openvpn_client_server_ca',
                'serial' => 3,
                'crt' => $data['ca'],
                'prv' => '');
            $config['cert'][] = array('refid'=>'5ac9f213dd1fa',
                'descr'=>'CSG2000P_openvpn_client',
                'crt'=>$data['cert'],
                'prv'=>$data['prv'],
                'caref'=>Cert::OVPN_SERVER_CA_REFID);
            unset($data['ca']);
            unset($data['cert']);
            unset($data['prv']);
            $config['openvpn']['openvpn-client']=array($data);

            write_config();

            openvpn_configure_single($data['vpnid']);
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function setSvrStatus($data){
        global $config;
        global $openvpn_compression_modes;
        global $openvpn_verbosity_level;

        $result = 0;
        try {
            self::svrConfInit();
            foreach ($data as $var=>$val){
                $data[$var] = trim($val);
            }
            $fields = explode(",", self::$svr_copy_fields);
            foreach($fields as $var=>$val){
                $fields[$var] = trim($val);
            }
            foreach ($data as $fieldname=>$fieldval) {
                $fieldname = trim($fieldname);
                if(!in_array($fieldname, $fields)){
                    echo $fieldname;
                    throw new AppException('OVPN_1');
                }
            }
            if (isset($data['disable'])){
                if('yes' != $data['disable']) {
                    throw new AppException('OVPN_100');
                }
            }
            if('yes'!=$data['disable']){
                unset($data['disable']);
            }
            if (!in_array($data['mode'], Openvpn::SERVER_MODE)) {
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
            if (!isset($cipherlist[$data['crypto']])) {
                throw new AppException('OVPN_105');
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('OVPN_106');
            }
            if(!in_array($data['dh_length'], array(1024, 2048, 4096))){
                throw new AppException('OVPN_107');
            }
            $digestlist = self::getDigestlist();
            if (!isset($digestlist[$data['digest']])) {
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
            if(!empty($data['tunnel_network']) && !Util::checkCidr($data['tunnel_network'], true, 'ipv4')){
                throw new AppException('OVPN_111');
            }
            $data['tunnel_networkv6'] = '';
            if(!empty($data['local_network']) && !Util::checkCidr($data['local_network'], false, 'ipv4')){
                throw new AppException('OVPN_112');
            }
            $data['local_networkv6'] = '';
            if(!empty($data['remote_network']) && !Util::checkCidr($data['remote_network'], true, 'ipv4')){
                throw new AppException('OVPN_113');
            }
            $data['remote_networkv6'] = '';
            $data['maxclients'] = intval($data['maxclients']);
            if($data['maxclients']<1 || $data['maxclients']>1024){
                throw new AppException('OVPN_114');
            }
            if(!isset($openvpn_compression_modes[$data['compression']])){
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
                }else if(!is_ipaddr($data['dns_server'.$i])){
                    echo $data['dns_server'.$i];
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
            $data['vpnid'] = Openvpn::VPNID_SVR;
            $data['local_group'] = 'openvpn';
            $data['description'] = 'openvpnsvr1';
            $data['tls'] = 'Iw0KIyAyMDQ4IGJpdCBPcGVuVlBOIHN0YXRpYyBrZXkNCiMNCi0tLS0tQkVHSU4gT3BlblZQTiBTdGF0aWMga2V5IFYxLS0tLS0NCjA1MjExNTYzN2MyY2M1N2ZjMWFhMjc4ZGY0NDk4NzhiDQozNmFmYTY5MzVlNmNjNGNkYWViZTQ0YzI3OTFiYjA2OA0KMWQ0MTI3NmZkZGEzYjc3NTM1MjFkZDc2YWUyNjM0NmMNCmM5YmE2ZDI1ZGIyY2JkOGQwNDk3YmFlZTM3ODdmMzViDQo0Mzc0ZGMxZWViOTliMzFlOGY0ZGEwODg3MTg5ZDFjZQ0KMGE5YzNlYjUzZTY1N2ZmYzQ4NzZkN2ZkNjdiNDQ0NTgNCjZhMWZmMTI4NzkzOWIzMjFhNjI4MTIwYWZlMzdiOThhDQozZGM2ZmY5ZTRjNjQ0NTI1YmRkZTFiYjRlNjUzNDYyYg0KNjFhMDI5MTJjMWUzMDg2NTIxNjcwNGI2MzBhNzBlMGENCmI4YjNiMTU5MjJkMWJjN2FiZmYyNGZlMmEyOTFmYmVhDQpmN2VhZmM4NTBlNDI1ZWZkNTA5MTFmMGVkMTI0NDYzZQ0KZWExZDhkZTAzYWEyYWI1ZGMwYmUzYTkzZjg0YmQ1MzINCmI1ZTU3ZDRmNWE0NmFkZjA1YTk1YzliZjVjYTJlNDQ4DQo5ZTUzNzU5ZTI0MGZjMDFhZDNhMzkxYWE5MTQ1Zjc1MA0KOTZlNGM3OWRhMjA2MWMxMTdlYWUyOTYyYmI1ZTk0NjENCjlhN2Y0ZDE1MGM1NGVmMGIyNzE4ODExNmQzM2U0N2RmDQotLS0tLUVORCBPcGVuVlBOIFN0YXRpYyBrZXkgVjEtLS0tLQ0K';
            $config['openvpn']['openvpn-server'][0] = $data;
            self::setFirewall();

            write_config();
            openvpn_configure_single($data['vpnid']);
            openvpn_configure_csc();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }

    private static function initOpenvpnGroup(){
        global $config;

        if(!is_array($config['system']['group'])){
            $config['system']['group'] = array();
        }
        $group = array('name'=>'openvpn',
            'description'=>'openvpn',
            'gid'=>2000);
        $config['system']['group'][] = $group;
        write_config();

        return $group;
    }

    public static function getUsers(){
        global $config;

        $openvpnUsers = array();
        $group = System::getGroup('openvpn');
        if(!$group){
            $group = self::initOpenvpnGroup();
        }
        $cscs = array();
        foreach($config['openvpn']['openvpn-csc'] as $idx=>$csc){
            $cscs[$csc['common_name']] = $csc;
        }
        if(is_array($group['member'])){
            foreach($config['system']['user'] as $idx=>$user){
                if(in_array($user['uid'], $group['member'])){
                    $openvpnuser = array('Username'=>$user['name'], 'Password'=>'', 'Ip'=>'');
                    if(isset($cscs[$user['name']])){
                        $openvpnuser['config'] = $cscs[$user['name']];
                    }
                    $openvpnUsers[] = $openvpnuser;
                }
            }
        }

        return $openvpnUsers;
    }

    public static function addUser($data){
        global $config;

        $result = 0;
        try {
            foreach ($data as $var=>$val){
                if(!is_array($val)){
                    $data[$var] = trim($val);
                }
            }
            if(!isset($data['Username']) || strlen($data['Username'])<1){
                throw new AppException('OVPN_300');
            }
            if(!isset($data['Password']) || strlen($data['Password'])<1){
                throw new AppException('OVPN_301');
            }
            $csc = false;
            if(isset($data['config'])){
                $user_config = $data['config'];
                $csc = array();
                $csc['custom_options'] = $user_config['custom_options'];
                if(empty($data['Username'])){
                    throw new AppException('OVPN_302');
                }else{
                    $csc['common_name'] = $data['Username'];
                }

                if(isset($user_config['block'])){
                    if('yes' != $user_config['block']) {
                        throw new AppException('OVPN_303');
                    }
                    $csc['block'] = 'yes';
                }
                $csc['description'] = 'openvpn user:'.$data['Username'];
                if(!empty($user_config['tunnel_network']) && !Util::checkCidr($user_config['tunnel_network'], false, 'ipv4')){
                    throw new AppException('OVPN_304');
                }
                list($ip, $mask) = explode('/', $user_config['tunnel_network']);
                if($mask>30 || $mask<1){
                    throw new AppException('OVPN_304');
                }
                $csc['tunnel_network'] = $user_config['tunnel_network'];
                if(!empty($user_config['local_network']) && !Util::checkCidr($user_config['local_network'], true, 'ipv4')){
                    throw new AppException('OVPN_305');
                }
                $csc['local_network'] = $user_config['local_network'];
                if(!empty($user_config['remote_network']) && !Util::checkCidr($user_config['remote_network'], true, 'ipv4')){
                    throw new AppException('OVPN_306');
                }
                $csc['remote_network'] = $user_config['remote_network'];
                if(isset($user_config['gwredir'])){
                    if('yes' != $user_config['gwredir']) {
                        throw new AppException('OVPN_307');
                    }
                    $csc['gwredir'] = 'yes';
                }
                if(isset($user_config['push_reset'])){
                    if('yes' != $user_config['push_reset']) {
                        throw new AppException('OVPN_308');
                    }
                    $csc['push_reset'] = 'yes';
                }
                if(isset($user_config['dns_domain'])){
                    $csc['dns_domain'] = $user_config['dns_domain'];
                }else{
                    $csc['dns_domain'] = '';
                }
                for($i=1; $i<=4; $i++){
                    if(!isset($user_config['dns_server'.$i])){
                        break;
                    }
                    if(!is_ipaddr($user_config['dns_server'.$i])){
                        throw new AppException('OVPN_309');
                    }
                    $csc['dns_server'.$i] = $user_config['dns_server'.$i];
                }
                for($i=1; $i<=2; $i++){
                    if(!isset($user_config['ntp_server'.$i])){
                        break;
                    }
                    if(!is_ipaddr($user_config['ntp_server'.$i])){
                        throw new AppException('OVPN_310');
                    }
                    $csc['ntp_server'.$i] = $user_config['ntp_server'.$i];
                }
                $csc['ovpn_servers'] = Openvpn::VPNID_SVR;
            }
            if(false !== $csc){
                if(!is_array($config['openvpn']['openvpn-csc'])){
                    $config['openvpn']['openvpn-csc'] = array();
                }
                $config['openvpn']['openvpn-csc'][] = $csc;
            }
            foreach($config['cert'] as $idx=>$cert){
                if(Cert::CSG2000P_CA_REFID == $cert['caref'] && 'openvpn user:'.$data['Username'] == $cert['desct']){
                    throw new AppException('OVPN_311');
                    break;
                }
            }
            $cert = array();
            $cert['refid'] = uniqid();
            $cert['descr'] = 'openvpn user:'.$data['Username'];
            $cert['caref'] = Cert::CSG2000P_CA_REFID;

            $dn = array(
                'countryName' => Cert::DN_COUNTRY,
                'stateOrProvinceName' => Cert::DN_STATE,
                'localityName' => Cert::DN_CITY,
                'organizationName' => Cert::DN_ORG,
                'emailAddress' => Cert::DN_EMAIL,
                'commonName' => $data['Username']);

            if (!cert_create(
                $cert,
                $cert['caref'],
                2048,
                3650,
                $dn,
                'sha256',
                'usr_cert'
            )) {
                throw new AppException('OVPN_312');
            }
            if(!is_array($config['cert'])){
                $config['cert'] = array();
            }
            $config['cert'][] = $cert;
            $user = array(
                'name'=> $data['Username'],
                'scope'=>'user',
                'descr'=>'openvpn user',
                'expires'=>'',
                'authorizedkeys'=>'',
                'ipsecpsk'=>'',
                'otp_seed'=>'',
                'cert'=>$cert['refid']
            );
            $user['uid']=$config['system']['nextuid']++;
            local_user_set_password($user, $data['Password']);

            $config['system']['user'][] = $user;

            local_user_set($user);
            local_user_set_groups($user, array('openvpn'));

            write_config();
            openvpn_configure_csc();
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
            $deluser = false;
            foreach($config['system']['user'] as $idx=>$user){
                if($user['name'] == $data['Username'] && 'openvpn user' == $user['descr']){
                    $deluser = $config['system']['user'][$idx];
                    unset($config['system']['user'][$idx]);
                }
            }
            if(false === $deluser){
                throw new AppException('OVPN_400');
            }
            local_user_del($deluser);

            write_config();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }

        return $result;
    }
}