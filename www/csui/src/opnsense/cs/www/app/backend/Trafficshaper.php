<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/openvpn.inc');
require_once('auth.inc');

use \OPNsense\Core\Backend;

class Trafficshaper extends Csbackend
{
    protected static $ERRORCODE = array(
        'TRAFFIC_100'=>'参数不正确',
    );

    const BWMetric = array("bit","Kbit","Mbit");
    const Mask = array("none","src-ip","dst-ip");
    const Scheduler = array("","fifo","rr","qfq");
    const Proto = array("ip","ip4","ip6","udp","tcp","tcp_ack","tcp_ack_not","icmp","igmp","esp","ah","gre");
    const SrcAnDstPort = array(
        "any","cvsup","domain","ftp","hbci","http","https","aol","auth",
        "imap","imaps","ipsec-msft","isakmp","l2f","ldap","ms-streaming",
        "afs3-fileserver","microsoft-ds","ms-wbt-server","wins","msnp",
        "nntp","ntp","netbios-dgm","netbios-ns","netbios-ssn","openvpn",
        "pop3","pop3s","pptp","radius","radius-acct","avt-profile-1","sip",
        "smtp","igmpv3lite","urd","snmp","snmptrap","ssh","nat-stun-port",
        "submission","teredo","telnet","tftp","rfb"
    );
    const Direction = array("","in","out");

