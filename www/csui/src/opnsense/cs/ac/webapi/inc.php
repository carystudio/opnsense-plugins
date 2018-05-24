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

	public static function createAp($ap,$apWifiInfo){
		$pdo = self::getPdo();

		try{
			$ap['apstate'] = 0x1ff;
			$ap['apstatus'] = 0;
			$ap['ledstate'] = 0xff;
			$ap['apname'] = str_replace(':', '', $ap['apmac']);
			$ap['apkey'] = 'csapkey2017';
			$ap['gid'] = 0;
			$ap['hftimes'] = 0;
			$pdo->beginTransaction();
			$sth = $pdo->prepare("INSERT INTO `APLIST` (`apname`, `apmac`, `ipaddr`, `netmask`, `gateway`, `pridns`, `secdns`, `apstate`, `apstatus`,
			`ledstate`, `apkey`, `csid`, `model`, `svnnum`, `builddate`,  `uptime`, `softver`, `timestamp`, `aptype`, `username`, `password`,
			`gid`, `hftimes`,`schmode`,`schweek`,`schhour`,`schminute`,`rechour`) values (:apname, :apmac, :ipaddr, :netmask, :gateway, :pridns, :secdns, :apstate, :apstatus, :ledstate, :apkey, :csid, 
			:model, :svnnum, :builddate, :uptime, :softver, :timestamp, :aptype, :username, :password, :gid, :hftimes, :schmode, :schweek, :schhour, :schminute, :rechour)");
			$ap_res = $sth->execute($ap);
			if(!$ap_res){
				throw new AppException('add APLIST fail');
			}
			$ap['id'] = $pdo->lastInsertId();
			$wifi0_status_dat = array('apid'=>$ap['id'],'country'=>'CN','htmode'=>$apWifiInfo['APS2G']['htmode'],'channel'=>$apWifiInfo['APS2G']['channel'],'clientnum'=>$apWifiInfo['APS2G']['clientnum'],'txpower'=>$apWifiInfo['APS2G']['txpower']);
			$wifi1_status_dat = array('apid'=>$ap['id'],'country'=>'CN','htmode'=>$apWifiInfo['APS5G']['htmode'],'channel'=>$apWifiInfo['APS5G']['channel'],'clientnum'=>$apWifiInfo['APS5G']['clientnum'],'txpower'=>$apWifiInfo['APS5G']['txpower']);

			$wifi0_sth = $pdo->prepare("INSERT INTO `WIFI0_STATUS` (`apid`, `country`,`htmode`,`channel`,`clientnum`,`txpower`) values (:apid, :country, :htmode, :channel, :clientnum, :txpower)");
			$wifi1_sth = $pdo->prepare("INSERT INTO `WIFI1_STATUS` (`apid`, `country`,`htmode`,`channel`,`clientnum`,`txpower`) values (:apid, :country, :htmode, :channel, :clientnum, :txpower)");

			if(isset($apWifiInfo['APS2G'])){
				if(!$apWifiInfo['APS2G']['ssid']){
					$apWifiInfo['APS2G']['ssid'] = str_replace(':', '', $ap['apmac']);
				}
				$wlan2_status_dat = array('apid'=>$ap['id'],'usefor'=>1, 'ssid'=>$apWifiInfo['APS2G']['ssid'],'hide'=>$apWifiInfo['APS2G']['hide'],'isolate'=>$apWifiInfo['APS2G']['isolate'],'encryption'=>$apWifiInfo['APS2G']['encryption'],'passphrase'=>$apWifiInfo['APS2G']['passphrase'],'stanum'=>$apWifiInfo['APS2G']['stanum'],'vlanid'=>$apWifiInfo['APS2G']['vlanid']);
				$wlan2_sth = $pdo->prepare("INSERT INTO `WLAN_STATUS` (`apid`, `usefor`,`ssid`,`hide`,`isolate`,`encryption`,`passphrase`,`stanum`,`vlanid`) values (:apid, :usefor, :ssid,:hide,:isolate,:encryption,:passphrase,:stanum,:vlanid)");
				$wlan2_status = $wlan2_sth->execute($wlan2_status_dat);
				if(!$wlan2_status){
					throw new AppException('add wlan_status fail');
				}
			}
			if(isset($apWifiInfo['APS5G'])){
				if(!$apWifiInfo['APS5G']['ssid']){
					$apWifiInfo['APS5G']['ssid'] = str_replace(':', '', $ap['apmac']);
				}
				$wlan5_status_dat = array('apid'=>$ap['id'],'usefor'=>2, 'ssid'=>$apWifiInfo['APS5G']['ssid'],'hide'=>$apWifiInfo['APS5G']['hide'],'isolate'=>$apWifiInfo['APS5G']['isolate'],'encryption'=>$apWifiInfo['APS5G']['encryption'],'passphrase'=>$apWifiInfo['APS5G']['passphrase'],'stanum'=>$apWifiInfo['APS5G']['stanum'],'vlanid'=>$apWifiInfo['APS5G']['vlanid']);
				$wlan5_sth = $pdo->prepare("INSERT INTO `WLAN_STATUS` (`apid`, `usefor`,`ssid`,`hide`,`isolate`,`encryption`,`passphrase`,`stanum`,`vlanid`) values (:apid, :usefor, :ssid,:hide,:isolate,:encryption,:passphrase,:stanum,:vlanid)");
				$wlan5_status = $wlan5_sth->execute($wlan5_status_dat);
				if(!$wlan5_status){
					throw new AppException('add wlan_status fail');
				}
			}
			if(!isset($apWifiInfo['APS2G']) && !isset($apWifiInfo['APS5G'])){
				$wlan_status_dat = array('apid'=>$ap['id'],'usefor'=>3, 'ssid'=>str_replace(':', '', $ap['apmac']));
				$wlan_sth = $pdo->prepare("INSERT INTO `WLAN_STATUS` (`apid`, `usefor`,`ssid`) values (:apid, :usefor, :ssid)");
				$wlan_status = $wlan_sth->execute($wlan_status_dat);
				if(!$wlan_status){
					throw new AppException('add wlan_status fail');
				}
			}

			$wifi0_status = $wifi0_sth->execute($wifi0_status_dat);
			if(!$wifi0_status){
				throw new AppException('add wifi0_status fail');
			}
			$wifi1_status = $wifi1_sth->execute($wifi1_status_dat);
			if(!$wifi1_status){
				throw new AppException('add wifi1_status fail');
			}

			$pdo->commit();
		}catch(PDOException $pex){
			$pdo->rollBack();
			echo $pex->getTraceAsString();
			return false;
		}

		return $ap;
	}

	public static function updateApInfo($ap){
		$pdo = self::getPdo();
		
		$sth = $pdo->prepare("UPDATE `APLIST` set 
		apmac=:apmac,ipaddr=:ipaddr,netmask=:netmask,
		gateway=:gateway,pridns=:pridns,secdns=:secdns,apstate=:apstate,
		csid=:csid,model=:model,svnnum=:svnnum,
		builddate=:builddate,uptime=:uptime,softver=:softver,timestamp=:timestamp,
		aptype=:aptype,username=:username,password=:password where id=:id");
		$res = $sth->execute($ap);
	
		return $res;
	}
	
	public static function updateAp($ap){
		$pdo = self::getPdo();
		

		$sth = $pdo->prepare("UPDATE `APLIST` set 
		apname=:apname,apmac=:apmac,ipaddr=:ipaddr,netmask=:netmask,
		gateway=:gateway,pridns=:pridns,secdns=:secdns,apstate=:apstate,apstatus=:apstatus,
		ledstate=:ledstate,apkey=:apkey,csid=:csid,model=:model,svnnum=:svnnum,
		builddate=:builddate,uptime=:uptime,softver=:softver,timestamp=:timestamp,
		aptype=:aptype,username=:username,password=:password,gid=:gid,hftimes=:hftimes,
		schmode=:schmode,schweek=:schweek,schhour=:schhour,schminute=:schminute,rechour=:rechour where id=:id");

		$res = $sth->execute($ap);

		return $res;
	}
	
	public static function getUpgrade($csid){
		$pdo = self::getPdo();
		
		$sth = $pdo->prepare("SELECT * FROM AP_UPGRADE WHERE csid=:csid limit 1");
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

	public static function setState($ap, $state, $update=true){
		$ap['apstate'] = $ap['apstate'] & (~$state);
		if($update){
			Db::updateAp($ap);
		}

		return $ap;
	}

	public static function test($ap){
		Db::updateAp($ap);
	}

	public static function deleteState($ap, $state, $update=true){

		$ap['apstate'] = $ap['apstate'] | $state;

		if($update){
			Db::updateAp($ap);
		}

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
				$ap = self::deleteState($ap, Action::STATE_UPGRADE);
				unset($result['key']);
				$ap['apstatus'] = '0';
				$result['action'] = 'SessionOver';
			}else{
				$ap['apstatus'] = '3';
				$result['action'] = "SetUpgrade";
				$result['url']="http://".$_SERVER['HTTP_HOST']."/firmware/".$upgrade['filepath'];
			}
		}else if(0 == (Action::STATE_RESET & $ap['apstate'])){
			$ap['apstatus'] = '1';
			$result['action']='SetReset';
		}else if(0 == (Action::STATE_REBOOT & $ap['apstate'])){
			$ap['apstatus'] = '2';
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
			$result['ledState'] = (string)$ap['ledstate'];
			$result['rebooSchedule'] = array(
				"mode"=>(string)$ap['schmode'],
				"week"=>(string)$ap['schweek'],
				"hour"=>(string)$ap['schhour'],
				"minute"=>(string)$ap['schminute'],
				"recHour"=>(string)$ap['rechour']
			);
			$result['action'] = "SetSysConfig";
		}else{
			unset($result['key']);
			$ap['apstatus'] = '0';
			$result['action'] = 'SessionOver';
		}
		if(file_exists('/usr/local/opnsense/cs/tmp/apinfo.log')){
			file_put_contents('/usr/local/opnsense/cs/tmp/apinfo.log',date("Y-m-d h:i:s")." ---------getAction-----action:".$result['action']."---- \n\r ".json_encode($result)."\n\r",FILE_APPEND);
		}

		if('SessionOver' == $result['action']){
			$ap = Action::deleteState($ap, Action::STATE_ONLINE);
		}

		return json_encode($result);
	}
}
