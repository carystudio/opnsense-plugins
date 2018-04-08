<?php

require_once ('util.inc');
require_once("interfaces.inc");
/**
 * Created by PhpStorm.
 * User: heimi
 * Date: 2017/2/6
 * Time: 15:34
 */
class Util
{
    private static $interface_name = false;
    private static $client_ip = false;

    public static function maskbit2ip($bit)
    {
        $bit = intval($bit);
        $lan = ((1<<$bit) -1)<<(32-$bit) ;
        $lan = str_split(''.decbin($lan), 8);
        $maskip=bindec($lan[0]).'.'.bindec($lan[1]).'.'.bindec($lan[2]).'.'.bindec($lan[3]);

        return $maskip;
    }

    public static function maskip2bit($ip)
    {
        $ips = explode('.',$ip);
        if(!is_array($ips) || 4 != count($ips)){
            return false;
        }

        $bit=(intval($ips[0])<<24) + (intval($ips[1])<<16) + (intval($ips[2])<<8) + intval($ips[3]);
        $bit = decbin($bit);
        $len = strlen($bit);
        if(32 != $len){
            return false;
        }
        $masklen=0;
        $flag='1';
        for($i=0; $i<32; $i++){
            if('1'==$bit[$i]){
                $masklen++;
                if('1'!=$flag){
                    return false;
                }
            }

            $flag=$bit[$i];
        }

        return $masklen;
    }

    public static function getInterfaceName($num=0){
        if(false == self::$interface_name){
            $inf_list = get_interface_list();
            self::$interface_name = array();
            foreach($inf_list as $name=>$inf){
                self::$interface_name[] = $name;
            }
        }

        if(0==$num){
            return self::$interface_name;
        }else{
            $idx=1;
            foreach(self::$interface_name as $name){
                if($idx == $num){
                    return $name;
                }
                $idx++;
            }
            return '';
        }
    }

    //mode 0:数字和字母 1:数字 2:字母
    public static function getRandStr($strLen,$mode=0){
        $mode = intval($mode);
        if(0>$mode || 2<$mode){
            $mode = 0;
        }
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));

        $number = '';
        $number_len = $strLen;
        if($mode==1){//数字
            $stuff = '1234567890';
        }elseif($mode==2){//字母
            $stuff = 'abcdefghijklmnopqrstuvwxyz';
        }else{
            $stuff = '1234567890abcdefghijklmnopqrstuvwxyz';//附加码显示范围ABCDEFGHIJKLMNOPQRSTUVWXYZ
        }
        $stuff_len = strlen($stuff) - 1;
        for ($i = 0; $i < $number_len; $i++) {
            $number .= substr($stuff, mt_rand(0, $stuff_len), 1);
        }

        return $number;
    }

    public static function getLanMacByIp($ip){
        $arp_arr = array();
        exec('/usr/sbin/arp -an', $arp_arr);
        foreach ($arp_arr as $arp) {
            if(strpos($arp, $ip)>0){
                $arp_info = explode(' ', $arp);
                if(is_macaddr($arp_info[3])){
                    return $arp_info[3];
                }
            }
        }

        return false;
    }

    public static function getClientIp($request)
    {
        if(false == self::$client_ip){
            // determine orginal sender of this request
            $trusted_proxy = array(); // optional, not implemented
            if ($request->getHeader('X-Forwarded-For') != "" &&
                (
                    explode('.', $request->getClientAddress())[0] == '127' ||
                    in_array($request->getClientAddress(), $trusted_proxy)
                )
            ) {
                // use X-Forwarded-For header to determine real client
                self::$client_ip = $request->getHeader('X-Forwarded-For');
            } else {
                // client accesses the Api directly
                self::$client_ip = $request->getClientAddress();
            }
        }

        return self::$client_ip;
    }

    public static function checkCidr($value, $multiple = false, $ipproto = 'ipv4', $allow_hosts = false){
        if (empty($value)) {
            return false;
        }
        $networks = explode(',', $value);

        if (!$multiple && (count($networks) > 1)) {
            return false;
        }
        foreach ($networks as $network) {
            if ($ipproto == 'ipv4') {
                if(!openvpn_validate_cidr_ipv4($network, $allow_hosts)){
                    return false;
                }
            } else {
                if(!openvpn_validate_cidr_ipv6($network)){
                    return false;
                }
            }
        }

        return true;
    }
}