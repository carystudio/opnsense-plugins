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
                $config['wol']['wolentry'] = array();
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


}