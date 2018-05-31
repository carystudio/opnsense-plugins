<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once("util.inc");
require_once("auth.inc");


class User extends Csbackend
{
    protected static $ERRORCODE = array(
        'User_100'=>'原密码不正确',
        'User_101'=>'修改密码失败'
    );

    public static function changePassword($data){
        global $config;
        $result = 0;
        try {
            $changed = false;
            foreach($config['system']['user'] as $idx=>$user){
                if('root'==$user['name']){
                    $res = authenticate_user('root', $data['OldPassword']);
                    if(!$res){
                        throw new AppException('old_pwd_error');
                    }
                    local_user_set_password($config['system']['user'][$idx], $data['NewPassword']);
                    local_user_set($user);
                    $changed = true;
                }
            }
            if(!$changed){
                throw new AppException('pwd_modify_fail');
            }
            write_config();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = 100;
        }

        return $result;
    }

}