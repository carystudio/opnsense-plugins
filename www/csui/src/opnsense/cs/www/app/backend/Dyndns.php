<?php
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/dyndns.inc');

class Dyndns extends Csbackend
{
    protected static $ERRORCODE = array(
        'DDNS_100'=>'开启参数不正确'
    );

    private static function getCacheIp($dyndns){
        $filename = dyndns_cache_file($dyndns, 4);
        $fdata = '';
        if (file_exists($filename) && !empty($dyndns['enable'])) {
            $ipaddr = get_dyndns_ip($dyndns['interface'], 4);
            $fdata = @file_get_contents($filename);
        }

        $filename_v6 = dyndns_cache_file($dyndns, 6);
        $fdata6 = '';
        if (file_exists($filename_v6) && !empty($dyndns['enable'])) {
            $ipv6addr = get_dyndns_ip($dyndns['interface'], 6);
            $fdata6 = @file_get_contents($filename_v6);
        }

        if (!empty($fdata)) {
            $cached_ip_s = explode('|', $fdata);
            $cached_ip = $cached_ip_s[0];
            return $cached_ip;
        } elseif (!empty($fdata6)) {
            $cached_ipv6_s = explode('|', $fdata6);
            $cached_ipv6 = $cached_ipv6_s[0];
            return $cached_ipv6;
        } else {
            return '';
        }
    }

    public static function getDdnsTypeList() {
        $dyndnsList = dyndns_list();

        return $dyndnsList;
    }

    public static function getDdnsList()
    {
        global $config;

        $result = array();
        if(isset($config['dyndnses']) && isset($config['dyndnses']['dyndns']) && is_array($config['dyndnses']['dyndns']) && count($config['dyndnses']['dyndns'])){
            $result = $config['dyndnses']['dyndns'];
        }
        foreach($result as $idx=>$ddns){
            $result[$idx]['cached_ip'] = self::getCacheIp($ddns);
            $result[$idx]['interfaceDescr'] = $config['interfaces'][$ddns['interface']]['descr'];
        }

        return $result;
    }

    public static function getDdns($data){
        global $config;

        $dyndns = array();
        if(isset($config['dyndnses']) && isset($config['dyndnses']['dyndns']) && is_array($config['dyndnses']['dyndns'])){
            foreach($config['dyndnses']['dyndns'] as $tmp_ddns){
                if($data['id'] == $tmp_ddns['id']){
                    $dyndns = $tmp_ddns;
                    break ;
                }
            }
        }

        return $dyndns;
    }

    public static function setDdns($data){
        global $config;

        $result = 0;
        try{
            $ddns = array();
            if(isset($data['enable'])){
                if('yes'!=$data['enable']){
                    throw new AppException('DDNS_100');
                }
                $ddns['enable'] = '1';
            }
            $type_list = dyndns_list();
            if(!isset($data['type']) || !key_exists($data['type'], $type_list)){
                throw new AppException('DDNS_101');
            }
            $ddns['type'] = $data['type'];

            if(!isset($data['interface']) || (!empty($data['interface']) && !isset($config['interfaces'][$data['interface']]))){
                throw new AppException('DDNS_102');
            }
            $ddns['interface'] = $data['interface'];

            if(isset($data['requestif']) && !empty($data['interface']) && !isset($config['interfaces'][$data['interface']])){
                throw new AppException('DDNS_103');
            }
            $ddns['requestif'] = $data['requestif'];

            if(!isset($data['host']) || empty($data['host'])){
                throw new AppException('DDNS_104');
            }
            $ddns['host'] = $data['host'];

            if(!isset($data['mx'])){
                throw new AppException('DDNS_105');
            }
            $ddns['mx'] = $data['mx'];
            if(isset($data['wildcard'])){
                if('yes'!=$data['wildcard']){
                    throw new AppException('DDNS_106');
                }
                $ddns['wildcard'] = '1';
            }
            if(isset($data['verboselog'])){
                if('yes'!=$data['verboselog']){
                    throw new AppException('DDNS_107');
                }
                $ddns['verboselog'] = '1';
            }
            if(!isset($data['username']) || empty($data['username'])){
                throw new AppException('DDNS_108');
            }
            $ddns['username'] = $data['username'];

            if(!isset($data['password']) || empty($data['password'])){
                throw new AppException('DDNS_109');
            }
            $ddns['password'] = $data['password'];

            if(!isset($data['descr']) || strlen($data['descr'])>60){
                throw new AppException('DDNS_110');
            }
            $ddns['descr'] = $data['descr'];
            $ddns['updateurl'] = isset($data['updateurl'])?$data['updateurl']:'';
            if('custom' == $ddns['type']){
                if(empty($ddns['updateurl'])){
                    throw new AppException('DDNS_111');
                }
                if(isset($data['curl_ipresolve_v4'])){
                    if('yes'!=$data['curl_ipresolve_v4']){
                        throw new AppException('DDNS_112');
                    }
                    $ddns['curl_ipresolve_v4'] = '1';
                }

                if(isset($data['curl_ssl_verifypeer'])){
                    if('yes'!=$data['curl_ssl_verifypeer']){
                        throw new AppException('DDNS_113');
                    }
                    $ddns['curl_ssl_verifypeer'] = '1';
                }

            }

            $ddns['zoneid'] = isset($data['zoneid'])?$data['zoneid']:'';
            $ddns['resultmatch'] = isset($data['resultmatch'])?$data['resultmatch']:'';
            $ddns['ttl'] = isset($data['ttl'])?$data['ttl']:'';
            if(!is_array($config['dyndnses'])){
                $config['dyndnses'] = array();
            }
            if(!is_array($config['dyndnses']['dyndns'])){
                $config['dyndnses']['dyndns'] = array();
            }
            if(isset($data['id'])){
                if(!isset($config['dyndnses']['dyndns'][$data['id']])){
                    throw new AppException('DDNS_120');
                }
                $config['dyndnses']['dyndns'][$data['id']] = $ddns;
                $id = $data['id'];
            }else{
                $config['dyndnses']['dyndns'][] = $ddns;
                $id = count($config['dyndnses']['dyndns']) - 1;
            }

            for($i = 0; $i < count($config['dyndnses']['dyndns']); $i++) {
                $config['dyndnses']['dyndns'][$i]['id'] = $i;
            }


            write_config();
            system_cron_configure();
            dyndns_configure_client($config['dyndnses']['dyndns'][$id]);
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