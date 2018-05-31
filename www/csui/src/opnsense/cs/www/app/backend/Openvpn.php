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
            ,cert_depth,strictusercn,digest,disable,duplicate_cn,vpnid,reneg-sec,use-common-name,tlsauth_enable,ca,cert,prv,
            ,dns_domain_enable,dns_server_enable,shared_key";

     private static $client_copy_fields = "auth_user,auth_pass,disable,mode,protocol,interface
            ,local_port,server_addr,server_port,resolve_retry,remote_random,reneg-sec
            ,proxy_addr,proxy_port,proxy_user,proxy_passwd,proxy_authtype
            ,custom_options,ns_cert_type,dev_mode,crypto,digest,engine
            ,tunnel_network,tunnel_networkv6,remote_network,remote_networkv6,use_shaper
            ,compression,passtos,no_tun_ipv6,route_no_pull,route_no_exec,verbosity_level,tls,shared_key,ca,cert,prv";

     const SERVER_MODE = array('p2p_tls', 'p2p_shared_key', 'server_tls', 'server_user');
     const CLIENT_MODE = array('p2p_tls', 'p2p_shared_key');

     const PROXY_AUTHTYPE = array('none', 'basic', 'ntlm');
     const VPNID_SVR = 1;
     const VPNID_CLIENT = 2;
     const OVPN_SERVER_DESCR = 'CSG2000P_OVPN_SERVER';
     const USER_GROUP = 'openvpn';
     const USER_RELATE_PREFIX = 'openvpn user:';
     const USER_DESCR = 'openvpn user';
     const FILTER_LISTEN_ACCEPT_NAME = 'OpenVPN_LISTEN_ACCEPT';
     const FILTER_SUBNET_ACCEPT_NAME = 'OpenVPN_SUBNET_ACCEPT';



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
            $openvpnclient['caref'] = Cert::OVPN_CLIENT_CA_REFID;
            $openvpnclient['certref'] = Cert::OVPN_CLIENT_CERT_REFID;

            $config['openvpn']['openvpn-client'][] = $openvpnclient;

            foreach($config['ca'] as $idx=>$a_ca){
                if(Cert::OVPN_CLIENT_CA_REFID == $a_ca['refid'] || Cert::OVPN_CLIENT_CA_DESCR == $a_ca['descr']){
                    unset($config['ca'][$idx]);
                }
            }
            $config['ca'][] = array('refid' => Cert::OVPN_CLIENT_CA_REFID,
                'descr' => Cert::OVPN_CLIENT_CA_DESCR,
                'serial' => 3);
            foreach($config['cert'] as $idx=>$a_cert){
                if(Cert::OVPN_CLIENT_CERT_REFID == $a_cert['refid'] || Cert::OVPN_CLIENT_CERT_DESCR == $a_cert['descr']){
                    unset($config['cert'][$idx]);
                }
            }
            $config['cert'][] = array('refid'=>Cert::OVPN_CLIENT_CERT_REFID,
                'descr'=>Cert::OVPN_CLIENT_CERT_DESCR,
                'caref'=>Cert::OVPN_CLIENT_CA_REFID);
        }

    }

    private static function initOpenvpnSvrCa($idx){
        global $config;

        $ca = array('refid' => Cert::OVPN_SERVER_CA_REFID,
            'descr' => Cert::OVPN_SERVER_CA_DESCR,
            'serial' => 4);
        $dn = array(
            'countryName' => Cert::DN_COUNTRY,
            'stateOrProvinceName' => Cert::DN_STATE,
            'localityName' => Cert::DN_CITY,
            'organizationName' => Cert::DN_ORG,
            'emailAddress' => Cert::DN_EMAIL,
            'commonName' => Cert::OVPN_SERVER_CA_CN);
        if (!ca_create($ca, Cert::KEY_LEN, Cert::LIFE_TIME, $dn, Cert::DIGEST_ALG)) {
            throw new AppException('OVPN_98');
        }
        if(false!==$idx){
            $config['ca'][$idx] = $ca;
        }else{
            $config['ca'][] = $ca;
        }
    }

    private static function initOpenvpnSvrCert($idx){
        global $config;

        $cert = array('refid'=>Cert::OVPN_SERVER_CERT_REFID,
            'descr'=>Cert::OVPN_SERVER_CERT_DESCR,
            'caref'=>Cert::OVPN_SERVER_CA_REFID);
        $dn = array(
            'countryName' => Cert::DN_COUNTRY,
            'stateOrProvinceName' => Cert::DN_STATE,
            'localityName' => Cert::DN_CITY,
            'organizationName' => Cert::DN_ORG,
            'emailAddress' => Cert::DN_EMAIL,
            'commonName' => Cert::OVPN_SERVER_CERT_CN);

        if (!cert_create(
            $cert,
            $cert['caref'],
            2048,
            3650,
            $dn,
            'sha256',
            'server_cert'
        )) {
            throw new AppException('OVPN_99');
        }
        if(false !== $idx){
            $config['cert'][$idx] = $cert;
        }else{
            $config['cert'][] = $cert;
        }
    }

    private static function svrConfInit(){
        global $config;

        $updated = false;
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
            $openvpnsvr['description'] = Openvpn::OVPN_SERVER_DESCR;
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
            $openvpnsvr['caref'] = Cert::OVPN_SERVER_CA_REFID;
            $openvpnsvr['certref'] = Cert::OVPN_SERVER_CERT_REFID;
            $openvpnsvr['dh_length'] = 1024;
            $openvpnsvr['cert_depth'] = 1;
            $openvpnsvr['duplicate_cn'] = 1;

            $config['openvpn']['openvpn-server'][] = $openvpnsvr;
            $updated = true;
        }
        $create_cert = false;
        $ca_idx = false;
        $ca_create = true;
        foreach($config['ca'] as $idx=>$a_ca){
            if(Cert::OVPN_SERVER_CA_REFID == $a_ca['refid'] || Cert::OVPN_SERVER_CA_DESCR == $a_ca['descr']){
                if(empty($a_ca['crt']) || empty($a_ca['prv'])){
                    $ca_idx = $idx;
                    $create_cert = true;
                }else{
                    $ca_create = false;
                }
            }
        }
        if($ca_create){
            self::initOpenvpnSvrCa($ca_idx);
            $updated = true;
        }

        $cert_idx = false;
        $cert_create = true;
        foreach($config['cert'] as $idx=>$a_cert){
            if(Cert::OVPN_SERVER_CERT_REFID == $a_cert['refid'] || Cert::OVPN_SERVER_CERT_DESCR == $a_cert['descr']){
                if($create_cert || empty($a_cert['crt']) || empty($a_cert['prv'])){
                    $cert_idx = $idx;
                }else{
                    $cert_create = false;
                }
            }
        }
        if($cert_create){
            self::initOpenvpnSvrCert($cert_idx);
            $updated = true;
        }

        if($updated){
            write_config();
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
        if(!isset($openvpnClient['reneg-sec'])){
            $openvpnClient['reneg-sec'] = 3600;
        }
        if('p2p_tls' == $openvpnClient['mode']){
            $ca = Cert::getCa($openvpnClient['caref']);
            $cert = Cert::getCert($openvpnClient['certref']);
            if($ca){
                $openvpnClient['ca'] = $ca['crt'];
            }
            if($cert){
                $openvpnClient['cert'] = $cert['crt'];
                $openvpnClient['prv'] = $cert['prv'];
            }
        }
        if(isset($openvpnClient['caref'])){
            unset($openvpnClient['caref']);
        }
        if(isset($openvpnClient['certref'])) {
            unset($openvpnClient['certref']);
        }
        if(!isset($openvpnClient['tls'])){
            $openvpnClient['tls'] = '';
        }

        return $openvpnClient;
    }

    public static function getSvrStatus(){
        global $config;

        self::svrConfInit();

        $openvpnSvr = $config['openvpn']['openvpn-server'][0];
        if(!isset($openvpnSvr['disable']) && '1'!=$openvpnSvr['disable']){
            $openvpnSvr['disable'] = '0';
        }

        if(isset($openvpnSvr['tls']) && !empty($openvpnSvr['tls'])){
            $openvpnSvr['tlsauth_enable'] = 'yes';
        }else{
            $openvpnSvr['tlsauth_enable'] = 'no';
            $openvpnSvr['tls'] = '';
        }
        if(!isset($openvpnSvr['reneg-sec'])){
            $openvpnSvr['reneg-sec'] = 3600;
        }
        unset($openvpnSvr['caref']);
        unset($openvpnSvr['certref']);
        unset($openvpnSvr['description']);
        if(isset($openvpnSvr['local_group'])){
            unset($openvpnSvr['local_group']);
        }
        if(isset($openvpnSvr['authmode'])){
            unset($openvpnSvr['authmode']);
        }

        return $openvpnSvr;
    }

    private static function setFirewall(){
        global $config;

        foreach($config['filter']['rule'] as $idx=>$rule){
            if(Openvpn::FILTER_SUBNET_ACCEPT_NAME == $rule['descr'] || Openvpn::FILTER_LISTEN_ACCEPT_NAME==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }
        }
        if(!isset($config['openvpn']['openvpn-server'][0]['disable']) || 'yes'!=$config['openvpn']['openvpn-server'][0]['disable']){
            $config['filter']['rule'][] = array(
                'type'=>'pass',
                'interface'=>$config['openvpn']['openvpn-server'][0]['interface'],
                'ipprotocol'=>'inet',
                'statetype'=>'keep state',
                'protocol'=>strtolower($config['openvpn']['openvpn-server'][0]['protocol']),
                'descr'=>Openvpn::FILTER_LISTEN_ACCEPT_NAME,
                'source'=>array('any'=>1),
                'destination'=>array(
                    'network'=>$config['openvpn']['openvpn-server'][0]['interface'].'ip',
                    'port'=>$config['openvpn']['openvpn-server'][0]['local_port']),
            );
        }
        if((!isset($config['openvpn']['openvpn-server'][0]['disable']) || 'yes'!=$config['openvpn']['openvpn-server'][0]['disable']) ||
            (!isset($config['openvpn']['openvpn-client'][0]['disable']) || 'yes'!=$config['openvpn']['openvpn-client'][0]['disable'])) {
            $config['filter']['rule'][] = array(
                'type' => 'pass',
                'interface' => 'openvpn',
                'ipprotocol' => 'inet',
                'statetype' => 'keep state',
                'descr' => Openvpn::FILTER_SUBNET_ACCEPT_NAME,
                'source' => array('any' => '1'),
                'destination' => array('any' => 1,)
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
                    throw new AppException('params_error');
                }
            }
            if (isset($data['disable'])){
                if('yes' != $data['disable']) {
                    throw new AppException('enabled_param_error');
                }
            }
            if (!in_array($data['mode'], self::CLIENT_MODE)) {
                throw new AppException('server_mode_error');
            }
            if (!in_array($data['protocol'], array('TCP', 'UDP'))) {
                throw new AppException('protocol_param_error');
            }
            if (!in_array($data['dev_mode'], array('tun', 'tap'))) {
                throw new AppException('device_mode_error');
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('interface_param_error');
            }
            if(empty($data['server_addr'])){
                throw new AppException('remote_server_addr_no_empty');
            }
            $data['server_port'] = intval($data['server_port']);
            if($data['server_port']>65535 || $data['server_port']<1){
                throw new AppException('remote_server_port_error');
            }
            if(isset($data['resolve_retry']) && 'yes' != $data['resolve_retry']){
                throw new AppException('retry_dns_error');
            }
            if(isset($data['proxy_addr']) && !empty($data['proxy_addr'])){
                if(!isset($data['proxy_port'])){
                    throw new AppException('proxy_port_error');
                }
                $data['proxy_port'] = intval($data['proxy_port']);
                if($data['proxy_port']>65535 || $data['proxy_port']<1){
                    throw new AppException('proxy_port_error');
                }
            }else if(!empty($data['proxy_port'])){
                throw new AppException('proxy_port_error');
            }
            if(!in_array($data['proxy_authtype'], Openvpn::PROXY_AUTHTYPE)){
                throw new AppException('proxy_auth_error');
            }
            if('none' != $data['proxy_authtype'] && (empty($data['proxy_user']) || empty($data['proxy_passwd']))){
                throw new AppException('username_pwd_no_empty');
            }
            if(isset($data['local_port']) && !empty($data['local_port'])){
                $data['local_port'] = intval($data['local_port']);
                if($data['local_port']>65535 || $data['local_port']<1){
                    throw new AppException('local_port_range_1_65535');
                }
            }

            if('p2p_shared_key'!=$data['mode']){
                if(!is_numeric($data['reneg-sec'])){
                    throw new AppException('renegotiate_param_error');
                }else{
                    $data['reneg-sec'] = intval($data['reneg-sec']);
                    if($data['reneg-sec']<0){
                        throw new AppException('renegotiate_param_error');
                    }
                }
            }else if(isset($data['reneg-sec'])){
                unset($data['reneg-sec']);
            }

            if('p2p_tls' == $data['mode']){
                if(empty($data['ca'])){
                    throw new AppException('certificate_auth_error');
                }
                $ca = base64_decode($data['ca']);
                if(!$ca || !strstr($ca, "BEGIN CERTIFICATE") || !strstr($ca, "END CERTIFICATE")){
                    throw new AppException('certificate_auth_error');
                }
                if(empty($data['cert'])){
                    throw new AppException('client_ca_error');
                }
                $cert = base64_decode($data['cert']);
                if(!$cert || empty($cert) || !strstr($cert, "BEGIN CERTIFICATE") || !strstr($cert, "END CERTIFICATE")){
                    throw new AppException('client_ca_error');
                }
                if(empty($data['prv'])){
                    throw new AppException('private_key_data_error');
                }
                $prv = base64_decode($data['prv']);
                if(empty($prv) || !strstr($prv, "BEGIN PRIVATE KEY") || !strstr($prv, "END PRIVATE KEY")){
                    throw new AppException('private_key_data_error');
                }
                $ca_subject = cert_get_subject($ca, false);
                $subject = cert_get_subject($cert, false);
                $issuer = cert_get_issuer($cert, false);
                if($ca_subject != $issuer){
                    throw new AppException('peer_certificate_auth_error');
                }
            }else {
                if(empty($data['shared_key'])){
                    throw new AppException('pre_share_key_no_empty');
                }
            }

            $cipherlist = self::getCipherlist();
            if (!isset($cipherlist[$data['crypto']])) {
                throw new AppException('encryp_algorithm_error');
            }
            $digestlist = self::getDigestlist();
            if (!isset($digestlist[$data['digest']])) {
                throw new AppException('Certification_algorithm_error');
            }
            $engines = self::getEnginelist();
            if ('none'!=$data['engine'] && !isset($engines[$data['engine']])) {
                throw new AppException('hardware_encryption_error');
            }
            if(isset($data['use_shaper']) &&!empty($data['use_shaper'])){
                if(!is_numeric($data['use_shaper'])){
                    throw new AppException('hard_out_limit_error');
                }else{
                    $data['use_shaper'] = intval($data['use_shaper']);
                    if($data['use_shaper']<100 || $data['use_shaper']>104857600){
                        throw new AppException('hard_out_limit_error');
                    }
                }
            }

            if(!isset($openvpn_compression_modes[$data['compression']])){
                throw new AppException('compressed_error');
            }
            if (isset($data['passtos'])){
                if('yes' != $data['passtos']) {
                    throw new AppException('server_type_param_error');
                }
            }
            if (isset($data['route_no_pull'])){
                if('yes' != $data['route_no_pull']) {
                    throw new AppException('withdraw_route_error');
                }
            }
            if (isset($data['route_no_exec'])){
                if('yes' != $data['route_no_exec']) {
                    throw new AppException('add_or_remove_error');
                }
            }
            $data['verbosity_level'] = intval($data['verbosity_level']);
            if(!isset($openvpn_verbosity_level[$data['verbosity_level']])){
                throw new AppException('redundancy_level_error');
            }
            $data['vpnid'] = Openvpn::VPNID_CLIENT;
            $data['description'] = 'openvpnclient1';
            $data['no_tun_ipv6'] = 'yes';
            foreach($config['ca'] as $idx=>$ca){
                if(Cert::OVPN_CLIENT_CA_DESCR == $ca['descr']){
                    unset($config['ca'][$idx]);
                }
            }
            foreach($config['cert'] as $idx=>$cert){
                if(CERT::OVPN_CLIENT_CERT_DESCR == $cert['descr']){
                    unset($config['cert'][$idx]);
                }
            }
            if(empty($data['tls'])){
                unset($data['tls']);
            }

            if('p2p_tls' == $data['mode']){
                $config['ca'][] = array('refid' => Cert::OVPN_CLIENT_CA_REFID,
                    'descr' => Cert::OVPN_CLIENT_CA_DESCR,
                    'serial' => 3,
                    'crt' => $data['ca'],
                    'prv' => '');
                $config['cert'][] = array('refid'=>'5ac9f213dd1fa',
                    'descr'=>Cert::OVPN_CLIENT_CERT_DESCR,
                    'crt'=>$data['cert'],
                    'prv'=>$data['prv'],
                    'caref'=>Cert::OVPN_CLIENT_CA_REFID);
                $data['caref'] = Cert::OVPN_CLIENT_CA_REFID;
                $data['certref'] = Cert::OVPN_CLIENT_CERT_REFID;
            }
            if(isset($data['ca'])){
                unset($data['ca']);
            }
            if(isset($data['cert'])) {
                unset($data['cert']);
            }
            if(isset($data['prv'])) {
                unset($data['prv']);
            }
            $config['openvpn']['openvpn-client']=array($data);

            self::setFirewall();
            write_config();

            openvpn_configure_single($data['vpnid']);
            filter_configure();
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
                    throw new AppException('params_error');
                }
            }
            if (isset($data['disable'])){
                if('yes' != $data['disable']) {
                    throw new AppException('enabled_param_error');
                }
            }
            if('yes'!=$data['disable']){
                unset($data['disable']);
            }
            if (!in_array($data['mode'], Openvpn::SERVER_MODE)) {
                throw new AppException('server_mode_error');
            }
            if (!in_array($data['protocol'], array('TCP', 'UDP'))) {
                throw new AppException('protocol_param_error');
            }
            $data['local_port'] = intval($data['local_port']);
            if ($data['local_port']>65535 || $data['local_port']<=0) {
                throw new AppException('port_range_1_65535');
            }
            if (!in_array($data['dev_mode'], array('tun', 'tap'))) {
                throw new AppException('device_mode_error');
            }
            $cipherlist = self::getCipherlist();
            if (!isset($cipherlist[$data['crypto']])) {
                throw new AppException('encryption_algorithm_error');
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('interface_param_error');
            }
            if('p2p_shared_key'!=$data['mode']){
                if(!in_array($data['dh_length'], array(1024, 2048, 4096))){
                    throw new AppException('dh_length_error');
                }
                $data['cert_depth'] = intval($data['cert_depth']);
                if($data['cert_depth']>5 || $data['cert_depth']<1){
                    throw new AppException('certificate_depth_error');
                }
            }else{
                if(isset($data['dh_length'])){
                    unset($data['dh_length']);
                }
                if(isset($data['cert_depth'])){
                    unset($data['cert_depth']);
                }
            }
            $digestlist = self::getDigestlist();
            if (!isset($digestlist[$data['digest']])) {
                throw new AppException('certification_algorithm_error');
            }
            $engines = openvpn_get_engines();
            if ('none'!=$data['engine'] && !in_array($data['engine'], $engines)) {
                throw new AppException('hardware_encryption_error');
            }

            if(!empty($data['tunnel_network']) && !Util::checkCidr($data['tunnel_network'], false, 'ipv4')){
                throw new AppException('network_tunnelv4_error');
            }
            $data['tunnel_networkv6'] = '';
            if(!empty($data['local_network']) && !Util::checkCidr($data['local_network'], true, 'ipv4')){
                throw new AppException('local_networkv4_error');
            }
            $data['local_networkv6'] = '';
            if(!empty($data['remote_network']) && !Util::checkCidr($data['remote_network'], true, 'ipv4')){
                throw new AppException('remote_networkv4_error');
            }
            $data['remote_networkv6'] = '';
            $data['maxclients'] = intval($data['maxclients']);
            if($data['maxclients']<1 || $data['maxclients']>1024){
                throw new AppException('concurrent_range_1_1024');
            }
            if(!isset($openvpn_compression_modes[$data['compression']])){
                throw new AppException('compressed_error');
            }
            if(isset($data['client2client']) && 'yes'==$data['client2client']){
                $data['client2client'] = 'yes';
            }else{
                unset($data['client2client']);
            }
            if(isset($data['duplicate_cn']) && 'yes'==$data['duplicate_cn']){
                $data['duplicate_cn'] = 'yes';
            }else{
                unset($data['duplicate_cn']);
            }
            $data['no_tun_ipv6'] = 'yes';
            if(isset($data['pool_enable']) && 'yes'==$data['pool_enable']){
                $data['pool_enable'] = 'yes';
            }else{
                unset($data['pool_enable']);
            }
            if(isset($data['dynamic_ip']) && 'yes'==$data['dynamic_ip']){
                $data['dynamic_ip'] = 'yes';
            }else{
                unset($data['dynamic_ip']);
            }
            if(isset($data['topology_subnet']) && 'yes'==$data['topology_subnet']){
                $data['topology_subnet'] = 'yes';
            }else{
                unset($data['topology_subnet']);
            }
            if(isset($data['dns_domain_enable']) && 'yes'==$data['dns_domain_enable']){
                $data['dns_domain_enable'] = 'yes';
            }else{
                unset($data['dns_domain_enable']);
            }
            if(isset($data['dns_server_enable']) && 'yes'==$data['dns_server_enable']){
                $data['dns_server_enable'] = 'yes';
            }else{
                unset($data['dns_server_enable']);
            }
            for($i=1; $i<=4 ;$i++){
                if(empty($data['dns_server'.$i])){
                    for($j=$i; $j<=4; $j++){
                        unset($data['dns_server'.$j]);
                    }
                }else if(!is_ipaddr($data['dns_server'.$i])){
                    echo $data['dns_server'.$i];
                    throw new AppException('dns_server_error');
                }
            }
            if(isset($data['push_register_dns']) && 'yes'==$data['push_register_dns']){
                $data['push_register_dns'] = 'yes';
            }else{
                unset($data['push_register_dns']);
            }
            if(empty($data['ntp_server1'])){
                unset($data['ntp_server1']);
                unset($data['ntp_server2']);
            }else{
                if(!is_ipaddr($data['ntp_server1'])){
                    throw new AppException('ntp_server_error');
                }
                if(empty($data['ntp_server2'])){
                    unset($data['ntp_server2']);
                }else{
                    if(!is_ipaddr($data['ntp_server2'])){
                        throw new AppException('ntp_server_error');
                    }
                }
            }
            $data['verbosity_level'] = intval($data['verbosity_level']);
            if(!isset($openvpn_verbosity_level[$data['verbosity_level']])){
                throw new AppException('redundancy_level_error');
            }
            $data['reneg-sec'] = intval($data['reneg-sec']);

            if(isset($data['ca'])){
                unset($data['ca']);
            };
            if(isset($data['cert'])){
                unset($data['cert']);
            }
            if(isset($data['prv'])){
                unset($data['prv']);
            }
            $data['caref'] = Cert::OVPN_SERVER_CA_REFID;
            $data['certref'] = Cert::OVPN_SERVER_CERT_REFID;

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
            $data['description'] = Openvpn::OVPN_SERVER_DESCR;
            if('server_user' == $data['mode']){
                $data['authmode'] = 'Local Database';
                $data['local_group'] = 'openvpn';
            }else{
                if(isset($data['authmode'])){
                    unset($data['authmode']);
                }
                if(isset($data['local_group'])){
                    unset($data['local_group']);
                }
            }
            if('p2p_shared_key'== $data['mode'] || 'server_tls'== $data['mode']){
                unset($data['reneg-sec']);
            }
            if('p2p_shared_key'== $data['mode']){
                if(empty(trim($data['shared_key']))){
                    $data['shared_key'] = openvpn_create_key();
                    $data['shared_key'] = base64_encode($data['shared_key']);
                }
            }else{
                if('yes' == $data['tlsauth_enable']){
                    $data['tls'] = 'Iw0KIyAyMDQ4IGJpdCBPcGVuVlBOIHN0YXRpYyBrZXkNCiMNCi0tLS0tQkVHSU4gT3BlblZQTiBTdGF0aWMga2V5IFYxLS0tLS0NCjA1MjExNTYzN2MyY2M1N2ZjMWFhMjc4ZGY0NDk4NzhiDQozNmFmYTY5MzVlNmNjNGNkYWViZTQ0YzI3OTFiYjA2OA0KMWQ0MTI3NmZkZGEzYjc3NTM1MjFkZDc2YWUyNjM0NmMNCmM5YmE2ZDI1ZGIyY2JkOGQwNDk3YmFlZTM3ODdmMzViDQo0Mzc0ZGMxZWViOTliMzFlOGY0ZGEwODg3MTg5ZDFjZQ0KMGE5YzNlYjUzZTY1N2ZmYzQ4NzZkN2ZkNjdiNDQ0NTgNCjZhMWZmMTI4NzkzOWIzMjFhNjI4MTIwYWZlMzdiOThhDQozZGM2ZmY5ZTRjNjQ0NTI1YmRkZTFiYjRlNjUzNDYyYg0KNjFhMDI5MTJjMWUzMDg2NTIxNjcwNGI2MzBhNzBlMGENCmI4YjNiMTU5MjJkMWJjN2FiZmYyNGZlMmEyOTFmYmVhDQpmN2VhZmM4NTBlNDI1ZWZkNTA5MTFmMGVkMTI0NDYzZQ0KZWExZDhkZTAzYWEyYWI1ZGMwYmUzYTkzZjg0YmQ1MzINCmI1ZTU3ZDRmNWE0NmFkZjA1YTk1YzliZjVjYTJlNDQ4DQo5ZTUzNzU5ZTI0MGZjMDFhZDNhMzkxYWE5MTQ1Zjc1MA0KOTZlNGM3OWRhMjA2MWMxMTdlYWUyOTYyYmI1ZTk0NjENCjlhN2Y0ZDE1MGM1NGVmMGIyNzE4ODExNmQzM2U0N2RmDQotLS0tLUVORCBPcGVuVlBOIFN0YXRpYyBrZXkgVjEtLS0tLQ0K';
                }
            }
            $config['openvpn']['openvpn-server'][0] = $data;
            self::setFirewall();

            write_config();
            openvpn_configure_single($data['vpnid']);
            openvpn_configure_csc();
            filter_configure();
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
        if(is_array($config['openvpn']['openvpn-csc'])){
            foreach($config['openvpn']['openvpn-csc'] as $idx=>$csc){
                $cscs[$csc['common_name']] = $csc;
            }
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
        $enable = 1;
        if(isset($config['openvpn']['openvpn-server'][0]['disable']) && 'yes'==$config['openvpn']['openvpn-server'][0]['disable']){
            $enable = 0;
        }

        return array('users'=>$openvpnUsers, 'server_enable'=>$enable, 'mode'=>$config['openvpn']['openvpn-server'][0]['mode']);
    }

    public static function setUser($data){
        global $config;

        $result = 0;
        try {
            foreach ($data as $var=>$val){
                if(!is_array($val)){
                    $data[$var] = trim($val);
                }
            }
            if(!isset($data['Username']) || strlen($data['Username'])<1){
                throw new AppException('username_no_empty');
            }
            $user_update_idx = false;
            foreach($config['system']['user'] as $idx=>$tmp_user){
                if($tmp_user['name'] == $data['Username'] && 'user' == $tmp_user['scope'] && $tmp_user['descr'] == Openvpn::USER_DESCR){
                    $user_update_idx = $idx;
                    break ;
                }
            }
            $csc = false;
            if(isset($data['config'])){
                $user_config = $data['config'];
                $csc = array();
                $csc['custom_options'] = $user_config['custom_options'];
                if(empty($data['Username'])){
                    throw new AppException('username_no_empty');
                }else{
                    $csc['common_name'] = $data['Username'];
                }
                if(!$user_update_idx && (!isset($data['Password']) || strlen($data['Password'])<1)){
                    throw new AppException('password_no_empty');
                }
                if(isset($user_config['block'])){
                    if('yes' != $user_config['block']) {
                        throw new AppException('intercepty_connect_error');
                    }
                    $csc['block'] = 'yes';
                }
                $csc['description'] = Openvpn::USER_RELATE_PREFIX.$data['Username'];
                if(!empty($user_config['tunnel_network']) && !Util::checkCidr($user_config['tunnel_network'], false, 'ipv4')){
                    throw new AppException('tunnelv4_network_error');
                }
                $csc['tunnel_network'] = $user_config['tunnel_network'];
                if(!empty($user_config['local_network']) && !Util::checkCidr($user_config['local_network'], true, 'ipv4')){
                    throw new AppException('localv4_network_error');
                }
                $csc['local_network'] = $user_config['local_network'];
                if(!empty($user_config['remote_network']) && !Util::checkCidr($user_config['remote_network'], true, 'ipv4')){
                    throw new AppException('remotev4_network_error');
                }
                $csc['remote_network'] = $user_config['remote_network'];
                if(isset($user_config['gwredir'])){
                    if('yes' != $user_config['gwredir']) {
                        throw new AppException('redirect_gateway_error');
                    }
                    $csc['gwredir'] = 'yes';
                }
                if(isset($user_config['push_reset'])){
                    if('yes' != $user_config['push_reset']) {
                        throw new AppException('server_define_error');
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
                        throw new AppException('dns_server_error');
                    }
                    $csc['dns_server'.$i] = $user_config['dns_server'.$i];
                }
                for($i=1; $i<=2; $i++){
                    if(!isset($user_config['ntp_server'.$i])){
                        break;
                    }
                    if(!is_ipaddr($user_config['ntp_server'.$i])){
                        throw new AppException('time_server_error');
                    }
                    $csc['ntp_server'.$i] = $user_config['ntp_server'.$i];
                }
                $csc['ovpn_servers'] = Openvpn::VPNID_SVR;
            }

            if(false !== $user_update_idx){
                self::updateUser($user_update_idx, $data, $csc);
            }else{
                self::addUser($data, $csc);
            }

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

    private static function updateUser($user_idx, $data, $csc){
        global $config;

        if(false !== $csc){
            if(!is_array($config['openvpn']['openvpn-csc'])){
                $config['openvpn']['openvpn-csc'] = array();
            }
            $updated = false;
            foreach($config['openvpn']['openvpn-csc'] as $idx=>$tmp_csc){
                if($tmp_csc['common_name'] == $csc['common_name']){
                    $config['openvpn']['openvpn-csc'][$idx] = $csc;
                    $updated = true;
                    break ;
                }
            }
            if(!$updated){
                $config['openvpn']['openvpn-csc'][] = $csc;
            }
        }else{
            if(isset($config['openvpn']['openvpn-csc']) && is_array($config['openvpn']['openvpn-csc'])){
                foreach($config['openvpn']['openvpn-csc'] as $idx=>$tmp_csc){
                    if($tmp_csc['common_name'] == $data['Username']){
                        unset($config['openvpn']['openvpn-csc'][$idx]);
                    }
                }
            }
        }
        if(isset($data['Password']) && strlen($data['Password'])>0){
            local_user_set_password($config['system']['user'][$user_idx], $data['Password']);
        }
    }

    private static function addUser($data, $csc){
        global $config;

        if(false !== $csc){
            if(!is_array($config['openvpn']['openvpn-csc'])){
                $config['openvpn']['openvpn-csc'] = array();
            }
            $config['openvpn']['openvpn-csc'][] = $csc;
        }
        foreach($config['cert'] as $idx=>$cert){
            if(Cert::OVPN_SERVER_CA_REFID == $cert['caref'] && Openvpn::USER_RELATE_PREFIX.$data['Username'] == $cert['desct']){
                throw new AppException('user_exist');
                break;
            }
        }
        $cert = array();
        $cert['refid'] = uniqid();
        $cert['descr'] = 'openvpn user:'.$data['Username'];
        $cert['caref'] = Cert::OVPN_SERVER_CA_REFID;

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
            Cert::KEY_LEN,
            Cert::LIFE_TIME,
            $dn,
            Cert::DIGEST_ALG,
            'usr_cert'
        )) {
            throw new AppException('create_user_ca_error');
        }
        if(!is_array($config['cert'])){
            $config['cert'] = array();
        }
        $config['cert'][] = $cert;
        $user = array(
            'name'=> $data['Username'],
            'scope'=>'user',
            'descr'=>Openvpn::USER_DESCR,
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
    }

    public static function delUser($data){
        global $config;
        $result = 0;
        try {
            $deluser = false;
            foreach($config['system']['user'] as $idx=>$user){
                if($user['name'] == $data['Username'] && Openvpn::USER_DESCR == $user['descr']){
                    $deluser = $config['system']['user'][$idx];
                    unset($config['system']['user'][$idx]);
                    foreach($config['cert'] as $idx=>$cert){
                        if(Openvpn::USER_RELATE_PREFIX.$user['name'] == $cert['descr']){
                            unset($config['cert'][$idx]);
                        }
                    }
                    foreach($config['openvpn']['openvpn-csc'] as $idx=>$csc){
                        if(Openvpn::USER_RELATE_PREFIX.$user['name'] == $csc['description']){
                            unset($config['openvpn']['openvpn-csc'][$idx]);
                        }
                    }
                }
            }
            if(false === $deluser){
                throw new AppException('del_user_fail');
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

    private static function openvpn_client_export_prefix($srvid, $usrid = null, $crtid = null)
    {
        global $config;

        // lookup server settings
        $settings = $config['openvpn']['openvpn-server'][$srvid];
        if (empty($settings)) {
            return false;
        }
        if (!empty($settings['disable'])) {
            return false;
        }

        $host = empty($config['system']['hostname']) ? "openvpn" : $config['system']['hostname'];
        $prot = ($settings['protocol'] == 'UDP' ? 'udp' : $settings['protocol']);
        $port = $settings['local_port'];

        $filename_addition = "";
        if ($usrid && is_numeric($usrid)) {
            $filename_addition = "-".$config['system']['user'][$usrid]['name'];
        } elseif ($crtid && is_numeric($crtid)) {
            $filename_addition = "-" . str_replace(' ', '_', cert_get_cn($config['cert'][$crtid]['crt']));
        }

        return "{$host}-{$prot}-{$port}{$filename_addition}";
    }

    private static function openvpn_client_export_validate_config($srvid, $usrid, $crtid)
    {
        global $config, $input_errors;
        $nokeys = false;

        // lookup server settings
        $settings = $config['openvpn']['openvpn-server'][$srvid];
        if (empty($settings)) {
            $input_errors[] = gettext("Could not locate server configuration.");
            return false;
        }
        if (!empty($settings['disable'])) {
            $input_errors[] = gettext("You cannot export for disabled servers.");
            return false;
        }

        // lookup server certificate info
        $server_cert = lookup_cert($settings['certref']);
        if (!$server_cert) {
            $input_errors[] = gettext("Could not locate server certificate.");
        } else {
            $server_ca = ca_chain($server_cert);
            if (empty($server_ca)) {
                $input_errors[] = gettext("Could not locate the CA reference for the server certificate.");
            }
            $servercn = cert_get_cn($server_cert['crt']);
        }

        // lookup user info
        if (is_numeric($usrid)) {
            $user = $config['system']['user'][$usrid];
            if (!$user) {
                $input_errors[] = gettext("Could not find user settings.");
            }
        }

        // lookup user certificate info
        if ($settings['mode'] == "server_tls_user") {
            if ($settings['authmode'] == "Local Database") {
                $cert = $user['cert'][$crtid];
            } else {
                $cert = $config['cert'][$crtid];
            }
            if (!$cert) {
                $input_errors[] = gettext("Could not find client certificate.");
            } else {
                // If $cert is not an array, it's a certref not a cert.
                if (!is_array($cert)) {
                    $cert = lookup_cert($cert);
                }
            }
        } elseif (($settings['mode'] == "server_tls") || (($settings['mode'] == "server_tls_user") && ($settings['authmode'] != "Local Database"))) {
            $cert = $config['cert'][$crtid];
            if (!$cert) {
                $input_errors[] = gettext("Could not find client certificate.");
            }
        } else {
            $nokeys = true;
        }

        if ($input_errors) {
            return false;
        }

        return array($settings, $server_cert, $server_ca, $servercn, $user, $cert, $nokeys);
    }

    private static function openvpn_client_export_build_remote_lines($settings, $useaddr, $interface, $expformat, $nl) {
        global $config;
        $remotes = array();
        if (($useaddr == "serveraddr") || ($useaddr == "servermagic") || ($useaddr == "servermagichost")) {
            $interface = $settings['interface'];
            if (!empty($settings['ipaddr']) && is_ipaddr($settings['ipaddr'])) {
                $server_host = $settings['ipaddr'];
            } else {
                if (!$interface || ($interface == "any")) {
                    $interface = "wan";
                }
                if (in_array(strtolower($settings['protocol']), array("udp6", "tcp6"))) {
                    $server_host = get_interface_ipv6($interface);
                } else {
                    $server_host = get_interface_ip($interface);
                }
            }
        } else if ($useaddr == "serverhostname" || empty($useaddr)) {
            $server_host = empty($config['system']['hostname']) ? "" : "{$config['system']['hostname']}.";
            $server_host .= "{$config['system']['domain']}";
        } else {
            $server_host = $useaddr;
        }

        $proto = strtolower($settings['protocol']);
        if (strtolower(substr($settings['protocol'], 0, 3)) == "tcp") {
            $proto .= "-client";
        }

        if (($expformat == "inlineios") && ($proto == "tcp-client")) {
            $proto = "tcp";
        }

        if (($useaddr == "servermagic") || ($useaddr == "servermagichost")) {
            $destinations = openvpn_client_export_find_port_forwards($server_host, $settings['local_port'], $proto, true, ($useaddr == "servermagichost"));
            foreach ($destinations as $dest) {
                $remotes[] = "remote {$dest['host']} {$dest['port']} {$dest['proto']}";
            }
        } else {
            $remotes[] = "remote {$server_host} {$settings['local_port']} {$proto}";
        }

        return implode($nl, $remotes);
    }


    private static function openvpn_client_export_config($srvid, $usrid, $crtid, $useaddr, $verifyservercn, $randomlocalport, $usetoken, $nokeys = false, $proxy, $expformat = "baseconf", $outpass = "", $skiptls=false, $doslines=false, $openvpnmanager, $advancedoptions = "")
    {
        global $config, $input_errors;

        $nl = ($doslines) ? "\r\n" : "\n";
        $conf = "";

        $validconfig = self::openvpn_client_export_validate_config($srvid, $usrid, $crtid);
        if (!$validconfig) {
            return false;
        }

        list($settings, $server_cert, $server_ca, $servercn, $user, $cert, $nokeys) = $validconfig;

        // determine basic variables
        $remotes = self::openvpn_client_export_build_remote_lines($settings, $useaddr, $interface, $expformat, $nl);
        $server_port = $settings['local_port'];
        $cipher = $settings['crypto'];
        $digest = !empty($settings['digest']) ? $settings['digest'] : "SHA1";

        // add basic settings
        $devmode = empty($settings['dev_mode']) ? "tun" : $settings['dev_mode'];
        if (($expformat != "inlinedroid") && ($expformat != "inlineios")) {
            $conf .= "dev {$devmode}{$nl}";
        }
        if (!empty($settings['tunnel_networkv6']) && ($expformat != "inlinedroid") && ($expformat != "inlineios")) {
            $conf .= "tun-ipv6{$nl}";
        }
        $conf .= "persist-tun{$nl}";
        $conf .= "persist-key{$nl}";

        //  if ((($expformat != "inlinedroid") && ($expformat != "inlineios")) && ($proto == "tcp"))
        //    $conf .= "proto tcp-client{$nl}";
        $conf .= "cipher {$cipher}{$nl}";
        $conf .= "auth {$digest}{$nl}";
        $conf .= "tls-client{$nl}";
        $conf .= "client{$nl}";
        if (isset($settings['reneg-sec']) && $settings['reneg-sec'] != "") {
            $conf .= "reneg-sec {$settings['reneg-sec']}{$nl}";
        }
        if (($expformat != "inlinedroid") && ($expformat != "inlineios")) {
            $conf .= "resolv-retry infinite{$nl}";
        }
        $conf .= "$remotes{$nl}";

        /* Use a random local port, otherwise two clients will conflict if they run at the same time.
          May not be supported on older clients (Released before May 2010) */
        if (($randomlocalport != 0) && (substr($expformat, 0, 7) != "yealink") && ($expformat != "snom")) {
            $conf .= "lport 0{$nl}";
        }

        /* This line can cause problems with auth-only setups and also with Yealink/Snom phones
          since they are stuck on an older OpenVPN version that does not support this feature. */
        if (!empty($servercn) && !$nokeys) {
            switch ($verifyservercn) {
                case "none":
                    break;
                case "tls-remote":
                    $conf .= "tls-remote {$servercn}{$nl}";
                    break;
                case "tls-remote-quote":
                    $conf .= "tls-remote \"{$servercn}\"{$nl}";
                    break;
                default:
                    if ((substr($expformat, 0, 7) != "yealink") && ($expformat != "snom")) {
                        $conf .= "verify-x509-name \"{$servercn}\" name{$nl}";
                    }
            }
        }

        if (!empty($proxy)) {
            if ($proxy['proxy_type'] == "http") {
                if (strtoupper(substr($settings['protocol'], 0, 3)) == "UDP") {
                    $input_errors[] = gettext("This server uses UDP protocol and cannot communicate with HTTP proxy.");
                    return;
                }
                $conf .= "http-proxy {$proxy['ip']} {$proxy['port']} ";
            }
            if ($proxy['proxy_type'] == "socks") {
                $conf .= "socks-proxy {$proxy['ip']} {$proxy['port']} ";
            }
            if ($proxy['proxy_authtype'] != "none") {
                if (!isset($proxy['passwdfile'])) {
                    $proxy['passwdfile'] = openvpn_client_export_prefix($srvid, $usrid, $crtid) . "-proxy";
                }
                $conf .= " {$proxy['passwdfile']} {$proxy['proxy_authtype']}";
            }
            $conf .= "{$nl}";
        }

        // add user auth settings
        switch($settings['mode']) {
            case 'server_user':
            case 'server_tls_user':
                $conf .= "auth-user-pass{$nl}";
                break;
        }

        // add key settings
        $prefix = self::openvpn_client_export_prefix($srvid, $usrid, $crtid);
        $cafile = "{$prefix}-ca.crt";
        if ($nokeys == false) {
            if ($expformat == "yealink_t28") {
                $conf .= "ca /yealink/config/openvpn/keys/ca.crt{$nl}";
                $conf .= "cert /yealink/config/openvpn/keys/client1.crt{$nl}";
                $conf .= "key /yealink/config/openvpn/keys/client1.key{$nl}";
            } elseif ($expformat == "yealink_t38g") {
                $conf .= "ca /phone/config/openvpn/keys/ca.crt{$nl}";
                $conf .= "cert /phone/config/openvpn/keys/client1.crt{$nl}";
                $conf .= "key /phone/config/openvpn/keys/client1.key{$nl}";
            } elseif ($expformat == "yealink_t38g2") {
                $conf .= "ca /config/openvpn/keys/ca.crt{$nl}";
                $conf .= "cert /config/openvpn/keys/client1.crt{$nl}";
                $conf .= "key /config/openvpn/keys/client1.key{$nl}";
            } elseif ($expformat == "snom") {
                $conf .= "ca /openvpn/ca.crt{$nl}";
                $conf .= "cert /openvpn/phone1.crt{$nl}";
                $conf .= "key /openvpn/phone1.key{$nl}";
            } elseif ($usetoken) {
                $conf .= "ca {$cafile}{$nl}";
                $conf .= "cryptoapicert \"SUBJ:{$user['name']}\"{$nl}";
            } elseif (substr($expformat, 0, 6) != "inline") {
                $conf .= "pkcs12 {$prefix}.p12{$nl}";
            }
        } else if ($settings['mode'] == "server_user") {
            if (substr($expformat, 0, 6) != "inline") {
                $conf .= "ca {$cafile}{$nl}";
            }
        }

        if ($settings['tls'] && !$skiptls) {
            if ($expformat == "yealink_t28") {
                $conf .= "tls-auth /yealink/config/openvpn/keys/ta.key 1{$nl}";
            } elseif ($expformat == "yealink_t38g") {
                $conf .= "tls-auth /phone/config/openvpn/keys/ta.key 1{$nl}";
            } elseif ($expformat == "yealink_t38g2") {
                $conf .= "tls-auth /config/openvpn/keys/ta.key 1{$nl}";
            } elseif ($expformat == "snom") {
                $conf .= "tls-auth /openvpn/ta.key 1{$nl}";
            } elseif (substr($expformat, 0, 6) != "inline") {
                $conf .= "tls-auth {$prefix}-tls.key 1{$nl}";
            }
        }

        // Prevent MITM attacks by verifying the server certificate.
        // - Disable for now, it requires the server cert to include special options
        //$conf .= "remote-cert-tls server{$nl}";

        if (is_array($server_cert) && ($server_cert['crt'])) {
            $purpose = cert_get_purpose($server_cert['crt'], true);
            if ($purpose['server'] == 'Yes') {
                $conf .= "remote-cert-tls server{$nl}";
            }
        }

        // add optional settings
        if (!empty($settings['compression'])) {
            $conf .= "comp-lzo {$settings['compression']}{$nl}";
        }

        if ($settings['passtos']) {
            $conf .= "passtos{$nl}";
        }

        if ($openvpnmanager) {
            if (!empty($settings['client_mgmt_port'])) {
                $client_mgmt_port = $settings['client_mgmt_port'];
            } else {
                $client_mgmt_port = 166;
            }
            $conf .= $nl;
            $conf .= "# dont terminate service process on wrong password, ask again{$nl}";
            $conf .= "auth-retry interact{$nl}";
            $conf .= "# open management channel{$nl}";
            $conf .= "management 127.0.0.1 {$client_mgmt_port}{$nl}";
            $conf .= "# wait for management to explicitly start connection{$nl}";
            $conf .= "management-hold{$nl}";
            $conf .= "# query management channel for user/pass{$nl}";
            $conf .= "management-query-passwords{$nl}";
            $conf .= "# disconnect VPN when management program connection is closed{$nl}";
            $conf .= "management-signal{$nl}";
            $conf .= "# forget password when management disconnects{$nl}";
            $conf .= "management-forget-disconnect{$nl}";
            $conf .= $nl;
        };

        // add advanced options
        $advancedoptions = str_replace("\r\n", "\n", $advancedoptions);
        $advancedoptions = str_replace("\n", $nl, $advancedoptions);
        $advancedoptions = str_replace(";", $nl, $advancedoptions);
        $conf .= $advancedoptions;
        $conf .= $nl;

        switch ($expformat) {
            case "zip":
                // create template directory
                $tempdir = "/tmp/{$prefix}";
                @mkdir($tempdir, 0700, true);

                file_put_contents("{$tempdir}/{$prefix}.ovpn", $conf);

                $cafile = "{$tempdir}/{$cafile}";
                file_put_contents("{$cafile}", $server_ca);
                if ($settings['tls']) {
                    $tlsfile = "{$tempdir}/{$prefix}-tls.key";
                    file_put_contents($tlsfile, base64_decode($settings['tls']));
                }

                // write key files
                if ($settings['mode'] != "server_user") {
                    $crtfile = "{$tempdir}/{$prefix}-cert.crt";
                    file_put_contents($crtfile, base64_decode($cert['crt']));
                    $keyfile = "{$tempdir}/{$prefix}.key";
                    file_put_contents($keyfile, base64_decode($cert['prv']));

                    // convert to pkcs12 format
                    $p12file = "{$tempdir}/{$prefix}.p12";
                    if ($usetoken) {
                        openvpn_client_pem_to_pk12($p12file, $outpass, $crtfile, $keyfile);
                    } else {
                        openvpn_client_pem_to_pk12($p12file, $outpass, $crtfile, $keyfile, $cafile);
                    }
                }
                $command = "cd " . escapeshellarg("{$tempdir}/..")
                    . " && /usr/local/bin/zip -r "
                    . escapeshellarg("/tmp/{$prefix}-config.zip")
                    . " " . escapeshellarg($prefix);
                exec($command);
                // Remove temporary directory
                exec("rm -rf " . escapeshellarg($tempdir));
                return "/tmp/{$prefix}-config.zip";
                break;
            case "inline":
            case "inlinedroid":
            case "inlineios":
                // Inline CA
                $conf .= "<ca>{$nl}" . trim($server_ca) . "{$nl}</ca>{$nl}";
                if ($settings['mode'] != "server_user") {
                    // Inline Cert
                    $conf .= "<cert>{$nl}" . trim(base64_decode($cert['crt'])) . "{$nl}</cert>{$nl}";
                    // Inline Key
                    $conf .= "<key>{$nl}" . trim(base64_decode($cert['prv'])) . "{$nl}</key>{$nl}";
                } else {
                    // Work around OpenVPN Connect assuming you have a client cert even when you don't need one
                    $conf .= "setenv CLIENT_CERT 0{$nl}";
                }
                // Inline TLS
                if ($settings['tls']) {
                    $conf .= "<tls-auth>{$nl}" . trim(base64_decode($settings['tls'])) . "{$nl}</tls-auth>{$nl} key-direction 1{$nl}";
                }
                return $conf;
                break;
            case "yealink_t28":
            case "yealink_t38g":
            case "yealink_t38g2":
                // create template directory
                $tempdir = "/tmp/{$prefix}";
                $keydir  = "{$tempdir}/keys";
                mkdir($tempdir, 0700, true);
                mkdir($keydir, 0700, true);

                file_put_contents("{$tempdir}/vpn.cnf", $conf);

                $cafile = "{$keydir}/ca.crt";
                file_put_contents("{$cafile}", $server_ca);
                if ($settings['tls']) {
                    $tlsfile = "{$keydir}/ta.key";
                    file_put_contents($tlsfile, base64_decode($settings['tls']));
                }

                // write key files
                if ($settings['mode'] != "server_user") {
                    $crtfile = "{$keydir}/client1.crt";
                    file_put_contents($crtfile, base64_decode($cert['crt']));
                    $keyfile = "{$keydir}/client1.key";
                    file_put_contents($keyfile, base64_decode($cert['prv']));
                }
                exec("tar -C {$tempdir} -cf /tmp/client.tar ./keys ./vpn.cnf");
                // Remove temporary directory
                exec("rm -rf {$tempdir}");
                return '/tmp/client.tar';
            case "snom":
                // create template directory
                $tempdir = "/tmp/{$prefix}";
                mkdir($tempdir, 0700, true);

                file_put_contents("{$tempdir}/vpn.cnf", $conf);

                $cafile = "{$tempdir}/ca.crt";
                file_put_contents("{$cafile}", $server_ca);
                if ($settings['tls']) {
                    $tlsfile = "{$tempdir}/ta.key";
                    file_put_contents($tlsfile, base64_decode($settings['tls']));
                }

                // write key files
                if ($settings['mode'] != "server_user") {
                    $crtfile = "{$tempdir}/phone1.crt";
                    file_put_contents($crtfile, base64_decode($cert['crt']));
                    $keyfile = "{$tempdir}/phone1.key";
                    file_put_contents($keyfile, base64_decode($cert['prv']));
                }
                exec("cd {$tempdir}/ && tar -cf /tmp/vpnclient.tar *");
                // Remove temporary directory
                exec("rm -rf {$tempdir}");
                return '/tmp/vpnclient.tar';
            default:
                return $conf;
        }
    }

    private static function openvpn_client_export_sharedkey_config($srvid, $useaddr, $proxy, $zipconf = false)
    {
        global $config, $input_errors;

        // lookup server settings
        $settings = $config['openvpn']['openvpn-server'][$srvid];
        if (empty($settings)) {
            $input_errors[] = gettext("Could not locate server configuration.");
            return false;
        }
        if ($settings['disable']) {
            $input_errors[] = gettext("You cannot export for disabled servers.");
            return false;
        }

        // determine basic variables
        if ($useaddr == "serveraddr") {
            $interface = $settings['interface'];
            if (!empty($settings['ipaddr']) && is_ipaddr($settings['ipaddr'])) {
                $server_host = $settings['ipaddr'];
            } else {
                if (!$interface) {
                    $interface = "wan";
                }
                if (in_array(strtolower($settings['protocol']), array("udp6", "tcp6"))) {
                    $server_host = get_interface_ipv6($interface);
                } else {
                    $server_host = get_interface_ip($interface);
                }
            }
        } elseif ($useaddr == "serverhostname" || empty($useaddr)) {
            $server_host = empty($config['system']['hostname']) ? "" : "{$config['system']['hostname']}.";
            $server_host .= "{$config['system']['domain']}";
        } else {
            $server_host = $useaddr;
        }

        $server_port = $settings['local_port'];

        $proto = strtolower($settings['protocol']);
        if (strtolower(substr($settings['protocol'], 0, 3)) == "tcp") {
            $proto .= "-client";
        }

        $cipher = $settings['crypto'];
        $digest = !empty($settings['digest']) ? $settings['digest'] : "SHA1";

        // add basic settings
        $conf  = "dev tun\n";
        if (!empty($settings['tunnel_networkv6'])) {
            $conf .= "tun-ipv6\n";
        }
        $conf .= "persist-tun\n";
        $conf .= "persist-key\n";
        $conf .= "proto {$proto}\n";
        $conf .= "cipher {$cipher}\n";
        $conf .= "auth {$digest}\n";
        $conf .= "pull\n";
        $conf .= "resolv-retry infinite\n";
        if (isset($settings['reneg-sec']) && $settings['reneg-sec'] != "") {
            $conf .= "reneg-sec {$settings['reneg-sec']}\n";
        }
        $conf .= "remote {$server_host} {$server_port}\n";
        if (!empty($settings['local_network'])) {
            $conf .= openvpn_gen_routes($settings['local_network'], 'ipv4');
        }
        if (!empty($settings['local_networkv6'])) {
            $conf .= openvpn_gen_routes($settings['local_networkv6'], 'ipv6');
        }
        if (!empty($settings['tunnel_network'])) {
            list($ip, $mask) = explode('/', $settings['tunnel_network']);
            $mask = gen_subnet_mask($mask);
            $baselong = ip2long32($ip) & ip2long($mask);
            $ip1 = long2ip32($baselong + 1);
            $ip2 = long2ip32($baselong + 2);
            $conf .= "ifconfig $ip2 $ip1\n";
        }
        $conf .= "keepalive 10 60\n";
        $conf .= "ping-timer-rem\n";

        if (!empty($proxy)) {
            if ($proxy['proxy_type'] == "http") {
                if ($proto == "udp") {
                    $input_errors[] = gettext("This server uses UDP protocol and cannot communicate with HTTP proxy.");
                    return;
                }
                $conf .= "http-proxy {$proxy['ip']} {$proxy['port']} ";
            }
            if ($proxy['proxy_type'] == "socks") {
                $conf .= "socks-proxy {$proxy['ip']} {$proxy['port']} ";
            }
            if ($proxy['proxy_authtype'] != "none") {
                if (!isset($proxy['passwdfile'])) {
                    $proxy['passwdfile'] = self::openvpn_client_export_prefix($srvid) . "-proxy";
                }
                $conf .= " {$proxy['passwdfile']} {$proxy['proxy_authtype']}";
            }
            $conf .= "\n";
        }

        // add key settings
        $prefix = self::openvpn_client_export_prefix($srvid);
        $shkeyfile = "{$prefix}.secret";
        $conf .= "secret {$shkeyfile}\n";

        // add optional settings
        if ($settings['compression']) {
            $conf .= "comp-lzo\n";
        }
        if ($settings['passtos']) {
            $conf .= "passtos\n";
        }

        if ($zipconf == true) {
            // create template directory
            $tempdir = "/tmp/{$prefix}";
            mkdir($tempdir, 0700, true);
            file_put_contents("{$tempdir}/{$prefix}.ovpn", $conf);
            $shkeyfile = "{$tempdir}/{$shkeyfile}";
            file_put_contents("{$shkeyfile}", base64_decode($settings['shared_key']));
            if (!empty($proxy['passwdfile'])) {
                $pwdfle = "{$proxy['user']}\n";
                $pwdfle .= "{$proxy['password']}\n";
                file_put_contents("{$tempdir}/{$proxy['passwdfile']}", $pwdfle);
            }
            exec("cd {$tempdir}/.. && /usr/local/bin/zip -r /tmp/{$prefix}-config.zip {$prefix}");
            // Remove temporary directory
            exec("rm -rf {$tempdir}");
            return "/tmp/{$prefix}-config.zip";
        } else {
            file_put_contents("/tmp/{$prefix}.ovpn", $conf);
            return "/tmp/{$prefix}.ovpn";
        }
    }

    private static function getUser($username){
        global $config;

        $user = false;
        foreach($config['system']['user'] as $tmp_user){
            if($tmp_user['name'] == $username){
                $user = $tmp_user;
                break ;
            }
        }

        return $user;
    }

    public static function exportClientConf($data){
        global $config;

        try{
            if(!isset($data['username']) || empty($data['username'])){
                throw new AppException('export_cfg_error');
            }
            self::svrConfInit();

            $srvid = 0;
            $usrid = false;
            $crtid = false;
            $useaddr = 'serveraddr';
            $verifyservercn = 'auto';
            $randomlocalport = '1';
            $usetoken = '0';
            $proxy = '';
            $expformat = 'inline';
            $password = '';
            $openvpnmanager = null;
            $advancedoptions = '';
            if('p2p_shared_key' == $config['openvpn']['openvpn-server'][0]['mode']) {
                $exp_path = self::openvpn_client_export_sharedkey_config($srvid, $useaddr, $proxy, false);
                $fp = @fopen($exp_path, 'r');
                $exp_path = '';
                while (false !== ($line = fgets($fp))) {
                    if (0 === strpos($line, 'pull')) {
                        continue;
                    } else if (0 === strpos($line, 'reneg-sec')) {
                        continue;
                    } else if (0 === strpos($line, 'secret')) {
                        $exp_path .= "<secret>\n";
                        $secret = base64_decode($config['openvpn']['openvpn-server'][0]['shared_key']);
                        if ("\n" == $secret[strlen($secret) - 1]) {
                            $exp_path .= $secret;
                        } else {
                            $exp_path .= $secret . "\n";
                        }
                        $exp_path .= "</secret>\n";
                    } else {
                        $exp_path .= $line;
                    }
                }
            }else if('p2p_tls' == $config['openvpn']['openvpn-server'][0]['mode']){
                $user = self::getUser($data['username']);
                if(!$user){
                    throw new AppException('export_conf_error');
                }
                $cert = Cert::getCert($user['cert'][0]);
                if(!$cert){
                    throw new AppException('export_conf_error');
                }
                $ca = Cert::getCa($cert['caref']);
                if(!$ca){
                    throw new AppException('export_conf_error');
                }
                $t = time();
                /*$zip = new ZipArchive;
                $res = $zip->open('/usr/local/opnsense/cs/tmp/'.$data['username'].'_'.$t.'.zip');
                if ($res !== true) {
                    throw new AppException('export_conf_error');
                }
                $zip->addFromString('ca.crt', base64_decode($ca['crt']));
                $zip->addFromString($data['username'].'.crt', base64_decode($cert['crt']));
                $zip->addFromString($data['username'].'.key', base64_decode($cert['prv']));
                $zip->close();

                $exp_path = file_get_contents('/usr/local/opnsense/cs/tmp/'.$data['username'].'_'.$t.'.zip');
                unlink('/usr/local/opnsense/cs/tmp/'.$data['username'].'_'.$t.'.zip');*/
                $path = '/tmp/'.$data['username'].'_'.$t;
                mkdir($path);
                file_put_contents($path.'/ca.crt', base64_decode($ca['crt']));
                file_put_contents($path.'/'.$data['username'].'.crt', base64_decode($cert['crt']));
                file_put_contents($path.'/'.$data['username'].'.key', base64_decode($cert['prv']));
                chdir($path);
                exec(sprintf("zip %s.zip ca.crt %s.crt %s.key", $data['username'], $data['username'], $data['username']));
                chdir('/tmp');
                $exp_path = file_get_contents($path.'/'.$data['username'].'.zip');
                exec('rm -rf /tmp/'.$data['username'].'_'.$t);
            }else{
                if("server_user" == $config['openvpn']['openvpn-server'][0]['mode']){
                    $nokeys = true;
                } else {
                    $nokeys = false;
                    $user = self::getUser($data['username']);
                    if(!$user){
                        throw new AppException('export_conf_error');
                    }
                    $group = false;
                    foreach($config['system']['group'] as $tmp_group){
                        if($tmp_group['name'] == Openvpn::USER_GROUP){
                            $group = $tmp_group;
                            break;
                        }
                    }
                    if(!$group){
                        throw new AppException('openvpn_group_error');
                    }
                    if(!in_array($user['uid'], $group['member'])){
                        throw new AppException('user_no_in_group');
                    }
                    foreach($config['cert'] as $idx=>$cert){
                        if(in_array($cert['refid'],$user['cert'])){
                            $crtid = $idx;
                            break ;
                        }
                    }
                }

                $exp_path = self::openvpn_client_export_config($srvid, $usrid, $crtid, $useaddr, $verifyservercn,
                        $randomlocalport, $usetoken, $nokeys, $proxy, $expformat, $password,
                        false, false, $openvpnmanager, $advancedoptions);
            }

            $exp_name = self::openvpn_client_export_prefix(0, false, false);

            if (!$exp_path) {
                throw new AppException('export_conf_error');
            }

            $exp_size = strlen($exp_path);

            header('Pragma: ');
            header('Cache-Control: ');
            if('p2p_tls' == $config['openvpn']['openvpn-server'][0]['mode']){
                $exp_name .= '_'.$data['username'].'_key.zip';
                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename={$exp_name}");
            }else{
                $exp_name .= '.ovpn';
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename={$exp_name}");
            }

            header("Content-Length: $exp_size");
            header("X-Content-Type-Options: nosniff");
            echo $exp_path;
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }
    }

    public static function getEncryInfo(){
        $result = array();
        $result['cipherList'] = self::getCipherlist();
        $result['digestList'] = self::getDigestlist();
        $result['engineList'] = self::getEnginelist();

        return $result;
    }

    public static function getStatus(){
        $servers = openvpn_get_active_servers();
        $sk_servers = openvpn_get_active_servers("p2p");
        $clients = openvpn_get_active_clients();

        return array('servers'=>$servers, 'sk_servers'=>$sk_servers, 'clients'=>$clients);
    }

    public static function getLogs(){
        exec("/usr/local/sbin/clog /var/log/openvpn.log| grep -v \"CLOG\" | grep -v \"\033\" | /usr/bin/tail -r -n 100", $logarr);
        $logs = implode("\n", $logarr);

        echo $logs;
    }
}