    public static function getTraShaCfg(){
        global $config;

        $pipes = array();
        $queues = array();
        $rules = array();

        $descr = array("pdescr"=>array(),"qdescr"=>array(),"rdescr"=>array());
        foreach ($config['OPNsense']['TrafficShaper'] as $key=>$val) {
            if('pipes' == $key){
                if(!isset($val['pipe'])){
                    continue;
                }
                foreach ($val['pipe'] as $ke=>$va){
                    if(is_numeric($ke)){
                        foreach ($va as $k=>$v){
                            if('@attributes' == $k){
                                $va['uuid'] = $va[$k]['uuid'];
                                unset($va[$k]);
                                array_push($pipes,$va);
                                $ptmp = array("descr"=>$va['description'],"uuid"=>$va['uuid']);
                                array_push($descr['pdescr'],$ptmp);
                            }
                        }
                    }else{
                        if('@attributes' == $ke){
                            $val['pipe']['uuid'] = $va['uuid'];
                            unset($val['pipe'][$ke]);
                            array_push($rules,$val['pipe']);
                            $ptmp = array("descr"=>$va['description'],"uuid"=>$val['pipe']['uuid']);
                            array_push($descr['pdescr'],$ptmp);
                        }
                    }
                }
            }
            if('queues' == $key){
                if(!isset($val['queue'])){
                    continue;
                }
                foreach ($val['queue'] as $ke=>$va){
                    if(is_numeric($ke)){
                        foreach ($va as $k=>$v){
                            if('@attributes' == $k){
                                $va['uuid'] = $va[$k]['uuid'];
                                unset($va[$k]);
                                array_push($queues,$va);
                                $qtmp = array("descr"=>$va['description'],"uuid"=>$va['uuid']);
                                array_push($descr['qdescr'],$qtmp);
                            }
                        }
                    }else{
                        if('@attributes' == $ke){
                            $val['queue']['uuid'] = $va['uuid'];
                            unset($val['queue'][$ke]);
                            array_push($queues,$val['queue']);
                            $qtmp = array("descr"=>$val['queue']['description'],"uuid"=>$val['queue']['uuid']);
                            array_push($descr['qdescr'],$qtmp);
                        }
                    }
                }
            }
            if('rules' == $key){
                if(!isset($val['rule'])){
                    continue;
                }
                foreach ($val['rule'] as $ke=>$va){
                    if(is_numeric($ke)){
                        foreach ($va as $k=>$v){
                            if('@attributes' == $k){
                                $va['uuid'] = $va[$k]['uuid'];
                                unset($va[$k]);
                                array_push($rules,$va);
                                $rtmp = array("descr"=>$va['description'],"uuid"=>$va['uuid']);
                                array_push($descr['rdescr'],$rtmp);
                            }
                        }
                    }else{
                        if('@attributes' == $ke){
                            $val['rule']['uuid'] = $va['uuid'];
                            unset($val['rule'][$ke]);
                            array_push($rules,$val['rule']);
                            $rtmp = array("descr"=>$va['description'],"uuid"=>$val['rule']['uuid']);
                            array_push($descr['rdescr'],$rtmp);
                        }
                    }

                }
            }
        }

        foreach ($queues as $key=>$val){
            foreach ($val as $k=>$v){
                if('pipe' == $k){
                    foreach ($v as $i=>$x){
                        foreach ($descr['pdescr'] as $a=>$b){
                            if($x == $descr['pdescr'][$a]['uuid']){
                                $queues[$key]['pipeArr'][$i]['descr'] = $descr['pdescr'][$a]['descr'];
                                $queues[$key]['pipeArr'][$i]['uuid'] = $descr['pdescr'][$a]['uuid'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($rules as $key=>$val){
            foreach ($val as $k=>$v){
                if('target' == $k){
                    foreach ($descr as $a=>$b){
                        foreach ($b as $c=>$d){
                            if($v == $descr[$a][$c]['uuid']){
                                $rules[$key]['targetArr']['descr'] = $descr[$a][$c]['descr'];
                                $rules[$key]['targetArr']['uuid'] = $descr[$a][$c]['uuid'];
                            }
                        }
                    }
                }
            }
        }


        $cfgarr = array("pipes"=>$pipes,"queues"=>$queues,"rules"=>$rules,"descr"=>$descr);
        return $cfgarr;
    }

    public static function delPipe($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['uuid'])){
                throw new AppException("TRAFFIC_100");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['pipes'];
            if(!isset($pipe['pipe']) || '' == $pipe['pipe']){
                throw new AppException("TRAFFIC_101");  //用户不存在
            }
            $delFlag = false;
            foreach ($pipe['pipe'] as $key=>$val){
                foreach ($val as $k=>$v){
                    if("@attributes" == $k){
                        if($data['uuid'] == $val[$k]['uuid']){
                            unset($config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]);
                            $delFlag = true;
                        }
                    }
                }
            }

            if(!$delFlag){
                throw new AppException("TRAFFIC_101");  //用户不存在
            }
            write_config();
            self::reconfigure();
//            self::flushreload();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delQueue($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['uuid'])){
                throw new AppException("TRAFFIC_200");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['queues'];
            if(!isset($pipe['queue']) || '' == $pipe['queue']){
                throw new AppException("TRAFFIC_201");  //用户不存在
            }
            $delFlag = false;
            foreach ($pipe['queue'] as $key=>$val){
                if(is_numeric($key)){
                    foreach ($val as $k=>$v){
                        if("@attributes" == $k){
                            if($data['uuid'] == $val[$k]['uuid']){
                                unset($config['OPNsense']['TrafficShaper']['queues']['queue'][$key]);
                                $delFlag = true;
                            }
                        }
                    }
                }else{
                    if("@attributes" == $key){
                        if($data['uuid'] == $pipe['queue'][$key]['uuid']){
                            unset($config['OPNsense']['TrafficShaper']['queues']['queue']);
                            $delFlag = true;
                        }
                    }

                }
            }

            if(!$delFlag){
                throw new AppException("TRAFFIC_201");  //用户不存在
            }

            write_config();
            self::reconfigure();
//            self::flushreload();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delrule($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['uuid'])){
                throw new AppException("TRAFFIC_300");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['rules'];
            if(!isset($pipe['rule']) || '' == $pipe['rule']){
                throw new AppException("TRAFFIC_301");  //用户不存在
            }
            $delFlag = false;
            foreach ($pipe['rule'] as $key=>$val){
                foreach ($val as $k=>$v){
                    if("@attributes" == $k){
                        if($data['uuid'] == $val[$k]['uuid']){
                            unset($config['OPNsense']['TrafficShaper']['rules']['rule'][$key]);
                            $delFlag = true;
                        }
                    }
                }
            }

            if(!$delFlag){
                throw new AppException("TRAFFIC_301");  //用户不存在
            }

            write_config();
            self::reconfigure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function setPipe($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['enabled']) || !is_numeric($data['enabled'])){
                throw new AppException("TRAFFIC_400");  //开启参数不正确
            }
            if(!isset($data['bandwidth']) || !is_numeric($data['bandwidth'])){
                throw new AppException("TRAFFIC_401");  //带宽参数不正确
            }

            if(!isset($data['bandwidthMetric']) || !in_array($data['bandwidthMetric'],self::BWMetric)){
                throw new AppException("TRAFFIC_402");  //带宽单位参数不正确
            }

            if(!isset($data['queue']) || ('' != $data['queue'] && !is_numeric($data['queue'])) ){
                throw new AppException("TRAFFIC_403");  //队列参数不正确
            }
            if('' != $data['queue']){
                $queueCnt = intval($data['queue']);
                if($queueCnt<2 || $queueCnt>100){
                    throw new AppException("TRAFFIC_404");  //队列参数不正确，范围为2~100
                }
            }

            if(!isset($data['mask']) || !in_array($data['mask'],self::Mask)){
                throw new AppException("TRAFFIC_405");  //掩码参数不正确
            }
            if(!isset($data['scheduler']) || !in_array($data['scheduler'],self::Scheduler)){
                throw new AppException("TRAFFIC_406");  //调度程序类型参数不正确
            }

            if(!isset($data['codel_enable']) || ('' != $data['codel_enable'] && !is_numeric($data['codel_enable'])) ){
                throw new AppException("TRAFFIC_415");  //启用CoDel参数不正确
            }

            if(!isset($data['codel_target']) || ('' != $data['codel_target'] && !is_numeric($data['codel_target'])) ){
                throw new AppException("TRAFFIC_407");  //(FQ-)CoDel目标参数不正确
            }
            if('' != $data['codel_target']){
                $codel_traget = intval($data['codel_target']);
                if($codel_traget <1 || $codel_traget > 10000){
                    throw new AppException("TRAFFIC_407");  //(FQ-)CoDel目标参数不正确
                }
            }

            if(!isset($data['codel_interval']) || ('' != $data['codel_interval'] && !is_numeric($data['codel_interval'])) ){
                throw new AppException("TRAFFIC_407");  //(FQ-)CoDel目标参数不正确
            }
            if('' != $data['codel_interval']){
                $codel_interval = intval($data['codel_interval']);
                if($codel_interval <1 || $codel_interval > 10000){
                    throw new AppException("TRAFFIC_408");  //(FQ-)CoDel间隔参数不正确
                }
            }

            if(!isset($data['codel_ecn_enable']) || ('' != $data['codel_ecn_enable'] && !is_numeric($data['codel_ecn_enable'])) ){
                throw new AppException("TRAFFIC_409");  //FQ-)CoDel ECN参数不正确
            }

            if(!isset($data['fqcodel_quantum']) || ('' != $data['fqcodel_quantum'] && !is_numeric($data['fqcodel_quantum'])) ){
                throw new AppException("TRAFFIC_410");  //FQ-CoDel量参数不正确
            }
            if('' != $data['fqcodel_quantum']){
                $fqcodel_quantum = intval($data['fqcodel_quantum']);
                if($fqcodel_quantum <1 || $fqcodel_quantum > 65535){
                    throw new AppException("TRAFFIC_410");  //FQ-CoDel量参数不正确
                }
            }

            if(!isset($data['fqcodel_limit']) || ('' != $data['fqcodel_limit'] && !is_numeric($data['fqcodel_limit'])) ){
                throw new AppException("TRAFFIC_411");  //FQ-CoDel限制参数不正确
            }
            if('' != $data['fqcodel_limit']){
                $fqcodel_limit = intval($data['fqcodel_limit']);
                if($fqcodel_limit <1 || $fqcodel_limit > 65535){
                    throw new AppException("TRAFFIC_411");  //FQ-CoDel限制参数不正确
                }
            }

            if(!isset($data['fqcodel_flows']) || ('' != $data['fqcodel_flows'] && !is_numeric($data['fqcodel_flows'])) ){
                throw new AppException("TRAFFIC_412");  //FQ-CoDel限制参数不正确
            }
            if('' != $data['fqcodel_flows']){
                $fqcodel_flows = intval($data['fqcodel_flows']);
                if($fqcodel_flows <1 || $fqcodel_flows > 65535){
                    throw new AppException("TRAFFIC_412");  //FQ-CoDel流参数不正确
                }
            }

            if(!isset($data['description']) && '' == trim($data['description'])){
                throw new AppException("TRAFFIC_414");  //描述参数不正确
            }


            $addPipe = array(
                "number" => 10000,
                "enabled" => $data['enabled'],
                "bandwidth" => $data['bandwidth'],
                "bandwidthMetric" => $data['bandwidthMetric'],
                "queue" => $data['queue'],
                "mask" => $data['mask'],
                "scheduler" => $data['scheduler'],
                "codel_enable" => $data['codel_enable'],
                "codel_target" => $data['codel_target'],
                "codel_interval" => $data['codel_interval'],
                "codel_ecn_enable" => $data['codel_ecn_enable'],
                "fqcodel_quantum" => $data['fqcodel_quantum'],
                "fqcodel_limit" => $data['fqcodel_limit'],
                "fqcodel_flows" => $data['fqcodel_flows'],
                "origin" => 'TrafficShaper',
                "description" => $data['description']
            );

            $pipe = $config['OPNsense']['TrafficShaper']['pipes'];
            if(isset($data['uuid']) && '' != $data['uuid'] ){   //编辑
                $editFlag = false;
                if(!isset($pipe['pipe'])){
                    throw new AppException("TRAFFIC_413");  //管道不存在
                }
                foreach ($pipe['pipe'] as $key=>$val){
                    foreach ($val as $k=>$v){
                        if('@attributes' == $k){
                            if($v['uuid'] == $data['uuid']){
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['enabled'] = $data['enabled'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['bandwidth'] = $data['bandwidth'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['bandwidthMetric'] = $data['bandwidthMetric'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['queue'] = $data['queue'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['mask'] = $data['mask'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['scheduler'] = $data['scheduler'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['codel_enable'] = $data['codel_enable'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['codel_target'] = $data['codel_target'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['codel_interval'] = $data['codel_interval'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['codel_ecn_enable'] = $data['codel_ecn_enable'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['fqcodel_quantum'] = $data['fqcodel_quantum'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['fqcodel_limit'] = $data['fqcodel_limit'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['fqcodel_flows'] = $data['fqcodel_flows'];
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['description'] = $data['description'];
                                $editFlag = true;
                                break;
                            }
                        }
                    }
                }
                if(!$editFlag){
                    throw new AppException("TRAFFIC_413");  //管道不存在
                }
            }else{  //添加
                $uuid = self::getUuid();
                $addPipe['@attributes']['uuid'] = $uuid;
                if(isset($pipe['pipe'])){
                    $number = 10000;
                    foreach ($pipe['pipe'] as $key=>$val){
                        foreach ($val as $k=>$v){
                            if('number' == $k){
                                if(intval($v) >= $number){
                                    $number = intval($v);
                                }
                            }
                        }
                    }
                    $addPipe['number'] = intval($number) + 1;
                    array_push($pipe['pipe'],$addPipe);
                }else{
                    $pipe = array("pipe"=>array($addPipe));
                }
                $config['OPNsense']['TrafficShaper']['pipes'] = $pipe;
            }
            write_config();
            self::reconfigure();
//            self::flushreload();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getUuid(){
        $uuid1 = self::getRandStr(8,3);
        $uuid2 = self::getRandStr(4,3);
        $uuid3 = self::getRandStr(4,3);
        $uuid4 = self::getRandStr(4,3);
        $uuid5 = self::getRandStr(12,3);
        $uuid = $uuid1.'-'.$uuid2.'-'.$uuid3.'-'.$uuid4.'-'.$uuid5;
        return $uuid;
    }

    //mode 0:数字和字母 1:数字 2:字母
    public static function getRandStr($strLen,$mode=0){
        $mode = intval($mode);
        if(0>$mode || 3<$mode){
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
        }else if($mode==3){
            $stuff = '1234567890abcdef';
        }else{
            $stuff = '1234567890abcdefghijklmnopqrstuvwxyz';//附加码显示范围ABCDEFGHIJKLMNOPQRSTUVWXYZ
        }
        $stuff_len = strlen($stuff) - 1;
        for ($i = 0; $i < $number_len; $i++) {
            $number .= substr($stuff, mt_rand(0, $stuff_len), 1);
        }

        return $number;
    }
    
    public static function setQueue($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['enabled']) && !is_numeric($data['enabled'])){
                throw new AppException("TRAFFIC_500");      //开启参数不正确
            }
            if(!isset($data['pipe'])){
                throw new AppException("TRAFFIC_501");      //管道参数不正确
            }
            if(!isset($data['weight']) && !is_numeric($data['weight'])){
                throw new AppException("TRAFFIC_502");      //权重参数不正确
            }
            $weight = intval($data['weight']);
            if($weight < 1 || $weight > 100){
                throw new AppException("TRAFFIC_502");      //权重参数不正确
            }
            if(!isset($data['mask']) && !in_array($data['mask'],self::Mask)){
                throw new AppException("TRAFFIC_503");      //掩码参数不正确
            }
            if(!isset($data['codel_enable']) && !is_numeric($data['codel_enable'])){
                throw new AppException("TRAFFIC_504");      //启用CoDel参数不正确
            }
            if(!isset($data['codel_target']) || ('' != $data['codel_target'] && !is_numeric($data['codel_target'])) ){
                throw new AppException("TRAFFIC_505");      //(FQ-)CoDel目标参数不正确
            }
            if(!isset($data['codel_interval']) || ('' != $data['codel_interval'] && !is_numeric($data['codel_interval']))){
                throw new AppException("TRAFFIC_506");      // (FQ-)CoDel间隔参数不正确
            }
            if(!isset($data['codel_ecn_enable']) && !is_numeric($data['codel_ecn_enable'])){
                throw new AppException("TRAFFIC_507");      // (FQ-)CoDel ECN参数不正确
            }
            if(!isset($data['description'])){
                throw new AppException("TRAFFIC_508");      // 描述参数不正确
            }

            $pipes = $config['OPNsense']['TrafficShaper']['pipes'];
            if(!isset($pipes['pipe']) || '' == $pipes['pipe']){
                throw new AppException("TRAFFIC_509");      //管道不存在
            }
            $pipeFlag = false;
            foreach ($pipes['pipe'] as $key=>$val){
                foreach ($val as $k=>$v){
                    if('@attributes' == $k){
                        if($data['pipe'] == $v['uuid']){
                            $pipeFlag = true;
                            break;
                        }
                    }
                }
                if($pipeFlag){
                    break;
                }
            }
            if(!$pipeFlag){
                throw new AppException("TRAFFIC_501");      //管道参数不正确
            }


            $addQueue =array(
                "number"=>10000,
                "enabled"=>$data['enabled'],
                "pipe"=>array($data['pipe']),
                "weight"=>$data['weight'],
                "mask"=>$data['mask'],
                "codel_enable"=>$data['codel_enable'],
                "codel_target"=>$data['codel_target'],
                "codel_interval"=>$data['codel_interval'],
                "codel_ecn_enable"=>$data['codel_ecn_enable'],
                "description"=>$data['description'],
                "origin"=>"TrafficShaper"
            );

            $queues = $config['OPNsense']['TrafficShaper']['queues'];
            if(isset($data['uuid']) && '' != trim($data['uuid'])){  //编辑
                $editFlag = false;
                if(!isset($queues['queue'])){
                    throw new AppException("TRAFFIC_510");  //队列不存在
                }
                foreach ($queues['queue'] as $key=>$val){
                    if(is_numeric($key)){
                        foreach ($val as $k=>$v){
                            if('@attributes' == $k){
                                if($v['uuid'] == $data['uuid']){
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['enabled'] = $data['enabled'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['pipe'] = array($data['pipe']);
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['weight'] = $data['weight'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['mask'] = $data['mask'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['codel_enable'] = $data['codel_enable'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['codel_target'] = $data['codel_target'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['codel_interval'] = $data['codel_interval'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['codel_ecn_enable'] = $data['codel_ecn_enable'];
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['description'] = $data['description'];
                                    $editFlag = true;
                                    break;
                                }
                            }
                        }
                    }else{
                        if('@attributes' == $key){
                            if($val['uuid'] == $data['uuid']){
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['enabled'] = $data['enabled'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['pipe'] = array($data['pipe']);
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['weight'] = $data['weight'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['mask'] = $data['mask'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['codel_enable'] = $data['codel_enable'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['codel_target'] = $data['codel_target'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['codel_interval'] = $data['codel_interval'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['codel_ecn_enable'] = $data['codel_ecn_enable'];
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['description'] = $data['description'];
                                $editFlag = true;
                                break;
                            }
                        }
                    }
                    if($editFlag){
                        break;
                    }
                }
                if(!$editFlag){
                    throw new AppException("TRAFFIC_510");  //队列不存在
                }
            }else{  //添加
                $uuid = self::getUuid();
                $addQueue['@attributes']['uuid'] = $uuid;
                if(isset($queues['queue'])){
                    $number = 10000;
                    $cuntFlag = false;
                    foreach ($queues['queue'] as $key=>$val){
                        if(is_numeric($key)){
                            foreach ($val as $k=>$v){
                                if('number' == $k){
                                    if(intval($v) >= $number){
                                        $number = intval($v);
                                        $cuntFlag = true;
                                    }
                                }
                            }
                        }else{
                            if('number' == $key){
                                if(intval($val) >= $number){
                                    $number = intval($val);
                                }
                            }
                        }
                    }
                    $addQueue['number'] = intval($number) + 1;
                    if($cuntFlag){
                        array_push($queues['queue'],$addQueue);
                    }else{
                        $queuesTmp = $queues['queue'];
                        unset($queues);
                        $queues = [];
                        $queues['queue'][0] = $queuesTmp;
                        array_push($queues['queue'],$addQueue);
                    }

                }else{
                    $queues = array("queue"=>array($addQueue));
                }
                $config['OPNsense']['TrafficShaper']['queues'] = $queues;
            }

            write_config();
            self::reconfigure();
//            self::flushreload();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }
        return $result;
    }
    
    public static function setRule($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['sequence']) && !is_numeric($data['sequence'])){
                throw new AppException("TRAFFIC_600");  //序列不正确
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('TRAFFIC_601');      //接口参数不正确
            }
            if(!isset($data['interface2']) || ('' != $data['interface2'] && !isset($config['interfaces'][$data['interface2']]) )){
                throw new AppException('TRAFFIC_602');  //接口2参数不正确
            }
            if(!isset($data['proto']) && !in_array($data['proto'],self::Proto)){
                throw new AppException('TRAFFIC_603');  //协议参数不正确
            }
            if(!isset($data['source'])){
                throw new AppException('TRAFFIC_604');  //源参数不正确
            }
            if('' == $data['source']){
                $data['source'] = 'any';
            }
            if('' != $data['source']){
                if('any' != $data['source'] ){
                    if( !is_ipaddr($data['source'])){
                        $source = explode("/",$data['source']);
                        if(!is_ipaddr($source[0]) || !is_numeric($source[1])){
                            throw new AppException("TRAFFIC_604");   //源参数不正确
                        }
                    }
                }
            }
            if(!isset($data['source_not']) && !is_numeric($data['source_not'])){
                throw new AppException("TRAFFIC_605");   //源参数不正确
            }

            if(!isset($data['src_port'])){
                throw new AppException("TRAFFIC_606");   //源端口参数不正确
            }
            if('' == $data['src_port']){
                $data['src_port'] = 'any';
            }
            if(is_numeric($data['src_port'])){
                $srcPort = intval($data['src_port']);
                if($srcPort<1 || $srcPort>65535){
                    throw new AppException("TRAFFIC_606");   //源端口参数不正确
                }
            }else{
                if(!in_array($data['src_port'],self::SrcAnDstPort)){
                    throw new AppException("TRAFFIC_606");   //源端口参数不正确
                }
            }

            if(!isset($data['destination'])){
                throw new AppException('TRAFFIC_607');  //目的地参数不正确
            }
            if('' == $data['destination']){
                $data['destination'] = 'any';
            }
            if('' != $data['destination']){
                if('any' != $data['destination'] ){
                    if( !is_ipaddr($data['destination'])){
                        $destination = explode("/",$data['destination']);
                        if(!is_ipaddr($destination[0]) || !is_numeric($destination[1])){
                            throw new AppException("TRAFFIC_607");   //目的地参数不正确
                        }
                    }
                }
            }

            if(!isset($data['destination_not']) && !is_numeric($data['destination_not'])){
                throw new AppException("TRAFFIC_608");   //反转目的地参数不正确
            }

            if(!isset($data['dst_port'])){
                throw new AppException("TRAFFIC_609");   //目的端口参数不正确
            }
            if('' == $data['dst_port']){
                $data['dst_port'] = 'any';
            }
            if(is_numeric($data['dst_port'])){
                $dstPort = intval($data['dst_port']);
                if($dstPort<1 || $dstPort>65535){
                    throw new AppException("TRAFFIC_609");   //源端口参数不正确
                }
            }else{
                if(!in_array($data['dst_port'],self::SrcAnDstPort)){
                    throw new AppException("TRAFFIC_609");   //源端口参数不正确
                }
            }
            if(!isset($data['direction']) || !in_array($data['direction'],self::Direction)){
                throw new AppException("TRAFFIC_610");   //方向参数不正确
            }

            if(!isset($data['target'])){
                throw new AppException("TRAFFIC_611");   //目标参数不正确
            }

            $traffic = $config['OPNsense']['TrafficShaper'];
            if(!is_array($traffic) || ('' == $traffic['pipes'] && '' == $traffic['queues'])){
                throw new AppException("TRAFFIC_612");      //目标参数不存在
            }
            $trafficFlag = false;
            foreach ($traffic as $type=>$params){
                if('pipes' == $type){
                    if('' == $params){
                        continue;
                    }
                    foreach ($params['pipe'] as $key=>$val){
                        if(is_numeric($key)){
                            foreach ($val as $k=>$v){
                                if('@attributes' == $k){
                                    if($data['target'] == $v['uuid']){
                                        $trafficFlag = true;
                                        break;
                                    }
                                }
                            }
                        }else{
                            if('@attributes' == $key){
                                if($data['target'] == $val['uuid']){
                                    $trafficFlag = true;
                                    break;
                                }
                            }
                        }

                        if($trafficFlag){
                            break;
                        }
                    }
                    if($trafficFlag){
                        break;
                    }
                }
                if('queues' == $type){
                    if('' == $params){
                        continue;
                    }
                    foreach ($params['queue'] as $key=>$val){
                        if(is_numeric($key)){
                            foreach ($val as $k=>$v){
                                if('@attributes' == $k){
                                    if($data['target'] == $v['uuid']){
                                        $trafficFlag = true;
                                        break;
                                    }
                                }
                            }
                        }else{
                            if('@attributes' == $key){
                                if($data['target'] == $val['uuid']){
                                    $trafficFlag = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($trafficFlag){
                        break;
                    }
                }
                if($trafficFlag){
                    break;
                }
            }
            if(!$trafficFlag){
                throw new AppException("TRAFFIC_612");      //目标参数不存在
            }
            if(!isset($data['description']) || '' == $data['description']){
                throw new AppException("TRAFFIC_613");      //描述参数不存在
            }

            $addRule =array(
                "sequence"=>$data['sequence'],
                "interface"=>$data['interface'],
                "interface2"=>$data['interface2'],
                "proto"=>$data['proto'],
                "source"=>$data['source'],
                "source_not"=>$data['source_not'],
                "src_port"=>$data['src_port'],
                "destination"=>$data['destination'],
                "destination_not"=>$data['destination_not'],
                "dst_port"=>$data['dst_port'],
                "direction"=>$data["direction"],
                "target"=>$data['target'],
                "description"=>$data['description'],
                "origin"=>"TrafficShaper"
            );
            $rules = $config['OPNsense']['TrafficShaper']['rules'];
            if(isset($data['uuid']) && '' != $data['uuid']){    //编辑
                $editFlag = false;
                if(!isset($rules['rule'])){
                    throw new AppException("TRAFFIC_614");  //规则不存在
                }
                foreach ($rules['rule'] as $key=>$val){
                    foreach ($val as $k=>$v){
                        if('@attributes' == $k){
                            if($v['uuid'] == $data['uuid']){
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['sequence'] = $data['sequence'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['interface'] = $data['interface'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['interface2'] = $data['interface2'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['proto'] = $data['proto'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['source'] = $data['source'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['source_not'] = $data['source_not'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['src_port'] = $data['src_port'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['destination'] = $data['destination'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['destination_not'] = $data['destination_not'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['dst_port'] = $data['dst_port'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['direction'] = $data['direction'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['target'] = $data['target'];
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['description'] = $data['description'];
                                $editFlag = true;
                                break;
                            }
                        }
                    }
                    if($editFlag){
                        break;
                    }
                }
                if(!$editFlag){
                    throw new AppException("TRAFFIC_614");  //规则不存在
                }

            }else{  //添加
                $uuid = self::getUuid();
                $addRule['@attributes']['uuid'] = $uuid;
                if(isset($rules['rule'])){
                    array_push($rules['rule'],$addRule);
                }else{
                    $rules = array("rule"=>array($addRule));
                }
                $config['OPNsense']['TrafficShaper']['rules'] = $rules;
            }

            write_config();
            self::reconfigure();
//            self::flushreload();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }
        return $result;
    }


    /**
     * reconfigure ipfw, generate config and reload
     */
    private static function reconfigure()
    {
        // close session for long running action
//        $this->sessionClose();
        session_write_close();

        $backend = new Backend();
        $backend->configdRun('template reload OPNsense/IPFW');
        $bckresult = trim($backend->configdRun("ipfw reload"));
        if ($bckresult == "OK") {
            $status = "ok";
        } else {
            $status = "error reloading shaper (".$bckresult.")";
        }

        return array("status" => $status);
    }

    /**
     * flush all ipfw rules
     */
    private static function flushreload()
    {
        // close session for long running action
        session_write_close();

        $backend = new Backend();
        $status = trim($backend->configdRun("ipfw flush"));
        $status = trim($backend->configdRun("ipfw reload"));
        return array("status" => $status);
    }

    public static function setConfig(){
        $res = self::reconfigure();
//        $res = self::flushreload();
        return $res;
    }
    
}