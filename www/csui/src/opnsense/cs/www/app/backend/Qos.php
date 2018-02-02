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
            foreach($pipes as $pipe){
                if('1'==$pipe['enabled']){
                    if('perip_up'==$pipe['description'] || 'perip_down'==$pipe['description']){
                        $qosStatus['Type'] = '2';
                    }else if('auto_up'==$pipe['description'] || 'auto_down'==$pipe['description']){
                        $qosStatus['Type'] = '1';
                    }
                    if('0' != $qosStatus['Type']){
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

        $config['OPNsense']['TrafficShaper']['pipes']['pipe'] = array();
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
                throw new AppException('Qos_100');
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
                    throw new AppException('Qos_101');
                }
                if('1'==$data['Type']){
                    foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {
                        if('auto_up' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $up;
                        }else if('auto_down' == $pipe['description']){
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '1';
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['bandwidth'] = $down;
                        }else{
                            $config['OPNsense']['TrafficShaper']['pipes']['pipe'][$idx]['enabled'] = '0';
                        }

                    }
                }else if('2'==$data['Type']){
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
                        }else{
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
                throw new AppException('Qos_200');
            }
            $up = intval($data['Up']);
            $down = intval($data['Down']);

            $up_descr = 'perip_up_'.$data['Ip'];
            $down_descr = 'perip_down_'.$data['Ip'];
            $type = '0';
            foreach($config['OPNsense']['TrafficShaper']['pipes']['pipe'] as $idx=>$pipe) {//检查是否已存在
                if($pipe['description'] == $down_descr){
                    throw new AppException('Qos_201');
                }elseif ($pipe['description'] == $up_descr){
                    throw new AppException('Qos_201');
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
                throw new AppException('Qos_200');
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

}
