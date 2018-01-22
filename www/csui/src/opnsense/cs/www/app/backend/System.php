<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("ipsec.inc");
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once("util.inc");

use \OPNsense\Core\Backend;

class System extends Csbackend
{
    protected static $ERRORCODE = array(
        'System_100'=>'时间不正确',
        'System_200'=>'分不正确',
        'System_201'=>'时不正确',
        'System_202'=>'日不正确',
        'System_203'=>'月不正确',
        'System_204'=>'周不正确'
    );

    public static function getStatusInfo(){
        global $config;
        global $csg2000p_config;

        $statusInfo = array();

        //$statusInfo['wan']              = Network::getWanInfo();
        //$statusInfo['lan']              = Network::getLanInfo();


        // system info
        $statusInfo['system'] = array();
        preg_match("/sec = (\d+)/", get_single_sysctl("kern.boottime"), $matches);
        $statusInfo['system']['Uptime']          = time() - $matches[1];
        $statusInfo['system']['FirmwareVersion'] = $csg2000p_config->csg2000p->version;
        $statusInfo['system']['ReleaseDate']     = $csg2000p_config->csg2000p->build_date;
        $statusInfo['niclist'] = get_interface_list();
        $statusInfo['interface']        = array();
        foreach($config['interfaces'] as $inf=>$infinfo){
            $statusInfo['interface'][$infinfo['descr']]   = Network::getInfInfo($infinfo['descr']);
            if('static'!=$statusInfo['interface'][$infinfo['descr']]['Protocol']){
                if(file_exists('/var/etc/nameserver_'.$statusInfo['interface'][$infinfo['descr']]['Nic'])){
                    $dnsserver = file_get_contents('/var/etc/nameserver_'.$statusInfo['interface'][$infinfo['descr']]['Nic']);
                    $dnsserver = str_replace("\n", ' ', trim($dnsserver));
                    $statusInfo['interface'][$infinfo['descr']]['Dns'] = $dnsserver;
                }
            }
        }
        
        return $statusInfo;
    }


    public static function getGwTime(){
        return array('Time'=>date("YmdHis"));
    }

    public static function setGwTime($data){
        $result = 0;

        try{
            if(!is_numeric($data['Time']) || strlen($data['Time'])!=12|| '2'!=$data['Time'][0]){
                throw new AppException('System_100');
            }
            mwexec("/bin/date ".$data['Time']);

        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = '100';
        }

        return $result;
    }

    public static function backupConfig(){
        global $config;

        $conf = serialize($config);
        $data = Mcrypt::encrypt($conf);

        $filename="CSG2000P_".date("YmdHis")."_config.dat"; //文件名
        Header( "Content-type:  application/octet-stream ");
        Header( "Accept-Ranges:  bytes ");
        Header( "Accept-Length: " .strlen($data));
        header( "Content-Disposition:  attachment;  filename= {$filename}");
        echo $data;
        exit(0);

    }

    public static function restoreConfig($request){
        global $config;

        foreach ($request->getUploadedFiles() as $file) {
            $filecontent = file_get_contents($file->getTempName());
            $data = unserialize(Mcrypt::decrypt($filecontent));
            if(is_array($data) && isset($data['version']) && isset($data['system'])){
                $config = $data;
                write_config();
                echo "Success";
                mwexec('/sbin/shutdown -r +3sec');
            }

        }

        exit(0);
    }

    public static function getRebootSched(){
        global $config;

        $rebootSched = array("Enable"=>'0');
        if(isset($config['OPNsense']['cron']['jobs']) && is_array($config['OPNsense']['cron']['jobs'])){
            foreach ($config['OPNsense']['cron']['jobs'] as $job){
                if('Reboot_Schedule' == $job['description']){
                    if(isset($job['minutes']) && '*'!= $job['minutes']){
                        $rebootSched['TimeMin'] = $job['minutes'];
                    }
                    if(isset($job['hours']) && '*'!= $job['hours']){
                        $rebootSched['TimeHour'] = $job['hours'];
                    }
                    if(isset($job['months']) && '*'!= $job['months']){
                        $rebootSched['TimeMonth'] = $job['months'];
                    }
                    if(isset($job['days']) && '*'!= $job['days']){
                        $rebootSched['TimeDate'] = $job['days'];
                    }
                    if(isset($job['weekdays']) && '*'!= $job['weekdays']){
                        $rebootSched['TimeWeek'] = $job['weekdays'];
                    }
                    if(isset($job['enabled'])){
                        $rebootSched['Enable'] = $job['enabled'];
                    }
                    break ;
                }
            }
        }

        return $rebootSched;
    }

