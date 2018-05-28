<?php
require_once("services.inc");
require_once("config.inc");
require_once("system.inc");
require_once ('util.inc');
require_once("interfaces.inc");
require_once('plugins.inc.d/dyndns.inc');

class Wol extends Csbackend
{
    protected static $ERRORCODE = array(

    );

    public static function getEntryList(){
        global $config;

        $result = 0;
        if(!isset($config['wol']) || !isset($config['wol']['wolentry']) || !is_array($config['wol']['wolentry'])){
            $config['wol']['wolentry'] = array();
        }
        $entrys = $config['wol']['wolentry'];
        foreach($entrys as $idx=>$entry){
            $entrys[$idx]['id'] = $idx;
            $entrys[$idx]['descrInterface'] = $config['interfaces'][$entry['interface']]['descr'];
        }

        return $entrys;
    }

    public static function delEntry($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['id']) || !is_numeric($data['id'])){
                throw new AppException('WOL_200');
            }
            $id = intval($data['id']);
            if(!isset($config['wol']['wolentry'][$id])){
                throw new AppException('WOL_201');
            }
            unset($config['wol']['wolentry'][$id]);
            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function addEntry($data){
        global $config;

        $result = 0;
        try{
            $entry = array();
            if(!isset($data['interface']) || (!empty($data['interface']) && !isset($config['interfaces'][$data['interface']]))){
                throw new AppException('WOL_100');
            }
            $entry['interface'] = $data['interface'];

            if(!isset($data['mac']) || !is_macaddr($data['mac'])){
                throw new AppException('WOL_101');
            }
            $entry['mac'] = $data['mac'];

            if(!isset($data['descr']) || strlen($data['descr'])>30){
                throw new AppException('WOL_102');
            }
            $entry['descr'] = $data['descr'];
            if(!isset($config['wol']) || !isset($config['wol']['wolentry']) || !is_array($config['wol']['wolentry'])){
                $config['wol'] = array('wolentry' => array());
            }
            $config['wol']['wolentry'][] = $entry;

            write_config();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function wakeup($data){
        $result = 0;
        try {
            if (empty($data['mac']) || !is_macaddr($data['mac'])) {
                throw new AppException('WOL_300');
            }
            if (empty($data['if'])) {
                throw new AppException('WOL_301');
            } else {
                $ipaddr = get_interface_ip($data['if']);
                if (!is_ipaddr($ipaddr)) {
                    throw new AppException('WOL_302');
                }
            }

            /* determine broadcast address */
            $bcip = escapeshellarg(gen_subnet_max($ipaddr, get_interface_subnet($data['if'])));
            /* Execute wol command and check return code. */
            if (mwexec("/usr/local/bin/wol -i {$bcip} " . escapeshellarg($data['mac']))) {
                throw new AppException('WOL_303');
            }
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }
}