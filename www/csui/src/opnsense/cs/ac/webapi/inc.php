<?php
$DB_HOST = 'localhost';
$DB_PORT = 3306;
$DB_USERNAME = 'root';
$DB_PASSWORD = '';
$DB_NAME = 'csgateway';

$GET_FIELDS = array(
	'apMac'=>'apmac',
	'apIp'=>'ipaddr',
	'timeStamp' => 'timestamp',
//	'upTime'=> 'uptime',
	'apType'=>'aptype',
	'csid'=>'csid',
	'softVer'=>'softver',
	'svnNum'=>'svnnum',
	'apName'=>'apname',
	'softModel'=>'model',
	'buildDate'=>'builddate',
	'userName'=>'username',
	'password'=>'password',
	'ipAddr'=>'ipaddr',
	'netMask'=>'netmask',
	'gateway'=>'gateway',
	'pridns'=>'pridns',
	'secdns'=>'secdns');

class AppException extends \Exception{

}

class Db{
	private static $pdo = false;
	
	private static function getPdo(){
		global $DB_HOST;
		global $DB_PORT;
		global $DB_USERNAME;
		global $DB_PASSWORD;
		global $DB_NAME;
		
		
		if(!self::$pdo){
			$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $DB_HOST, $DB_PORT, $DB_NAME);
			self::$pdo = new PDO($dsn, $DB_USERNAME, $DB_PASSWORD);
			self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}	
		
		return self::$pdo;
	}
	
	public static function getApByMac($apmac){
		$pdo = self::getPdo();
		
		$sth = $pdo->query("SELECT * FROM APLIST WHERE apmac='".$apmac."' limit 1");
		$ap = $sth->fetch();
		if(false === $ap){
			return false;
		}
		
		return $ap;
	}

	public static function createAp($ap){
		$pdo = self::getPdo();

		try{
			$ap['apstate'] = 0x1ff;
			$ap['ledstate'] = 0xff;
			$ap['apname'] = str_replace(':', '', $ap['apmac']);
			$ap['apkey'] = 'csapkey2017';
			$ap['gid'] = 0;
			$ap['hftimes'] = 0;
			$pdo->beginTransaction();
			$sth = $pdo->prepare("INSERT INTO `APLIST` (`apname`, `apmac`, `ipaddr`, `netmask`, `gateway`, `pridns`, `secdns`, `apstate`, 
			`ledstate`, `apkey`, `csid`, `model`, `svnnum`, `builddate`,  `uptime`, `softver`, `timestamp`, `aptype`, `username`, `password`,
			`gid`, `hftimes`) values (:apname, :apmac, :ipaddr, :netmask, :gateway, :pridns, :secdns, :apstate, :ledstate, :apkey, :csid, 
			:model, :svnnum, :builddate, :uptime, :softver, :timestamp, :aptype, :username, :password, :gid, :hftimes)");
			$ap_res = $sth->execute($ap);
			$ap['id'] = $pdo->lastInsertId();
			$wifi_status_dat = array('apid'=>$ap['id'],'country'=>'CN');
			$wlan_status_dat = array('apid'=>$ap['id'],'usefor'=>3, 'ssid'=>str_replace(':', '', $ap['apmac']));
			$wifi0_sth = $pdo->prepare("INSERT INTO `WIFI0_STATUS` (`apid`, `country`) values (:apid, :country)");
			$wifi1_sth = $pdo->prepare("INSERT INTO `WIFI1_STATUS` (`apid`, `country`) values (:apid, :country)");
			$wlan_sth = $pdo->prepare("INSERT INTO `WLAN_STATUS` (`apid`, `usefor`,`ssid`) values (:apid, :usefor, :ssid)");
			$wifi0_status = $wifi0_sth->execute($wifi_status_dat);
			if(!$wifi0_status){
				throw new AppException('add wifi0_status fail');
			}
			$wifi1_status = $wifi1_sth->execute($wifi_status_dat);
			if(!$wifi1_status){
				throw new AppException('add wifi1_status fail');
			}
			$wlan_status = $wlan_sth->execute($wlan_status_dat);
			if(!$wlan_status){
				throw new AppException('add wlan_status fail');
			}
			$pdo->commit();
		}catch(PDOException $pex){
			$pdo->rollBack();
			echo $pex->getTraceAsString();
			return false;
		}

		return $ap;
	}

	public static function updateAp($ap){
		$pdo = self::getPdo();
		

		$sth = $pdo->prepare("UPDATE `APLIST` set 
		apname=:apname,apmac=:apmac,ipaddr=:ipaddr,netmask=:netmask,
		gateway=:gateway,pridns=:pridns,secdns=:secdns,apstate=:apstate,
		ledstate=:ledstate,apkey=:apkey,csid=:csid,model=:model,svnnum=:svnnum,
		builddate=:builddate,uptime=:uptime,softver=:softver,timestamp=:timestamp,
		aptype=:aptype,username=:username,password=:password,gid=:gid,hftimes=:hftimes where id=:id");
		$ap_data = array();
		foreach($ap as $var=>$val){
			$ap_data[':'.$var] = $val;
		}
		$res = $sth->execute($ap_data);
		
		return $res;
	}
	
	public static function getUpgrade($csid){
		$pdo = self::getPdo();
		
		$sth = $pdo->prepare("SELECT * FROM ap_upgrade WHERE csid=:csid limit 1");
		$sth->execute(array('csid'=>$csid));
		$upgrade = $sth->fetch();
		if(false === $upgrade){
			return false;
		}
		
		return $upgrade;
	}
	
	public static function getRadioStatus($apid){
		$pdo = self::getPdo();
		
		$wifi_status = array();
		$query = $pdo->prepare("SELECT * FROM `WIFI0_STATUS` WHERE apid=".intval($apid)." limit 1");
		$query->execute();
		$wifi0_status = $query->fetch();
		if(false !== $wifi0_status){
			unset($wifi0_status['id']);
			unset($wifi0_status['gid']);
			unset($wifi0_status['apid']);
			$wifi_status['RADIO0'] = $wifi0_status;
		}
		$query = $pdo->prepare("SELECT * FROM `WIFI1_STATUS` WHERE apid=".intval($apid)." limit 1");
		$query->execute();
		$wifi1_status = $query->fetch();
		if(false !== $wifi1_status){
			unset($wifi1_status['id']);
			unset($wifi1_status['gid']);
			unset($wifi1_status['apid']);
			$wifi_status['RADIO1'] = $wifi1_status;
		}
		
		return $wifi_status;
	}
	
	public static function getSsids($apid){
		$pdo = self::getPdo();
		
		$ssids = array();
		$query = $pdo->prepare("SELECT * FROM `WLAN_STATUS` WHERE apid=".intval($apid));
		$query->execute();
		while(($wlan_status = $query->fetch()) !== false){
			$ssids[] = $wlan_status;
		}
		
		return $ssids;
	}
	
	
}

