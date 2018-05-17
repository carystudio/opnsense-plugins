<?php
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("filter.inc");
require_once("interfaces.inc");
require_once('plugins.inc.d/dyndns.inc');

use \OPNsense\Core\Backend;

class Proxy extends Csbackend
{
    protected static $ERRORCODE = array(
        'PROXY_100'=>'开启参数不正确'
    );

    const NAT_RULE_NAME_HTTP = 'Proxy_Transport_Http';
    const NAT_RULE_NAME_HTTPS = 'Proxy_Transport_Https';
    const RULE_ASSOCID_HTTP = 'nat_5ad171d77ecde1.72402306';
    const RULE_ASSOCID_HTTPS = 'nat_5ad2aaf0c48076.10932785';
    const FILTER_RULE_NAME_HTTP = 'NAT Proxy_Transport_Http';
    const FILTER_RULE_NAME_HTTPS = 'NAT Proxy_Transport_Https';

    const FORWARDEDFORHANDLING = array('', 'on','off', 'transparent', 'truncate');
    const URIWHITESPACEHANFLING = array('', 'strip', 'deny', 'allow', 'encode', 'chop');

    private static function setFirewall(){
        global $config;

        //clean filter and nat rules
        if ($config['nat']['rule']) {
            foreach ($config['nat']['rule'] as $idx => $rule) {
                if (Proxy::RULE_ASSOCID_HTTP == $rule['associated-rule-id'] ||
                    Proxy::RULE_ASSOCID_HTTPS == $rule['associated-rule-id']
                ) {
                    unset($config['nat']['rule'][$idx]);
                }
            }
        }
        if ($config['filter']['rule']) {
            foreach ($config['filter']['rule'] as $idx => $rule) {
                if (Proxy::RULE_ASSOCID_HTTP == $rule['associated-rule-id'] ||
                    Proxy::RULE_ASSOCID_HTTPS == $rule['associated-rule-id']
                ) {
                    unset($config['filter']['rule'][$idx]);
                }
            }
        }

        if(isset($config['OPNsense']['proxy']['general']['enabled']) &&
            '1' == $config['OPNsense']['proxy']['general']['enabled']
        ){//add nat and filter rules
            if(isset($config['OPNsense']['proxy']['forward']['port'])){
                $proxy_http_port = intval($config['OPNsense']['proxy']['forward']['port']);
            }else{
                $proxy_http_port = 3128;
            }
            if(isset($config['OPNsense']['proxy']['forward']['sslbumpport'])){
                $proxy_https_port = intval($config['OPNsense']['proxy']['forward']['sslbumpport']);
            }else{
                $proxy_https_port = 3129;
            }
            if('1'== $config['OPNsense']['proxy']['forward']['transparentMode']){
                $nat_rule_http = array(
                    'protocol'=>'tcp',
                    'interface'=>'lan',
                    'ipprotocol'=>'inet',
                    'descr'=>Proxy::FILTER_RULE_NAME_HTTP,
                    'tag'=>'',
                    'tagged'=>'',
                    'poolopts'=>'',
                    'associated-rule-id'=>Proxy::RULE_ASSOCID_HTTP,
                    'target'=>'127.0.0.1',
                    'local-port'=>$proxy_http_port,
                    'source'=>array('network'=>'lan'),
                    'destination'=>array('network'=>'(self)', 'not'=>'1', 'port'=>80)
                );
                $nat_rule_https = array(
                    'protocol'=>'tcp',
                    'interface'=>'lan',
                    'ipprotocol'=>'inet',
                    'descr'=>Proxy::FILTER_RULE_NAME_HTTPS,
                    'tag'=>'',
                    'tagged'=>'',
                    'poolopts'=>'',
                    'associated-rule-id'=>Proxy::RULE_ASSOCID_HTTPS,
                    'target'=>'127.0.0.1',
                    'local-port'=>$proxy_https_port,
                    'source'=>array('network'=>'lan'),
                    'destination'=>array('network'=>'(self)', 'not'=>'1', 'port'=>443)
                );
                $filter_rule_http = array(
                    'source'=>array('network'=>'lan'),
                    'interface'=>'lan',
                    'protocol'=>'tcp',
                    'ipprotocol'=>'inet',
                    'destination'=>array('address'=>'127.0.0.1', 'port'=>$proxy_http_port),
                    'descr'=>Proxy::FILTER_RULE_NAME_HTTP,
                    'associated-rule-id'=>Proxy::RULE_ASSOCID_HTTP
                );
                $filter_rule_https = array(
                    'source'=>array('network'=>'lan'),
                    'interface'=>'lan',
                    'protocol'=>'tcp',
                    'ipprotocol'=>'inet',
                    'destination'=>array('address'=>'127.0.0.1', 'port'=>$proxy_https_port),
                    'descr'=>Proxy::FILTER_RULE_NAME_HTTPS,
                    'associated-rule-id'=>Proxy::RULE_ASSOCID_HTTPS
                );
                $config['nat']['rule'][] = $nat_rule_http;
                $config['filter']['rule'][] = $filter_rule_http;
                if('1' == $config['OPNsense']['proxy']['forward']['sslbump']){
                    $config['nat']['rule'][] = $nat_rule_https;
                    $config['filter']['rule'][] = $filter_rule_https;
                }
            }
        }
    }

