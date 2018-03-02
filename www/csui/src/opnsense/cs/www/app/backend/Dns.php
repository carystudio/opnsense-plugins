<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");

use \OPNsense\TrafficShaper\TrafficShaper;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Backend;
use \OPNsense\Core\Config;

class Dns extends Csbackend
{
    protected static $ERRORCODE = array(
        'Dns_100'=>'开启参数不正确',
        'Dns_101'=>'未设置DNS服务器不能，不能关闭DNS转发功能',
        'Dns_200'=>'IP不正确',
        'Dns_201'=>'域名不正确',
        'Dns_202'=>'覆盖的域名已存在',
        'Dns_300'=>'要删除的域名不存在'
    );
    private static function apply(){
        // Reload filter (we might need to sync to CARP hosts)
        filter_configure();
        /* Update resolv.conf in case the interface bindings exclude localhost. */
        system_resolvconf_generate();
        system_hosts_generate();
        dnsmasq_configure_do();
        services_dhcpd_configure();
        clear_subsystem_dirty('hosts');
    }

    public static function getDnsForwardStatus(){
        global $config;

        $dnsForwardStatus = array('Enable'=>'0','Overwrites'=>array());
        if(isset($config['dnsmasq']['enable'])){
            $dnsForwardStatus['Enable'] = '1';
            if('1' == $dnsForwardStatus['Enable'] && isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts'])){
                foreach($config['dnsmasq']['hosts'] as $host){
                    if('PORTAL_SERVER'== $host['descr'] || 'WeChat Local Login'==$host['descr']){
                        continue ;
                    }
                    $overwrite = array();
                    $overwrite['Domain'] = $host['host'].'.'.$host['domain'];
                    $overwrite['Ip'] = $host['ip'];
                    $overwrite['Descr'] = $host['descr'];
                    $dnsForwardStatus['Overwrites'][] = $overwrite;
                }
            }
        }

        return $dnsForwardStatus;
    }

    public static function setDnsForwardStatus($data){
        global $config;
        $result = 0;
        try{
            if('1'!=$data['Enable'] && '0'!=$data['Enable']){
                throw new AppException('Dns_100');
            }
            if('1'==$data['Enable']){
                $config['dnsmasq']['enable'] = '1';
                $config['dnsmasq']['interface'] = 'lan';
            }else{
                if(!is_array($config['system']['dnsserver']) || count($config['system']['dnsserver'])==0){
                    throw new AppException('Dns_101');
                }
                unset($config['dnsmasq']['enable']);
            }

        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        write_config();
        self::apply();

        return $result;
    }

    public static function addDnsOverwrite($data){
        global $config;
        $result = 0;
        try{
            if(!isset($data['Ip']) || !is_ipaddr($data['Ip'])){
                throw new AppException('Dns_200');
            }
            $domain = '';
            if(isset($data['Domain'])){
                $domain = trim($data['Domain']);
            }
            if(strlen($domain)<4 || !strpos($domain, '.')){
                throw new AppException('Dns_201');
            }
            $descr = '';
            if(isset($data['Descr'])){
                $descr = trim($data['Descr']);
            }
           /* if(strlen($descr)==0){
                throw new AppException('1109');
            }*/
            if(isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts'])){
                foreach($config['dnsmasq']['hosts'] as $host) {//检查是否已存在
                    if($host['host'].'.'.$host['domain'] == $domain){
                        throw new AppException('Dns_202');
                    }
                }
            }

            $domain_info = explode('.', $domain);
            $host = $domain_info[0];
            $domain = substr($domain, strlen($host)+1);
            $overwrite = array();
            $overwrite['host'] = $host;
            $overwrite['domain'] = $domain;
            $overwrite['ip'] = $data['Ip'];
            $overwrite['descr'] = $descr;
            $overwrite['aliases'] = '';
            $config['dnsmasq']['hosts'][] = $overwrite;

            write_config();
            self::apply();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delDnsOverwrite($data)
    {
        global $config;
        $result = 0;
        try {
            $domain = '';
            if(isset($data['Domain'])){
                $domain = trim($data['Domain']);
            }
            if(strlen($domain)<4 || !strpos($domain, '.')){
                throw new AppException('Dns_201');
            }
            $deleted = false;
            if(isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts'])){
                foreach($config['dnsmasq']['hosts'] as $idx=>$host){
                    if($host['host'].'.'.$host['domain'] == $domain){
                        unset($config['dnsmasq']['hosts'][$idx]);
                        $deleted = true;
                    }
                }
            }
            if(!$deleted){
                throw new AppException('Dns_300');
            }
            write_config();
            self::apply();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = 100;
        }

        return $result;
    }

}