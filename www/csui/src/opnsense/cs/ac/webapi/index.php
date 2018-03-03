<?php
require_once('inc.php');

/*
$ap = Db::getApByMac('11:22:33:aa:bb:cc');
var_dump($ap);
*/
$postcontent = file_get_contents('php://input', 'r');
$data = json_decode($postcontent, true);

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

			$ap = Db::createAp($ap);
			if (!ap) {
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
				$ap = Action::deleteState($ap, Action::STATE_UPGRADE);
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO);
					$ap = Action::setState($ap, Action::STATE_WLAN);
					$ap = Action::setState($ap, Action::STATE_SYSTEM);
				}
				break;
			case 'SetReset':
				$ap = Action::deleteState($ap, Action::STATE_RESET);
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO);
					$ap = Action::setState($ap, Action::STATE_WLAN);
					$ap = Action::setState($ap, Action::STATE_SYSTEM);
				}
				break;
			case 'SetReboot':
				$ap = Action::deleteState($ap, Action::STATE_REBOOT);
				if(0 == $status){
					$ap = Action::setState($ap, Action::STATE_RADIO);
					$ap = Action::setState($ap, Action::STATE_WLAN);
					$ap = Action::setState($ap, Action::STATE_SYSTEM);
				}

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
			if('SessionOver' == $result['action']){
				$ap = Action::deleteState($ap, Action::STATE_ONLINE);
			}
		}
	}
}catch(AppException $aex){
	$result = json_encode(array('action'=>'SessionOver', 'apMac'=>$data['apMac'], 'errmsg'=>$aex->getMessage()));
}catch(Exception $ex){
	$result = json_encode(array('action'=>'SessionOver', 'apMac'=>$data['apMac'], 'errmsg'=>'error'));
}

echo $result;