class Action{
	const STATE_ONLINE = 0x1;
	const STATE_UPGRADE = 0x2;
	const STATE_RESET = 0x4;
	const STATE_REBOOT = 0x8;
	const STATE_RADIO = 0x10;
	const STATE_WLAN = 0x20;
	const STATE_SYSTEM = 0x40;
	const STATE_AUTH = 0x80;
	const STATE_NETWORK = 0x100;

	public static function setState($ap, $state){
		$ap['apstate'] = $ap['apstate'] & (~$state);
		Db::updateAp($ap);

		return $ap;
	}

	public static function deleteState($ap, $state){

		$ap['apstate'] = $ap['apstate'] | $state;
		Db::updateAp($ap);

		return $ap;
	}

	public static function getSessionOver($ap, $errmsg=false){
		if($errmsg){
			$pkg = array("action"=>"SessionOver", "apMac"=>$ap['apmac'], 'errmsg'=>$errmsg);
		}else{
			$pkg = array("action"=>"SessionOver", "apMac"=>$ap['apmac']);
		}

		$pkgstr = json_encode($pkg);

		return $pkgstr;
	}

	public static function getAction($ap){
		//固件升级>复位>重启>RADIO > WLAN > SYSTEM
		$result = array('apMac'=>$ap['apmac'], 'key'=>$ap['apkey']);
		if(0 == (Action::STATE_UPGRADE & $ap['apstate'])){
			$upgrade = Db::getUpgrade($ap['csid']);
			if(false === $upgrade){
				self::deleteState($ap, Action::STATE_UPGRADE);
				unset($result['key']);
				$result['action'] = 'SessionOver';
			}else{
				$result['action'] = "SetUpgrade";
				$result['url']="http://".$_SERVER['HTTP_HOST'].':'.$_SERVER['HTTP_PORT']."/firmware/".$upgrade['filepath'];
			}
		}else if(0 == (Action::STATE_RESET & $ap['apstate'])){
			$result['action']='SetReset';
		}else if(0 == (Action::STATE_REBOOT & $ap['apstate'])){
			$result['action']='SetReboot';
		}else if(0 == (Action::STATE_RADIO & $ap['apstate'])){
			$radios = Db::getRadioStatus($ap['id']);
			$result['action'] = "SetRadioConfig";
			if(isset($radios['RADIO0'])){
				$result['RADIO0'] = $radios['RADIO0'];
			}
			if(isset($radios['RADIO1'])){
				$result['RADIO1'] = $radios['RADIO1'];
			}
		}else if(0 == (Action::STATE_WLAN & $ap['apstate'])){
			$wlanstates = Db::getSsids($ap['id']);
			$result['SSIDS']=$wlanstates;
			$result['action']="SetWlanConfig";
		}else if(0 == (Action::STATE_SYSTEM & $ap['apstate'])){
			$result['apName'] = $ap['apname'];
			$result['action'] = "SetSysConfig";
		}else{
			unset($result['key']);
			$result['action'] = 'SessionOver';
		}

		if('SessionOver' == $result['action']){
			$ap = Action::deleteState($ap, Action::STATE_ONLINE);
		}

		return json_encode($result);
	}
}
