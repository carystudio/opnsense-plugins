<?php

use Phalcon\Mvc\Controller;
require_once("config.inc");
require_once("util.inc");
require_once ("interfaces.inc");
require_once ("services.inc");

class UserController extends BaseController
{
    public function lanAction()
    {
        global $config;
        var_dump($config);
        $this->view->setVar('wanip', $config['interfaces']['wan']['ipaddr']);
        $this->view->setVar('lanip', $config['interfaces']['lan']['ipaddr']);
        $this->view->setVar('lanNetmask',Util::maskbit2ip($config['interfaces']['lan']['subnet']));
        if(isset($config['dhcpd']['lan']['enable'])){
            $this->view->setVar('dhcpEnabled','1');
        }else{
            $this->view->setVar('dhcpEnabled','0');
        }
        $this->view->setVar('dhcpLease',$config['dhcpd']['lan']['maxleasetime']);
        $this->view->setVar('dhcpStart',$config['dhcpd']['lan']['range']['from']);
        $this->view->setVar('dhcpEnd',$config['dhcpd']['lan']['range']['to']);
        header("Pragma: no-cache");
    }

    public function setting_lanAction()
    {
        global $config;

        $result = array("Success"=>'0');
        try{
            $para = json_decode($this->request->getRawBody(), true);
            if(!isset($para['lanIp'])){
                throw new AppException('request para not correct');
            }
            $lanIp = $para['lanIp'];
            $lanNetmask = $para['lanNetmask'];
            $lanMaskLen = intval($para['lanMaskLen']);
            $lanDhcpType = $para['lanDhcpType'];
            $dhcpStart = $para['dhcpStart'];
            $dhcpEnd = $para['dhcpEnd'];
            $dhcpLease = intval($para['dhcpLease']);
            if(!is_ipaddrv4($lanIp)){
                throw new AppException('lan ip not correct');
            }
            if(!is_ipaddrv4($lanNetmask)){
                throw new AppException('lan netmask not correct');
            }
            if($lanMaskLen>=31 || $lanNetmask<1){
                throw new AppException('lan netmask not correct');
            }
            if('1'==$lanDhcpType){
                if(!is_ipaddrv4($dhcpStart)){
                    throw new AppException('dhcp start not correct');
                }
                if(!is_ipaddrv4($dhcpEnd)){
                    throw new AppException('dhcp end not correct');
                }
            }
            $config['interfaces']['lan']['ipaddr'] = $lanIp;
            $config['interfaces']['lan']['subnet'] = $lanMaskLen;
            if('1'==$lanDhcpType){
                $config['dhcpd']['lan']['enable']='';
            }else{
                unset($config['dhcpd']['lan']['enable']);
            }
            $config['dhcpd']['lan']['maxleasetime'] = $dhcpLease;
            $config['dhcpd']['lan']['range']['from'] = $dhcpStart;
            $config['dhcpd']['lan']['range']['to'] = $dhcpEnd;
            write_config();
            interface_bring_down('lan', false, $config['dhcpd']['lan']);
            interface_configure('lan', true);
            reconfigure_dhcpd();
        }catch(AppException $aex){
            $result['msg'] = $aex->getMessage();
        }catch(Exception $ex){
            $result['msg'] = '99';
        }

        echo json_encode($result);
        
    }

    public function wanAction()
    {
       $ip = Util::maskbit2ip(24);
        echo $ip;
        $bit = Util::maskip2bit('255.255.255.1');
        var_dump($bit);
        //echo $lan;
    }
}

