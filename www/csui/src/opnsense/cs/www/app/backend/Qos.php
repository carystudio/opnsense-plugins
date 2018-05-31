<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");

use \OPNsense\TrafficShaper\TrafficShaper;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Backend;
use \OPNsense\Core\Config;

class Qos extends Csbackend
{
    protected static $ERRORCODE = array(
        'Qos_100'=>'QOS开启参数不正确',
        'Qos_101'=>'带宽不正确',
        'Qos_200'=>'IP不正确',
        'Qos_201'=>'IP已存在'
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


    private static function generateNewId($startAt, $arr)
    {
        $newId = $startAt;
        for ($i=0; $i < count($arr); ++$i) {
            if ($arr[$i]['number'] > $newId && isset($arr[$i+1])) {
                if ($arr[$i+1]['number'] - $arr[$i]['number'] > 1) {
                    // gap found
                    $newId = $arr[$i]['number'] + 1;
                    break;
                }
            } elseif ($arr[$i]['number'] >= $newId) {
                // last item is higher than target
                $newId = $arr[$i]['number'] + 1;
            }
        }

        return $newId;
    }

    public static function test($request){
        $mdlShaper = new TrafficShaper();
        $mdlShaper->pipes->pipe;
        $grid = new UIModelGrid($mdlShaper->pipes->pipe);
        $result = $grid->fetchBindRequest(
            $request,
            array("enabled","number", "bandwidth","bandwidthMetric","burst","description","mask","origin"),
            "number"
        );
        return $result;
    }

    public static function getQosStatus(){
        global $config;

        $qosStatus = array('Type'=>'0','Down'=>'0', 'Up'=>'0');
        if(isset($config['OPNsense']['TrafficShaper']['pipes']['pipe'])){
            $pipes = $config['OPNsense']['TrafficShaper']['pipes']['pipe'];
            $qosStatus['Type'] = '3';
            foreach($pipes as $pipe){
                if('1'==$pipe['enabled']){
                    if('perip_up'==$pipe['description'] || 'perip_down'==$pipe['description']){
                        $qosStatus['Type'] = '2';
                    }else if('auto_up'==$pipe['description'] || 'auto_down'==$pipe['description']){
                        $qosStatus['Type'] = '1';
                    }
                    if('3' != $qosStatus['Type']){
                        break ;
                    }
                }
            }
            $custom = array();
            foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {
                if('2'==$qosStatus['Type']){
                    if(0 !== strpos($pipe['description'], 'perip_')){
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                    }else{
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                        if('perip_down'==$pipe['description']){
                            $qosStatus['Down'] = $pipe['bandwidth'];
                        }else if('perip_up'==$pipe['description']){
                            $qosStatus['Up'] = $pipe['bandwidth'];
                        }else if(strpos($pipe['description'], 'perip_down_')===0){
                            $ip = substr($pipe['description'], 11);
                            if(!isset($custom[$ip])){
                                $custom[$ip] = array('Down'=>'0', 'Up'=>'0');
                            }
                            $custom[$ip]['Down'] = $pipe['bandwidth'];
                        }else if(strpos($pipe['description'], 'perip_up_')===0){
                            $ip = substr($pipe['description'], 9);
                            if(!isset($custom[$ip])){
                                $custom[$ip] = array('Down'=>'0', 'Up'=>'0');
                            }
                            $custom[$ip]['Up'] = $pipe['bandwidth'];
                        }
                    }
                }else if('1'==$qosStatus['Type']){
                    if(0 !== strpos($pipe['description'], 'auto_')){
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                    }else{
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                        if('auto_down'==$pipe['description']){
                            $qosStatus['Down'] = $pipe['bandwidth'];
                        }else if('auto_up'==$pipe['description']){
                            $qosStatus['Up'] = $pipe['bandwidth'];
                        }
                    }
                }else if('3'==$qosStatus['Type']){
                    if(0 !== strpos($pipe['description'], 'advanced_')){
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                    }else{
                        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                    }
                }
            }
            if('2'==$qosStatus['Type'] && count($custom)>0){
                $qosStatus['Custom'] = array();
                foreach($custom as $ip=>$bandwidth){
                    $qosStatus['Custom'][] = array('Ip'=>$ip, 'Down'=>$bandwidth['Down'], 'Up'=>$bandwidth['Up']);
                }

            }
        }

        return $qosStatus;
    }

    private static function initQos(){
        global $config;

        if(!is_array($config['OPNsense'])){
            $config['OPNsense']=array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper'])){
            $config['OPNsense']['TrafficShaper'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['pipes'])){
            $config['OPNsense']['TrafficShaper']['pipes'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['pipes']['pipe'])){
            $config['OPNsense']['TrafficShaper']['pipes']['pipe'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['queues'])){
            $config['OPNsense']['TrafficShaper']['queues'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['queues']['queue'])){
            $config['OPNsense']['TrafficShaper']['queues']['queue'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['rules'])){
            $config['OPNsense']['TrafficShaper']['rules'] = array();
        }
        if(!is_array($config['OPNsense']['TrafficShaper']['rules']['rule'])){
            $config['OPNsense']['TrafficShaper']['rules']['rule'] = array();
        }
        $pipe = array();
        $pipe['@attributes'] = Array('uuid' => '67973a6f-739a-415b-b0c3-48f52b078896');
        $pipe['number'] = '10000';
        $pipe['enabled'] = '0';
        $pipe['bandwidth'] = '1024';
        $pipe['bandwidthMetric'] = 'Kbit';
        $pipe['queue'] = '';
        $pipe['mask'] = 'none';
        $pipe['scheduler'] = '';
        $pipe['codel_enable'] = '0';
        $pipe['codel_target'] = '';
        $pipe['codel_interval'] = '';
        $pipe['codel_ecn_enable'] = '0';
        $pipe['fqcodel_quantum'] = '';
        $pipe['fqcodel_limit'] = '';
        $pipe['fqcodel_flows'] = '';
        $pipe['origin'] = 'TrafficShaper';
        $pipe['description'] = 'perip_up';
        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $pipe;

        $pipe = array();
        $pipe['@attributes'] = Array('uuid' => 'cd27ef99-bcff-4394-8c37-456f9a4915f4');
        $pipe['number'] = '10001';
        $pipe['enabled'] = '0';
        $pipe['bandwidth'] = '2048';
        $pipe['bandwidthMetric'] = 'Kbit';
        $pipe['queue'] = '';
        $pipe['mask'] = 'dst-ip';
        $pipe['scheduler'] = '';
        $pipe['codel_enable'] = '0';
        $pipe['codel_target'] = '';
        $pipe['codel_interval'] = '';
        $pipe['codel_ecn_enable'] = '0';
        $pipe['fqcodel_quantum'] = '';
        $pipe['fqcodel_limit'] = '';
        $pipe['fqcodel_flows'] = '';
        $pipe['origin'] = 'TrafficShaper';
        $pipe['description'] = 'perip_down';
        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $pipe;

        $pipe = array();
        $pipe['@attributes'] = Array('uuid' => '1ee8326b-30ff-4d25-ab00-42f25c619cb8');
        $pipe['number'] = '10002';
        $pipe['enabled'] = '0';
        $pipe['bandwidth'] = '10240';
        $pipe['bandwidthMetric'] = 'Kbit';
        $pipe['queue'] = '';
        $pipe['mask'] = 'none';
        $pipe['scheduler'] = 'fq_codel';
        $pipe['codel_enable'] = '0';
        $pipe['codel_target'] = '';
        $pipe['codel_interval'] = '';
        $pipe['codel_ecn_enable'] = '0';
        $pipe['fqcodel_quantum'] = '';
        $pipe['fqcodel_limit'] = '';
        $pipe['fqcodel_flows'] = '';
        $pipe['origin'] = 'TrafficShaper';
        $pipe['description'] = 'auto_down';
        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $pipe;

        $pipe = array();
        $pipe['@attributes'] = Array('uuid' => 'c49c322a-c70b-4091-9277-3fc5cde7da62');
        $pipe['number'] = '10003';
        $pipe['enabled'] = '0';
        $pipe['bandwidth'] = '5120';
        $pipe['bandwidthMetric'] = 'Kbit';
        $pipe['queue'] = '';
        $pipe['mask'] = 'none';
        $pipe['scheduler'] = 'fq_codel';
        $pipe['codel_enable'] = '1';
        $pipe['codel_target'] = '';
        $pipe['codel_interval'] = '';
        $pipe['codel_ecn_enable'] = '0';
        $pipe['fqcodel_quantum'] = '';
        $pipe['fqcodel_limit'] = '';
        $pipe['fqcodel_flows'] = '';
        $pipe['origin'] = 'TrafficShaper';
        $pipe['description'] = 'auto_up';
        $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $pipe;

        //初始队列
        $queue = array();
        $queue['@attributes'] = Array('uuid' => '0131ac0a-a933-43b0-875a-0e1211543702');
        $queue['number'] = '10002';
        $queue['enabled'] = '1';
        $queue['pipe'] = Array('c49c322a-c70b-4091-9277-3fc5cde7da62');
        $queue['weight'] = '10';
        $queue['mask'] = 'src-ip';
        $queue['codel_enable'] = '0';
        $queue['codel_target'] = '';
        $queue['codel_interval'] = '';
        $queue['codel_ecn_enable'] = '0';
        $queue['description'] = 'queue_auto_up';
        $queue['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['queues']['queue'][] = $queue;

        $queue = array();
        $queue['@attributes'] = Array('uuid' => 'aaa73761-ed10-4939-b99d-72124aa93653');
        $queue['number'] = '10003';
        $queue['enabled'] = '1';
        $queue['pipe'] = Array('1ee8326b-30ff-4d25-ab00-42f25c619cb8');
        $queue['weight'] = '10';
        $queue['mask'] = 'dst-ip';
        $queue['codel_enable'] = '0';
        $queue['codel_target'] = '';
        $queue['codel_interval'] = '';
        $queue['codel_ecn_enable'] = '0';
        $queue['description'] = 'queue_auto_down';
        $queue['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['queues']['queue'][] = $queue;

        //初始规则
        $config['OPNsense']['TrafficShaper']['rules']['rule'] = array();
        $rule = array();
        $rule['@attributes'] = Array('uuid' => 'c2f285cc-3730-434b-ada9-0a9106595437');
        $rule['sequence'] = '3000';
        $rule['interface'] = 'lan';
        $rule['interface2'] = '';
        $rule['proto'] = 'ip';
        $rule['source'] = 'any';
        $rule['source_not'] = '0';
        $rule['src_port'] = 'any';
        $rule['destination'] = 'any';
        $rule['destination_not'] = '0';
        $rule['dst_port'] = 'any';
        $rule['direction'] = 'in';
        $rule['target'] = '67973a6f-739a-415b-b0c3-48f52b078896';
        $rule['description'] = 'rule_perip_up';
        $rule['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $rule;

        $rule = array();
        $rule['@attributes'] = Array('uuid' => 'd3ffa548-463b-4ffd-8266-f621338a9fb3');
        $rule['sequence'] = '3001';
        $rule['interface'] = 'lan';
        $rule['interface2'] = '';
        $rule['proto'] = 'ip';
        $rule['source'] = 'any';
        $rule['source_not'] = '0';
        $rule['src_port'] = 'any';
        $rule['destination'] = 'any';
        $rule['destination_not'] = '0';
        $rule['dst_port'] = 'any';
        $rule['direction'] = 'out';
        $rule['target'] = 'cd27ef99-bcff-4394-8c37-456f9a4915f4';
        $rule['description'] = 'rule_perip_down';
        $rule['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $rule;

        $rule = array();
        $rule['@attributes'] = Array('uuid' => '35ade29d-138e-495a-96c1-6b574f6d5661');
        $rule['sequence'] = '3002';
        $rule['interface'] = 'lan';
        $rule['interface2'] = '';
        $rule['proto'] = 'ip';
        $rule['source'] = 'any';
        $rule['source_not'] = '0';
        $rule['src_port'] = 'any';
        $rule['destination'] = 'any';
        $rule['destination_not'] = '0';
        $rule['dst_port'] = 'any';
        $rule['direction'] = 'in';
        $rule['target'] = '0131ac0a-a933-43b0-875a-0e1211543702';
        $rule['description'] = 'rule_auto_up';
        $rule['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $rule;

        $rule = array();
        $rule['@attributes'] = Array('uuid' => 'b3cdd739-b4a5-49a6-96ff-9aa1fc247d63');
        $rule['sequence'] = '3003';
        $rule['interface'] = 'lan';
        $rule['interface2'] = '';
        $rule['proto'] = 'ip';
        $rule['source'] = 'any';
        $rule['source_not'] = '0';
        $rule['src_port'] = 'any';
        $rule['destination'] = 'any';
        $rule['destination_not'] = '0';
        $rule['dst_port'] = 'any';
        $rule['direction'] = 'out';
        $rule['target'] = 'aaa73761-ed10-4939-b99d-72124aa93653';
        $rule['description'] = 'rule_auto_down';
        $rule['origin'] = 'TrafficShaper';
        $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $rule;
    }

    public static function setQos($data){
        global $config;
        $result = 0;
        try{
            if(!isset($data['Type'])){
                throw new AppException('qos_enabled_error');
            }
            if('0' == $data['Type']){//把所有的管道,队列和规则清空
                $config['OPNsense']['TrafficShaper']['pipes'] = array('pipe'=>array());
                $config['OPNsense']['TrafficShaper']['queues'] = array('queue'=>array());
                $config['OPNsense']['TrafficShaper']['rules'] = array('rule'=>array());
            }else{
                if(!isset($config['OPNsense']['TrafficShaper']['pipes']['pipe']) ||
                    !isset($config['OPNsense']['TrafficShaper']['queues']['queue']) ||
                    !isset($config['OPNsense']['TrafficShaper']['rules']['rule'])){
                    self::initQos();
                }
                $down = intval($data['Down']);
                $up = intval($data['Up']);
                if($down<1 || $up <1){
                    throw new AppException('bandwidth_error');
                }
                if('1'==$data['Type']){
                    foreach($config['OPNsense']['TrafficShaper']['rules']['rule'] as $idx=>$rule) {
                        if(0===strpos($rule['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['rules']['rule'][$idx]);
                        }
                    }
                    foreach($config['OPNsense']['TrafficShaper']['queues']['queue'] as $idx=>$queue) {
                        if(0===strpos($queue['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['queues']['queue'][$idx]);
                        }
                    }
                    foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {
                        if('auto_up' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $up;
                        }else if('auto_down' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $down;
                        }else if(0===strpos($pipe['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]);
                        }else{
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }
                    }
                }else if('2'==$data['Type']){
                    foreach($config['OPNsense']['TrafficShaper']['rules']['rule'] as $idx=>$rule) {
                        if(0===strpos($rule['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['rules']['rule'][$idx]);
                        }
                    }
                    foreach($config['OPNsense']['TrafficShaper']['queues']['queue'] as $idx=>$queue) {
                        if(0===strpos($queue['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['queues']['queue'][$idx]);
                        }
                    }
                    foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {
                        if('perip_up' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $up;
                        }else if('perip_down' == $pipe['description']) {
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $down;
                        }else if(0===strpos($pipe['description'], 'perip_up_')) {
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                        }else if(0===strpos($pipe['description'], 'perip_down_')){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                        }else if(0===strpos($pipe['description'], 'advanced_')){
                            unset($config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]);
                        }else{
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }
                    }
                }else if('3' == $data['Type']){
                    foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {
                        if('perip_up' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }else if('perip_down' == $pipe['description']) {
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }else if(0===strpos($pipe['description'], 'perip_up_')) {
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }else if(0===strpos($pipe['description'], 'perip_down_')){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }else if('auto_up' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }else if('auto_down' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }
                    }
                }
            }
            write_config();
            $backend = new Backend();
            $backend->configdRun('template reload OPNsense/IPFW');
            $bckresult = trim($backend->configdRun("ipfw reload"));
            if ($bckresult != "OK") {
                $result = array('msg'=>"error reloading shaper (".$bckresult.")");
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function addQosCustom($data){
        global $config;
        $result = 0;
        try{
            if(!isset($data['Ip']) || !is_ipaddr($data['Ip'])){
                throw new AppException('ip_error');
            }
            $up = intval($data['Up']);
            $down = intval($data['Down']);

            $up_descr = 'perip_up_'.$data['Ip'];
            $down_descr = 'perip_down_'.$data['Ip'];
            $type = '0';
            foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {//检查是否已存在
                if($pipe['description'] == $down_descr){
                    throw new AppException('ip_exist');
                }elseif ($pipe['description'] == $up_descr){
                    throw new AppException('ip_exist');
                }
                if(('auto_up'==$pipe['description'] || 'auto_down'==$pipe['description'])&& '1'==$pipe['enabled']){
                    $type='1';
                }else if(('perip_up'==$pipe['description'] || 'perip_down'==$pipe['description'])&& '1'==$pipe['enabled']){
                    $type='2';
                }
            }
            if($up>0){//限制上行带宽
                $up_pipe = array();
                if('2' == $type){
                    $up_pipe['enabled'] = '1';
                }else{
                    $up_pipe['enabled'] = '0';
                }
                $up_pipe['@attributes'] = Array('uuid' => sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                ));
                $newid = self::generateNewId(10000, $config['OPNsense']['TrafficShaper']['pipes']['pipe']);
                $up_pipe['number'] = $newid;
                $up_pipe['bandwidth'] = $up;
                $up_pipe['bandwidthMetric'] = 'Kbit';
                $up_pipe['queue'] = '';
                $up_pipe['mask'] = 'src-ip';
                $up_pipe['scheduler'] = '';
                $up_pipe['codel_enable'] = '0';
                $up_pipe['codel_target'] = '';
                $up_pipe['codel_interval'] = '';
                $up_pipe['codel_ecn_enable'] = '0';
                $up_pipe['fqcodel_quantum'] = '';
                $up_pipe['fqcodel_limit'] = '';
                $up_pipe['fqcodel_flows'] = '';
                $up_pipe['description'] = $up_descr;
                $up_pipe['origin'] = 'TrafficShaper';
                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $up_pipe;

                $up_rule = array();
                $up_rule['sequence'] = '1';
                $up_rule['interface'] = 'lan';
                $up_rule['interface2'] = '';
                $up_rule['proto'] = 'ip';
                $up_rule['source'] = $data['Ip'];
                $up_rule['source_not'] = '0';
                $up_rule['src_port'] = 'any';
                $up_rule['destination'] = 'any';
                $up_rule['destination_not'] = '0';
                $up_rule['dst_port'] = 'any';
                $up_rule['direction'] = 'in';
                $up_rule['target'] = $up_pipe['@attributes']['uuid'];
                $up_rule['description'] = 'rule_'.$up_descr;
                $up_rule['origin'] = "TrafficShaper";
                $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $up_rule;
            }
            if($down>0){//限制下行带宽
                $down_pipe = array();
                if('2' == $type){
                    $down_pipe['enabled'] = '1';
                }else{
                    $down_pipe['enabled'] = '0';
                }
                $down_pipe['@attributes'] = Array('uuid' => sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                ));
                $newid = self::generateNewId(10000, $config['OPNsense']['TrafficShaper']['pipes']['pipe']);
                $down_pipe['number'] = $newid;
                $down_pipe['bandwidth'] = $down;
                $down_pipe['bandwidthMetric'] = 'Kbit';
                $down_pipe['queue'] = '';
                $down_pipe['mask'] = 'dst-ip';
                $down_pipe['scheduler'] = '';
                $down_pipe['codel_enable'] = '0';
                $down_pipe['codel_target'] = '';
                $down_pipe['codel_interval'] = '';
                $down_pipe['codel_ecn_enable'] = '0';
                $down_pipe['fqcodel_quantum'] = '';
                $down_pipe['fqcodel_limit'] = '';
                $down_pipe['fqcodel_flows'] = '';
                $down_pipe['description'] = $down_descr;
                $up_pipe['origin'] = 'TrafficShaper';
                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][] = $down_pipe;

                $down_rule = array();
                $down_rule['sequence'] = '1';
                $down_rule['interface'] = 'lan';
                $down_rule['interface2'] = '';
                $down_rule['proto'] = 'ip';
                $down_rule['source'] = 'any';
                $down_rule['source_not'] = '0';
                $down_rule['src_port'] = 'any';
                $down_rule['destination'] = $data['Ip'];
                $down_rule['destination_not'] = '0';
                $down_rule['dst_port'] = 'any';
                $down_rule['direction'] = 'out';
                $down_rule['target'] = $down_pipe['@attributes']['uuid'];
                $down_rule['description'] = 'rule_'.$down_descr;
                $down_rule['origin'] = "TrafficShaper";
                $config['OPNsense']['TrafficShaper']['rules']['rule'][] = $down_rule;
            }
            write_config();

            $backend = new Backend();
            $backend->configdRun('template reload OPNsense/IPFW');
            $bckresult = trim($backend->configdRun("ipfw reload"));
            if ($bckresult != "OK") {
                $result = array('msg'=>"error reloading shaper (".$bckresult.")");
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delQosCustom($data)
    {
        global $config;
        $result = 0;
        try {
            if(!isset($data['Ip']) || !is_ipaddr($data['Ip'])){
                throw new AppException('ip_error');
            }
            $pipe_up_descr = 'perip_up_'.$data['Ip'];
            $pipe_down_descr = 'perip_down_'.$data['Ip'];
            $rule_up_descr = 'rule_'.$pipe_up_descr;
            $rule_down_descr = 'rule_'.$pipe_down_descr;
            foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {//检查是否已存在
                if($pipe['description'] == $pipe_up_descr){
                    unset($config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]);
                }else if ($pipe['description'] == $pipe_down_descr){
                    unset($config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]);
                }
            }
            foreach($config['OPNsense']['TrafficShaper']['rules']['rule'] as $idx=>$rule) {//检查是否已存在
                if($rule['description'] == $rule_up_descr){
                    unset($config['OPNsense']['TrafficShaper']['rules']['rule'][$idx]);
                }else if ($rule['description'] == $rule_down_descr){
                    unset($config['OPNsense']['TrafficShaper']['rules']['rule'][$idx]);
                }
            }

            write_config();
            $backend = new Backend();
            $backend->configdRun('template reload OPNsense/IPFW');
            $bckresult = trim($backend->configdRun("ipfw reload"));
            if ($bckresult != "OK") {
                $result = array('msg'=>"error reloading shaper (".$bckresult.")");
            }
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = 100;
        }

        return $result;
    }



    public static function getAdvCfg(){
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
                    if(0!==strpos($va['description'], 'advanced_')){
                        continue;
                    }
                    foreach ($va as $k=>$v){
                        if('@attributes' == $k){
                            $va['uuid'] = $va[$k]['uuid'];
                            unset($va[$k]);
                            $description = substr($va['description'],strlen("advanced_pipe_"));
                            $va['description'] = $description;
                            array_push($pipes,$va);
                            $ptmp = array("descr"=>$va['description'],"uuid"=>$va['uuid']);
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
                    if(0!==strpos($va['description'], 'advanced_')){
                        continue;
                    }
                    foreach ($va as $k=>$v){
                        if('@attributes' == $k){
                            $va['uuid'] = $va[$k]['uuid'];
                            $description = substr($va['description'],strlen("advanced_queue_"));
                            $va['description'] = $description;
                            unset($va[$k]);
                            array_push($queues,$va);
                            $qtmp = array("descr"=>$va['description'],"uuid"=>$va['uuid']);
                            array_push($descr['qdescr'],$qtmp);
                        }
                    }
                }
            }
            if('rules' == $key){
                if(!isset($val['rule'])){
                    continue;
                }
                foreach ($val['rule'] as $ke=>$va) {
                    if (0 !== strpos($va['description'], 'advanced_')) {
                        continue;
                    }
                    foreach ($va as $k => $v) {
                        if ('@attributes' == $k) {
                            $va['uuid'] = $va[$k]['uuid'];
                            $description = substr($va['description'],strlen("advanced_rule_"));
                            $va['description'] = $description;
                            unset($va[$k]);
                            array_push($rules, $va);
                            $rtmp = array("descr" => $va['description'], "uuid" => $va['uuid']);
                            array_push($descr['rdescr'], $rtmp);
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
                throw new AppException("param_error");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['pipes'];
            if(!isset($pipe['pipe']) || '' == $pipe['pipe']){
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("param_error");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['queues'];
            if(!isset($pipe['queue']) || '' == $pipe['queue']){
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("param_error");  //参数不正确
            }
            $pipe = $config['OPNsense']['TrafficShaper']['rules'];
            if(!isset($pipe['rule']) || '' == $pipe['rule']){
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("user_no_exist");  //用户不存在
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
                throw new AppException("enabled_param_error");  //开启参数不正确
            }
            if(!isset($data['bandwidth']) || !is_numeric($data['bandwidth'])){
                throw new AppException("bandwidth_param_error");  //带宽参数不正确
            }

            if(!isset($data['bandwidthMetric']) || !in_array($data['bandwidthMetric'],self::BWMetric)){
                throw new AppException("bandwidth_unit_error");  //带宽单位参数不正确
            }

            if(!isset($data['queue']) || ('' != $data['queue'] && !is_numeric($data['queue'])) ){
                throw new AppException("queue_error");  //队列参数不正确
            }
            if('' != $data['queue']){
                $queueCnt = intval($data['queue']);
                if($queueCnt<2 || $queueCnt>100){
                    throw new AppException("queue_range_2_100");  //队列参数不正确，范围为2~100
                }
            }

            if(!isset($data['mask']) || !in_array($data['mask'],self::Mask)){
                throw new AppException("mask_error");  //掩码参数不正确
            }
            if(!isset($data['scheduler']) || !in_array($data['scheduler'],self::Scheduler)){
                throw new AppException("sched_type_error");  //调度程序类型参数不正确
            }

            if(!isset($data['codel_enable']) || ('' != $data['codel_enable'] && !is_numeric($data['codel_enable'])) ){
                throw new AppException("fqcodel_enabled_error");  //启用CoDel参数不正确
            }

            if(!isset($data['codel_target']) || ('' != $data['codel_target'] && !is_numeric($data['codel_target'])) ){
                throw new AppException("fqcodel_target_error");  //(FQ-)CoDel目标参数不正确
            }
            if('' != $data['codel_target']){
                $codel_traget = intval($data['codel_target']);
                if($codel_traget <1 || $codel_traget > 10000){
                    throw new AppException("fqcodel_target_error");  //(FQ-)CoDel目标参数不正确
                }
            }

            if(!isset($data['codel_interval']) || ('' != $data['codel_interval'] && !is_numeric($data['codel_interval'])) ){
                throw new AppException("fqcodel_target_error");  //(FQ-)CoDel目标参数不正确
            }
            if('' != $data['codel_interval']){
                $codel_interval = intval($data['codel_interval']);
                if($codel_interval <1 || $codel_interval > 10000){
                    throw new AppException("fqcodel_interval_error");  //(FQ-)CoDel间隔参数不正确
                }
            }

            if(!isset($data['codel_ecn_enable']) || ('' != $data['codel_ecn_enable'] && !is_numeric($data['codel_ecn_enable'])) ){
                throw new AppException("fqcodel_ecn_error");  //FQ-)CoDel ECN参数不正确
            }

            if(!isset($data['fqcodel_quantum']) || ('' != $data['fqcodel_quantum'] && !is_numeric($data['fqcodel_quantum'])) ){
                throw new AppException("fqcodel_quantity_error");  //FQ-CoDel量参数不正确
            }
            if('' != $data['fqcodel_quantum']){
                $fqcodel_quantum = intval($data['fqcodel_quantum']);
                if($fqcodel_quantum <1 || $fqcodel_quantum > 65535){
                    throw new AppException("fqcodel_quantity_error");  //FQ-CoDel量参数不正确
                }
            }

            if(!isset($data['fqcodel_limit']) || ('' != $data['fqcodel_limit'] && !is_numeric($data['fqcodel_limit'])) ){
                throw new AppException("fqcodel_limit_error");  //FQ-CoDel限制参数不正确
            }
            if('' != $data['fqcodel_limit']){
                $fqcodel_limit = intval($data['fqcodel_limit']);
                if($fqcodel_limit <1 || $fqcodel_limit > 65535){
                    throw new AppException("fqcodel_limit_error");  //FQ-CoDel限制参数不正确
                }
            }

            if(!isset($data['fqcodel_flows']) || ('' != $data['fqcodel_flows'] && !is_numeric($data['fqcodel_flows'])) ){
                throw new AppException("fqcodel_flows_error");  //FQ-CoDel限制参数不正确
            }
            if('' != $data['fqcodel_flows']){
                $fqcodel_flows = intval($data['fqcodel_flows']);
                if($fqcodel_flows <1 || $fqcodel_flows > 65535){
                    throw new AppException("fqcodel_flows_error");  //FQ-CoDel流参数不正确
                }
            }

            if(!isset($data['description']) && '' == trim($data['description'])){
                throw new AppException("descr_error");  //描述参数不正确
            }

            $description = 'advanced_pipe_'.$data['description'];
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
                "description" => $description
            );

            $pipe = $config['OPNsense']['TrafficShaper']['pipes'];
            if(isset($data['uuid']) && '' != $data['uuid'] ){   //编辑
                $editFlag = false;
                if(!isset($pipe['pipe'])){
                    throw new AppException("pipe_no_exist");  //管道不存在
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
                                $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$key]['description'] = $description;
                                $editFlag = true;
                                break;
                            }
                        }
                    }
                }
                if(!$editFlag){
                    throw new AppException("pipe_no_exist");  //管道不存在
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
                throw new AppException("enabled_param_error");      //开启参数不正确
            }
            if(!isset($data['pipe'])){
                throw new AppException("pipe_param_error");      //管道参数不正确
            }
            if(!isset($data['weight']) && !is_numeric($data['weight'])){
                throw new AppException("weight_param_error");      //权重参数不正确
            }
            $weight = intval($data['weight']);
            if($weight < 1 || $weight > 100){
                throw new AppException("weight_param_error");      //权重参数不正确
            }
            if(!isset($data['mask']) && !in_array($data['mask'],self::Mask)){
                throw new AppException("mask_param_error");      //掩码参数不正确
            }
            if(!isset($data['codel_enable']) && !is_numeric($data['codel_enable'])){
                throw new AppException("enabled_codel_error");      //启用CoDel参数不正确
            }
            if(!isset($data['codel_target']) || ('' != $data['codel_target'] && !is_numeric($data['codel_target'])) ){
                throw new AppException("fqcodel_target_error");      //(FQ-)CoDel目标参数不正确
            }
            if(!isset($data['codel_interval']) || ('' != $data['codel_interval'] && !is_numeric($data['codel_interval']))){
                throw new AppException("fqcodel_interval_error");      // (FQ-)CoDel间隔参数不正确
            }
            if(!isset($data['codel_ecn_enable']) && !is_numeric($data['codel_ecn_enable'])){
                throw new AppException("fqcodel_ecn_error");      // (FQ-)CoDel ECN参数不正确
            }
            if(!isset($data['description'])){
                throw new AppException("descr_error");      // 描述参数不正确
            }

            $pipes = $config['OPNsense']['TrafficShaper']['pipes'];
            if(!isset($pipes['pipe']) || '' == $pipes['pipe']){
                throw new AppException("pipe_no_exist");      //管道不存在
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
                throw new AppException("pipe_param_error");      //管道参数不正确
            }

            $description = 'advanced_queue_'.$data['description'];
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
                "description"=> $description,
                "origin"=>"TrafficShaper"
            );

            $queues = $config['OPNsense']['TrafficShaper']['queues'];
            if(isset($data['uuid']) && '' != trim($data['uuid'])){  //编辑
                $editFlag = false;
                if(!isset($queues['queue'])){
                    throw new AppException("queue_no_exist");  //队列不存在
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
                                    $config['OPNsense']['TrafficShaper']['queues']['queue'][$key]['description'] = $description;
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
                                $config['OPNsense']['TrafficShaper']['queues']['queue']['description'] = $description;
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
                throw new AppException("sequence_error");  //序列不正确
            }
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('interface_param_error');      //接口参数不正确
            }
            if(!isset($data['interface2']) || ('' != $data['interface2'] && !isset($config['interfaces'][$data['interface2']]) )){
                throw new AppException('interface2_param_error');  //接口2参数不正确
            }
            if(!isset($data['proto']) && !in_array($data['proto'],self::Proto)){
                throw new AppException('protocol_param_error');  //协议参数不正确
            }
            if(!isset($data['source'])){
                throw new AppException('source_error');  //源参数不正确
            }
            if('' == $data['source']){
                $data['source'] = 'any';
            }
            if('' != $data['source']){
                if('any' != $data['source'] ){
                    if( !is_ipaddr($data['source'])){
                        $source = explode("/",$data['source']);
                        if(!is_ipaddr($source[0]) || !is_numeric($source[1])){
                            throw new AppException("source_error");   //源参数不正确
                        }
                    }
                }
            }
            if(!isset($data['source_not']) && !is_numeric($data['source_not'])){
                throw new AppException("invert_source_error");   //源参数不正确
            }

            if(!isset($data['src_port'])){
                throw new AppException("source_port_error");   //源端口参数不正确
            }
            if('' == $data['src_port']){
                $data['src_port'] = 'any';
            }
            if(is_numeric($data['src_port'])){
                $srcPort = intval($data['src_port']);
                if($srcPort<1 || $srcPort>65535){
                    throw new AppException("source_port_error");   //源端口参数不正确
                }
            }else{
                if(!in_array($data['src_port'],self::SrcAnDstPort)){
                    throw new AppException("source_port_error");   //源端口参数不正确
                }
            }

            if(!isset($data['destination'])){
                throw new AppException('dest_param_error');  //目的地参数不正确
            }
            if('' == $data['destination']){
                $data['destination'] = 'any';
            }
            if('' != $data['destination']){
                if('any' != $data['destination'] ){
                    if( !is_ipaddr($data['destination'])){
                        $destination = explode("/",$data['destination']);
                        if(!is_ipaddr($destination[0]) || !is_numeric($destination[1])){
                            throw new AppException("dest_param_error");   //目的地参数不正确
                        }
                    }
                }
            }

            if(!isset($data['destination_not']) && !is_numeric($data['destination_not'])){
                throw new AppException("invert_dest_error");   //反转目的地参数不正确
            }

            if(!isset($data['dst_port'])){
                throw new AppException("dest_port_error");   //目的端口参数不正确
            }
            if('' == $data['dst_port']){
                $data['dst_port'] = 'any';
            }
            if(is_numeric($data['dst_port'])){
                $dstPort = intval($data['dst_port']);
                if($dstPort<1 || $dstPort>65535){
                    throw new AppException("dest_port_error");   //源端口参数不正确
                }
            }else{
                if(!in_array($data['dst_port'],self::SrcAnDstPort)){
                    throw new AppException("dest_port_error");   //源端口参数不正确
                }
            }
            if(!isset($data['direction']) || !in_array($data['direction'],self::Direction)){
                throw new AppException("dire_error");   //方向参数不正确
            }

            if(!isset($data['target'])){
                throw new AppException("target_param_error");   //目标参数不正确
            }

            $traffic = $config['OPNsense']['TrafficShaper'];
            if(!is_array($traffic) || ('' == $traffic['pipes'] && '' == $traffic['queues'])){
                throw new AppException("target_error");      //目标参数不存在
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
                throw new AppException("target_no_exist");      //目标参数不存在
            }
            if(!isset($data['description']) || '' == $data['description']){
                throw new AppException("descr_error");      //描述参数不存在
            }

            $description = 'advanced_rule_'.$data['description'];
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
                "description"=> $description,
                "origin"=>"TrafficShaper"
            );
            $rules = $config['OPNsense']['TrafficShaper']['rules'];
            if(isset($data['uuid']) && '' != $data['uuid']){    //编辑
                $editFlag = false;
                if(!isset($rules['rule'])){
                    throw new AppException("rule_no_exist");  //规则不存在
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
                                $config['OPNsense']['TrafficShaper']['rules']['rule'][$key]['description'] = $description;
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
                    throw new AppException("rule_no_exist");  //规则不存在
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