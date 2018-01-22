<?php
/**
 *    Copyright (C) 2015 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace OPNsense\Auth;

use OPNsense\Core\Config;

/**
 * Class Local user database connector (using legacy xml structure).
 * @package OPNsense\Auth
 */
class PortalCenter implements IAuthConnector
{
    private $prop = false;
    private $server = '';
    private $mcode = '';
    private $gatewayid = '';
    private $port = 80;
    private $result = false;
    private $client_ip = '';
    private $client_mac = '';

    /**
     * type name in configuration
     * @return string
     */
    public static function getType()
    {
        return 'PortalCenter';
    }

    /**
     * set connector properties
     * @param array $config connection properties
     */
    public function setProperties($config)
    {
        if(false == $this->prop){
            $this->prop = array();
            $this->prop['server'] = 'totolink.carystudio.com';
            $this->prop['port'] = 80;
            $this->prop['mcode'] = file_get_contents('/usr/local/opnsense/cs/tmp/mcode.txt');
            $this->prop['gatewayid'] = file_get_contents('/usr/local/opnsense/cs/tmp/portal_gatewayid');
            if(is_array($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $this->prop['client_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
                $this->prop['client_mac'] = \Util::getLanMacByIp($this->prop['client_ip']);
            }else{
                $this->prop['client_ip'] = '';
                $this->prop['client_mac'] = '';
            }

        }
        // local authenticator doesn't use any additional settings.
        if(isset($config['client_ip'])){
            $this->prop['client_ip'] = $config['client_ip'];
        }
        if(isset($config['client_mac'])){
            $this->prop['client_mac'] = intval($config['client_mac']);
        }

    }

    /**
     * unused
     * @return array mixed named list of authentication properties
     */
    public function getLastAuthProperties()
    {
        return $this->prop;
    }

    private function sendAuth($stage, $sessionid, $client_ip, $client_mac, $incoming, $outgoing){
        $url = 'http://'.$this->prop['server'].':'.$this->prop['port'].'/c/api/v3/auth/?stage='.$stage.'&ip='.$client_ip.
            '&mac='.$client_mac.'&token='.$sessionid.
            '&incoming='.$incoming.'&outgoing='.$outgoing.'&gw_id='.$this->prop['gatewayid'].'&mcode='.$this->prop['mcode'];
        $result = file_get_contents($url);
        return $result;
    }

    /**
     * find user settings in local database
     * @param string $username username to find
     * @return user array,or false
     */
    protected function getUser($username)
    {
        $result = $this->sendAuth('login',$username, $this->prop['client_ip'], $this->prop['client_mac'], 0, 0);
        $res = sscanf($result, "%s %d\n%d %d %d %d %d %d %s");
        if('Auth:'==$res[0]){
            $this->result = $res;
            return $res;
        }else{
            return false;
        }
    }

    /**
     * authenticate user against local database (in config.xml)
     * @param string|SimpleXMLElement $username username (or xml object) to authenticate
     * @param string $password user password
     * @return bool authentication status
     */
    public function authenticate($username, $password)
    {
        $user = $this->getUser($username);


        return $user;
    }

    public function centerAccounting($sessionInfo){
        $result = $this->sendAuth('counters',
            $sessionInfo['sessionid'],
            $sessionInfo['ip_address'],
            $sessionInfo['mac_address'],
            ($sessionInfo['bytes_out'] - $sessionInfo['cur_bytes_out']),
            ($sessionInfo['bytes_in'] - $sessionInfo['cur_bytes_in']));
        $res = sscanf($result, "%s %d");
        if('Auth:'==$res[0]){
            return array($res[0],$res[1]);
        }else{
            return 1;
        }
    }

    public function centerStopAccounting($sessionInfo){
        $result = $this->sendAuth('logout',
            $sessionInfo['sessionid'],
            $sessionInfo['ip_address'],
            $sessionInfo['mac_address'],
            ($sessionInfo['bytes_out'] - $sessionInfo['cur_bytes_out']),
            ($sessionInfo['bytes_in'] - $sessionInfo['cur_bytes_in']));
        $res = sscanf($result, "%s %d");
        if('Auth:'==$res[0]){
            return $res[1];
        }else{
            return 1;
        }
    }
}