    public static function setRebootSched($data){
        global $config;

        $result = 0;
        try{
            $enable = false;
            if(isset($data['Enable']) && '1'==$data['Enable']){
                $enable = true;
                $min = isset($data['TimeMin'])&&strlen($data['TimeMin'])>0?intval($data['TimeMin']):'*';
                $hour = isset($data['TimeHour'])&&strlen($data['TimeHour'])>0?intval($data['TimeHour']):'*';
                $date = isset($data['TimeDate'])&&strlen($data['TimeDate'])>0?intval($data['TimeDate']):'*';
                $month = isset($data['TimeMonth'])&&strlen($data['TimeMonth'])>0?intval($data['TimeMonth']):'*';
                $week = isset($data['TimeWeek'])&&strlen($data['TimeWeek'])>0?intval($data['TimeWeek']):'*';
                if('*'!=$min && ($min<0 || $min>59)){
                    throw new AppException('System_200');
                }
                if('*'!=$hour && ($hour<0 || $hour>23)){
                    throw new AppException('System_201');
                }
                if('*'!=$date && ($date<1 || $date>31)){
                    throw new AppException('System_202');
                }
                if('*'!=$month && ($month<1 || $month>12)){
                    throw new AppException('System_203');
                }
                if('*'!=$week && ($week<0 || $week>6)){
                    throw new AppException('System_204');
                }
                $newjob = array();
                $newjob['@attributes'] = Array('uuid'=> 'ebc6e274-6f04-4e1a-a39b-c6fdb6799c04');
                $newjob['origin'] = 'cron';
                $newjob['enabled'] = '1';
                $newjob['minutes'] = $min;
                $newjob['hours'] = $hour;
                $newjob['days'] = $date;
                $newjob['months'] = $month;
                $newjob['weekdays'] = $week;
                $newjob['who'] = 'root';
                $newjob['command'] = 'firmware reboot';
                $newjob['parameters'] = '';
                $newjob['description'] = 'Reboot_Schedule';
            }

            if(isset($config['OPNsense']['cron']['jobs']) && is_array($config['OPNsense']['cron']['jobs'])){
                foreach ($config['OPNsense']['cron']['jobs'] as $idx=>$job){
                    if('Reboot_Schedule' == $job['description']){
                        unset($config['OPNsense']['cron']['jobs'][$idx]);
                    }else if('ebc6e274-6f04-4e1a-a39b-c6fdb6799c04'==$job['@attributes']['uuid']){
                        unset($config['OPNsense']['cron']['jobs'][$idx]);
                    }
                }
            }else{
                $config['OPNsense']['cron']['jobs']['job'] = array();
            }


            if($enable){
                $config['OPNsense']['cron']['jobs']['job'] = $newjob;
            }
            write_config();
            $backend = new Backend();

            // generate template
            $backend->configdRun('template reload OPNsense/Cron');

            // (res)start daemon
            $backend->configdRun("cron restart");
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function reboot(){
        system_reboot();

        return 0;
    }

    public static function getArpInfo(){
        global $config;
        exec("/usr/sbin/arp -n -a",$arp);
        $arpinfo = array();
        $infos = array();
        foreach ($arp as $k=>$v){
            if(0 === strpos($v,'?')){
                $arpinfo[] = $v;
            }
        }
        $interfaces = array();
        foreach($config['interfaces'] as $interface){
            if(strpos($interface['descr'],'lan')===0){
                $interfaces[] = $interface['if'];
            }
        }
        if(count($arpinfo)>0){
            foreach ($arpinfo as $val){
                $line = explode(" ", $val);
                $a_arp = array();
                $a_arp["ip"] = ltrim( rtrim($line[1],')' ) , '(' );
                $a_arp["mac"] = $line[3];
                if(in_array($line[5], $interfaces)){
                    $infos[] = $a_arp;
                }
            }
        }

        return $infos;
    }
}