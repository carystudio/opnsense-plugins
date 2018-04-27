<?php

require_once ('certs.inc');

/**
 * Created by PhpStorm.
 * User: heimi
 * Date: 2017/2/6
 * Time: 15:34
 */
class Cert
{
    const DN_COUNTRY = 'CN';
    const DN_STATE = 'GD';
    const DN_CITY = 'SZ';
    const DN_ORG = 'CS';
    const DN_EMAIL = 'carystudio@carystudio.com';

    const KEY_LEN = 2048;
    const LIFE_TIME = 3650;
    const DIGEST_ALG = 'sha256';


    const CSG2000P_CA_CN = 'CSG2000P-ca';
    const CSG2000P_CA_REFID = '592fa6d7bf05a';
    
    const OVPN_CLIENT_CA_REFID = '5ac9f07897dde';
    const OVPN_CLIENT_CA_DESCR = 'CSG2000P_openvpn_client_ca';
    const OVPN_CLIENT_CERT_REFID = '5ac9f213dd1fa';
    const OVPN_CLIENT_CERT_DESCR = 'CSG2000P_openvpn_client';
    const OVPN_SERVER_CA_CN = 'OVPN_SERVER_CA';
    const OVPN_SERVER_CA_REFID = '8e7ff0eg9bd8k';
    const OVPN_SERVER_CA_DESCR = 'CSG2000P_openvpn_server_ca';
    const OVPN_SERVER_CERT_CN = 'OVPN_SERVER_CERT';
    const OVPN_SERVER_CERT_REFID = 'oknd7234nhdgs';
    const OVPN_SERVER_CERT_DESCR = 'CSG2000P_openvpn_server';

    public static function getCa($refid){
        global $config;

        foreach($config['ca'] as $ca){
            if($ca['refid'] == $refid){
                return $ca;
            }
        }

        return false;
    }

    public static function getCert($refid){
        global $config;

        foreach($config['cert'] as $cert){
            if($cert['refid'] == $refid){
                return $cert;
            }
        }

        return false;
    }
}