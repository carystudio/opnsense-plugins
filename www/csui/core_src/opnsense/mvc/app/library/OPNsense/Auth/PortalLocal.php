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
use OPNsense\PortalHelper;

/**
 * Class Local user database connector (using legacy xml structure).
 * @package OPNsense\Auth
 */
class PortalLocal implements IAuthConnector
{
    private $db = false;
    private $user = false;
    private $lastAuthProperties = array();

    private function getDbConn(){
        if(false === $this->db){
            $this->db = new \SQLite3('/var/captiveportal/captiveportal.sqlite');
        }

        return $this->db;
    }
    /**
     * type name in configuration
     * @return string
     */
    public static function getType()
    {
        return 'PortalLocal';
    }

    /**
     * set connector properties
     * @param array $config connection properties
     */
    public function setProperties($config)
    {
        // local authenticator doesn't use any additional settings.
        $this->lastAuthProperties = $config;
    }

    /**
     * unused
     * @return array mixed named list of authentication properties
     */
    public function getLastAuthProperties()
    {
        if(is_array($this->user)){
            foreach($this->user as $var=>$val){
                $this->lastAuthProperties[$var] = $val;
            }
        }

        return $this->lastAuthProperties;
    }

    /**
     * find user settings in local database
     * @param string $username username to find
     * @return user array,or false
     */
    protected function getUser($username)
    {
        $db = PortalHelper::getDbConn();
        $res = $db->query("select  * from users where username='$username' limit 1");
        $user = $res->fetchArray(SQLITE3_ASSOC);

        return $user;
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

        if ($user) {
            if($user['password']==$password){
                $this->user = $user;
                return true;
            }
        }

        return false;
    }

    public function localAccounting($sessionInfo){
        $result = 1;
        $t = time();
        $user = $this->getUser($sessionInfo['username']);
        $db = PortalHelper::getDbConn();
        if($user['expire_time'] <= $t){
            if($user['remain_time']>0){
                $usedtime = $sessionInfo['last_accessed'] - $sessionInfo['last_accounting'];

                $sql="update users set remain_time=remain_time-".intval($usedtime)." where username='".$sessionInfo['username']."'";
                echo $sql;
                $res = $db->exec($sql);
                $res = $db->exec("update session_info set last_accounting=".intval($sessionInfo['last_accessed'])." where sessionid='".$sessionInfo['sessionid']."'");
                $user = $this->getUser($sessionInfo['username']);
                if($user['remain_time']<=0){
                    $result = 0;
                }
            }else{
                $result = 0;
            }
        }
        if($result && '1'!=$user['concurrent_logins']){
            $res = $db->query("select  * from cp_clients where username='".$sessionInfo['username']."' and deleted=0 order by created desc limit 1");
            $onlineuser = $res->fetchArray(SQLITE3_ASSOC);
            if($onlineuser && $onlineuser['sessionid']!=$sessionInfo['sessionid']){
                file_put_contents('/tmp/session.tmp',$onlineuser['sessionid'].'|'.$sessionInfo['sessionid']."\n");
                $result = 0;
            }
        }


        return $result;
    }
}
