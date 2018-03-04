<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once("gwlb.inc");

use \OPNsense\Core\Backend;
use \OPNsense\CaptivePortal\CaptivePortal;
use \Phalcon\Db\Adapter\Pdo\Sqlite;
use \Phalcon\Logger\Adapter\Syslog;

class Accontrol extends Csbackend
{
    protected static $ERRORCODE = array(
        'AcControl_test'=>'AC测试',
        "AcControl_001" => "更新AP名称失败",
    );

	const STATE_ONLINE = 0x1;
    const STATE_UPGRADE = 0x2;
    const STATE_RESET = 0x4;
    const STATE_REBOOT = 0x8;
    const STATE_RADIO = 0x10;
    const STATE_WLAN = 0x20;
    const STATE_SYSTEM = 0x40;
    const STATE_AUTH = 0x80;
    const STATE_NETWORK = 0x100;

    private static $pdo = false;

    private static function setState($curstate, $state){
        $curstate = $curstate & (~$state);

        return $curstate;
    }

    private static function deleteState($curstate, $state){
        $curstate = $curstate | $state;

        return $curstate;
    }
    protected static $CONFIG = array(
        'ONLINE_STATE'=>511,  //在线 1 1111 1111    0x1FF
        'UPGRADE_COMMAND'=>509,   //更新固件 1 1111 1101  0x1FD
        'RESET_COMMAND'=>507,     //复位 1 1111 1011    0x1FB
        'REBOOT_COMMAND'=>503,    //重启 1 1111 0111    0x1F7
        'RADIO_CONFIG'=>495,     //RADIO设置 1 1110 1111    0x1EF
        'WLAN_CONFIG'=>479,      //WLAN设置 1 1101 1111     0x1DF
        'SYSTEM_CONFIG'=>447,    //系统设置 1 1011 1111   0x1BF
        'AUTH_VERIFY'=>383,      //权限管理 1 0111 1111   0x17F
        'NETWORK_STATE'=>255,   //网络状态  0 1111 1111    0xFF
//        'MAX_STATE_NUM'=>512,
    );

    private static function getPdo(){
        if(!self::$pdo){
            self::$pdo = new PDO("mysql:dbname=csgateway;host=127.0.0.1", "root", '');
        }

        return self::$pdo;
    }

	private static function getAp($apid){
        $pdo = self::getPdo();

        $sth = $pdo->prepare("select * from APLIST WHERE id=:id");
        $sth->execute(array('id'=>intval($apid)));
        $ap = $sth->fetch(PDO::FETCH_ASSOC);

        return $ap;
    }

    /* *
     * 功能：更新APLIST表中的apstate字段
     *  @params:
     *      $config：配置数值 (int)
     *      $id：APLIST的id
     * */
    private function setConfig($cfg,$id){
        $dbh = self::getPdo();
        $sql = "select * from APLIST WHERE id = '" . $id . "'";
        $arrInfo = array();
        $count = 0;
        foreach ($dbh->query($sql,PDO::FETCH_ASSOC) as $key=>$row) {
            foreach ($row as $k=>$v){
                if('apstate' == $k){
                    $apstate = $v & $cfg;
                    $excSql = "update APLIST set apstate = '".$apstate."' where id = '".$id."'";
                    $count = $dbh->exec($excSql);
                }
            }
        }
        return $count;
    }

    public static function acScanAp($data){
        $arr = array();
        if('exec' == $data['type']){
            //TODO 获取 cste_heartbeat 进程ID，若没有启动，则启动，取进程ID存在问题
//            exec('ps -aux | grep cste_heartbeat',$process);
//            if(!$process){
//                exec('cste_heartbeat &');
//            }
            exec('killall -30 cste_heartbeat');
            return $arr;
        }else if('scan' == $data['type']){
            $dbh = self::getPdo();
            $sql = "select * from APLIST";
            $arr = array();
            foreach ($dbh->query($sql,PDO::FETCH_ASSOC) as $key=>$row) {
                $arr[$key] = $row;
            }
            return $arr;
        }else{
            return false;
        }
    }

    public static function setApIp($data){
        $arr = array();
        if($data){
            file_put_contents('/tmp/apipsetting',json_encode($data));
            if(file_exists("/tmp/apipsetting")){
                exec('killall -31 cste_heartbeat');
            }
            return $arr;
        }else{
            return false;
        }
    }

