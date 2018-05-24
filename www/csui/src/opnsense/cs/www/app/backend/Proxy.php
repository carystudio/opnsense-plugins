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

        $cicap = $config['OPNsense']['cicap'];
        $clamav = $config['OPNsense']['clamav'];

        $icap = array();
        $icap['enabled'] = $cicap['general']['enabled'];
        $icap['enable_accesslog'] = $cicap['general']['enable_accesslog'];
        $icap['servername'] = $cicap['general']['servername'];
        $icap['maxfilesize'] = $clamav['general']['maxfilesize'];
        $icap['scanarchive'] = $clamav['general']['scanarchive'];
        $icap['scanelf'] = $clamav['general']['scanelf'];
        $icap['scanhtml'] = $clamav['general']['scanhtml'];
        $icap['scanhwp3'] = $clamav['general']['scanhwp3'];
        $icap['scanole2'] = $clamav['general']['scanole2'];
        $icap['scanpdf'] = $clamav['general']['scanpdf'];
        $icap['scanpe'] = $clamav['general']['scanpe'];
        $icap['scanswf'] = $clamav['general']['scanswf'];
        $icap['scanxmldocs'] = $clamav['general']['scanxmldocs'];
        $icap['disablecache'] = $clamav['general']['disablecache'];

        $proxy['ICAP'] = $icap;

        $proxy['memory'] = get_memory();
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

    private static function cicapStatus()
    {
        global $config;

        $backend = new Backend();
        $response = $backend->configdRun("cicap status");

        if (strpos($response, "not running") > 0) {
            if ((string)$config['OPNsense']['cicap']['general']['enabled'] == 1) {
                $status = "stopped";
            } else {
                $status = "disabled";
            }
        } elseif (strpos($response, "is running") > 0) {
            $status = "running";
        } elseif ((string)$config['OPNsense']['cicap']['general']['enabled'] == 0) {
            $status = "disabled";
        } else {
            $status = "unkown";
        }

        return array("status" => $status);
    }

    private static function cicapConfigure()
    {
        global $config;

        $backend = new Backend();

        // stop cicap if it is running or not
        $response = $backend->configdRun("cicap stop");

        // generate template
        $backend->configdRun('template reload OPNsense/CICAP');

        // (res)start daemon
        if ((string)$config['OPNsense']['cicap']['general']['enabled'] == 1) {
            $response = $backend->configdRun("cicap start");
        }

        return array("status" => "ok");
    }

    private static function clamavStatus()
    {
        global $config;

        $backend = new Backend();
        $response = $backend->configdRun("clamav status");

        if (strpos($response, "not running") > 0) {
            if ((string)$config['OPNsense']['clamav']['general']['enabled'] == 1) {
                $status = "stopped";
            } else {
                $status = "disabled";
            }
        } elseif (strpos($response, "is running") > 0) {
            $status = "running";
        } elseif ((string)$config['OPNsense']['clamav']['general']['enabled'] == 0) {
            $status = "disabled";
        } else {
            $status = "unkown";
        }

        return array("status" => $status);
    }

    private static function clanavConfigure()
    {
        global $config;

        $backend = new Backend();

        // stop clamav if it is running or not
        $response = $backend->configdRun("clamav stop");

        // (res)start daemon
        if ((string)$config['OPNsense']['clamav']['general']['enabled'] == 1) {
            // generate template
            $backend->configdRun('template reload OPNsense/ClamAV');
            $response = $backend->configdRun("clamav start");
        }

        return array("status" => "ok");

    }

    private static function icapConfigure(){
        global $config;

        $backend = new Backend();

//        $clamavStatus = self::clamavStatus();
//        $cicapStatus = self::cicapStatus();
        if(!is_dir("/tmp/watch_tmp")){
            mkdir("/tmp/watch_tmp");
        }

        $backend->configdRun("clamav stop");
        $backend->configdRun("cicap stop");

        // generate template
        $backend->configdRun('template reload OPNsense/ClamAV');
        $backend->configdRun('template reload OPNsense/CICAP');

        // (res)start daemon
        if ((string)$config['OPNsense']['clamav']['general']['enabled'] == 1) {
            if(!file_exists("/tmp/watch_tmp/runicapcmd.txt")){
                file_put_contents("/tmp/watch_tmp/runicapcmd.txt",'/bin/sh /usr/local/opnsense/cs/script/seticap.sh start');
            }
//            exec('/bin/sh /usr/local/opnsense/cs/script/seticap.sh start');
        }

        return array("status" => "ok");
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
                $config['OPNsense']['cicap']['general']['enabled'] = 0;
                $config['OPNsense']['clamav']['general']['enabled'] = 0;
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

                if(isset($data['ICAP'])){
                    if(!isset($data['ICAP']['enabled']) || !Util::check0and1($data['ICAP']['enabled'])){
                        throw new AppException('PROXY_200');    //ICAP开启参数错误
                    }
                    if('1' == $data['ICAP']['enabled']){
                        $conf_data['forward']['transparentMode'] = '1';
                    }
                }

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

                $icapEnabled = '0';
                if(isset($data['general']['enabled']) && '1' == $data['general']['enabled']){
                    $memory = get_memory();
                    if($memory[0] < 1800){
                        throw new AppException('PROXY_300');     //内存不足，无法开启病毒检测功能
                    }
                    if(!isset($data['ICAP']['enabled']) || !Util::check0and1($data['ICAP']['enabled'])){
                        throw new AppException('PROXY_200');     //ICAP开启参数错误
                    }
                    $icapEnabled = $data['ICAP']['enabled'];

                    if(!isset($data['ICAP']['enable_accesslog']) || !Util::check0and1($data['ICAP']['enable_accesslog'])){
                        throw new AppException('PROXY_201');     //ICAP访问日志开启参数错误
                    }
                    if(!isset($data['CLAMAV']['disablecache']) || !Util::check0and1($data['CLAMAV']['disablecache'])){
                        throw new AppException('PROXY_202');     //ICAP缓存参数不正确
                    }
                    if(!isset($data['CLAMAV']['maxfilesize']) || !is_numeric($data['CLAMAV']['maxfilesize'])){
                        throw new AppException('PROXY_203');     //病毒检测最大扫描文件参数不正确
                    }
                    if($data['CLAMAV']['maxfilesize'] < 1 || $data['CLAMAV']['maxfilesize'] > 10000){
                        throw new AppException('PROXY_203');     //病毒检测最大扫描文件参数不正确
                    }
                    $data['CLAMAV']['maxfilesize'] = $data['CLAMAV']['maxfilesize'].'M';
                    $data['ICAP']['maxobjectsize'] = $data['ICAP']['maxobjectsize'].'M';
                    if(!isset($data['ICAP']['servername'])){
                        throw new AppException('PROXY_212');     //ICAP管理员邮箱参数错误
                    }
                    if($data['CLAMAV']['maxfilesize'] != $data['ICAP']['maxobjectsize']){
                        throw new AppException('PROXY_203');    //病毒检测最大扫描文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanpe']) || !is_numeric($data['CLAMAV']['scanpe'])){
                        throw new AppException('PROXY_204');    //扫描可执行文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanelf']) || !is_numeric($data['CLAMAV']['scanelf'])){
                        throw new AppException('PROXY_205');    //Scan executeable and linking format参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanole2']) || !is_numeric($data['CLAMAV']['scanole2'])){
                        throw new AppException('PROXY_206');    //扫描OLE2文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanpdf']) || !is_numeric($data['CLAMAV']['scanpdf'])){
                        throw new AppException('PROXY_207');    //扫描PDF文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanswf']) || !is_numeric($data['CLAMAV']['scanswf'])){
                        throw new AppException('PROXY_208');    //扫描SWF文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanxmldocs']) || !is_numeric($data['CLAMAV']['scanxmldocs'])){
                        throw new AppException('PROXY_209');    //扫描XMLDOCS文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanhwp3']) || !is_numeric($data['CLAMAV']['scanhwp3'])){
                        throw new AppException('PROXY_210');    //扫描HWP3文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanhtml']) || !is_numeric($data['CLAMAV']['scanhtml'])){
                        throw new AppException('PROXY_211');    //扫描HWP3文件参数不正确
                    }
                    if(!isset($data['CLAMAV']['scanarchive']) || !is_numeric($data['CLAMAV']['scanarchive'])){
                        throw new AppException('PROXY_211');    //扫描HWP3文件参数不正确
                    }

                    self::setCICAP($data);
                    self::setClamAv($data);
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
                    'enable'=>$icapEnabled,
                    'RequestURL'=>'icap://127.0.0.1:1344/avscan',
                    'ResponseURL'=>'icap://127.0.0.1:1344/avscan',
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
            self::icapConfigure();
            self::configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    private static function setCICAP($data){
        global $config;

        $data = $data["ICAP"];
        if(!isset($config['OPNsense']['cicap'])){
            $CICAP_INIT = array(
                "antivirus"=>array(
                    "@attributes"=>array("version"=>"1.0.0"),
                    "enable_clamav"=>"1",
                    "scanfiletypes"=>"TEXT,DATA,EXECUTABLE,ARCHIVE,GIF,JPEG,MSOFFICE",
                    "sendpercentdata"=>"5",
                    "startsendpercentdataafter"=>"2M",
                    "allow204responses"=>"1",
                    "passonerror"=>"0",
                    "maxobjectsize"=>"5M",
                ),
                "general"=>array(
                    "@attributes"=>array("version"=>"1.0.1"),
                    "enabled"=>"0",
                    "timeout"=>"300",
                    "maxkeepaliverequests"=>"100",
                    "keepalivetimeout"=>"600",
                    "startservers"=>"3",
                    "maxservers"=>"10",
                    "minsparethreads"=>"10",
                    "maxsparethreads"=>"20",
                    "threadsperchild"=>"10",
                    "maxrequestsperchild"=>"0",
                    "listenaddress"=>"127.0.0.1",
                    "serveradmin"=>"",
                    "servername"=>"",
                    "enable_accesslog"=>"1",
                    "localSquid"=>"1"
                )
            );
            $config['OPNsense']['cicap'] = $CICAP_INIT;
        }

        $config['OPNsense']['cicap']['general']['enabled'] = $data['enabled'];
        $config['OPNsense']['cicap']['general']['servername'] = $data['servername'];
        $config['OPNsense']['cicap']['general']['listenaddress'] = "127.0.0.1";
        $config['OPNsense']['cicap']['general']['enable_accesslog'] = $data['enable_accesslog'];
        $config['OPNsense']['cicap']['antivirus']['maxobjectsize'] = $data['maxobjectsize'];
        $config['OPNsense']['cicap']['antivirus']['enable_clamav'] = '1';

        write_config();
    }

    private static function setClamAv($data){
        global $config;

        if(!isset($config['OPNsense']['clamav'])){
            $CLAMAV_INIT = array(
                "general"=>array(
                    "@attributes"=>array("version"=>"1.0.0"),
                    "enabled"=>'0',
                    "fc_enabled"=>'1',
                    "enabletcp"=>"1",
                    "maxthreads"=>"10",
                    "maxqueue"=>"100",
                    "idletimeout"=>"30",
                    "maxdirrecursion"=>"20",
                    "followdirsym"=>"0",
                    "followfilesym"=>"0",
                    "disablecache"=>"1",
                    "scanpe"=>"1",
                    "scanelf"=>"1",
                    "detectbroken"=>"0",
                    "scanole2"=>"1",
                    "ole2blockmarcros"=>"0",
                    "scanpdf"=>"1",
                    "scanswf"=>"1",
                    "scanxmldocs"=>"1",
                    "scanhwp3"=>"1",
                    "scanmailfiles"=>"1",
                    "scanhtml"=>"1",
                    "scanarchive"=>"1",
                    "arcblockenc"=>"0",
                    "maxscansize"=>"100M",
                    "maxfilesize"=>"25M",
                    "maxrecursion"=>"16",
                    "maxfiles"=>"10000",
                    "fc_logverbose"=>"0",
                    "fc_databasemirror"=>"database.clamav.net",
                    "fc_timeout"=>"60"
                ),
            );

            $config['OPNsense']['clamav'] = $CLAMAV_INIT;
        }

        $config['OPNsense']['clamav']['general']['enabled'] = $data['ICAP']['enabled'];
        $config['OPNsense']['clamav']['general']['fc_enabled'] = '1';
        $config['OPNsense']['clamav']['general']['disablecache'] = $data['CLAMAV']['disablecache'];
        $config['OPNsense']['clamav']['general']['maxfilesize'] = $data['CLAMAV']['maxfilesize'];
        $config['OPNsense']['clamav']['general']['scanpe'] = $data['CLAMAV']['scanpe'];
        $config['OPNsense']['clamav']['general']['scanelf'] = $data['CLAMAV']['scanelf'];
        $config['OPNsense']['clamav']['general']['scanole2'] = $data['CLAMAV']['scanole2'];
        $config['OPNsense']['clamav']['general']['scanpdf'] = $data['CLAMAV']['scanpdf'];
        $config['OPNsense']['clamav']['general']['scanswf'] = $data['CLAMAV']['scanswf'];
        $config['OPNsense']['clamav']['general']['scanxmldocs'] = $data['CLAMAV']['scanxmldocs'];
        $config['OPNsense']['clamav']['general']['scanhwp3'] = $data['CLAMAV']['scanhwp3'];
        $config['OPNsense']['clamav']['general']['scanhtml'] = $data['CLAMAV']['scanhtml'];
        $config['OPNsense']['clamav']['general']['scanarchive'] = $data['CLAMAV']['scanarchive'];

        write_config();
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