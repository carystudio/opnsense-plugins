<?php
require_once('inc.php');

/*
$ap = Db::getApByMac('11:22:33:aa:bb:cc');
var_dump($ap);
*/
$postcontent = file_get_contents('php://input', 'r');
$data = json_decode($postcontent, true);
//if(file_exists('/usr/local/opnsense/cs/tmp/acap.log')){
//	file_put_contents('/usr/local/opnsense/cs/tmp/acap.log',date("Y-m-d h:i:s").' '.json_encode($data)."\n\r",FILE_APPEND);
//}

try{
	if(!is_array($data)||!isset($data['action'])){
		throw new AppException('wrong data.'.$postcontent);
	}
	if('get' == $data['action']) {
		//check para
		foreach ($GET_FIELDS as $getvar => $dbvar) {
			if (!isset($data[$getvar])) {
				throw new AppException(sprintf('get paramater[%s] not exist.', $getvar));
			}
		}
		$ap = Db::getApByMac($data['apMac']);
		if (false === $ap) {
			$ap = array();
			foreach ($GET_FIELDS as $getvar => $dbvar) {
				$ap[$dbvar] = $data[$getvar];
			}
			$ap['uptime'] = time();

			$apWifiInfo = array();
			if(isset($data['APS2G'])){
				//WLAN_STATUS
				$apWifiInfo['APS2G']['usefor'] = 1;
				if(isset($data['APS2G']["SSIDS"])){
					$apWifiInfo['APS2G']['ssid'] = $data['APS2G']["SSIDS"][0]['SSID'];
					$apWifiInfo['APS2G']['hide'] = $data['APS2G']["SSIDS"][0]["HideSSID"];
					$apWifiInfo['APS2G']['isolate'] = $data['APS2G']["SSIDS"][0]["NoForward"];
					$apWifiInfo['APS2G']['encryption'] = $data['APS2G']["SSIDS"][0]["EncrypType"];
					$apWifiInfo['APS2G']['passphrase'] = $data['APS2G']["SSIDS"][0]["WlanKey"];
					$apWifiInfo['APS2G']['stanum'] = $data['APS2G']["SSIDS"][0]["MaxStaNum"];
					$apWifiInfo['APS2G']['vlanid'] = $data['APS2G']["SSIDS"][0]["VlanID"];
				}

				//WIFI0_STATUS
				$apWifiInfo['APS2G']['country'] = $data['APS2G']["CountryCode"]==''?'CN':$data['APS2G']["CountryCode"];
				$apWifiInfo['APS2G']['htmode'] = $data['APS2G']["HT_BW"]==''?'0':$data['APS2G']["HT_BW"];
				$apWifiInfo['APS2G']['channel'] = $data['APS2G']["Channel"]==''?'0':$data['APS2G']["Channel"];
				$apWifiInfo['APS2G']['txpower'] = $data['APS2G']["TxPower"]==''?'100':$data['APS2G']["TxPower"];
				$apWifiInfo['APS2G']['clientnum'] = $data['APS2G']["StaNum"]==''?'0':$data['APS2G']["StaNum"];
			}

			if(isset($data['APS5G'])){
				//WLAN_STATUS
				$apWifiInfo['APS5G']['usefor'] = 2;
				if(isset($data['APS5G']["SSIDS"])){
					$apWifiInfo['APS5G']['ssid'] = $data['APS5G']["SSIDS"][0]['SSID'];
					$apWifiInfo['APS5G']['hide'] = $data['APS5G']["SSIDS"][0]["HideSSID"];
					$apWifiInfo['APS5G']['isolate'] = $data['APS5G']["SSIDS"][0]["NoForward"];
					$apWifiInfo['APS5G']['encryption'] = $data['APS5G']["SSIDS"][0]["EncrypType"];
					$apWifiInfo['APS5G']['passphrase'] = $data['APS5G']["SSIDS"][0]["WlanKey"];
					$apWifiInfo['APS5G']['stanum'] = $data['APS5G']["SSIDS"][0]["MaxStaNum"];
					$apWifiInfo['APS5G']['vlanid'] = $data['APS5G']["SSIDS"][0]["VlanID"];
				}

				//WIFI0_STATUS
				$apWifiInfo['APS5G']['country'] = $data['APS5G']["CountryCode"]==''?'CN':$data['APS5G']["CountryCode"];
				$apWifiInfo['APS5G']['htmode'] = $data['APS5G']["HT_BW"]==''?'0':$data['APS5G']["HT_BW"];
				$apWifiInfo['APS5G']['channel'] = $data['APS5G']["Channel"]==''?'0':$data['APS5G']["Channel"];
				$apWifiInfo['APS5G']['txpower'] = $data['APS5G']["TxPower"]==''?'100':$data['APS5G']["TxPower"];
				$apWifiInfo['APS5G']['clientnum'] = $data['APS5G']["StaNum"]==''?'0':$data['APS5G']["StaNum"];
			}
			
			if(isset($data['rebooSchedule'])){
				$ap['schmode'] = $data['rebooSchedule']['mode']?$data['rebooSchedule']['mode']:'0';
				$ap['schweek'] = $data['rebooSchedule']['week']?$data['rebooSchedule']['week']:'255';
				$ap['schhour'] = $data['rebooSchedule']['hour']?$data['rebooSchedule']['hour']:'0';
				$ap['schminute'] = $data['rebooSchedule']['minute']?$data['rebooSchedule']['minute']:'0';
				$ap['rechour'] = $data['rebooSchedule']['recHour']?$data['rebooSchedule']['recHour']:'1';
			}else{
				$ap['schmode'] = '0';
				$ap['schweek'] = '255';
				$ap['schhour'] = '0';
				$ap['schminute'] = '0';
				$ap['rechour'] = '1';
			}

			$ap = Db::createAp($ap,$apWifiInfo);

			if (!$ap) {
				throw new AppException('save ap data error');
			}
		}
		$result = Action::getAction($ap);
	}else {
		$ap = Db::getApByMac($data['apMac']);
		if (false === $ap) {
			throw new AppException('ap not exist. please send get first.');
		}
		$result = false;
		$status = intval($data['status']);
		if (0 == $status) {
			$ap = Action::deleteState($ap, Action::STATE_AUTH);
		}else{
			$ap = Action::setState($ap, Action::STATE_AUTH);
		}
		switch ($data['action']) {
			case 'SetUpgrade':
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO, false);
					$ap = Action::setState($ap, Action::STATE_WLAN, false);
					$ap = Action::setState($ap, Action::STATE_SYSTEM, false);
				}
				$ap = Action::deleteState($ap, Action::STATE_UPGRADE);
				$result = array('action' => 'SessionOver', 'apMac' => $data['apMac'], 'errmsg' => 'action error');
				break;
			case 'SetReset':
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO, false);
					$ap = Action::setState($ap, Action::STATE_WLAN, false);
					$ap = Action::setState($ap, Action::STATE_SYSTEM, false);
				}
				$ap = Action::deleteState($ap, Action::STATE_RESET);
				break;
			case 'SetReboot':
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO, false);
					$ap = Action::setState($ap, Action::STATE_WLAN, false);
					$ap = Action::setState($ap, Action::STATE_SYSTEM, false);
				}
				$ap = Action::deleteState($ap, Action::STATE_REBOOT);
				break;
			case 'SetRadioConfig':
				$ap = Action::deleteState($ap, Action::STATE_RADIO);
				break;
			case 'SetWlanConfig':
				$ap = Action::deleteState($ap, Action::STATE_WLAN);
				break;
			case 'SetSysConfig':
				$ap = Action::deleteState($ap, Action::STATE_SYSTEM);
				break;
			default:
				$result = array('action' => 'SessionOver', 'apMac' => $data['apMac'], 'errmsg' => 'action error');
		}

		if(false === $result){
			$result = Action::getAction($ap);
		}
	}
}catch(AppException $aex){
	$result = json_encode(array('action'=>'SessionOver', 'apMac'=>$data['apMac'], 'errmsg'=>$aex->getMessage()));
}catch(Exception $ex){
	$result = json_encode(array('action'=>'SessionOver', 'apMac'=>$data['apMac'], 'errmsg'=>'error'));
}

echo $result;