    public static function setApName($data){
        $result = 0;
        try{
            $ap = self::getAp($data['apid']);
            if(false === $ap){
                throw new AppException('apid error');
            }
            $pdo = self::getPdo();
            $sql = "UPDATE APLIST set apname=:apname where id=:id";
            $sth = $pdo->prepare($sql);

            $pdo ->beginTransaction();
            $sth->execute(array('apname'=>$data['apname'], 'id'=>intval($data['apid'])));
            if(1 == $sth->rowCount()){
                $apstate = self::setState($ap['apstate'], Accontrol::STATE_SYSTEM);
                $sth = $pdo->prepare('UPDATE APLIST SET apstate=:apstate where id=:id');
                $res = $sth->execute(array('apstate'=>$apstate, 'id'=>$ap['id']));
                if(false === $res){
                    throw new AppException('update apstate error');
                }
            }
            $pdo->commit();
        }catch(AppException $aex){
            $pdo->rollBack();
            $result = 1;
        }catch(Exception $ex){
            $pdo->rollBack();
            $result = 2;
        }

        return $result;
    }

    public static function setApReboot($data){
        $arr = array();
        if($data){
            $deviceId = explode(',',$data['id']);
            foreach ($deviceId as $key=>$val){
                Accontrol::setConfig(Accontrol::$CONFIG['REBOOT_COMMAND'],$val);
            }
            return $arr;
        }else{
            return false;
        }
    }

    public static function setAcReset($data){
        $output = '';
        $returncode = 0;
        exec('/usr/local/bin/mysql -uroot </usr/local/opnsense/cs/ac/db/init.sql', $output, $returncode);

        return $returncode;
    }

    public static function setApLedState($data){
        $res = array();
        try{
            if($data){
                var_dump($data);
                if('2' == $data["ledState"]){
                    $ledState = 0;
                }else{
                    $ledState = 1;
                }
                $dbh = self::getPdo();
                $deviceId = explode(',',$data['id']);
                foreach ($deviceId as $key=>$val){
                    $excSql = "update APLIST set ledstate = '".$ledState."' where id = '".$val."'";
                    $conut = $dbh->exec($excSql);
                    if(0 == $conut){
                        throw new AppException('AcControl_001');
                    }
                    Accontrol::setConfig(Accontrol::$CONFIG['SYSTEM_CONFIG'],$val);
                }
            }else{
                return false;
            }
        } catch (AppException $aex) {
            $res = $aex->getMessage();
        } catch (Exception $ex) {
            $res = '100';
        }
        return $res;
    }

    public static function setApRestore($data){
        $res = 0;
        try{
            if(!is_array($data) && count($data)==0){
                throw new AppException('data error');
            }
            $pdo = self::getPdo();
            foreach($data as $apid){
                $ap = self::getAp(intval($apid));
                var_dump($ap);
                if($ap){
                    $ap['apstate'] = self::setState($ap['apstate'], Accontrol::STATE_RESET);
                }
                $sth = $pdo->prepare('UPDATE APLIST SET apstate=:apstate where id=:id');
                $res = $sth->execute(array('apstate'=>$ap['apstate'], 'id'=>$ap['id']));
                if(false === $res){
                    throw new AppException('update apstate error');
                }
            }
        } catch (AppException $aex) {
            $res = $aex->getMessage();
        } catch (Exception $ex) {
            $res = '100';
        }
        return $res;
    }

    public static function setApUpgrade($data){
        $result = 0;
        try{
            if(!is_array($data) && count($data)==0){
                throw new AppException('data error');
            }
            $pdo = self::getPdo();
            foreach($data as $apid){
                $ap = self::getAp(intval($apid));
                if($ap){
                    $ap['apstate'] = self::setState($ap['apstate'], Accontrol::STATE_UPGRADE);
                }
                $sth = $pdo->prepare('UPDATE APLIST SET apstate=:apstate where id=:id');
                $res = $sth->execute(array('apstate'=>$ap['apstate'], 'id'=>$ap['id']));
                if(false === $res){
                    throw new AppException('update apstate error');
                }
            }
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = '100';
        }
        return $result;
    }
}