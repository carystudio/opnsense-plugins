<?php
class FirewallController extends BaseController
{
    // IP/端口过滤
    public function ipport_filteringAction()
    {
        require_once("interfaces.inc");
        require_once("system.inc");
        require_once("util.inc");
        require_once("filter.inc");

        global $config;
        if (true == $this->request->isPost()) 
        {
            $post = $this->request->getPost();
            $tesmp = [];
            // 删除操作
            if (isset($post['del'])) {
                $array_filter = explode(',',$post['id']);
                for ($i=0; $i < count($array_filter); $i++) {
                    $tesmp[$i] = $config['filter']['rule'][$array_filter[$i]];
                    unset($config['filter']['rule'][$array_filter[$i]]);
                }
                
                write_config();
                mark_subsystem_dirty('filter');

                echo json_encode(['code'=>1,'msg'=>'删除成功！','data'=>$tesmp]);
                exit;
            }

            /*array(13) {
                ["type"]=> string(4) "pass"
                ["interface"]=> string(4) "opt1"
                ["ipprotocol"]=> string(4) "inet"
                ["statetype"]=> string(10) "keep state"
                ["descr"]=> string(14) "sdafwesdfcxxxx"
                ["direction"]=> string(3) "any"
                ["quick"]=> string(3) "yes"
                ["floating"]=> string(3) "yes"
                ["protocol"]=> string(3) "udp"
                ["source"]=> array(1) { ["any"]=> string(1) "1" }
                ["destination"]=> array(2) {
                    ["address"]=> string(14) "192.168.15.125" 
                    ["port"]=> string(8) "645-1569"
                }*/
            $config_temp                = [];
            $config_temp['type']        = 'pass';
            $config_temp['interface']   = $post['interface'];
            $config_temp['ipprotocol']  = 'inet';
            $config_temp['statetype']   = 'keep state';
            $config_temp['descr']       = $post['descr'];
            $config_temp['direction']   = 'any';
            $config_temp['quick']       = 'yes';
            $config_temp['floating']    = 'yes';
            $config_temp['protocol']    = $post['protocol'];
            $config_temp['source']      = array(['any'=>'1']);
            $config_temp['destination'] = array(['address'=>$post['address'],'port'=>$post['port']]);
            $config_temp['created']     = make_config_revision_entry();
            $config_temp['updated']     = make_config_revision_entry();

            $config['filter']['rule'][] = $config_temp;

             // sort filter items per interface, not really necessary but leaves a bit nicer sorted config.xml behind.
            filter_rules_sort();
            system_cron_configure();
            // write to config
            write_config();
            mark_subsystem_dirty('filter');
            // apply changes
            filter_configure();
            clear_subsystem_dirty('filter');

            echo json_encode(['code'=>1,'msg'=>'保存成功！']);
            exit;
        }

        $this->view->setVar("filter", $config['filter']['rule']);
    }
    
    // MAC过滤
	public function mac_filteringAction()
    {
	
    }
    
    // URL过滤
	public function url_filteringAction()
    {
	
    }

