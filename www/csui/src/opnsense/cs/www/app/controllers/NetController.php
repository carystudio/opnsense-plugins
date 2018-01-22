<?php

require_once("util.inc");
require_once("filter.inc");
require_once("rrd.inc");
require_once("interfaces.inc");
require_once("config.inc");

class NetController extends BaseController
{
    public function showloginAction()
    {
	
    }

    public function wanAction()
    {
    }

    public function set_wanAction()
    {
    	global $config;
    	if (true == $this->request->isPost()) {
            $post = $this->request->getPost();

			$config['interfaces'][$post['descr']]['enable'] = $post['enable'];
			$config['interfaces'][$post['descr']]['mtu']    = $post['mtu'];
            if ('dhcp' == $post['type']) {
	            $config['interfaces'][$post['descr']]['ipaddr'] = $post['type'];
            }elseif ('static' == $post['type']) {
            	// TODO 子网掩码待完成
                $config['interfaces'][$post['descr']]['ipaddr'] = $post['ipaddr'];
                $config['interfaces'][$post['descr']]['subnet'] = Util::maskip2bit($post['mask']);
                // 网关添加
                $gateways_length = count($config['gateways']['gateway_item']);
                $config['gateways']['gateway_item'][$gateways_length]['interface']  = $post['descr'];
                $config['gateways']['gateway_item'][$gateways_length]['gateway']    = $post['gateway'];
                $config['gateways']['gateway_item'][$gateways_length]['name']       = strtoupper($post['descr']) . 'GW';
                $config['gateways']['gateway_item'][$gateways_length]['ipprotocol'] = 'inet';

	            $config['interfaces'][$post['descr']]['gateway'] = strtoupper($post['descr']) . 'GW';

            }elseif ('pppoe' == $post['type']) {
            	// TODO 待完成。。。
            	// 问题是多个账号的时候下标不知道定义在哪里？
				$config['ppps']['ppp'][0]['ptpid']    = 0;
				$config['ppps']['ppp'][0]['type']     = 'pppoe';
				$config['ppps']['ppp'][0]['if']       = 'pppoe0';
				$config['ppps']['ppp'][0]['ports']    = $config['interfaces'][$post['descr']]['if'];
				$config['ppps']['ppp'][0]['username'] = $post['pppoeUser'];
				$config['ppps']['ppp'][0]['password'] = base64_encode($post['pppoePass']);
            	// 
				$config['interfaces'][$post['descr']]['if']     = 'pppoe0';
				$config['interfaces'][$post['descr']]['ipaddr'] = 'pppoe';
            }

        	$config['system']['dnsserver'][0] = $post['dns'];
        	$config['system']['dnsserver'][1] = $post['dns1'];

            write_config();


            // 待定 （更新系统配置）
            clear_subsystem_dirty('interfaces');
            if (file_exists('/tmp/.interfaces.apply')) {
                $toapplylist = unserialize(file_get_contents('/tmp/.interfaces.apply'));
                foreach ($toapplylist as $ifapply => $ifcfgo) {
                    if (isset($config['interfaces'][$ifapply]['enable'])) {
                        interface_bring_down($ifapply, false, $ifcfgo);
                        interface_configure($ifapply, true);
                    } else {
                        interface_bring_down($ifapply, true, $ifcfgo);
                    }
                }
            }
            /* restart plugins */
            if (function_exists('plugins_configure')) {
                plugins_configure('interface');
            }
            /* sync filter configuration */
            setup_gateways_monitor();
            filter_configure();
            enable_rrd_graphing();
            if (is_subsystem_dirty('staticroutes') && (system_routing_configure() == 0)) {
                clear_subsystem_dirty('staticroutes');
            }

            // interface_configure($post['descr'], true);
            // interface_bring_down($post['descr'], false, $config['dhcpd']['lan']);
            // reconfigure_dhcpd();

            $res = ['code'=>1,'msg'=>'更新成功！'];
            echo json_encode($res);
            exit();
        }

		var_dump($config['interfaces']);
		// var_dump($config['dhcpd']);
  //       var_dump($config['system']['dnsserver']);
		var_dump($config['gateways']);
    }

    /**
     * 当wan切换的时候请求的数据
     * @Author Yexk
     * @Date   2017-02-10
     * @return [string]     [对象字符串]
     */
    public function get_wanAction()
    {
        global $config;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            $my_config = $config;
            $my_config['cs_mask'] = !empty($config['interfaces'][$_POST['ifname']]['subnet']) ? Util::maskbit2ip($config['interfaces'][$_POST['ifname']]['subnet']) : '0.0.0.0';
            $cs_getways = '0.0.0.0';
            foreach ($config['gateways']['gateway_item'] as $k => $v) {
                if (in_array($_POST['ifname'],$v)) {
                    $cs_getways = $v['gateway'];
                }
            }
            $my_config['cs_getways'] = $cs_getways;

            echo json_encode($my_config);

            exit;
        }

    }


    public function dhcpAction()
    {
        
    }

    public function arpinfoAction()
    {

    }

    // 端口分流
    public function port_shuntAction()
    {

    }
    
    public function routeAction()
    {

    }


}
