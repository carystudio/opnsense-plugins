<?php
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
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
            $config['nat']['rule'][] = $nat_rule_https;
            $config['filter']['rule'][] = $filter_rule_http;
            $config['filter']['rule'][] = $filter_rule_https;
        }
    }

    private static function initConf(){
        global $config;

        $proxy_data_json = <<<EOF
<proxy version="0.0.0">
      <general>
        <enabled>0</enabled>
        <icpPort/>
        <logging>
          <enable>
            <accessLog>1</accessLog>
            <storeLog>1</storeLog>
          </enable>
          <ignoreLogACL/>
          <target/>
        </logging>
        <alternateDNSservers/>
        <dnsV4First>0</dnsV4First>
        <forwardedForHandling>on</forwardedForHandling>
        <uriWhitespaceHandling>strip</uriWhitespaceHandling>
        <useViaHeader>1</useViaHeader>
        <suppressVersion>0</suppressVersion>
        <VisibleEmail>admin@localhost.local</VisibleEmail>
        <VisibleHostname/>
        <cache>
          <local>
            <enabled>0</enabled>
            <directory>/var/squid/cache</directory>
            <cache_mem>256</cache_mem>
            <maximum_object_size/>
            <size>100</size>
            <l1>16</l1>
            <l2>256</l2>
            <cache_linux_packages>0</cache_linux_packages>
            <cache_windows_updates>0</cache_windows_updates>
          </local>
        </cache>
        <traffic>
          <enabled>0</enabled>
          <maxDownloadSize>2048</maxDownloadSize>
          <maxUploadSize>1024</maxUploadSize>
          <OverallBandwidthTrotteling>1024</OverallBandwidthTrotteling>
          <perHostTrotteling>256</perHostTrotteling>
        </traffic>
      </general>
      <forward>
        <interfaces>lan</interfaces>
        <port>3128</port>
        <sslbumpport>3129</sslbumpport>
        <sslbump>0</sslbump>
        <sslurlonly>0</sslurlonly>
        <sslcertificate/>
        <sslnobumpsites/>
        <ssl_crtd_storage_max_size>4</ssl_crtd_storage_max_size>
        <sslcrtd_children>5</sslcrtd_children>
        <ftpInterfaces/>
        <ftpPort>2121</ftpPort>
        <ftpTransparentMode>0</ftpTransparentMode>
        <addACLforInterfaceSubnets>1</addACLforInterfaceSubnets>
        <transparentMode>0</transparentMode>
        <acl>
          <allowedSubnets/>
          <unrestricted/>
          <bannedHosts/>
          <whiteList/>
          <blackList/>
          <browser/>
          <mimeType/>
          <safePorts>80:http,21:ftp,443:https,70:gopher,210:wais,1025-65535:unregistered ports,280:http-mgmt,488:gss-http,591:filemaker,777:multiling http</safePorts>
          <sslPorts>443:https</sslPorts>
          <remoteACLs>
            <blacklists/>
            <UpdateCron/>
          </remoteACLs>
        </acl>
        <icap>
          <enable>0</enable>
          <RequestURL>icap://[::1]:1344/avscan</RequestURL>
          <ResponseURL>icap://[::1]:1344/avscan</ResponseURL>
          <SendClientIP>1</SendClientIP>
          <SendUsername>0</SendUsername>
          <EncodeUsername>0</EncodeUsername>
          <UsernameHeader>X-Username</UsernameHeader>
          <EnablePreview>1</EnablePreview>
          <PreviewSize>1024</PreviewSize>
          <OptionsTTL>60</OptionsTTL>
          <exclude/>
        </icap>
        <authentication>
          <method/>
          <realm>OPNsense proxy authentication</realm>
          <credentialsttl>2</credentialsttl>
          <children>5</children>
        </authentication>
      </forward>
      </proxy>
EOF;

        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($proxy_data_json, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
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
                if(!isset($data['general']['logging']['enable']['accessLog']) ||
                    !Util::check0and1($data['general']['logging']['enable']['accessLog'])){
                    throw new AppException('PROXY_101');
                }
                if(!isset($data['general']['logging']['enable']['storeLog']) ||
                    !Util::check0and1($data['general']['logging']['enable']['storeLog'])){
                    throw new AppException('PROXY_102');
                }
                if(!isset($data['general']['dnsV4First']) ||
                    !Util::check0and1($data['general']['dnsV4First'])){
                    throw new AppException('PROXY_103');
                }
                if(!isset($data['general']['forwardedForHandling']) ||
                    !in_array($data['general']['forwardedForHandling'], Proxy::FORWARDEDFORHANDLING)){
                    throw new AppException('PROXY_104');
                }
                if(!isset($data['general']['uriWhitespaceHandling']) ||
                    !in_array($data['general']['uriWhitespaceHandling'], Proxy::URIWHITESPACEHANFLING)){
                    throw new AppException('PROXY_105');
                }
                if(!isset($data['general']['suppressVersion']) ||
                    !Util::check0and1($data['general']['suppressVersion'])){
                    throw new AppException('PROXY_106');
                }
                if(isset($data['general']['VisibleEmail']) && false === filter_var($data['general']['VisibleEmail'], FILTER_VALIDATE_EMAIL)){
                    throw new AppException('PROXY_107');
                }

                if(!isset($data['general']['cache']['local']['cache_mem']) ||
                    !is_numeric($data['general']['cache']['local']['cache_mem'])){
                    throw new AppException('PROXY_108');
                }
                $data['general']['cache']['local']['cache_mem'] = intval($data['general']['cache']['local']['cache_mem']);
                if($data['general']['cache']['local']['cache_mem']>2048 ||
                    $data['general']['cache']['local']['cache_mem']<128){
                    throw new AppException('PROXY_108');
                }

                if(!isset($data['general']['cache']['local']['cache_linux_packages']) ||
                    !Util::check0and1($data['general']['cache']['local']['cache_linux_packages'])){
                    throw new AppException('PROXY_109');
                }

                if(!isset($data['general']['cache']['local']['cache_windows_updates']) ||
                    !Util::check0and1($data['general']['cache']['local']['cache_windows_updates'])){
                    throw new AppException('PROXY_110');
                }
                if(!isset($data['general']['traffic']['enabled']) ||
                    !Util::check0and1($data['general']['traffic']['enabled'])){
                    throw new AppException('PROXY_111');
                }
                if('0' == $data['general']['traffic']['enabled']){
                    $data['general']['traffic']['maxDownloadSize'] = $config['OPNsense']['proxy']['general']['traffic']['maxDownloadSize'];
                    $data['general']['traffic']['maxUploadSize'] = $config['OPNsense']['proxy']['general']['traffic']['maxUploadSize'];
                    $data['general']['traffic']['OverallBandwidthTrotteling'] = $config['OPNsense']['proxy']['general']['traffic']['OverallBandwidthTrotteling'];
                    $data['general']['traffic']['perHostTrotteling'] = $config['OPNsense']['proxy']['general']['traffic']['perHostTrotteling'];
                }else{
                    if(!isset($data['general']['traffic']['maxDownloadSize']) ||
                        (!empty($data['general']['traffic']['maxDownloadSize']) && !is_numeric($data['general']['traffic']['maxDownloadSize']))
                    ){
                        throw new AppException('PROXY_112');
                    }

                    if(!isset($data['general']['traffic']['maxUploadSize']) ||
                        (!empty($data['general']['traffic']['maxUploadSize']) && !is_numeric($data['general']['traffic']['maxUploadSize']))
                    ){
                        throw new AppException('PROXY_113');
                    }

                    if(!isset($data['general']['traffic']['OverallBandwidthTrotteling']) ||
                        (!empty($data['general']['traffic']['OverallBandwidthTrotteling']) && !is_numeric($data['general']['traffic']['OverallBandwidthTrotteling']))
                    ){
                        throw new AppException('PROXY_114');
                    }

                    if(!isset($data['general']['traffic']['perHostTrotteling']) ||
                        (!empty($data['general']['traffic']['perHostTrotteling']) && !is_numeric($data['general']['traffic']['perHostTrotteling']))
                    ){
                        throw new AppException('PROXY_115');
                    }

                    if(empty($data['general']['traffic']['OverallBandwidthTrotteling']) &&
                        !empty($data['general']['traffic']['perHostTrotteling'])){
                        throw new AppException('PROXY_116');
                    }
                    if(empty($data['general']['traffic']['perHostTrotteling']) &&
                        !empty($data['general']['traffic']['OverallBandwidthTrotteling'])){
                        throw new AppException('PROXY_116');
                    }
                }
                $data['forward']['interfaces'] = 'lan';
                if(!isset($data['forward']['port']) ||
                    !is_numeric($data['forward']['port'])){
                    throw new AppException('PROXY_117');
                }
                $data['forward']['port'] = intval($data['forward']['port']);
                if($data['forward']['port']>65535 || $data['forward']['port']<1){
                    throw new AppException('PROXY_117');
                }

                if(!isset($data['forward']['sslbumpport']) ||
                    !is_numeric($data['forward']['sslbumpport'])){
                    throw new AppException('PROXY_117');
                }
                $data['forward']['sslbumpport'] = intval($data['forward']['sslbumpport']);
                if($data['forward']['sslbumpport']>65535 || $data['forward']['sslbumpport']<1 ||
                    $data['forward']['sslbumpport'] == $data['forward']['port']){
                    throw new AppException('PROXY_117');
                }
                if(!isset($data['forward']['sslbump']) ||
                    !Util::check0and1($data['forward']['sslbump'])
                ){
                    throw new AppException('PROXY_118');
                }
                if(!isset($data['forward']['sslurlonly']) ||
                    !Util::check0and1($data['forward']['sslurlonly'])
                ){
                    throw new AppException('PROXY_119');
                }
                if(!isset($data['forward']['ssl_crtd_storage_max_size']) ||
                    !is_numeric($data['forward']['ssl_crtd_storage_max_size'])){
                    throw new AppException('PROXY_120');
                }
                if(!isset($data['forward']['sslcrtd_children']) ||
                    !is_numeric($data['forward']['sslcrtd_children'])){
                    throw new AppException('PROXY_120');
                }
                if(!isset($data['forward']['addACLforInterfaceSubnets']) ||
                    !Util::check0and1($data['forward']['addACLforInterfaceSubnets'])
                ){
                    throw new AppException('PROXY_121');
                }
                if(!isset($data['forward']['transparentMode']) ||
                    !Util::check0and1($data['forward']['transparentMode'])
                ){
                    throw new AppException('PROXY_122');
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
                $data['forward']['authentication'] = array('realm'=>'CSG2000P proxy authentication',
                    'credentialsttl'=>2,'children'=>'5');

                $config['OPNsense']['proxy'] = $data;
            }

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