    private static function initConf(){
        global $config;

        $proxy_data = 'a:3:{s:11:"@attributes";a:1:{s:7:"version";s:5:"1.0.0";}s:7:"general";a:13:{s:7:"enabled";s:1:"0";s:7:"icpPort";s:0:"";s:7:"logging";a:3:{s:6:"enable";a:2:{s:9:"accessLog";s:1:"1";s:8:"storeLog";s:1:"1";}s:12:"ignoreLogACL";s:0:"";s:6:"target";s:0:"";}s:19:"alternateDNSservers";s:0:"";s:10:"dnsV4First";s:1:"0";s:20:"forwardedForHandling";s:2:"on";s:21:"uriWhitespaceHandling";s:5:"strip";s:12:"useViaHeader";s:1:"1";s:15:"suppressVersion";s:1:"0";s:12:"VisibleEmail";s:21:"admin@localhost.local";s:15:"VisibleHostname";s:0:"";s:5:"cache";a:1:{s:5:"local";a:9:{s:7:"enabled";s:1:"0";s:9:"directory";s:16:"/var/squid/cache";s:9:"cache_mem";s:3:"256";s:19:"maximum_object_size";s:0:"";s:4:"size";s:3:"100";s:2:"l1";s:2:"16";s:2:"l2";s:3:"256";s:20:"cache_linux_packages";s:1:"0";s:21:"cache_windows_updates";s:1:"0";}}s:7:"traffic";a:5:{s:7:"enabled";s:1:"0";s:15:"maxDownloadSize";s:4:"2048";s:13:"maxUploadSize";s:4:"1024";s:26:"OverallBandwidthTrotteling";s:4:"1024";s:17:"perHostTrotteling";s:3:"256";}}s:7:"forward";a:17:{s:10:"interfaces";s:3:"lan";s:4:"port";s:4:"3128";s:11:"sslbumpport";s:4:"3129";s:7:"sslbump";s:1:"0";s:10:"sslurlonly";s:1:"0";s:14:"sslcertificate";s:0:"";s:14:"sslnobumpsites";s:0:"";s:25:"ssl_crtd_storage_max_size";s:1:"4";s:16:"sslcrtd_children";s:1:"5";s:13:"ftpInterfaces";s:0:"";s:7:"ftpPort";s:4:"2121";s:18:"ftpTransparentMode";s:1:"0";s:25:"addACLforInterfaceSubnets";s:1:"1";s:15:"transparentMode";s:1:"0";s:3:"acl";a:10:{s:14:"allowedSubnets";s:0:"";s:12:"unrestricted";s:0:"";s:11:"bannedHosts";s:0:"";s:9:"whiteList";s:0:"";s:9:"blackList";s:0:"";s:7:"browser";s:0:"";s:8:"mimeType";s:0:"";s:9:"safePorts";s:133:"80:http,21:ftp,443:https,70:gopher,210:wais,1025-65535:unregistered ports,280:http-mgmt,488:gss-http,591:filemaker,777:multiling http";s:8:"sslPorts";s:9:"443:https";s:10:"remoteACLs";a:2:{s:10:"blacklists";s:0:"";s:10:"UpdateCron";s:0:"";}}s:4:"icap";a:11:{s:6:"enable";s:1:"0";s:10:"RequestURL";s:24:"icap://[::1]:1344/avscan";s:11:"ResponseURL";s:24:"icap://[::1]:1344/avscan";s:12:"SendClientIP";s:1:"1";s:12:"SendUsername";s:1:"0";s:14:"EncodeUsername";s:1:"0";s:14:"UsernameHeader";s:10:"X-Username";s:13:"EnablePreview";s:1:"1";s:11:"PreviewSize";s:4:"1024";s:10:"OptionsTTL";s:2:"60";s:7:"exclude";s:0:"";}s:14:"authentication";a:4:{s:6:"method";s:0:"";s:5:"realm";s:29:"OPNsense proxy authentication";s:14:"credentialsttl";s:1:"2";s:8:"children";s:1:"5";}}}';
        $values = unserialize($proxy_data);
        if(is_array($values)){
            $config['OPNsense']['proxy'] = $values;
            self::setFirewall();
            write_config();

            return true;
        }else{
            return false;
        }
    }

