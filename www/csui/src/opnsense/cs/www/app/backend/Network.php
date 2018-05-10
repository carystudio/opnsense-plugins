<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("auth.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once("gwlb.inc");
require_once("/usr/local/www/widgets/api/plugins/traffic.inc");

use \OPNsense\Core\Backend;

class Network extends Csbackend
{
    protected static $ERRORCODE = array(
        'Network_100'=>'接口名称不正确',
        'Network_101'=>'IP不正确',
        'Network_102'=>'子网掩码不正确',
        'Network_103'=>'DHCP服务器开启参数不正确',
        'Network_104'=>'DHCP地址池开始IP不正确',
        'Network_105'=>'DHCP地址池结束IP不正确',
        'Network_106'=>'接口名称不存在',
        'Network_200'=>'接口不存在',
        'Network_201'=>'网卡不正确',
        'Network_202'=>'接口名称不正确',
        'Network_203'=>'DNS1不正确',
        'Network_204'=>'监视IP不正确',
        'Network_205'=>'IP地址不正确',
        'Network_206'=>'子网掩网不正确',
        'Network_207'=>'默认网关不正确',
        'Network_208'=>'用户名和密码不能为空',
        'Network_209'=>'连接方式不正确',
        'Network_210'=>'权重不正确(1-4)',
        'Network_211'=>'优先级不正确(1-4)',
        'Network_212'=>'WAN接口指定的DNS不能相同',
		'Network_213'=>'MAC克隆不正确',

        'Network_300'=>'接口不正确',
        'Network_301'=>'网卡不可用',
        'Network_302'=>'取接口名称失败',
        'Network_303'=>'取接口标识失败',

        'Network_400'=>'MAC不正确',
        'Network_401'=>'IP不正确',
        'Network_402'=>'备注不能为空',
        'Network_403'=>'该MAC地址已添加过规则',
        'Network_404'=>'该IP地址已添加过规则',
        'Network_405'=>'静态DHCP地址需要与lan ip地址同一网段',

        'Network_500'=>'接口名称不正确',
        'Network_501'=>'网关不在网段内',
        'Network_502'=>'静态路由描述不能为空',
        'Network_503'=>'静态路由已存在',
        'Network_600'=>'接口不存在',
        'Network_601'=>'静态路由不存在',

        'Network_700'=>'网络接口不存在',
        'Network_701'=>'网络接口属于组，不能删除',
        'Network_702'=>'网络接口属于桥，不能删除',
        'Network_703'=>'网络接口属于gre,不能删除',
        'Network_704'=>'网络接口属于gif,不能删除',
        'Network_705'=>'网卡不正确'
    );

    private static $availableNic = false;
    private static $infInfo = array();
    private static $infStatus = false;
    
    public static function getInfStatus($inf=false){
    	if(!self::$infStatus){
    		self::$infStatus = get_interfaces_info();
    	}

        if(false == $inf){
            return self::$infStatus;
        }else{
            if(isset(self::$infStatus[$inf])){
                return self::$infStatus[$inf];
            }else{
                return false;
            }
        }

    }

    private static function applyGatewayConfig(){
        global $config;

        $retval = system_routing_configure();

        configd_run('dyndns reload');
        configd_run('ipsecdns reload');
        configd_run('filter reload');

        /* reconfigure our gateway monitor */
        setup_gateways_monitor();

        if ($retval == 0) {
            clear_subsystem_dirty('staticroutes');
        }

     /*   foreach ($config['gateways']['gateway_group'] as $gateway_group) {
            $gw_subsystem = 'gwgroup.' . $gateway_group['name'];
            if (is_subsystem_dirty($gw_subsystem)) {
                clear_subsystem_dirty($gw_subsystem);
            }
        }*/
    }

    private static function getAvailableNic($include=false){
        global  $config;

        if(false == self::$availableNic){
            $niclist = get_interface_list();
            foreach($config['interfaces'] as $if_name=>$if_info){
                if('wan'==substr($if_info['descr'],0,3)){
                    if(isset($niclist[$config['interfaces'][$if_name]['if']])){
                        unset($niclist[$config['interfaces'][$if_name]['if']]);
                    }
                }
				if('pppoe'==substr($if_info['if'],0,5)){
                    foreach($config['ppps']['ppp'] as $idx=>$ppp){
                        if($ppp['if'] == $if_info['if']){
                            unset($niclist[$config['ppps']['ppp'][$idx]['ports']]);
                        }
                    }
                }
            }
			if(false == $include){
                if(isset($config['bridges']['bridged'][0]['members'])){
                    $lan_ifs = trim($config['bridges']['bridged'][0]['members']);
                    $lan_ifs = explode(',', $lan_ifs);
                    foreach($lan_ifs as $lan_if){
                        $lan_if = trim($lan_if);
                        if(isset($niclist[$config['interfaces'][$lan_if]['if']])){
                            unset($niclist[$config['interfaces'][$lan_if]['if']]);
                        }
                    }
                }
            }

            self::$availableNic = $niclist;
        }

        return self::$availableNic;
    }

    public static function getWanInfo(){
        global $config;

        $wanInfo = array();
        $wanInfo['AvailableNic'] = self::getAvailableNic();
        $wanInfo['Interfaces'] = array();
        $niclist = get_interface_list();
        foreach($config['interfaces'] as $idx=>$infinfo){
            if(strpos($infinfo['descr'], 'wan')===0){
                $wanInfo['Interfaces'][] = self::getInfInfo($infinfo['descr']);
            }
        }

        foreach ($wanInfo['Interfaces'] as $key=>$val){
            foreach ($val as $k=>$v){
                if('Nic' == $k){
                    $wanInfo['Interfaces'][$key]['Mac'] = $niclist[$val['Nic']]['mac'];
                }
            }

        }

        return $wanInfo;
    }

    public static function getLanInfo(){
        global $config;

        $lanInfo = array();
        $lanInfo['Nics'] = self::getAvailableNic('lan');
        $lanInfo['Interfaces'] = array();
        foreach($config['interfaces'] as $idx=>$infinfo){
            if(strpos($infinfo['descr'], 'lan')===0){
                $lanInfo['Interfaces'][] = self::getInfInfo($infinfo['descr']);
            }
        }



        return $lanInfo;
    }

    private static function getWanDns($interface){
        global $config;

        $interface = strtoupper($interface);
        for($i=0; $i<4; $i++){
            $j=$i+1;
            if(strpos($config['system']['dns'.$j.'gw'], $interface)===0){
                return $config['system']['dnsserver'][$i];
            }
        }

        return '';
    }

    public static function getInfInfo($interface){
        global $config;

        if(isset(self::$infInfo[$interface])){
            return self::$infInfo[$interface];
        }

        $interfaceInfo = array();
        $interfaceInfo['Interface'] = $interface;
        foreach($config['interfaces'] as $inf=>$infinfo){
            if($interface == $infinfo['descr']){
                if(isset($infinfo['mtu']) && !empty($infinfo['mtu'])){
                    $interfaceInfo['Mtu'] = $infinfo['mtu'];
                }else{
                    $interfaceInfo['Mtu'] = '1500';
                }

                if(isset($infinfo['enable']) ){
                    $interfaceInfo['Enable'] = '1';
                }else{
                    $interfaceInfo['Enable'] = '0';
                }
                if(!isset($infinfo['ipaddr'])){
                    $interfaceInfo['Protocol'] = '';
                    $interfaceInfo['Nic'] = $infinfo['if'];
                }else if('dhcp'==$infinfo['ipaddr']){
                    $interfaceInfo['Protocol'] = 'dhcp';
                    $interfaceInfo['Nic'] = $infinfo['if'];
                }else if('pppoe'==$infinfo['ipaddr']){
                    $interfaceInfo['Nic'] = '';
                    $interfaceInfo['Protocol'] = 'pppoe';
                    $interfaceInfo['Username'] = '';
                    $interfaceInfo['Password'] = '';
                    if(isset($config['ppps']['ppp']) && is_array($config['ppps']['ppp'])){
                        foreach($config['ppps']['ppp'] as $ppp){
                            if($ppp['if'] == $infinfo['if']){
                                $interfaceInfo['Nic'] = $ppp['ports'];
                                $interfaceInfo['Username'] = $ppp['username'];
                                $interfaceInfo['Password'] = base64_decode($ppp['password']);
                                break;
                            }
                        }
                    }
                    if("1500"==$interfaceInfo['Mtu']){
                        $interfaceInfo['Mtu']="1492";
                    }
                }else{
                    $interfaceInfo['Protocol'] = 'static';
                    $interfaceInfo['Nic'] = $infinfo['if'];
                    $interfaceInfo['Ip'] = $infinfo['ipaddr'];
                    $interfaceInfo['Netmask'] = Util::maskbit2ip($infinfo['subnet']);
                    if(strpos($interface, 'wan')===0){
                        $interfaceInfo['Gateway'] = '';
                        if(isset($config['gateways']) && isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                            foreach($config['gateways']['gateway_item'] as $gateway){
                                if($gateway['name']==$infinfo['gateway']){
                                    $interfaceInfo['Gateway'] = $gateway['gateway'];
                                }
                            }
                        }
                    }

                    if(strpos($interface, 'lan')===0){
                        $interfaceInfo['DhcpSvrEnable'] = '0';
                        foreach($config['dhcpd'] as $infidx=>$dhcpd){
                            if($inf == $infidx){
                                if(isset($dhcpd['enable'])){
                                    $interfaceInfo['DhcpSvrEnable'] = '1';
                                    $interfaceInfo['DhcpStart'] = $dhcpd['range']['from'];
                                    $interfaceInfo['DhcpEnd'] = $dhcpd['range']['to'];
                                    if(isset($dhcpd['defaultleasetime'])){
                                        $interfaceInfo['DhcpLeasetime'] = intval($dhcpd['defaultleasetime']);
                                    }else{
                                        $interfaceInfo['DhcpLeasetime'] = 7200;
                                    }
                                }
                            }
                        }
                        $interfaceInfo['Nic'] = array();
                        if(strpos($infinfo['if'], 'bridge')===0){
                            foreach($config['bridges']['bridged'] as $idx=>$bridge_info){
                                if($bridge_info['bridgeif'] == $infinfo['if']){
                                    $members = explode(',', $bridge_info['members']);
                                    foreach($members as $member){
                                        $interfaceInfo['Nic'][] = $config['interfaces'][$member]['if'];
                                    }

                                }
                            }

                        }
                    }

                }
                if(strpos($interface,'wan')===0){//WAN接口才取DNS
                    $interfaceInfo['Dns'] = self::getWanDns($interface);
                    $wan_gateway = self::getWanGateway($infinfo);
                    $interfaceInfo['Monitor'] = '';
                    if($wan_gateway){
                        if(!isset($wan_gateway['monitor_disable']) || '1'!=$wan_gateway['monitor_disable']){
                            $interfaceInfo['Monitor'] = $wan_gateway['monitor'];
                        }
                    }
                    $interfaceInfo['Tier'] = '1';
                    $interfaceInfo['Weight'] = '1';
                    if(isset($config['gateways']) && isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                        $gateway_name = strtoupper($interfaceInfo['Interface'].'_'.$interfaceInfo['Protocol']);

                        foreach($config['gateways']['gateway_item'] as $gateway){
                            if($gateway['name'] == $gateway_name){
                                if(!empty($gateway['tier'])){
                                    $interfaceInfo['Tier'] = $gateway['tier'];
                                }
                                if(!empty($gateway['weight'])){
                                    $interfaceInfo['Weight'] = $gateway['weight'];
                                }
                            }
                        }
                    }
					$interfaceInfo['MacClone'] = $infinfo['spoofmac'];
                }

								$infStatus = self::getInfStatus($inf);
								if(false != $infStatus){
                		$interfaceInfo['Status'] = $infStatus;
                }else{
                		$interfaceInfo['Status'] = array();;
                }
                $interfaceInfo['Status']['subnet'] = Util::maskbit2ip($interfaceInfo['Status']['subnet']);
                self::$infInfo[$interface] = $interfaceInfo;

                return $interfaceInfo;
            }
        }

        return false;
    }

    private static function setInterfaceDhcpSvr($interface, $enable, $range_from='', $range_to='', $leasetime=0){
        global $config;

        if(!isset($config['dhcpd'])){
            $config['dhcpd'] = array();
        }
        if(!isset($config['dhcpd'][$interface])){
            $config['dhcpd'][$interface] = array();
        }

        if(1==$enable){
            $config['dhcpd'][$interface]['enable'] = 1;
            $config['dhcpd'][$interface]['range']['from'] = $range_from;
            $config['dhcpd'][$interface]['range']['to'] = $range_to;
            $config['dhcpd'][$interface]['maxleasetime'] = 86400;
            if($leasetime<=0){
                $config['dhcpd'][$interface]['defaultleasetime'] = 7200;
            }else{
                $config['dhcpd'][$interface]['defaultleasetime'] = intval($leasetime);
            }
        }else{
            if(isset($config['dhcpd'][$interface][enable])){
                unset($config['dhcpd'][$interface][enable]);
            }
        }
    }

    private static function setup_lan_subnet_alias(){
        global $config;

        if(!isset($config['aliases']) || !is_array($config['aliases'])){
            $config['aliases'] = array();
        }
        if(!isset($config['aliases']['alias']) || !is_array($config['aliases']['alias'])){
            $config['aliases']['alias'] = array();
        }
        foreach($config['interfaces'] as $ifname=>$ifinfo){
            if(strpos($ifinfo['descr'], 'lan')===0){//lan
                $alias_name = strtoupper($ifinfo['descr']).'_TO_MULTIWAN';
                foreach($config['aliases']['alias'] as $idx=>$alias){
                    if($alias['name']==$alias_name && $alias['descr']==$alias_name){
                        unset($config['aliases']['alias'][$idx]);
                    }
                }
                $address = $ifinfo['ipaddr'].'/'.$ifinfo['subnet'];
                if(isset($config['OPNsense']['captiveportal']['zones']['zone']['enabled']) &&
                    '1'==$config['OPNsense']['captiveportal']['zones']['zone']['enabled']){
                    $address = $ifinfo['ipaddr'].'/32';
                }
                $alias = array(
                    'name'=>$alias_name,
                    'type'=>'network',
                    'descr'=>$alias_name,
                    'address'=> $address,
                    'detail'=>'Entry added '.date("Y-m-d H:i:s")
                );
                $config['aliases']['alias'][] = $alias;
            }
        }
    }

    public static function setLanInfo(Array $data){
        global $config;

        $result = false;
        try {
            if (strpos($data['Interface'], 'lan') !== 0) {
                throw new AppException('Network_100');
            }

            if (!is_ipaddr($data['Ip'])) {
                throw new AppException('Network_101');
            }
            $netmask = Util::maskip2bit($data['Netmask']);
            if (false === $netmask) {
                throw new AppException('Network_102');
            }
            if ('1' != $data['DhcpSvrEnable'] && '0' != $data['DhcpSvrEnable']) {
                throw new AppException('Network_103');
            }
            $lan = array();
            $lan['if'] = 'bridge0';
            $lan['descr'] = $data['Interface'];
            $lan['enable'] = '1';
            $lan['spoofmac'] = '';
            $lan['ipaddr'] = $data['Ip'];
            $lan['subnet'] = $netmask;
            $lan['ipaddrv6'] = 'track6';
            $lan['track6-interface'] = '';
            $lan['track6-prefix-id'] = '0';
            $if_members = array();
            foreach($config['interfaces'] as $if_name=>$if_info){
                if(in_array($if_info['if'], $data['Nic'])){
                    $if_members[] = $if_name;
                }
            }
			$old_members = explode(',', $config['bridges']['bridged'][0]['members']);
            foreach($old_members as $ifname){
                unset($config['interfaces'][$ifname]['enable']);
            }
            foreach($if_members as $ifname){
                $config['interfaces'][$ifname]['enable'] = '1';
            }
            $config['bridges']['bridged'][0]['members'] = implode(',', $if_members);

            $seted = false;
            foreach($config['interfaces'] as $infidx=>$tmp_infinfo){
                if ($tmp_infinfo['descr'] == $data['Interface']) {
                    $ifname = $infidx;
                    $config['interfaces'][$infidx] = $lan;
                    if ('1' == $data['DhcpSvrEnable']) {
                        if (!is_ipaddr($data['DhcpStart'])) {
                            throw new AppException('Network_104');
                        }
                        if (!is_ipaddr($data['DhcpEnd'])) {
                            throw new AppException('Network_105');
                        }
                        if (300 > intval($data['DhcpReleaseTime'])) {
                            $data['DhcpReleaseTime'] = 86400;
                        }
                        self::setInterfaceDhcpSvr($infidx, 1, $data['DhcpStart'], $data['DhcpEnd'], $data['DhcpReleaseTime']);
                    } else {
                        self::setInterfaceDhcpSvr($infidx, 0);
                    }
                    $seted = true;
                    break ;
                }
            }
            if(!$seted){
                throw new AppException('Network_106');
            }
            self::setup_lan_subnet_alias();
            $dnsmasq_restart = false;
            if(isset($config['dnsmasq']) && isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts'])){
                foreach($config['dnsmasq']['hosts'] as $idx=>$host){
                    if('PORTAL_SERVER' == $host['descr'] || 'WeChat Local Login' == $host['descr']){
                        if($host['ip'] != $lan['ipaddr']){
                            $config['dnsmasq']['hosts'][$idx]['ip'] = $lan['ipaddr'];
                            $dnsmasq_restart = true;
                        }
                    }
                }
            }
            $remote_restart = false;
            if(isset($config['nat']) && isset($config['nat']['rule']) && is_array($config['nat']['rule'])){
                foreach($config['nat']['rule'] as $idx=>$natrule){
                    if('remote_web' == $natrule['descr'] || 'remote_ssh'==$natrule['descr']){
                        if($natrule['target'] != $lan['ipaddr']){
                            $config['nat']['rule'][$idx]['target'] = $lan['ipaddr'];
                            $remote_restart = true;
                        }

                    }
                }
            }
            if(isset($config['OPNsense']['captiveportal']['zones']['zone'])){
                $config['OPNsense']['captiveportal']['zones']['zone']['enabled']='0';
            }


            write_config();

            Portal::reconfigureAction();
            interface_bridge_configure($config['bridges']['bridged'][0]);
            if (isset($lan['enable']) && isset($ifname)) {
                interface_bring_down($ifname, false, $lan);
                interface_configure($ifname, true);
            } else if(isset($ifname)) {
                interface_bring_down($ifname, true, $lan);
            }

            setup_gateways_monitor();
            filter_configure();
            //enable_rrd_graphing();
            if($dnsmasq_restart){
                system_resolvconf_generate();
                dnsmasq_configure_do();
                clear_subsystem_dirty('hosts');
            }

            if($remote_restart){
                system_login_configure();
                $backend = new Backend();
                if('enabled'==$config['system']['ssh']['enabled']){
                    $backend->configdRun('sshd restart');
                }else{
                    $backend->configdRun('sshd stop');
                }
                clear_subsystem_dirty('natconf');
            }
            DhcpdHelper::reconfigure_dhcpd();

            $result = '0';
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = '100';
        }

        return $result;
    }

    private static function getNewInterfaceName($interface){
        global $config;

        $inf = '';
        for($i=1; $i<=count($config['interfaces']); $i++){
            $exist = false;
            $inf = $interface.$i;
            foreach($config['interfaces'] as $ifidx=>$ifinfo){
                if($ifinfo['descr'] == $inf){
                    $exist = true;
                    break ;
                }
            }
            if(!$exist){
                break;
            }
        }

        return $inf;
    }

    private static function getNewIfidx(){
        global $config;

        $ifname = '';
        for($i=1; $i<count($config['interfaces']); $i++){
            $exist = false;
            $ifname = 'opt'.$i;
            foreach($config['interfaces'] as $ifidx=>$ifinfo){
                if($ifidx == $ifname){
                    $exist = true;
                }
            }
            if(!$exist){
                break;
            }
        }

        return $ifname;
    }

    public static function setInterfaceBind($data){
        global $config;

        $result = 0;
        try {
            $nic = $data['Nic'];
            $if = $data['Interface'];
            if(!in_array($if, array('wan','lan'))){
                throw new AppException('Network_300');
            }
            $nic_list = self::getAvailableNic();
            if(!isset($nic_list[$nic])){
                throw new AppException('Network_301');
            }
            $inf = self::getNewInterfaceName($if);
            if(''==$inf){
                throw new AppException('Network_302');
            }
			$ifname = '';
            foreach($config['interfaces'] as $ifname_tmp=>$ifinfo){
                if($ifinfo['if'] == $nic){
                    $ifname = $ifname_tmp;
                    break;
                }
            }
            if(empty($ifname)){
                $ifname = self::getNewIfidx();
                if(''==$ifname){
                    throw new AppException('Network_303');
                }
            }

            $wan['if'] = $nic;
            $wan['descr'] = $inf;
            $wan['enable'] = '1';
            $wan['ipaddr']='dhcp';
            $wan['gateway']='';
            $wan['mtu'] = 1500;
            $wan['spoofmac'] = '';
            $wan['blockbogons'] = '1';
            $wan['dhcphostname'] = '';
            $wan['alias-address'] = '';
            $wan['alias-subnet'] = '32';
            $wan['dhcprejectfrom'] = '';
            $wan['adv_dhcp_pt_timeout'] = '';
            $wan['adv_dhcp_pt_retry'] = '';
            $wan['adv_dhcp_pt_select_timeout'] = '';
            $wan['adv_dhcp_pt_reboot'] = '';
            $wan['adv_dhcp_pt_backoff_cutoff'] = '';
            $wan['adv_dhcp_pt_initial_interval'] = '';
            $wan['adv_dhcp_pt_values'] = 'SavedCfg';
            $wan['adv_dhcp_send_options'] = '';
            $wan['adv_dhcp_request_options'] = '';
            $wan['adv_dhcp_required_options'] = '';
            $wan['adv_dhcp_option_modifiers'] = '';
            $wan['adv_dhcp_config_advanced'] = '';
            $wan['adv_dhcp_config_file_override'] = '';
            $wan['adv_dhcp_config_file_override_path'] = '';
            $config['interfaces'][$ifname] = $wan;

			self::setMultiWan();
            write_config();
            self::applyGatewayConfig();

            interface_configure($ifname, true);
            plugins_configure('newwanip');
            /* sync filter configuration */
            setup_gateways_monitor();
            filter_configure();
            rrd_configure();

            $result = array('Interface'=>$wan['descr'], 'Nic'=>$wan['if']);
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = '100';
        }

        return $result;
    }

    public static function delInterfaceBind($data){
        global $config;

        $result = 0;
        try {
            $nic = $data['Nic'];
            $if = $data['Interface'];
            $ifname = '';
            foreach($config['interfaces'] as $idx=>$ifinfo){
                if($ifinfo['descr'] == $if){
                    $ifname = $idx;
					break ;
                }
            }
            if(empty($ifname)){
                throw new AppException('Network_700');
            }
            if (link_interface_to_group($ifname)) {
                throw new AppException('Network_701');
                $input_errors[] = gettext("The interface is part of a group. Please remove it from the group to continue");
            } else if (link_interface_to_bridge($ifname)) {
                throw new AppException('Network_702');
                $input_errors[] = gettext("The interface is part of a bridge. Please remove it from the bridge to continue");
            } else if (link_interface_to_gre($ifname)) {
                throw new AppException('Network_703');
                $input_errors[] = gettext("The interface is part of a gre tunnel. Please delete the tunnel to continue");
            } else if (link_interface_to_gif($ifname)) {
                throw new AppException('Network_704');
                $input_errors[] = gettext("The interface is part of a gif tunnel. Please delete the tunnel to continue");
            } else {
                                // no validation errors, delete entry
                unset($config['interfaces'][$ifname]['enable']);
                $realid = get_real_interface($ifname);
                interface_bring_down($ifname, true);   /* down the interface */

				$realif = $config['interfaces'][$ifname]['if'];
                if(strpos($realif, 'pppoe') === 0){
                    foreach($config['ppps']['ppp'] as $idx=>$ppp){
                        if($ppp['if'] == $realif){
                            $realif = $ppp['ports'];
                            break;
                        }
                    }
                }
                if('pppoe'!=$config['interfaces'][$ifname]['ipaddr'] && $config['interfaces'][$ifname]['if'] != $nic){
                    throw new AppException('Network_705');
                }else if('pppoe' == $config['interfaces'][$ifname]['ipaddr']){
                    if(isset($config['ppps']['ppp']) && is_array($config['ppps']['ppp'])){
                        foreach($config['ppps']['ppp'] as $idx=>$ppp){
                            if($ppp['if'] == $config['interfaces'][$ifname]['if'] && $ppp['ports']!=$nic){
                                throw new AppException('Network_705');
                            }else if($ppp['if'] == $config['interfaces'][$ifname]['if'] && $ppp['ports']==$nic){
                                unset($config['ppps']['ppp'][$idx]);
                            }
                        }
                    }
                }
				$config['interfaces'][$ifname] = array('if'=>$realif,
                    'spoofmac'=>'',
                    'descr'=>$realif);
                $wan = strpos($if, 'wan')===0;
                if($wan){
                    if(isset($config['gateways']['gateway_item'])){
                        foreach($config['gateways']['gateway_item'] as $gn=>$gateway){
                            if($gateway['interface']==$ifname && strpos($gateway['name'], strtoupper($if))===0){
                                unset($config['gateways']['gateway_item'][$gn]);
                            }
                        }
                    }
                }
                //unset($config['interfaces'][$ifname]);  /* delete the specified OPTn or LAN*/

                if (isset($config['dhcpd'][$ifname])) {
                    unset($config['dhcpd'][$ifname]);
                    services_dhcpd_configure();
                }
                if (isset($config['filter']['rule'])) {
                    foreach ($config['filter']['rule'] as $x => $rule) {
                        if ($rule['interface'] == $ifname) {
                            unset($config['filter']['rule'][$x]);
                        }
                    }
                }
                if (isset($config['nat']['rule'])) {
                    foreach ($config['nat']['rule'] as $x => $rule) {
                        if ($rule['interface'] == $ifname) {
                            unset($config['nat']['rule'][$x]['interface']);
                        }
                    }
                }

                self::applyGatewayConfig();
                self::setMultiWan();
                write_config();

                /* If we are in firewall/routing mode (not single interface)
                 * then ensure that we are not running DHCP on the wan which
                 * will make a lot of ISP's unhappy.
                 */
                if (!empty($config['interfaces']['lan']) && !empty($config['dhcpd']['wan']) && !empty($config['dhcpd']['wan'])) {
                    unset($config['dhcpd']['wan']);
                }
            }
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = '100';
        }

        return $result;
    }

    private static function getWanGateway($waninfo){
        global $config;

        if('dhcp'==$waninfo['ipaddr']){
            $gateway_name = strtoupper($waninfo['descr']).'_DHCP';
        }else if('pppoe'==$waninfo['ipaddr']){
            $gateway_name =strtoupper($waninfo['descr']).'_PPPOE';
        }else{//static
            $gateway_name = strtoupper($waninfo['descr']).'_STATIC';
        }
        if(isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
            foreach($config['gateways']['gateway_item'] as $idx=>$tmp_gw){
                if($tmp_gw['name']==$gateway_name){
                    return $tmp_gw;
                }
            }
        }


        return false;
    }

    private static function setWanGateway($ifname, $waninfo, $monitorip=false, $wangw=false, $weight=1, $tier=1){
        global $config;

        $gateway = array();
        $gateway['interface'] = $ifname;
        if(!$wangw){
            $gateway['gateway'] = 'dynamic';
        }else{
            $gateway['gateway'] = $wangw;
        }
        $gateway['weight'] = $weight;
        $gateway['tier'] = $tier;
        $gateway['ipprotocol'] = 'inet';
        $gateway['interval'] = '';
        $gateway['avg_delay_samples'] = '';
        $gateway['avg_loss_samples'] = '';
        $gateway['avg_loss_delay_samples'] = '';
        if(!$monitorip){
            $gateway['monitor_disable'] = '1';
        }else{
            $gateway['monitor'] = $monitorip;
        }

        if('wan1'==$waninfo['descr']){
            $gateway['defaultgw'] = '1';
        }
        if('dhcp'==$waninfo['ipaddr']){
            $gateway['name'] = strtoupper($waninfo['descr']).'_DHCP';
        }else if('pppoe'==$waninfo['ipaddr']){
            $gateway['name'] =strtoupper($waninfo['descr']).'_PPPOE';
        }else{//static
            $gateway['name'] = strtoupper($waninfo['descr']).'_STATIC';
        }
        $gateway['descr'] = 'Interface '.strtoupper($waninfo['descr']). ' Gateway';

        if(!isset($config['gateways'])){
            $config['gateways'] = array('gateway_item'=>array());
        }else if(!isset($config['gateways']['gateway_item'])){
            $config['gateways']['gateway_item'] = array();
        }
        foreach($config['gateways']['gateway_item'] as $idx=>$tmp_gw){
            if($tmp_gw['interface'] == $ifname && strpos($tmp_gw['name'], strtoupper($waninfo['descr']))===0){
                unset($config['gateways']['gateway_item'][$idx]);
            }
        }
        $config['gateways']['gateway_item'][] = $gateway;
    }

    private static function setWanPpp($waninfo, $nic=false, $username=false, $password=false){
        global $config;

        if(!isset($config['ppps']) || !is_array($config['ppps'])){
            $config['ppps'] = array('ppp'=>array());
        }else if(!isset($config['ppps']['ppp']) || !is_array($config['ppps']['ppp'])){
            $config['ppps']['ppp'] = array();
        }

        foreach($config['ppps']['ppp'] as $idx=>$tmp_ppp){
            if($tmp_ppp['ports'] == $nic){
                unset($config['ppps']['ppp'][$idx]);
            }
        }

        if('pppoe'==$waninfo['ipaddr']){
            for($i=0; $i<=$config['ppps']['ppp']; $i++){
                $exist = false;
                foreach($config['ppps']['ppp'] as $idx=>$tmp_ppp){
                    if($i == intval($tmp_ppp['ptpid'])){
                        $exist = true;
                    }
                }
                if(!$exist){
                    break;
                }
            }

            $ppp = array();
            $ppp['ptpid'] = $i;
            $ppp['type'] = 'pppoe';
            $ppp['if'] = 'pppoe'.$i;
            $ppp['ports'] = $nic;
            $ppp['username'] = $username;
            $ppp['password'] = base64_encode($password);

            $config['ppps']['ppp'][] = $ppp;

            return $ppp['if'];
        }

    }

    private static function setMultiWan(){
        global $config;

        $wan_gateways = array();
        $gateways = false;
        if(isset($config['gateways']) && isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
            $gateways = $config['gateways']['gateway_item'];
        }
        foreach($config['interfaces'] as $ifinfo){
            if(strpos($ifinfo['descr'], 'wan')===0 && isset($ifinfo['ipaddr']) && !empty($ifinfo['ipaddr'])){
                $wan_gateways[] = array('descr'=>$ifinfo['descr'],'ipaddr'=>$ifinfo['ipaddr']);
            }
        }
        if(count($wan_gateways)<=1){
            if(isset($config['gateways']['gateway_group'])){
                unset($config['gateways']['gateway_group']);
            }
            foreach($config['filter']['rule'] as $idx=>$rule){
                if('DEFAULT_ALLOW_MULTIWAN_RULE'==$rule['descr']){
                    $config['filter']['rule'][$idx]['source']['any']='1';
                    $config['filter']['rule'][$idx]['disabled'] = '1';
                }
            }
        }else{
            $gateway_group = array('name'=>'MULTIWAN',
                'item'=>array(),
                'trigger'=>'downloss',
                'descr'=>'MULTIWAN:load barance group');
            foreach($wan_gateways as $wan_gateway){
                if('dhcp'!=$wan_gateway['ipaddr'] && 'pppoe'!=$wan_gateway['ipaddr']){
                    $wan_gateway['ipaddr'] = 'static';
                }
                $gateway_name = strtoupper($wan_gateway['descr'].'_'.$wan_gateway['ipaddr']);
                $tier = "1";
                if($gateways){
                    foreach($gateways as $gateway_item){
                        if($gateway_item['name'] == $gateway_name){
                            $tier = $gateway_item['tier'];
                        }
                    }
                }
                $gateway_group['item'][] = $gateway_name.'|'.$tier.'|ADDRESS';
            }
            $config['gateways']['gateway_group'] = array($gateway_group);
            foreach($config['filter']['rule'] as $idx=>$rule){
                if('DEFAULT_ALLOW_MULTIWAN_RULE'==$rule['descr']){
                    $config['filter']['rule'][$idx]['gateway']='MULTIWAN';
                    $config['filter']['rule'][$idx]['source']=array('address'=>'LAN1_TO_MULTIWAN');
                    unset($config['filter']['rule'][$idx]['disabled']);
                }
            }
        }
    }

    public static function setWanInfo(Array $data){
        global $config;

        $result = 0;
        try{
            $ifname = '';
            foreach($config['interfaces'] as $ifidx=>$ifinfo){
                if($data['Interface'] == $ifinfo['descr']){
                    $ifname = $ifidx;
                }
            }
            if(empty($ifname)){
                throw new AppException('Network_200');
            }
            $old_wan = $config['interfaces'][$ifname];
            $old_wan['ppps'] = $config['ppps'];
            if('pppoe'==$old_wan['ipaddr']){
                if(isset($config['ppps']) && isset($config['ppps']['ppp'])&& is_array($config['ppps']['ppp'])){
                    foreach($config['ppps']['ppp'] as $ppp){
                        if($ppp['if'] == $old_wan['if']){
                            if($ppp['ports']!=$data['Nic']){
                                throw new AppException('Network_201');
                            }
                        }
                    }
                }
            }else if($old_wan['if'] != $data['Nic']){
                throw new AppException('Network_201');
            }

            if(strpos($data['Interface'], 'wan')!==0){
                throw new AppException('Network_202');
            }

            $mtu = intval($data['Mtu']);
            if($mtu<=0 || $mtu>1500){
                $mtu=1480;
            }
            if(!is_ipaddr($data['Dns'])){
                throw new AppException('Network_203');
            }
            $weight = intval($data['Weight']);
            $tier = intval($data['Tier']);
            if($weight<1 || $weight>4){
                throw new AppException('Network_210');
            }
            if($tier<1 || $tier>4){
                throw new AppException('Network_211');
            }
            if(strlen($data['Monitor'])>0){
                if(!is_ipaddr($data['Monitor'])) {
                    throw new AppException('Network_204');
                }
                $monitor = $data['Monitor'];
            }else{
                $monitor = $data['Dns'];
            }
			if(isset($data['MacClone']) && !empty($data['MacClone'])){
                if(!is_macaddr($data['MacClone'])){
                    throw new AppException('Network_213');
                }
            }else{
                $data['MacClone'] = '';
            }


            $destroy = false;
            $wan = array();
            $wan['descr'] = $data['Interface'];

            if('dhcp'==$data['Protocol']){
                $wan['if'] = $data['Nic'];
                $wan['enable'] = '1';
                $wan['ipaddr']='dhcp';
                $wan['mtu'] = $mtu;
                $wan['spoofmac'] = $data['MacClone'];
                $wan['blockbogons'] = '1';
                $wan['dhcphostname'] = '';
                $wan['alias-address'] = '';
                $wan['alias-subnet'] = '32';
                $wan['dhcprejectfrom'] = '';
                $wan['adv_dhcp_pt_timeout'] = '';
                $wan['adv_dhcp_pt_retry'] = '';
                $wan['adv_dhcp_pt_select_timeout'] = '';
                $wan['adv_dhcp_pt_reboot'] = '';
                $wan['adv_dhcp_pt_backoff_cutoff'] = '';
                $wan['adv_dhcp_pt_initial_interval'] = '';
                $wan['adv_dhcp_pt_values'] = 'SavedCfg';
                $wan['adv_dhcp_send_options'] = '';
                $wan['adv_dhcp_request_options'] = '';
                $wan['adv_dhcp_required_options'] = '';
                $wan['adv_dhcp_option_modifiers'] = '';
                $wan['adv_dhcp_config_advanced'] = '';
                $wan['adv_dhcp_config_file_override'] = '';
                $wan['adv_dhcp_config_file_override_path'] = '';
                $config['interfaces'][$ifname] = $wan;

                self::setWanGateway($ifname, $wan, $monitor, false, $weight, $tier);
                self::setWanPpp($wan, $data['Nic']);
                if('pppoe'==$old_wan['ipaddr']){
                    $destroy = true;
                }
            }else if('static'==$data['Protocol']){
                $data['AutoDns'] = '0';
                if(!is_ipaddr($data['Ip'])){
                    throw new AppException('Network_205');
                }
                if(!is_ipaddr($data['Netmask'])){
                    throw new AppException('Network_206');
                }
                if(!is_ipaddr($data['Gateway'])){
                    throw new AppException('Network_207');
                }
                $wan['if'] = $data['Nic'];
                $wan['enable'] = '1';
                $wan['spoofmac'] = $data['MacClone'];
                $wan['blockbogons'] = '1';
                $wan['ipaddr'] = $data['Ip'];
                $wan['subnet'] = Util::maskip2bit($data['Netmask']);
                $wan['mtu'] = $mtu;
                $wan['gateway'] = strtoupper($wan['descr']).'_STATIC';
                $config['interfaces'][$ifname] = $wan;

                self::setWanGateway($ifname, $wan, $monitor, $data['Gateway'], $weight, $tier);
                self::setWanPpp($wan, $data['Nic']);
                if('pppoe'==$old_wan['ipaddr']){
                    $destroy = true;
                }
            }else if('pppoe'==$data['Protocol']){
                if(strlen($data['Username'])==0 || strlen($data['Password'])==0){
                    throw new AppException('Network_208');
                }
                if('pppoe' == $old_wan['ipaddr']){
                    $wan['if'] = $old_wan['if'];
                }else{
                    $wan['if'] = '';
                }
                $wan['enable'] = '1';
                $wan['ipaddr'] = 'pppoe';
                $wan['spoofmac'] = $data['MacClone'];
                $wan['blockbogons'] = '1';
                $wan['mtu'] = $mtu>1492?1492:$mtu;

                $wan['gateway'] = '';
                self::setWanGateway($ifname, $wan, $monitor, false, $weight, $tier);
                $wan['if'] = self::setWanPpp($wan, $data['Nic'], $data['Username'], $data['Password']);
                $config['interfaces'][$ifname] = $wan;

                if('pppoe'!=$old_wan['ipaddr']){
                    $destroy = true;
                }
            }else{
                throw new AppException('Network_209');
            }


            $config['system']['dnsallowoverride'] = '1';
            $setdns = false;
            if(!is_array($config['system']['dnsserver'])){
                $config['system']['dnsserver'] = array();
            }
            if('dhcp'!=$wan['ipaddr']&& 'pppoe'!=$wan['ipaddr']){
                $wan['ipaddr']='static';
            }
            for($i=0; $i<count($config['system']['dnsserver']); $i++){
                $j=$i+1;
                if('none'==$config['system']['dns'.$j.'gw']){
                    $config['system']['dns'.$j.'gw'] = strtoupper($wan['descr']).'_'.strtoupper($wan['ipaddr']);
                    $setdns = true;
                }else if(strpos($config['system']['dns'.$j.'gw'], strtoupper($wan['descr']))===0){
                    $config['system']['dnsserver'][$i] = $data['Dns'];
                    $config['system']['dns'.$j.'gw'] = strtoupper($wan['descr']).'_'.strtoupper($wan['ipaddr']);
                    $setdns = true;
                }else{
                    if($config['system']['dnsserver'][$i] == $data['Dns']){
                        throw new AppException('Network_212');
                    }
                }
            }
            if(!$setdns){
                $config['system']['dnsserver'][]=$data['Dns'];
                $cnt = count($config['system']['dnsserver']);
                $config['system']['dns'.$cnt.'gw'] = strtoupper($wan['descr']).'_'.strtoupper($wan['ipaddr']);
            }

            self::setMultiWan();

            write_config();
            if (!empty($old_wan['ipaddr']) && $old_wan['ipaddr'] == 'dhcp' && $config['interfaces'][$ifname]['ipaddr'] != 'dhcp') {
                // change from dhcp to something else, kill dhclient
                kill_dhclient_process($old_wan['if']);
            }
            if (!empty($old_wan['ipaddrv6']) && $old_wan['ipaddrv6'] == 'dhcp6' && $config['interfaces'][$ifname]['ipaddrv6'] != 'dhcp6') {
                // change from dhcp to something else, kill dhcp6c
                killbypid("/var/run/dhcp6c_{$old_wan['if']}.pid");
            }
            if (isset($config['interfaces'][$ifname]['enable'])) {
                $old_wan['realif'] = $old_wan['if'];
                if(isset($old_wan['ppps']['ppp']) && is_array($old_wan['ppps']['ppp'])){
                    interface_bring_down($ifname, $destroy, array('ifcfg'=>$old_wan,'ppps'=>$old_wan['ppps']['ppp']));
                }else{
                    interface_bring_down($ifname, $destroy, array('ifcfg'=>$old_wan));
                }
                interface_configure($ifname, true);
            } else {
                interface_bring_down($ifname, true, $config['interfaces'][$ifname]);
            }

            plugins_configure('newwanip');

            /* sync filter configuration */
            setup_gateways_monitor();
            filter_configure();
            rrd_configure();
            if (is_subsystem_dirty('staticroutes') && (system_routing_configure() == 0)) {
                clear_subsystem_dirty('staticroutes');
            }
            /*
            system_routing_configure();
            filter_configure();
            setup_gateways_monitor();
            //enable_rrd_graphing();

            system_resolvconf_generate();
            self::applyGatewayConfig();
            */
            $result = 0;
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = '100';
        }

        return $result;
    }

    public static function getStaticDhcp(){
        global $config;

        $static_dhcp = array();
        if(is_array($config['dhcpd']['lan']['staticmap'])){
        	foreach($config['dhcpd']['lan']['staticmap'] as $macip){
            $a_macip = array('Mac'=>$macip['mac'],'Ip'=>$macip['ipaddr'],'Descr'=>'');
            if(isset($macip['descr'])){
                $a_macip['Descr']=$macip['descr'];
            }
            $static_dhcp[] = $a_macip;
        	}
        }

        return $static_dhcp;
    }

    public static function addStaticDhcp($data){
        global $config;

        $result = 0;
        try{
            if(!is_macaddr($data['Mac'])){
                throw new AppException('Network_400');
            }
            if(!is_ipaddr($data['Ip'])){
                throw new AppException('Network_401');
            }
//            if(!isset($data['Descr']) || strlen($data['Descr'])==0){
//                throw new AppException('Network_402');
//            }

            $netMask = 32 - intval($config['interfaces']['lan']['subnet']);
            $long = sprintf("%u", ip2long($data['Ip']));
            $lanIp = sprintf("%u", ip2long($config['interfaces']['lan']['ipaddr']));
            $ip_net = ($long>>$netMask)<<$netMask;
            $lan_net = ($lanIp>>$netMask)<<$netMask;

            if($ip_net != $lan_net){
                throw new AppException('Network_405');
            }

            if ($config['dhcpd']['lan']['staticmap']){
                foreach($config['dhcpd']['lan']['staticmap'] as $macip){
                    if($data['Mac'] == $macip['mac']){
                        throw new AppException('Network_403');
                    }
                    if($data['Ip'] == $macip['ipaddr']){
                        throw new AppException('Network_404');
                    }
                }
            }
            $config['dhcpd']['lan']['staticmap'][] = array('mac'=>$data['Mac'], 'ipaddr'=>$data['Ip'], 'descr'=>$data['Descr']);
            write_config();
            services_dhcpd_configure();
        }catch (AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delStaticDhcp($data){
        global $config;

        $result = 0;
        try{
            if (!is_array($data['Mac']) || count($data['Mac'])<=0) {
                throw new AppException('Network_400');
            }
            foreach($data['Mac'] as $mac){
                if(!is_macaddr($mac)){
                    throw new AppException('Network_400');
                }
                foreach($config['dhcpd']['lan']['staticmap'] as $idx=>$map){
                    if($map['mac'] == $mac){
                        unset($config['dhcpd']['lan']['staticmap'][$idx]);
                    }
                }
            }
            write_config();
            services_dhcpd_configure();
        }catch (AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getStaticRoute(){
        global $config;

        $static_route = array();
        if(isset($config['staticroutes']['route']) && is_array($config['staticroutes']['route'])){
            foreach($config['staticroutes']['route'] as $route){
                $netinfo = explode('/',$route['network']);
                if(count($netinfo)!=2){
                    continue;
                }
                $ip = $netinfo[0];
                $netmask = Util::maskbit2ip($netinfo[1]);
                $a_route = array('Interface'=>'', 'Ip'=>$ip,'Netmask'=>$netmask, 'Gateway'=>'', 'Descr'=>'');
                if(isset($route['descr'])){
                    $a_route['Descr']=$route['descr'];
                }
                if(isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                    foreach($config['gateways']['gateway_item'] as $gateway){
                        if($gateway['name'] == $route['gateway']){
                            $a_route['Gateway'] = $gateway['gateway'];
                            $a_route['Interface'] = $config['interfaces'][$gateway['interface']]['descr'];
                            break;
                        }
                    }
                }

                $static_route[] = $a_route;
            }
        }

        return $static_route;
    }

    public static function addStaticRoute($data){
        global $config;

        if(!isset($config['gateways']) || !is_array($config['gateways'])){
            $config['gateways'] = array();
        }
        $result = 0;
        try{
            $ifexist = false;
            $ifname='';
            foreach($config['interfaces'] as $idx=>$ifinfo){
                if($ifinfo['descr'] == $data['Interface']){
                    $ifexist = true;
                    $ifname = $idx;
                    break ;
                }
            }
            if(!$ifexist){
                throw new AppException('Network_500');
            }

            if(!is_ipaddr($data['Ip'])){
                throw new AppException('Network_205');
            }
            if(!is_ipaddr($data['Netmask'])){
                throw new AppException('Network_206');
            }
            $netmask = Util::maskip2bit($data['Netmask']);
            if(false === $netmask){
                throw new AppException('Network_206');
            }
            if(!is_ipaddr($data['Gateway'])){
                throw new AppException('Network_207');
            }
            if(is_ipaddr($config['interfaces'][$ifname]['ipaddr'])){
                $inf_net = $config['interfaces'][$ifname]['ipaddr'].'/'.$config['interfaces'][$ifname]['subnet'];
            }else{
                $infinfo = get_interface_info($ifname);
                $inf_net = $infinfo['ipaddr'].'/'.$infinfo['subnet'];
            }
            $subnet = $data['Ip'].'/'.$netmask;
            if(!ip_in_subnet($data['Gateway'], $inf_net)){
                throw new AppException('Network_501');
            }

            if(!isset($data['Descr']) || strlen($data['Descr'])==0){
                throw new AppException('Network_502');
            }
            if(isset($config['staticroutes'])&&isset($config['staticroutes']['route'])&&is_array($config['staticroutes']['route'])){
                foreach($config['staticroutes']['route'] as $a_route){
                    if($a_route['network'] == $subnet){
                        throw new AppException('Network_503');
                    }
                }
            }else{
                if(!isset($config['staticroutes']) || !is_array($config['staticroutes'])){
                    $config['staticroutes'] = array();
                }
                $config['staticroutes']['route'] = array();
            }

            $gatewayname = '';
            if(isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                //check whether the gateway exists
                foreach($config['gateways']['gateway_item'] as $idx=>$now_gateway){
                    if($now_gateway['interface'] == $ifname && $now_gateway['gateway'] == $data['Gateway']){
                        $gatewayname = $now_gateway['name'];
                        break ;
                    }
                }
            }else{
                $config['gateways']['gateway_item'] = array();
            }

            if(empty($gatewayname)){//gateway not exists, create it
                for($i=0; $i<=count($config['gateways']['gateway_item']); $i++){
                    $gatewayname = 'STATIC'.$i;
                    $gw_exist = false;
                    foreach($config['gateways']['gateway_item'] as $idx=>$gateway_tmp){
                        if($gateway_tmp['name']==$gatewayname){
                            $gw_exist = true ;
                        }
                    }
                    if(!$gw_exist){
                        break ;
                    }
                }

                $gateway = array();
                $gateway['interface'] = $ifname;
                $gateway['gateway'] = $data['Gateway'];
                $gateway['name'] = $gatewayname;
                $gateway['weight'] = '1';
                $gateway['ipprotocol'] = 'inet';
                $gateway['interval'] = '';
                $gateway['descr'] = '';
                $gateway['avg_delay_samples'] = '';
                $gateway['avg_loss_samples'] = '';
                $gateway['avg_loss_delay_samples'] = '';
                $gateway['monitor_disable'] = '1';
                $config['gateways']['gateway_item'][] = $gateway;
            }


            $static_route = array('network'=>$subnet, 'gateway'=>$gatewayname, 'descr'=>$data['Descr']);
            $config['staticroutes']['route'][] = $static_route;

            write_config();
            system_routing_configure();
            filter_configure();
            setup_gateways_monitor();
            clear_subsystem_dirty('staticroutes');
        }catch (AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delStaticRoute($data){
        global $config;

        $result = 0;
        try{
            $ifname = '';
            foreach($config['interfaces'] as $idx=>$ifinfo){
                if($ifinfo['descr'] == $data['Interface']){
                    $ifname = $idx;
                }
            }
            if(empty($ifname)){
                throw new AppException('Network_600');
            }
            if(!is_ipaddr($data['Ip'])){
                throw new AppException('Network_205');
            }
            if(!is_ipaddr($data['Netmask'])){
                throw new AppException('Network_206');
            }
            $netmask = Util::maskip2bit($data['Netmask']);
            if(false === $netmask){
                throw new AppException('Network_206');
            }

            if(!isset($config['staticroutes']) || !is_array($config['staticroutes']) ||
                !isset($config['staticroutes']['route']) || !is_array($config['staticroutes']['route'])) {
                throw new AppException('Network_601');
            }
            $subnet=$data['Ip'].'/'.$netmask;
            $deleted = false;
            $gatewayname = '';
            foreach($config['staticroutes']['route'] as $idx=>$a_route){
                if($a_route['network'] == $subnet){
                    $gatewayname = $a_route['gateway'];
                    unset($config['staticroutes']['route'][$idx]);
                    $deleted = true;
                }
            }
            if(false === $deleted){
                throw new AppException('Network_601');
            }
            if(!empty($gatewayname)){
                $gateway_useless = true;
                foreach($config['staticroutes']['route'] as $idx=>$a_route){//check whether the gateway is still used
                    if($a_route['gateway'] == $gatewayname){
                        $gateway_useless = false;
                        break ;
                    }
                }
                if($gateway_useless && isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                    foreach ($config['gateways']['gateway_item'] as $j=>$gateway){
                        if($gateway['name'] == $gatewayname){
                            unset($config['gateways']['gateway_item'][$j]);
                            break ;
                        }
                    }
                }
            }

            mwexec("/sbin/route delete -inet $subnet");
            write_config();
            system_routing_configure();
            filter_configure();
            setup_gateways_monitor();
            clear_subsystem_dirty('staticroutes');
        }catch (AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getArpTable(){
        global $config;

        $static_route = array();
        if(isset($config['staticroutes']['route']) && is_array($config['staticroutes']['route'])){
            foreach($config['staticroutes']['route'] as $route){
                $netinfo = explode('/',$route['network']);
                if(count($netinfo)!=2){
                    continue;
                }
                $ip = $netinfo[0];
                $netmask = Util::maskbit2ip($netinfo[1]);
                $a_route = array('Interface'=>'', 'Ip'=>$ip,'Netmask'=>$netmask, 'Gateway'=>'', 'Descr'=>'');
                if(isset($route['descr'])){
                    $a_route['Descr']=$route['descr'];
                }
                if(isset($config['gateways']['gateway_item']) && is_array($config['gateways']['gateway_item'])){
                    foreach($config['gateways']['gateway_item'] as $gateway){
                        if($gateway['name'] == $route['gateway']){
                            $a_route['Gateway'] = $gateway['gateway'];
                            $a_route['Interface'] = $gateway['interface'];
                            break;
                        }
                    }
                }

                $static_route[] = $a_route;
            }
        }

        return $static_route;
    }

    public static function getNetInfo(){
        $t = time();
        $traffic1 = traffic_api();
        sleep(1);
        $traffic2 = traffic_api();
        $waninfo = self::getWanInfo();
        $data = array();
        foreach($waninfo['Interfaces'] as $a_waninfo){
            $iftraffic = array();
            $iftraffic['type'] = $a_waninfo['Protocol'];
            $iftraffic['status'] = "1";
            $iftraffic['ip'] = $a_waninfo['Status']['ipaddr'];
            $iftraffic['gateway'] = $a_waninfo['Status']['gateway'];
            $iftraffic['timestamp'] = (string)$t;
            $iftraffic['up'] = $traffic2['interfaces'][$a_waninfo['Nic']]['bytes transmitted']-$traffic1['interfaces'][$a_waninfo['Nic']]['bytes transmitted'];
            $iftraffic['down'] = $traffic2['interfaces'][$a_waninfo['Nic']]['bytes received']-$traffic1['interfaces'][$a_waninfo['Nic']]['bytes received'];;
            $data[$a_waninfo['Nic']] = $iftraffic;
        }
        return $data;
    }

    public static function getLinksData(){
        $result = array("statistics"=>array('upload'=>0, 'download'=>0));
        $connections = array();
        $real_interface = get_real_interface('lan');
        if (does_interface_exist($real_interface)) {
            $netmask = find_interface_subnet($real_interface);
            $intsubnet = gen_subnet(find_interface_ip($real_interface), $netmask) . "/$netmask";
            $cmd_args = " -c " . $intsubnet . " ";
            $cmd_args .= " -R ";
            $cmd_action = "/usr/local/bin/rate -v -i {$real_interface} -nlq 1 -Aba 20 {$cmd_args} | tr \"|\" \" \" | awk '{ printf \"%s:%s:%s:%s:%s\\n\", $1,  $2,  $4,  $6,  $8
}'";
            exec($cmd_action, $listedIPs);
            for ($idx = 2 ; $idx < count($listedIPs) ; ++$idx) {
                $fields = explode(':', $listedIPs[$idx]);
                if (!empty($pconfig['hostipformat'])) {
                    $addrdata = gethostbyaddr($fields[0]);
                } else {
                    $addrdata = $fields[0];
                }
                $connections[] = array('src' => $addrdata, 'count_upload' => $fields[4], 'count_download' => $fields[3],'download'=>$fields[1], 'upload'=>$fields[2]);
                $result['statistics']['upload'] += $fields[4];
                $result['statistics']['download'] += $fields[3];
            }
        }
        $result['connections'] = $connections;

        return $result;
    }

    public static function getLanWanInf(){
        global $config;

        $result = array();
        foreach($config['interfaces'] as $inf=>$interface){
            if(strpos($interface['descr'], 'lan')===0 || strpos($interface['descr'], 'wan')===0){
                $result[] = array('interface'=>$inf, 'name'=>$interface['descr']);
            }
        }

        return $result;
    }
}