    // 端口转发
	public function port_forwardAction()
    {

        require_once("interfaces.inc");
        require_once("util.inc");
        require_once("system.inc");
        require_once("filter.inc");

        global $config;
        if (true == $this->request->isPost()) 
        {
            $post = $this->request->getPost();
            $port_forward = &$config['nat']['rule'];

            // 删除操作
            if (isset($post['del'])) {
                $array_filter = explode(',',$post['id']);
                for ($i=0; $i < count($array_filter); $i++) {

                    for ($j=0; $j < count($config['filter']['rule']); $j++) { 

                        if ($port_forward[$array_filter[$i]]['associated-rule-id'] == $config['filter']['rule'][$j]['associated-rule-id']) {
                            unset($config['filter']['rule'][$j]);
                        }
                    }

                    unset($port_forward[$array_filter[$i]]);
                }
                
                write_config();
                mark_subsystem_dirty('filter');
                mark_subsystem_dirty('natconf');

                filter_configure();
                clear_subsystem_dirty('natconf');
                clear_subsystem_dirty('filter');
                echo json_encode(['code'=>1,'msg'=>'删除成功！','data'=>'']);
                exit;
            }

              /*array(11) {
                ["protocol"]   => string(3) "tcp"
                ["interface"]  => string(3) "lan"
                ["ipprotocol"] => string(4) "inet"
                ["descr"]              => string(15) " 描xxxxx述..."
                ["associated-rule-id"] => string(0) ""
                ["target"]             => string(14) "192.168.154.78"
                ["local-port"]         => string(3) "588"
                ["source"]             => 
                ["destination"]=> array(2) {
                  ["address"]=> string(14) "192.168.154.78"
                  ["port"]=> string(10) "8012-46547"
                }
                ["updated"]=> array(3) {
                  ["username"]=> string(17) "root@192.168.24.1"
                  ["time"]=> string(15) "1486201585.4132"
                  ["description"]=> string(35) "/firewall_nat_edit.php made changes"
                }
                ["created"]=> array(3) {
                  ["username"]=> string(17) "root@192.168.24.1"
                  ["time"]=> string(15) "1486200333.7201"
                  ["description"]=> string(35) "/firewall_nat_edit.php made changes"
                }
              }*/
            $config_temp                       = [];
            $config_temp['type']               = 'pass';
            $config_temp['interface']          = $post['interface'];
            $config_temp['protocol']           = $post['protocol'];
            $config_temp['ipprotocol']         = 'inet';
            $config_temp['descr']              = $post['descr'];
            $config_temp['associated-rule-id'] = uniqid("nat_", true);
            $config_temp['target']             = $post['address'];
            $config_temp['local-port']         = 80;
            $config_temp['source']             = array(['any'=>'1']);
            $config_temp['destination']        = array(['address'=>$post['address'],'port'=>$post['port']]);
            $config_temp['created']            = make_config_revision_entry();
            $config_temp['updated']            = make_config_revision_entry();
            // 保存数据
            $port_forward[] = $config_temp;

            write_config();
            mark_subsystem_dirty('natconf');


             /*array(8) {
                ["source"]=> array(1) {
                  ["any"]=> string(1) "1"
                }
                ["interface"]=> string(3) "lan"
                ["protocol"]=> string(7) "tcp/udp"
                ["ipprotocol"]=> string(4) "inet"
                ["destination"]=> array(2) {
                  ["address"]=> string(15) "192.168.102.102"
                  ["port"]=> string(6) "80-191"
                }
                ["descr"]=> string(25) "NAT 我是一个描述。"
                ["associated-rule-id"]=> string(27) "nat_5895d547e82324.99139475"
                ["created"]=> array(3) {
                  ["username"]=> string(17) "root@192.168.24.1"
                  ["time"]=> string(15) "1486214471.9509"
                  ["description"]=> string(35) "/firewall_nat_edit.php made changes"
                }
              }
            }*/

            $config_temp_filter                       = [];
            $config_temp_filter['interface']          = $post['interface'];
            $config_temp_filter['protocol']           = $post['protocol'];
            $config_temp_filter['ipprotocol']         = 'inet';
            $config_temp_filter['descr']              = substr("NAT " . $post['descr'], 0, 62);
            $config_temp_filter['source']             = array(['any'=>'1']);
            $config_temp_filter['destination']        = array(['address'=>$post['address'],'port'=>$post['port']]); 
            $config_temp_filter['associated-rule-id'] = $config_temp['associated-rule-id'];
            $config_temp_filter['created']            = make_config_revision_entry();
            $config['filter']['rule'][] = $config_temp_filter;
            write_config();
            mark_subsystem_dirty('filter');
           
            filter_configure();
            clear_subsystem_dirty('natconf');
            clear_subsystem_dirty('filter');

            echo json_encode(['code'=>1,'msg'=>'保存成功！']);
            exit;
        }

        $this->view->setVar("nat", $config['nat']['rule']);
    }

    // dns转发
    public function dns_forwardAction()
    {

    }

    // VPN穿透设置
    public function vpnpassAction()
    {
    
    }

    // DMZ设置
    public function dmzAction()
    {
    
    }

    // DoS设置
    public function dosAction()
    {
    
    }

    public function fwScheduleAction()
    {
    
    }


    public function qosAction()
    {
    
    }

	public function connlimitAction()
    {
    
    }
    
    public function testAction()
    {
        global $config;

        var_dump($config['nat']['rule']);
        var_dump($config['filter']['rule']);
	
    }

}
