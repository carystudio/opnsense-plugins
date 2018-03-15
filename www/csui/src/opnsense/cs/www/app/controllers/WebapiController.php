<?php
require_once("config.inc");
require_once('util.inc');
require_once('interfaces.inc');


class WebapiController extends BaseController
{
    private $ERRORCODE = array(
        '100'=>'未知错误',
        '110'=>'发送的数据格式不正确',
        '111'=>'发送的数据不正确',
        '112'=>'数据没有action',
        '113'=>'缺少action的数据',
        '114'=>'action不存在',
        '9999'=>'未登录',

        '1100'=>'dns1不正确',
        '1101'=>'dns2不正确',
        '1102'=>'dns1不能为空',
        '1103'=>'IP不正确',
        '1104'=>'子网掩码不正确',
        '1105'=>'默认网关不正确',
        '1106'=>'用户名和密码不能为空',
        '1107'=>'WAN连接方式不正确',
        '1108'=>'MAC不正确',
        '1109'=>'描述不能为空',
        '1110'=>'要删除的MAC不能为空',
        '1111'=>'网关不在接口所在的网络中',
        '1112'=>'该网段的静态路由已存在',
        '1113'=>'端口过滤类型不正确',
        '11131'=>'目录地址不能是LAN的IP',
        '1114'=>'要删除的静态路由不存在s',
        '1115'=>'缺少QOS类型',
        '1116'=>'带宽不能小于1kbit/s',
        '1117'=>'该IP限制已存在',
        '1118'=>'添加管道出错',
        '1119'=>'网络协议不正确',
        '1120'=>'开始和结束端口不正确',
        '1121'=>'IP/端口过滤规则已存在',
        '1122'=>'IP/端口过滤规则不存在',
        '1123'=>'端口不正确',
        '1124'=>'端口转发规则已存在',
        '1125'=>'端口转发规则不存在',
        '1126'=>'域名不正确',
        '1127'=>'域名已存在',
        '1128'=>'要删除的域名不存在',
        '1129'=>'旧密码不正确',
        '1130'=>'修改密码失败',
        '1131'=>'时间不正确',
        '1132'=>'分钟不正确',
        '1133'=>'时不正确',
        '1134'=>'月不正确',
        '1135'=>'日不正确',
        '1136'=>'星期不正确',
        '1137'=>'开启参数不正确',
        '1138'=>'服务器IP不正确',
        '1139'=>'地址池起始IP不正确',
        '1140'=>'地址池结束IP不正确',
        '11401'=>'Win服务器IP不正确',
        '1141'=>'用户名不能为空',
        '1142'=>'密码不能为空',
        '1143'=>'用户已存在',
        '1144'=>'用户不存在',
        '1145'=>'Portal类型不正确',
        '1146'=>'空闲超时时间不正确',
        '1147'=>'IP白名单不正确',
        '1148'=>'MAC白名单不正确',
        '1149'=>'热点名称不正确',
        '1150'=>'设置Portal服务发生错误',
        '1151'=>'取Portal用户发生错误',
        '1152'=>'有效时间不正确',
        '1153'=>'可登录时间不正确',
        '1154'=>'重复登录不正确',
        '1155'=>'密码长度不正确(6-15字节)',
        '1156'=>'用户已存在',
        '1157'=>'添加用户出错',
        '1158'=>'用户不存在',
        '1159'=>'删除用户出错',
        '11591'=>'更新用户出错',
        '1160'=>'L2TP加密类型不正确',
        '1161'=>'自动获取DNS服务器时不能关闭DNS转发',
        '1162'=>'Portal用户未登录',
        '1163'=>'Portal用户退出出错',
        '1164'=>'自定义模板未上传',
        '1165'=>'升级固件参数不正确'
    );

    private function checkData($para){
        if (!is_array($para['data']) || count($para['data'])<=0) {
            throw new AppException('113');
        }
    }

