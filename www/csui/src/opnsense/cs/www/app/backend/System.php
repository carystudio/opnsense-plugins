<?php
require_once('rrd.inc');
require_once('filter.inc');
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once("util.inc");
require_once("plugins.inc.d/ntpd.inc");

use \OPNsense\Core\Backend;

class System extends Csbackend
{
    protected static $ERRORCODE = array(
        'System_100'=>'时间不正确',
        'System_101'=>'请选择一个时区！',
        'System_102'=>'请输入NTP服务器',
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
        $pfctl_counters = json_decode(configd_run("filter list counters json"), true);
        $statusInfo['packages'] = $pfctl_counters;
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
        global $config;

        $a_ntpd = &config_read_array('ntpd');

        $pconfig = array();
        $pconfig['timeservers_host'] = array();
        $pconfig['timeservers_prefer'] = array();

        if (!empty($config['system']['timeservers'])) {
            $pconfig['timeservers_prefer'] = !empty($a_ntpd['prefer']) ? explode(' ', $a_ntpd['prefer']) : array();
            $pconfig['timeservers_host'] = explode(' ', $config['system']['timeservers']);
        }

        $pconfig['timezone'] = $config['system']['timezone'];
        return array('Time'=>date("YmdHis"),'config'=>$pconfig);
    }

    public static function setGwTime($data){
        global $config;
        $a_ntpd = &config_read_array('ntpd');
        $result = 0;

        try{
            if(!is_numeric($data['Time']) || strlen($data['Time'])!=12|| '2'!=$data['Time'][0]){
                throw new AppException('System_100');
            }
            if(!$data['timezone']){
                throw new AppException('System_101');
            }
            if(!$data['ntpServer']){
                throw new AppException('System_102');
            }
            $ntpServer = str_replace('*',' ',$data['ntpServer']);
            $config['system']['timeservers'] = trim($ntpServer);

            if (empty($config['system']['timeservers'])) {
                unset($config['system']['timeservers']);
            }

            $config['system']['timezone'] = $data['timezone'];

            if('1' == $data['ntpEnabled']){
                $a_ntpd['prefer'] = !empty($ntpServer) ? trim($ntpServer) : null;

                if (empty($a_ntpd['prefer'])) {
                    unset($a_ntpd['prefer']);
                }




                write_config();
                /* time zone change first */
                system_timezone_configure();

                write_config("Updated NTP Server Settings");
                ntpd_configure_start();

            }else{

                if (!empty($a_ntpd['prefer'])) {
                    unset($a_ntpd['prefer']);
                }
                write_config();
                /* time zone change first */
                system_timezone_configure();
                ntpd_configure_start();
                mwexec("/bin/date ".$data['Time']);
            }

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

        $filename="Config-CSG2000P-".date("Ymd")."_config.dat"; //文件名
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

    private static function initRebootSched(){
        global $config;

        if(!isset($config['OPNsense']['cron']) || !is_array($config['OPNsense']['cron'])){
            $config['OPNsense']['cron']= array();
        }
        if(!isset($config['OPNsense']['cron']['jobs']) || !is_array($config['OPNsense']['cron']['jobs'])){
            $config['OPNsense']['cron']['jobs'] = array();
        }
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
            self::initRebootSched();
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

    public static function loadDefSettings(){
        reset_factory_defaults(false);

        return 0;
    }
}