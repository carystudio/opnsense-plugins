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


class Packet extends Csbackend
{
    protected static $ERRORCODE = array(
        'PACKET_100'=>'开启参数不正确',
        'IPSEC_101'=>'接口参数不正确'
    );

    const  protos = array('icmp', 'icmp6', 'tcp', 'udp', 'arp', 'carp', 'esp',
                '!icmp', '!icmp6', '!tcp', '!udp', '!arp', '!carp', '!esp');

    /**
     *  start capture operation
     *  @param array $option, options to pass to tpcdump (interface, promiscuous, snaplen, fam, host, proto, port)
     */
    protected static function start_capture($options)
    {
        $cmd_opts = array();
        $filter_opts = array();
        $intf = get_real_interface($options['interface']);
        $cmd_opts[] = '-i ' . $intf;

        if (empty($options['promiscuous'])) {
            // disable promiscuous mode
            $cmd_opts[] = '-p';
        }

        if (!empty($options['snaplen']) && is_numeric($options['snaplen'])) {
            // setup Packet Length
            $cmd_opts[] = '-s '. $options['snaplen'];
        }

        if (!empty($options['count']) && is_numeric($options['count'])) {
            // setup count
            $cmd_opts[] = '-c '. $options['count'];
        }

        if (!empty($options['fam']) && in_array($options['fam'], array('ip', 'ip6'))) {
            // filter address family
            $filter_opts[] = $options['fam'];
        }

        if (!empty($options['proto'])) {
            // filter protocol
            $filter_opts[] = $options['proto'];
        }

        if (!empty($options['host'])) {
            // filter host argument
            $filter = '';
            $prev_token = '';
            foreach (explode(' ', $options['host']) as $token) {
                if (in_array(trim($token), array('and', 'or'))) {
                    $filter .= $token;
                } elseif (is_ipaddr($token)) {
                    $filter .= "host " . $prev_token . " " . $token;
                } elseif (is_subnet($token)) {
                    $filter .= "net " . $prev_token . " " . $token;
                }
                if (trim($token) == 'not') {
                    $prev_token = 'not';
                } else {
                    $prev_token = '';
                }
                $filter .= " ";
            }

            $filter_opts[] = "( ". $filter . " )";
        }

        if (!empty($options['port'])) {
            // filter port
            $filter_opts[] = "port " . str_replace("!", "not ", $options['port']);
        }

        if (!empty($intf)) {
            $cmd = '/usr/sbin/tcpdump ';
            $cmd .= implode(' ', $cmd_opts);
            $cmd .= ' -w /tmp/packetcapture.cap ';
            $cmd .= " ".escapeshellarg(implode(' and ', $filter_opts));
            //delete previous packet capture if it exists
            @unlink('/tmp/packetcapture.cap');
            mwexec_bg($cmd);
        }
    }

    public static function getCaptureStatus(){
        $processcheck = (trim(shell_exec("/bin/ps axw -O pid= | /usr/bin/grep tcpdump | /usr/bin/grep  packetcapture.cap | /usr/bin/egrep -v '(pflog|grep)'")));
        $data = array("status"=>false,"file"=>false);
        if (!empty($processcheck)) {
            $data['status'] = true;
        } else {
            $data['status'] = false;
        }
        if(file_exists("/tmp/packetcapture.cap")){
            $data['file'] = true;
        }
        return $data;
    }

    public static function getCaptureView($data){
        if (!empty($data['dnsquery'])) {
            $disabledns = "";
        } else {
            $disabledns = "-n";
        }
        switch (!empty($data['detail']) ? $data['detail'] : null) {
            case "full":
                $detail_args = "-vv -e";
                break;
            case "high":
                $detail_args = "-vv";
                break;
            case "medium":
                $detail_args = "-v";
                break;
            case "normal":
            default:
                $detail_args = "-q";
                break;
        }
        $result = array();
        $dump_output = array();
        exec("/usr/sbin/tcpdump {$disabledns} {$detail_args} -r /tmp/packetcapture.cap |  /usr/bin/tail -n 5000", $dump_output);
        foreach ($dump_output as $line) {
            if ($line[0] == ' ' && count($result) > 0) {
                $result[count($result)-1] .= "\n" . $line;
            } else {
                $result[] = $line;
            }
        }
        return $result;
    }

    public static function downloadCapture(){
        try{
            if(!file_exists("/tmp/packetcapture.cap")){
                throw new AppException("PACKET_200");   //文件不存在
            }
            // download capture file
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=packetcapture.cap");
            header("Content-Length: ".filesize("/tmp/packetcapture.cap"));
            $file = fopen("/tmp/packetcapture.cap", "r");
            while(!feof($file)) {
                print(fread($file, 32 * 1024));
                ob_flush();
            }
            fclose($file);
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $result = 100;
        }
    }

    public static function removeCapture(){
        @unlink('/tmp/packetcapture.cap');
    }

    public static function setCaptureStop(){
        $processes_running = trim(shell_exec("/bin/ps axw -O pid= | /usr/bin/grep tcpdump | /usr/bin/grep packetcapture.cap | /usr/bin/egrep -v '(pflog|grep)'"));
        foreach (explode("\n", $processes_running) as $process) {
            exec("kill ". explode(' ',$process)[0]);
        }
    }

    public static function setCapturePacketStart($data){
        global $config;

        $result = 0;
        try{
            if(!isset($config['interfaces'][$data['interface']])){
                throw new AppException('IPSEC_101');    //接口参数不正确
            }
            if ($data['fam'] !== "" && $data['fam'] !== "ip" && $data['fam'] !== "ip6") {
                throw new AppException('IPSEC_102');    //地址簇参数不合法
            }
            if ($data['proto'] !== "" && !in_array(ltrim(trim($data['proto']), '!'), $data)) {
                throw new AppException('IPSEC_103');    //协议参数不合法
            }
            if (!empty($data['host'])) {
                foreach (explode(' ', $data['host']) as $token) {
                    if (!in_array(trim($token), array('and', 'or','not')) && !is_ipaddr($token) && !is_subnet($token) ) {
                        throw new AppException('IPSEC_104');    //主机地址参数不合法，不是一个合法的IP地址或者CIDR块
                    }
                }
            }
            if (!empty($data['port']) && !is_port(ltrim(trim($data['port']), 'not'))) {
                throw new AppException('IPSEC_105');    //端口参数不合法
            }
            if (!empty($data['snaplen']) && (!is_numeric($data['snaplen']) || $data['snaplen'] < 0)) {
                throw new AppException('IPSEC_106');    //数据包长度参数不合法
            }
            if (!empty($data['count']) && (!is_numeric($data['count']) || $data['count'] < 0)) {
                throw new AppException('IPSEC_107');    //计算参数不合法
            }

            self::start_capture($data);
            

        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }
}