    public function indexAction()
    {
        $text = $this->request->getRawBody();
        $result = array('rescode'=>0, 'data'=>array());
        try{
            if(!$this->session->has('username')){
                throw new AppException('9999');
            }
            if(isset($_POST['action'])){
                $action = $_POST['action'];
                if('backupConfig'==$action){
                    System::backupConfig();
                }else if('restoreConfig'==$action){
                    System::restoreConfig($this->request);
                }else if('setPortalStatusUp'==$action){
                    Portal::setPortalStatusUp($this->request);
                }else if('setUploadFile'==$action){
                    Accontrol::setUploadFile($this->request);
                }else if('saveConfigFile'==$action){
                    System::saveConfigFile($this->request);
                }
            }
            $para = json_decode($text, true);
            if(NULL === $para){
                throw new AppException('110');
            }
            if (!is_array($para)) {
                throw new AppException('111');
            }
            if (!isset($para['action'])) {
                global $config;
                print_r($config);
                throw new AppException('112');
            }

            $action = $para['action'];
            if ('getWanInfo' == $action) {
                $result['rescode'] = Network::getWanInfo();
            } else if ('getLanInfo' == $action) {
                $result['rescode'] = Network::getLanInfo();
            } else if ('setLanInfo' == $action) {
                $this->checkData($para);
                $result['rescode'] = Network::setLanInfo($para['data']);
            } else if ('setWanInfo' == $action) {
                $this->checkData($para);
                $result['rescode']  = Network::setWanInfo($para['data']);
            }else if ('getStaticDhcp' == $action){
                $result['rescode']  = Network::getStaticDhcp();
            }else if('addStaticDhcp' == $action){
                $this->checkData($para);
                $result['rescode']  = Network::addStaticDhcp($para['data']);
            }else if('delStaticDhcp' == $action){
                $this->checkData($para);
                $result['rescode']  = Network::delStaticDhcp($para['data']);
            }else if('getStatusInfo' == $action){
                $result['rescode']  = System::getStatusInfo();
            }else if('reboot' == $action){
                $result['rescode']  = System::reboot();
            }elseif('getArpInfo' == $action){
                $result['rescode']  = System::getArpInfo();
            }else if('getStaticRoute' == $action){
                $result['rescode']  = Network::getStaticRoute();
            }else if('addStaticRoute' == $action){
                $this->checkData($para);
                $result['rescode']  = Network::addStaticRoute($para['data']);
            }else if('delStaticRoute' == $action){
                $this->checkData($para);
                $result['rescode']  = Network::delStaticRoute($para['data']);
            }else if('getQosStatus' == $action){
                $result['rescode']  = Qos::getQosStatus();
            }else if('setQos' == $action){
                $this->checkData($para);
                $result['rescode']  = Qos::setQos($para['data']);
            }else if('addQosCustom' == $action){
                $this->checkData($para);
                $result['rescode']  = Qos::addQosCustom($para['data']);
            }else if('delQosCustom' == $action){
                $this->checkData($para);
                $result['rescode']  = Qos::delQosCustom($para['data']);
            }else if('getFilterStatus' == $action){
                $result['rescode']  = Firewall::getFilterStatus();
            }else if('addFilter' == $action){
                $this->checkData($para);
                $result['rescode']  = Firewall::addFilter($para['data']);
            }else if('delFilter' == $action){
                $this->checkData($para);
                $result['rescode']  = Firewall::delFilter($para['data']);
            }else if('getPatStatus' == $action){
                $result['rescode']  = Firewall::getPatStatus();
            }else if('addPatItem' == $action){
                $this->checkData($para);
                $result['rescode']  = Firewall::addPatItem($para['data']);
            }else if('delPatItem' == $action){
                $this->checkData($para);
                $result['rescode']  = Firewall::delPatItem($para['data']);
            }else if('getDnsForwardStatus' == $action){
                $result['rescode']  = Dns::getDnsForwardStatus();
            }else if('setDnsForwardStatus' == $action){
                $this->checkData($para);
                $result['rescode']  = Dns::setDnsForwardStatus($para['data']);
            }else if('addDnsOverwrite' == $action){
                $this->checkData($para);
                $result['rescode']  = Dns::addDnsOverwrite($para['data']);
            }else if('delDnsOverwrite' == $action){
                $this->checkData($para);
                $result['rescode']  = Dns::delDnsOverwrite($para['data']);
            }else if('changePassword' == $action){
                $this->checkData($para);
                $result['rescode']  = User::changePassword($para['data']);
            }else if('getGwTime' == $action){
                $result['rescode']  = System::getGwTime();
            }else if('setGwTime' == $action){
                $this->checkData($para);
                $result['rescode']  = System::setGwTime($para['data']);
            }else if('getRemoteAccess' == $action){
                $result['rescode']  = Firewall::getRemoteAccess();
            }else if('setRemoteAccess' == $action){
                $this->checkData($para);
                $result['rescode']  = Firewall::setRemoteAccess($para['data']);
            }else if('getRebootSched' == $action){
                $result['rescode']  = System::getRebootSched();
            }else if('setRebootSched' == $action){
                $this->checkData($para);
                $result['rescode']  = System::setRebootSched($para['data']);
            }else if("LoadDefSettings" == $action){
                $result['rescode'] = System::loadDefSettings();
            }else if('getPptpdStatus' == $action){
                $result['rescode']  = Pptpd::getStatus();
            }else if('setPptpdStatus' == $action){
                $this->checkData($para);
                $result['rescode']  = Pptpd::setStatus($para['data']);
            }else if('getPptpdUsers' == $action){
                $result['rescode']  = Pptpd::getUsers();
            }else if('addPptpdUser' == $action){
                $this->checkData($para);
                $result['rescode']  = Pptpd::addUser($para['data']);
            }else if('delPptpdUser' == $action){
                $this->checkData($para);
                $result['rescode']  = Pptpd::delUser($para['data']);
            }else if('getPortalStatus' == $action){
                $result['rescode']  = Portal::getPortalStatus();
            }else if('setPortalStatus' == $action){
                $this->checkData($para);
                $result['rescode']  = Portal::setPortalStatus($para['data']);
            }else if('getPortalUsers' == $action){
                $result['rescode']  = Portal::getPortalUsers($para['data']);
            }else if('addPortalUser' == $action){
                $this->checkData($para);
                $result['rescode']  = Portal::addPortalUser($para['data']);
            }else if('delPortalUser' == $action){
                $this->checkData($para);
                $result['rescode']  = Portal::delPortalUser($para['data']);
            }else if('updatePortalUser' == $action){
                $this->checkData($para);
                $result['rescode']  = Portal::updatePortalUser($para['data']);
            }else if('getPortalSessions' == $action) {
                $result['rescode'] = Portal::getSessions();
            }else if('delPortalSession' == $action){
                $this->checkData($para);
                $result['rescode']  = Portal::delSession($para['data']);
            }else if('getL2tpStatus' == $action) {
                $result['rescode'] = L2tp::getStatus();
            }else if('setL2tpStatus' == $action){
                $this->checkData($para);
                $result['rescode']  = L2tp::setStatus($para['data']);
            }else if('getL2tpUsers' == $action) {
                $result['rescode'] = L2tp::getUsers();
            }else if('addL2tpUser' == $action){
                $this->checkData($para);
                $result['rescode']  = L2tp::addUser($para['data']);
            }else if('delL2tpUser' == $action){
                $this->checkData($para);
                $result['rescode']  = L2tp::delUser($para['data']);
            }else if('getFirmwareInfo' == $action) {
                $result['rescode'] = Firmware::getFirmwareInfo();
            }else if('upgradeFirmware' == $action) {
                $this->checkData($para);
                $result['rescode'] = Firmware::upgradeFirmware($para['data']);
            }else if('setInterfaceBind'==$action){
                $this->checkData($para);
                $result['rescode'] = Network::setInterfaceBind($para['data']);
            }else if('delInterfaceBind'==$action){
                $this->checkData($para);
                $result['rescode'] = Network::delInterfaceBind($para['data']);
                }else if('getNetInfo'==$action){
                $result['rescode'] = Network::getNetInfo($para['data']);
            }else if('getLinksData'==$action){
                $result['rescode'] = Network::getLinksData($para['data']);
            }else if("scanAp" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::acScanAp($para['data']);
            }else if("setApIp" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApIp($para['data']);
            }else if("setApName" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApName($para['data']);
            }else if("setApReboot" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApReboot($para['data']);
            }else if("setAcReset" == $action){
                $result['rescode'] = Accontrol::setAcReset($para['data']);
            }else if("setApLedState" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApLedState($para['data']);
			}else if("setApRestore" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApRestore($para['data']);
            }else if("setApUpgrade" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setApUpgrade($para['data']);
            }else if("getApStatusConfig" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::getApStatusConfig($para['data']);
            }else if("setQuick" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::setQuick($para['data']);
            }else if("getApUpgradeInfo" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::getApUpgradeInfo($para['data']);
            }else if("delApUpgradeFile" == $action){
                $this->checkData($para);
                $result['rescode'] = Accontrol::delApUpgradeFile($para['data']);
            }else if("updataConfig" == $action){
                System::updataConfig();
            }else if('test' == $action){
                $result['rescode'] = Dns::getErrors();
            }else{
                throw new AppException('114');
            }
        } catch (AppException $aex) {
            $result['rescode'] = $aex->getMessage();
        }catch(Exception $ex){
            $result['rescode'] = '100';
        }
        if(is_array($result['rescode'])){
            $result['data'] = $result['rescode'];
            $result['rescode'] = 0;
        }else{
            $result['rescode'] = $result['rescode'];
        }


        echo json_encode($result);
    }

    public function genErrorAction(){
        $backends = array('Dns','Firewall','Firmware','L2tp','Network','Portal','Pptpd','Qos','System','User');
        $array_str = "var data_msg_json = {\n";
        foreach($backends as $backend){
            $errors = $backend::getErrors();
            foreach($errors as $var=>$val){
                $array_str.="\t'$var':'$val',\n";
            }
        }
        $array_str.="}";
        $specjs_path = APP_PATH.'public/js/spec.js';
        $specjs_content = file_get_contents($specjs_path.'.tpl');
        $specjs_content = str_replace('//{data_msg_json}',$array_str, $specjs_content);
        $res = file_put_contents($specjs_path,$specjs_content);
        if($res){
            echo 'Gen Successfully!';
        }else{
            echo 'Gen Error!';
        }
    }
}

