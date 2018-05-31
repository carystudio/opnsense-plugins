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
        'System_204'=>'周不正确',

        'System_300'=>'请选择需要上传的配置文件'
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
        $monitorStatus = self::getMonitoringStatus();
        foreach($config['interfaces'] as $inf=>$infinfo){

            $statusInfo['interface'][$infinfo['descr']]   = Network::getInfInfo($infinfo['descr']);
            if('static'!=$statusInfo['interface'][$infinfo['descr']]['Protocol']){
                if(file_exists('/var/etc/nameserver_'.$statusInfo['interface'][$infinfo['descr']]['Nic'])){
                    $dnsserver = file_get_contents('/var/etc/nameserver_'.$statusInfo['interface'][$infinfo['descr']]['Nic']);
                    $dnsserver = str_replace("\n", ' ', trim($dnsserver));
                    $statusInfo['interface'][$infinfo['descr']]['Dns'] = $dnsserver;
                }
                $monStatus = "none";
                $monLoss = "0.0 %";
                if(isset($monitorStatus[$infinfo['if']]['status'])){
                    $monStatus = $monitorStatus[$infinfo['if']]['status'];
                    $monLoss = $monitorStatus[$infinfo['if']]['loss'];
                }

                $statusInfo['interface'][$infinfo['descr']]['MonitorStatus'] = $monStatus;
                $statusInfo['interface'][$infinfo['descr']]['MonitorLoss'] = $monLoss;
            }
        }

        $cpu = self::system_api_cpu_stats();
        $kernel = self::system_api_kernel();
        $sysTime = self::getGwTime();

        $used = $kernel['memory']['used'];
        $total = $kernel['memory']['total'];
        $memory = floor(100 * ($used/$total)) ."% ( ". floor($used/1024/1024) . " / " . floor($total/1024/1024) . " MB )";

        $statusInfo['system']['cpu'] = $cpu['used'];
        $statusInfo['system']['memory'] = $memory;
        $statusInfo['system']['systime'] = $sysTime['Time'];
        
        return $statusInfo;
    }

    private static function getMonitoringStatus(){
        // fetch gateways and let's pretend the order is safe to use...
        $a_gateways = return_gateways_array(true, false, true);
        $gateways_status = return_gateways_status(true);
        legacy_html_escape_form_data($a_gateways);
        $monStatus = array();
        foreach ($a_gateways as $gname => $gateway){
            if(0===strpos($gname, 'STATIC')){
               continue;
            }
            $arrTmp = array();
            if ($gateways_status[$gname]) {
                $status = $gateways_status[$gname];
                if (stristr($status['status'], "force_down")) {
                    $online = gettext("Offline (forced)");
                } elseif (stristr($status['status'], "down")) {
                    $online = gettext("Offline");
                } elseif (stristr($status['status'], "loss")) {
                    $online = gettext("Warning, Packetloss").': '.$status['loss'];
                } elseif (stristr($status['status'], "delay")) {
                    $online = gettext("Warning, Latency").': '.$status['delay'];
                } elseif ($status['status'] == "none") {
                    $online = gettext("Online");
                }
            } else if (isset($gateway['monitor_disable'])) {
                $online = gettext("Online");
            } else {
                $online = gettext("Pending");
            }

            $arrTmp['gateway'] = isset($gateway['gateway'])?$gateway['gateway']:'';
            $arrTmp['monitor'] = isset($gateway['monitor'])?$gateway['monitor']:'';
            $arrTmp['srcip'] = isset($gateways_status[$gname]['srcip'])?$gateways_status[$gname]['srcip']:'';
            $arrTmp['loss'] = isset($gateways_status[$gname]['loss'])?$gateways_status[$gname]['loss']:'';
            $arrTmp['status'] = isset($gateways_status[$gname]['status'])?$gateways_status[$gname]['status']:'';

            $monStatus[$gateway['interface']] = $arrTmp;
        }
        return $monStatus;
    }

    private static function system_api_cpu_stats()
    {
        $cpustats = array();
        // take a short snapshot to calculate cpu usage
        $diff = array('user', 'nice', 'sys', 'intr', 'idle');
        $cpuTicks1 = array_combine($diff, explode(" ", get_single_sysctl('kern.cp_time')));
        usleep(100000);
        $cpuTicks2 = array_combine($diff, explode(" ", get_single_sysctl('kern.cp_time')));
        $totalStart = array_sum($cpuTicks1);
        $totalEnd = array_sum($cpuTicks2);
        if ($totalEnd <= $totalStart) {
            // if for some reason the measurement is invalid, assume nothing has changed (all 0)
            $totalEnd = $totalStart;
        }
        $cpustats['used'] = floor(100 * (($totalEnd - $totalStart) - ($cpuTicks2['idle'] - $cpuTicks1['idle'])) / ($totalEnd - $totalStart));
        $cpustats['user'] = floor(100 * (($cpuTicks2['user'] - $cpuTicks1['user'])) / ($totalEnd - $totalStart));
        $cpustats['nice'] = floor(100 * (($cpuTicks2['nice'] - $cpuTicks1['nice'])) / ($totalEnd - $totalStart));
        $cpustats['sys'] = floor(100 * (($cpuTicks2['sys'] - $cpuTicks1['sys'])) / ($totalEnd - $totalStart));
        $cpustats['intr'] = floor(100 * (($cpuTicks2['intr'] - $cpuTicks1['intr'])) / ($totalEnd - $totalStart));
        $cpustats['idle'] = floor(100 * (($cpuTicks2['idle'] - $cpuTicks1['idle'])) / ($totalEnd - $totalStart));

        // cpu model and count
        $cpustats['model'] = get_single_sysctl("hw.model");
        $cpustats['cpus'] = get_single_sysctl('kern.smp.cpus');

        // cpu frequency
        $tmp = get_single_sysctl('dev.cpu.0.freq_levels');
        $cpustats['max.freq'] = !empty($tmp) ? explode("/", explode(" ", $tmp)[0])[0] : "-";
        $tmp = get_single_sysctl('dev.cpu.0.freq');
        $cpustats['cur.freq'] = !empty($tmp) ? $tmp : "-";
        $cpustats['freq_translate'] = sprintf(gettext("Current: %s MHz, Max: %s MHz"), $cpustats['cur.freq'], $cpustats['max.freq']);

        // system load
        exec("/usr/bin/uptime | /usr/bin/sed 's/^.*: //'", $load_average);
        $cpustats['load'] = explode(',', $load_average[0]);

        return $cpustats;
    }

    private static function system_api_kernel()
    {
        global $config;
        $result = array();

        $result['pf'] = array();
        $result['pf']['maxstates'] = !empty($config['system']['maximumstates']) ? $config['system']['maximumstates'] : default_state_size();
        exec('/sbin/pfctl -si |grep "current entries" 2>/dev/null', $states);
        $result['pf']['states'] = count($states) >  0 ? filter_var($states[0], FILTER_SANITIZE_NUMBER_INT) : 0;

        $result['mbuf'] = array();
        exec('/usr/bin/netstat -mb | /usr/bin/grep "mbuf clusters in use"', $mbufs);
        $result['mbuf']['total'] = count($mbufs) > 0 ? explode('/', $mbufs[0])[2] : 0;
        $result['mbuf']['max'] = count($mbufs) > 0 ? explode(' ', explode('/', $mbufs[0])[3])[0] : 0;

        $totalMem = get_single_sysctl("vm.stats.vm.v_page_count");
        $inactiveMem = get_single_sysctl("vm.stats.vm.v_inactive_count");
        $cachedMem = get_single_sysctl("vm.stats.vm.v_cache_count");
        $freeMem = get_single_sysctl("vm.stats.vm.v_free_count");
        $result['memory']['total'] = get_single_sysctl('hw.physmem');
        if ($totalMem != 0) {
            $result['memory']['used'] = round(((($totalMem - ($inactiveMem + $cachedMem + $freeMem))) / $totalMem)*$result['memory']['total'], 0);
        } else {
            $result['memory']['used'] = gettext('N/A');
        }

        return $result;
    }

    public static function getGwTime(){
        global $config;

        $a_ntpd = &config_read_array('ntpd');

        $pconfig = array();
        $pconfig['timeservers_host'] = array();
        $pconfig['timeservers_prefer'] = array();

        if (!empty($config['system']['timeservers'])) {
            $pconfig['timeservers_prefer'] = !empty($a_ntpd['prefer']) ? explode(' ', $a_ntpd['prefer']) : array();
            $pconfig['timeservers_noselect'] = !empty($a_ntpd['noselect']) ? explode(' ', $a_ntpd['noselect']) : array();
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
                throw new AppException('time_error');
            }
            if(!$data['timezone']){
                throw new AppException('select_time_zone');
            }
            if(!$data['ntpServer']){
                throw new AppException('enter_ntp_server');
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
                if (!empty($a_ntpd['noselect'])) {
                    unset($a_ntpd['noselect']);
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
                $a_ntpd['noselect'] = !empty($ntpServer) ? trim($ntpServer) : null;

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

        $filename="Config-CSG2000P-".date("Ymd").".dat"; //文件名
        Header( "Content-type:  application/octet-stream ");
        Header( "Accept-Ranges:  bytes ");
        Header( "Accept-Length: " .strlen($data));
        header( "Content-Disposition:  attachment;  filename= {$filename}");
        echo $data;
        exit(0);

    }

    public static function saveConfigFile($request){
        foreach ($request->getUploadedFiles() as $file) {
            $filecontent = file_get_contents($file->getTempName());
            if(file_exists('/tmp/file_config.conf')){
                unlink('/tmp/file_config.conf');
            }
            file_put_contents('/tmp/file_config.conf',$filecontent);
        }
        exit(0);
    }

    public static function updataConfig(){
        global $config;
        $result = 0;
        try{
            if(!file_exists('/tmp/file_config.conf')){
                throw new AppException('conf_file_no_exist');
            }

            $filecontent = file_get_contents('/tmp/file_config.conf');
            $data = unserialize(Mcrypt::decrypt($filecontent));
            unlink('/tmp/file_config.conf');
            if(is_array($data) && isset($data['version']) && isset($data['system'])){
                $config = $data;
                write_config();
                mwexec('/sbin/shutdown -r +3sec');
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = '100';
        }
        return $result;
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
                    throw new AppException('min_error');
                }
                if('*'!=$hour && ($hour<0 || $hour>23)){
                    throw new AppException('hour_error');
                }
                if('*'!=$date && ($date<1 || $date>31)){
                    throw new AppException('day_error');
                }
                if('*'!=$month && ($month<1 || $month>12)){
                    throw new AppException('month_error');
                }
                if('*'!=$week && ($week<0 || $week>6)){
                    throw new AppException('week_error');
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

    public static function getArpInfo($data=false){
        global $config;
        if($data && is_array($data) && isset($data['interface'])){
            exec("/usr/sbin/arp -n -a -i ".$config['interfaces'][$data['interface']]['if'],$arp);
        }else{
            exec("/usr/sbin/arp -n -a",$arp);
        }
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
                    if($data && 'gateway' == $data['except'] && '8' == count($line)){
                        continue;
                    }
                    $infos[] = $a_arp;
                }
            }
        }

        return $infos;
    }

    public static function loadDefSettings(){
        $DbFile = "/var/captiveportal/captiveportal.sqlite";
        if(file_exists($DbFile)){
            unlink($DbFile);
        }

        reset_factory_defaults(false);

        return 0;
    }

    public static function getLangConfig(){
        global $config;
        $data = array('defaultLang'=>'en');
        if('zh_CN' == $config['system']['language']){
            $data['defaultLang'] = 'cn';
        }
        return $data;
    }

    public static function setLangConfig($data){
        global $config;
        if('en' == $data['language']){
            $data['language'] = 'en_US';
        }else{
            $data['language'] = 'zh_CN';
        }
        if (!empty($data['language']) && $data['language'] != $config['system']['language']) {
            $config['system']['language'] = $data['language'];
        }

        write_config();

        return $data['language'];
    }

    public static function getGroup($groupname){
        global $config;

        foreach($config['system']['group'] as $idx=>$group){
            if($group['name'] == $groupname){
                return $group;
            }
        }

        return false;
    }
}