    public static function getConf() {
        global $config;

        if(!isset($config['OPNsense']['proxy'])){
            self::initConf();
        }

        $proxy = $config['OPNsense']['proxy'];
        unset($proxy['@attributes']);
        unset($proxy['general']['icpPort']);
        unset($proxy['general']['logging']['target']);
        unset($proxy['general']['cache']['local']['enabled']);
        unset($proxy['general']['cache']['local']['size']);
        unset($proxy['general']['cache']['local']['directory']);
        unset($proxy['general']['cache']['local']['l1']);
        unset($proxy['general']['cache']['local']['l2']);
        unset($proxy['general']['cache']['local']['maximum_object_size']);
        unset($proxy['forward']['icap']);
        unset($proxy['forward']['ftpInterfaces']);
        unset($proxy['forward']['ftpPort']);
        unset($proxy['forward']['ftpTransparentMode']);
        unset($proxy['forward']['acl']['remoteACLs']);

        return $proxy;
    }

    private static function status()
    {
        global $config;

        $backend = new Backend();
        $response = $backend->configdRun("proxy status");

        if (strpos($response, "not running") > 0) {
            if ((string)$config['OPNsense']['proxy']['general']['enabled'] == '1') {
                $status = "stopped";
            } else {
                $status = "disabled";
            }
        } elseif (strpos($response, "is running") > 0) {
            $status = "running";
        } elseif ((string)$config['OPNsense']['proxy']['general']['enabled'] == '0') {
            $status = "disabled";
        } else {
            $status = "unkown";
        }

        return array("status" => $status);
    }

    private static function configure(){
        global $config;

        $backend = new Backend();
        $runStatus = self::status();

        // some operations can not be performed by a squid -k reconfigure,
        // try to determine if we need a stop/start here
        if (is_file('/var/squid/ssl_crtd.id')) {
            $prev_sslbump_cert = trim(file_get_contents('/var/squid/ssl_crtd.id'));
        } else {
            $prev_sslbump_cert = "";
        }
        if ($config['OPNsense']['proxy']['forward']['sslcertificate'] != $prev_sslbump_cert) {
            $force_restart = true;
        }

        // stop squid when disabled
        if ($runStatus['status'] == "running" &&
            ((string)$config['OPNsense']['proxy']['general']['enabled'] == '0' || $force_restart)) {
            $backend->configdRun("proxy stop");
        }

        // generate template
        $backend->configdRun('template reload OPNsense/Proxy');

        // (res)start daemon
        if ((string)$config['OPNsense']['proxy']['general']['enabled'] == 1) {
            if ($runStatus['status'] == "running" && !$force_restart) {
                $backend->configdRun("proxy reconfigure");
            } else {
                $backend->configdRun("proxy start");
            }
        }

        filter_configure();
    }

    public static function setConf($data){
        global $config;

        $result = 0;
        try{
            $proxy = array();
            self::initConf();
            if(!isset($data['general']['enabled']) || !Util::check0and1($data['general']['enabled'])){
                throw new AppException('PROXY_100');
            }
            if('0'==$data['general']['enabled']){
                $config['OPNsense']['proxy']['general']['enabled'] = 0;
            }else{
                $conf_data = $config['OPNsense']['proxy'];
                $conf_data['general']['enabled'] = '1';
                if(!isset($data['general']['logging']['enable']['accessLog']) ||
                    !Util::check0and1($data['general']['logging']['enable']['accessLog'])){
                    throw new AppException('PROXY_101');
                }
                $conf_data['general']['logging']['enable']['accessLog'] = $data['general']['logging']['enable']['accessLog'];

                if(!isset($data['general']['logging']['enable']['storeLog']) ||
                    !Util::check0and1($data['general']['logging']['enable']['storeLog'])){
                    throw new AppException('PROXY_102');
                }
                $conf_data['general']['logging']['enable']['storeLog'] = $data['general']['logging']['enable']['storeLog'];

                if(!isset($data['general']['dnsV4First']) ||
                    !Util::check0and1($data['general']['dnsV4First'])){
                    throw new AppException('PROXY_103');
                }
                $conf_data['general']['dnsV4First'] = $data['general']['dnsV4First'];

                if(!isset($data['general']['forwardedForHandling']) ||
                    !in_array($data['general']['forwardedForHandling'], Proxy::FORWARDEDFORHANDLING)){
                    throw new AppException('PROXY_104');
                }
                $conf_data['general']['forwardedForHandling'] = $data['general']['forwardedForHandling'];

                if(!isset($data['general']['uriWhitespaceHandling']) ||
                    !in_array($data['general']['uriWhitespaceHandling'], Proxy::URIWHITESPACEHANFLING)){
                    throw new AppException('PROXY_105');
                }
                $conf_data['general']['uriWhitespaceHandling'] = $data['general']['uriWhitespaceHandling'];

                if(!isset($data['general']['suppressVersion']) ||
                    !Util::check0and1($data['general']['suppressVersion'])){
                    throw new AppException('PROXY_106');
                }
                $conf_data['general']['suppressVersion'] = $data['general']['suppressVersion'];

                if(isset($data['general']['VisibleEmail']) && false === filter_var($data['general']['VisibleEmail'], FILTER_VALIDATE_EMAIL)){
                    throw new AppException('PROXY_107');
                }
                $conf_data['general']['VisibleEmail'] = $data['general']['VisibleEmail'];

                if(!isset($data['general']['cache']['local']['cache_mem']) ||
                    !is_numeric($data['general']['cache']['local']['cache_mem'])){
                    throw new AppException('PROXY_108');
                }
                $conf_data['general']['cache']['local']['cache_mem'] = $data['general']['cache']['local']['cache_mem'];

                $data['general']['cache']['local']['cache_mem'] = intval($data['general']['cache']['local']['cache_mem']);
                if($data['general']['cache']['local']['cache_mem']>2048 ||
                    $data['general']['cache']['local']['cache_mem']<128){
                    throw new AppException('PROXY_108');
                }
                $conf_data['general']['cache']['local']['cache_mem'] = $data['general']['cache']['local']['cache_mem'];

                if(!isset($data['general']['cache']['local']['cache_linux_packages']) ||
                    !Util::check0and1($data['general']['cache']['local']['cache_linux_packages'])){
                    throw new AppException('PROXY_109');
                }
                $conf_data['general']['cache']['local']['cache_linux_packages'] = $data['general']['cache']['local']['cache_linux_packages'];

                if(!isset($data['general']['cache']['local']['cache_windows_updates']) ||
                    !Util::check0and1($data['general']['cache']['local']['cache_windows_updates'])){
                    throw new AppException('PROXY_110');
                }
                $conf_data['general']['cache']['local']['cache_windows_updates'] = $data['general']['cache']['local']['cache_windows_updates'];
                //cache local disabled, set default value
                $conf_data['general']['cache']['local']['enabled'] = '0';
                $conf_data['general']['cache']['local']['directory'] = '/var/squid/cache';
                $conf_data['general']['cache']['local']['maximum_object_size'] = '';
                $conf_data['general']['cache']['local']['size'] = 100;
                $conf_data['general']['cache']['local']['l1'] = 16;
                $conf_data['general']['cache']['local']['l2'] = 256;

                if(!isset($data['general']['traffic']['enabled']) ||
                    !Util::check0and1($data['general']['traffic']['enabled'])){
                    throw new AppException('PROXY_111');
                }
                $conf_data['general']['traffic']['enabled'] = $data['general']['traffic']['enabled'];
                if('0' != $data['general']['traffic']['enabled']){
                    if(!isset($data['general']['traffic']['maxDownloadSize']) ||
                        (!empty($data['general']['traffic']['maxDownloadSize']) && !is_numeric($data['general']['traffic']['maxDownloadSize']))
                    ){
                        throw new AppException('PROXY_112');
                    }
                    $conf_data['general']['traffic']['maxDownloadSize'] = $data['general']['traffic']['maxDownloadSize'];

                    if(!isset($data['general']['traffic']['maxUploadSize']) ||
                        (!empty($data['general']['traffic']['maxUploadSize']) && !is_numeric($data['general']['traffic']['maxUploadSize']))
                    ){
                        throw new AppException('PROXY_113');
                    }
                    $conf_data['general']['traffic']['maxUploadSize'] = $data['general']['traffic']['maxUploadSize'];

                    if(!isset($data['general']['traffic']['OverallBandwidthTrotteling']) ||
                        (!empty($data['general']['traffic']['OverallBandwidthTrotteling']) && !is_numeric($data['general']['traffic']['OverallBandwidthTrotteling']))
                    ){
                        throw new AppException('PROXY_114');
                    }
                    $conf_data['general']['traffic']['OverallBandwidthTrotteling'] = $data['general']['traffic']['OverallBandwidthTrotteling'];

                    if(!isset($data['general']['traffic']['perHostTrotteling']) ||
                        (!empty($data['general']['traffic']['perHostTrotteling']) && !is_numeric($data['general']['traffic']['perHostTrotteling']))
                    ){
                        throw new AppException('PROXY_115');
                    }
                    $conf_data['general']['traffic']['perHostTrotteling'] = $data['general']['traffic']['perHostTrotteling'];

                    if(empty($data['general']['traffic']['OverallBandwidthTrotteling']) &&
                        !empty($data['general']['traffic']['perHostTrotteling'])){
                        throw new AppException('PROXY_116');
                    }
                    $conf_data['general']['traffic']['OverallBandwidthTrotteling'] = $data['general']['traffic']['OverallBandwidthTrotteling'];

                    if(empty($data['general']['traffic']['perHostTrotteling']) &&
                        !empty($data['general']['traffic']['OverallBandwidthTrotteling'])){
                        throw new AppException('PROXY_116');
                    }
                    $conf_data['general']['traffic']['perHostTrotteling'] = $data['general']['traffic']['perHostTrotteling'];
                }
                $conf_data['forward']['interfaces'] = 'lan';
                if(!isset($data['forward']['port']) ||
                    !is_numeric($data['forward']['port'])){
                    throw new AppException('PROXY_117');
                }
                $data['forward']['port'] = intval($data['forward']['port']);
                if($data['forward']['port']>65535 || $data['forward']['port']<1){
                    throw new AppException('PROXY_117');
                }
                $conf_data['forward']['port'] = $data['forward']['port'];

                if(!isset($data['forward']['sslbumpport']) ||
                    !is_numeric($data['forward']['sslbumpport'])){
                    throw new AppException('PROXY_117');
                }
                $data['forward']['sslbumpport'] = intval($data['forward']['sslbumpport']);
                if($data['forward']['sslbumpport']>65535 || $data['forward']['sslbumpport']<1 ||
                    $data['forward']['sslbumpport'] == $data['forward']['port']){
                    throw new AppException('PROXY_117');
                }
                $conf_data['forward']['sslbumpport'] = $data['forward']['sslbumpport'];
                $conf_data['forward']['sslcertificate'] = '592fa6d7bf05a';

                if(!isset($data['forward']['sslbump']) ||
                    !Util::check0and1($data['forward']['sslbump'])
                ){
                    throw new AppException('PROXY_118');
                }
                $conf_data['forward']['sslbump'] = $data['forward']['sslbump'];

                if(!isset($data['forward']['sslurlonly']) ||
                    !Util::check0and1($data['forward']['sslurlonly'])
                ){
                    throw new AppException('PROXY_119');
                }
                $conf_data['forward']['sslurlonly'] = $data['forward']['sslurlonly'];

                if(!isset($data['forward']['ssl_crtd_storage_max_size']) ||
                    !is_numeric($data['forward']['ssl_crtd_storage_max_size'])){
                    throw new AppException('PROXY_120');
                }
                $conf_data['forward']['ssl_crtd_storage_max_size'] = $data['forward']['ssl_crtd_storage_max_size'];

                if(!isset($data['forward']['sslcrtd_children']) ||
                    !is_numeric($data['forward']['sslcrtd_children'])){
                    throw new AppException('PROXY_120');
                }
                $conf_data['forward']['sslcrtd_children'] = $data['forward']['sslcrtd_children'];

                if(!isset($data['forward']['addACLforInterfaceSubnets']) ||
                    !Util::check0and1($data['forward']['addACLforInterfaceSubnets'])
                ){
                    throw new AppException('PROXY_121');
                }
                $conf_data['forward']['addACLforInterfaceSubnets'] = $data['forward']['addACLforInterfaceSubnets'];

                if(!isset($data['forward']['transparentMode']) ||
                    !Util::check0and1($data['forward']['transparentMode'])
                ){
                    throw new AppException('PROXY_122');
                }
                $conf_data['forward']['transparentMode'] = $data['forward']['transparentMode'];

                if(!isset($data['forward']['acl']['safePorts'])){
                    throw new AppException('PROXY_123');
                }
                $safePorts = explode(',', $data['forward']['acl']['safePorts']);
                foreach($safePorts as $safePort){
                    $safePort_arr1 = explode(':', $safePort);
                    $safePort_arr2 = explode('-', $safePort_arr1[0]);
                    if(count($safePort_arr2)>2|| !is_numeric($safePort_arr2[0]) ||
                        (isset($safePort_arr2[1]) && !is_numeric($safePort_arr2[1]))){
                        throw new AppException('PROXY_123');
                    }
                }
                $conf_data['forward']['acl']['safePorts'] = $data['forward']['acl']['safePorts'];

                if(!isset($data['forward']['acl']['sslPorts'])){
                    throw new AppException('PROXY_124');
                }
                if(!empty($data['forward']['acl']['sslPorts'])){
                    $sslPorts = explode(',', $data['forward']['acl']['sslPorts']);
                    foreach($sslPorts as $sslPort){
                        $sslPort_arr1 = explode(':', $sslPort);
                        $sslPort_arr2 = explode('-', $sslPort_arr1[0]);
                        if(count($sslPort_arr2)>2|| !is_numeric($sslPort_arr2[0]) ||
                            (isset($sslPort_arr2[1]) && !is_numeric($sslPort_arr2[1]))){
                            throw new AppException('PROXY_124');
                        }
                    }
                }
                $conf_data['forward']['acl']['sslPorts'] = $data['forward']['acl']['sslPorts'];
                //no check data
                $conf_data['forward']['acl']['allowedSubnets'] = $data['forward']['acl']['allowedSubnets'];
                $conf_data['forward']['acl']['unrestricted'] = $data['forward']['acl']['unrestricted'];
                $conf_data['forward']['acl']['bannedHosts'] = $data['forward']['acl']['bannedHosts'];
                $conf_data['forward']['acl']['whiteList'] = $data['forward']['acl']['whiteList'];
                $conf_data['forward']['acl']['blackList'] = $data['forward']['acl']['blackList'];
                $conf_data['forward']['acl']['browser'] = $data['forward']['acl']['browser'];
                $conf_data['forward']['acl']['mimeType'] = $data['forward']['acl']['mimeType'];

                //ftp proxy disabled, set default value
                $conf_data['forward']['ftpInterfaces'] = '';
                $conf_data['forward']['ftpPort'] = 2121;
                $conf_data['forward']['ftpTransparentMode'] = 0;

                $conf_data['forward']['authentication'] = array('realm'=>'CSG2000P proxy authentication',
                    'credentialsttl'=>2,'children'=>'5');
                $conf_data['forward']['icap'] = array(
                    'enable'=>0,
                    'RequestURL'=>'icap://[::1]:1344/avscan',
                    'ResponseURL'=>'icap://[::1]:1344/avscan',
                    'SendClientIP'=>1,
                    'SendUsername'=>0,
                    'EncodeUsername'=>0,
                    'UsernameHeader'=>'X-Username',
                    'EnablePreview'=>1,
                    'PreviewSize'=>1024,
                    'OptionsTTL'=>60,
                    'exclude'=>''
                );
                
                $config['OPNsense']['proxy'] = $conf_data;
            }
            self::setFirewall();
            write_config();
            self::configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delDdns($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['id']) || !is_numeric($data['id'])){
                print_r($data);
                throw new AppException('DDNS_200');
            }
            if(!isset($config['dyndnses']['dyndns'][$data['id']])){
                throw new AppException('DDNS_201');
            }
            $conf = $config['dyndnses']['dyndns'][$data['id']];
            @unlink(dyndns_cache_file($conf, 4));
            @unlink(dyndns_cache_file($conf, 6));
            unset($config['dyndnses']['dyndns'][$data['id']]);

            $i = 0;
            foreach($config['dyndnses']['dyndns'] as $idx=>$dyndns) {
                $config['dyndnses']['dyndns'][$idx]['id'] = $i++;
            }
            write_config();
            system_cron_configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

}