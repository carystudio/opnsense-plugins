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


class Ipsec extends Csbackend
{
    protected static $ERRORCODE = array(
        'IPSEC_100'=>'开启参数不正确'
    );

    const FILTER_NAME_ESP = 'IPSEC_SERVER_ESP_ACCEPT';
    const FILTER_NAME_ISAKMP = 'IPSEC_SERVER_ISAKMP_ACCESS';
    const FILTER_NAME_NAT = 'IPSEC_SERVER_NAT_ACCEPT';
    const AUTO = array('', 'add', 'route', 'start');
    const IKETYPE = array('ikev1', 'ikev2');
    const MODE = array('main', 'aggressive');
    const AUTH_METHOD = array('rsasig', 'pre_shared_key');
    const MYID_TYPE = array('myaddress', 'address', 'fqdn','user_fqdn','asnldn', 'keyid tag', 'dyn_dns');
    const PEERID_TYPE = array('peeraddress', 'address', 'fqdn','user_fqdn','asnldn', 'keyid tag', 'dyn_dns');
    const EALGO = array('aes','aes128gcm16','aes192gcm16','aes256gcm16',
        'camellia','blowfish','3des', 'cast128','des');
    const EALGO_KEYLEN = array(
        'aes'=>array('128', '192', '256'),
        'camellia'=>array('128','192','256'),
        'blowfish'=>array('128','192','256'));
    const HASH_ALGO = array('md5', 'sha1', 'sha256', 'sha384', 'sha512','aesxcbc');
    const DHGROUP = array('1','2','5','14','15','16','17','18','19','20','21','22','23','24');
    const NAT_TRAVERSAL = array('off', 'on', 'force');
    const PHASE2MODE = array('tunnel', 'transport');
    const PHASE2PROTO = array('esp', 'ah');
    const PHASE2EALGOS = array('aes','aes128gcm16','aes192gcm16','aes256gcm16',
        'blowfish','3des', 'cast128','des', 'null');
    const PHASE2EALGOS_KEYLEN = array(
        'aes'=>array('auto','128', '192', '256'),
        'blowfish'=>array('auto', '128', '192', '256')
    );
    const PHASE2HASH_ALGO = array('hmac_md5', 'hmac_sha1', 'hmac_sha256', 'hmac_sha384',
        'hmac_sha512', 'aesxcbc');
    const PHASE2_PFSGROUP = array('0', '1','2','5','14','15','16','17','18');

    private static function ipsec_ikeid_used($ikeid) {
        global $config;

        if (!empty($config['ipsec']['phase1'])) {
            foreach ($config['ipsec']['phase1'] as $ph1ent) {
                if ($ikeid == $ph1ent['ikeid']) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function ipsec_ikeid_next()
    {
        $ikeid = 1;
        while (self::ipsec_ikeid_used($ikeid)) {
            $ikeid++;
        }

        return $ikeid;
    }


    private static function setFirewall(){
        global $config;

        foreach($config['filter']['rule'] as $idx=>$rule){
            if(Ipsec::FILTER_NAME_ESP == $rule['descr'] ||
                Ipsec::FILTER_NAME_ISAKMP==$rule['descr'] ||
                Ipsec::FILTER_NAME_NAT==$rule['descr']){
                unset($config['filter']['rule'][$idx]);
            }
        }

        $ipsec_infs = array();
        foreach($config['ipsec']['phase1'] as $tmp_p1){
            $enable = false;
            if(is_array($config['ipsec']['phase2'])){
                foreach($config['ipsec']['phase2'] as $tmp_p2){
                    if(!isset($tmp_p2['disabled'])){
                        $enable = true;
                        break ;
                    }
                }
            }

            if($enable){
                $ipsec_infs[] = $tmp_p1['interface'];
            }
        }
        if(count($ipsec_infs)==0){
            return ;
        }
        $infs = implode(',', $ipsec_infs);

        $config['filter']['rule'][] = array(
                'type'=>'pass',
                'interface'=>$infs,
                'ipprotocol'=>'inet',
                'statetype'=>'keep state',
                'descr'=>Ipsec::FILTER_NAME_ESP,
                'direction'=>'any',
                'quick'=>'yes',
                'floating'=>'yes',
                'protocol'=>'esp',
                'source'=>array('any'=>1),
                'destination'=>array('network'=>'(self)')
        );
        $config['filter']['rule'][] = array(
            'type'=>'pass',
            'interface'=>$infs,
            'ipprotocol'=>'inet',
            'statetype'=>'keep state',
            'descr'=>Ipsec::FILTER_NAME_ISAKMP,
            'direction'=>'any',
            'quick'=>'yes',
            'floating'=>'yes',
            'protocol'=>'tcp/udp',
            'source'=>array('any'=>1),
            'destination'=>array('network'=>'(self)', 'port'=>500)
        );
        $config['filter']['rule'][] = array(
            'type'=>'pass',
            'interface'=>$infs,
            'ipprotocol'=>'inet',
            'statetype'=>'keep state',
            'descr'=>Ipsec::FILTER_NAME_NAT,
            'direction'=>'any',
            'quick'=>'yes',
            'floating'=>'yes',
            'protocol'=>'tcp/udp',
            'source'=>array('any'=>1),
            'destination'=>array('network'=>'(self)', 'port'=>4500)
        );

    }

    private static function setEnable(){
        global $config;

        $ipsec_enable = false;
        if(is_array($config['ipsec']['phase2'])){
            foreach($config['ipsec']['phase2'] as $tmp_p2){
                if(!isset($tmp_p2['disabled'])){
                    $ipsec_enable = true;
                    break ;
                }
            }
        }

        if($ipsec_enable){
            $config['ipsec']['enable'] = 1;
        }else if(isset($config['ipsec']['enable'])){
            unset($config['ipsec']['enable']);
        }
    }

    private static function configure(){
        ipsec_configure_do();
        filter_configure();
    }

    public static function getPhase1($ikeid=false){
        global $config;

        $phase1 = array();
        if(isset($config['ipsec']['phase1'])){
            if(false == $ikeid){
                $phase1 = $config['ipsec']['phase1'];
                if(isset($phase1['disabled']) && '1'==$phase1['disabled']){
                    $phase1['disabled'] = 'yes';
                }
                foreach($phase1 as $idx=>$tmp_p1){
                    $phase1[$idx]['p1index'] = $idx;
                    if('rsasig' == $tmp_p1['authentication_method']){
                        $ca = Cert::getCa($tmp_p1['caref']);
                        $cert = Cert::getCert($tmp_p1['certref']);
                        if(false !== $ca){
                            $phase1[$idx]['ca'] = $ca['crt'];
                        }else{
                            $phase1[$idx]['ca'] = '';
                        }
                        if(false !== $cert){
                            $phase1[$idx]['cert'] = $cert['crt'];
                            $phase1[$idx]['prv'] = $cert['prv'];
                        }else{
                            $phase1[$idx]['cert'] = '';
                            $phase1[$idx]['prv'] = '';
                        }
                        unset($phase1[$idx]['caref']);
                        unset($phase1[$idx]['certref']);
                    }
                }
                return $phase1;
            }else{
                foreach($config['ipsec']['phase1'] as $tmp_p1){
                    if($tmp_p1['ikeid'] == $ikeid){
                        return $tmp_p1;
                    }
                }
                return false;
            }

        }

        return $phase1;
    }

    public static function setPhase1($data){
        global $config;

        $result = 0;
        try{
            $phase1 = array();
            if(isset($data['disabled'])){
                if('yes'!=$data['disabled']){
                    throw new AppException('IPSEC_100');
                }
                $phase1['disabled'] = '1';
            }

            if(!isset($data['auto']) || !in_array($data['auto'], Ipsec::AUTO)){
                throw new AppException('IPSEC_101');
            }
            $phase1['auto'] = $data['auto'];
            if(!isset($data['iketype']) || !in_array($data['iketype'], Ipsec::IKETYPE)){
                throw new AppException('IPSEC_102');
            }
            $phase1['iketype'] = $data['iketype'];
            $phase1['protocol'] = 'inet';
            if('any'!=$data['interface'] && !isset($config['interfaces'][$data['interface']])){
                throw new AppException('IPSEC_103');
            }
            $phase1['interface'] = $data['interface'];

            if(!isset($data['remote-gateway']) || !is_ipaddr($data['remote-gateway'])){
                throw new AppException('IPSEC_104');
            }
            $phase1['remote-gateway'] = $data['remote-gateway'];

            if(!isset($data['descr']) || strlen($data['descr'])>60){
                throw new AppException('IPSEC_105');
            }
            $phase1['descr'] = $data['descr'];

            if(!isset($data['authentication_method']) || !in_array($data['authentication_method'], Ipsec::AUTH_METHOD)){
                throw new AppException('IPSEC_106');
            }
            $phase1['authentication_method'] = $data['authentication_method'];

            if(!isset($data['myid_type']) || !in_array($data['myid_type'], Ipsec::MYID_TYPE)){
                throw new AppException('IPSEC_107');
            }
            if('any' == $data['interface'] && 'myaddress' == $data['myid_type']){
                throw new AppException('IPSEC_107');
            }
            $phase1['myid_type'] = $data['myid_type'];

            if('address' == $data['myid_type'] && !is_ipaddr($data['myid_data'])) {
                throw new AppException('IPSEC_108');
            }else if('user_fqdn' == $data['myid_type'] && false === filter_var($data['myid_data'], FILTER_VALIDATE_EMAIL)){
                throw new AppException('IPSEC_108');
            }else if('myaddress' != $data['myid_type'] && empty($data['myid_data'])){
                throw new AppException('IPSEC_108');
            }
            $phase1['myid_data'] = $data['myid_data'];

            if(!isset($data['peerid_type']) || !in_array($data['peerid_type'], Ipsec::PEERID_TYPE)){
                throw new AppException('IPSEC_109');
            }
            $phase1['peerid_type'] = $data['peerid_type'];

            if('address' == $data['peerid_type'] && !is_ipaddr($data['peerid_data'])){
                throw new AppException('IPSEC_110');
            }else if('peeraddress' != $data['peerid_type'] && empty($data['peerid_data'])){
                throw new AppException('IPSEC_110');
            }
            $phase1['peerid_data'] = $data['peerid_data'];

            if('pre_shared_key' == $data['authentication_method']){
                if(!isset($data['pre-shared-key']) || empty($data['pre-shared-key'])){
                    throw new AppException('IPSEC_111');
                }
                $phase1['pre-shared-key'] = $data['pre-shared-key'];
            }else{
                if(!isset($data['ca']) || empty($data['ca'])){
                    throw new AppException('IPSEC_112');
                }
                $ca = base64_decode($data['ca']);
                if(!$ca || !strstr($ca, "BEGIN CERTIFICATE") || !strstr($ca, "END CERTIFICATE")){
                    throw new AppException('IPSEC_112');
                }
                if(!isset($data['cert']) || empty($data['cert'])){
                    throw new AppException('IPSEC_113');
                }
                $cert = base64_decode($data['cert']);
                if(!$cert || !strstr($cert, "BEGIN CERTIFICATE") || !strstr($cert, "END CERTIFICATE")){
                    throw new AppException('IPSEC_113');
                }
                if(!isset($data['prv']) || empty($data['prv'])){
                    throw new AppException('IPSEC_114');
                }
                $prv = base64_decode($data['prv']);
                if(!prv || !strstr($prv, "BEGIN PRIVATE KEY") || !strstr($prv, "END PRIVATE KEY")){
                    throw new AppException('IPSEC_114');
                }
                $ca_subject = cert_get_subject($ca, false);
                $subject = cert_get_subject($cert, false);
                $issuer = cert_get_issuer($cert, false);
                if($ca_subject != $issuer){
                    throw new AppException('OVPN_216');
                }
            }
            if(!isset($data['ealgo']) || !in_array($data['ealgo'], Ipsec::EALGO)){
                throw new AppException('IPSEC_115');
            }
            $phase1['encryption-algorithm'] = array('name' => $data['ealgo']);

            if(array_key_exists($data['ealgo'], Ipsec::EALGO_KEYLEN)){
                if(!isset($data['ealgo_keylen']) || !is_numeric($data['ealgo_keylen'])){
                    throw new AppException('IPSEC_116');
                }
                if(!in_array($data['ealgo_keylen'], Ipsec::EALGO_KEYLEN[$data['ealgo']])){
                    throw new AppException('IPSEC_116');
                }
                $phase1['encryption-algorithm']['keylen'] = $data['ealgo_keylen'];
            }

            if(!isset($data['hash-algorithm']) || !in_array($data['hash-algorithm'], Ipsec::HASH_ALGO)){
                throw new AppException('IPSEC_117');
            }
            $phase1['hash-algorithm'] = $data['hash-algorithm'];

            if(!isset($data['dhgroup']) || !in_array($data['dhgroup'], Ipsec::DHGROUP)){
                throw new AppException('IPSEC_118');
            }
            $phase1['dhgroup'] = $data['dhgroup'];

            if(!isset($data['lifetime']) || !is_numeric($data['lifetime'])){
                throw new AppException('IPSEC_119');
            }
            $data['lifetime'] = intval($data['lifetime']);
            if($data['lifetime']<600){
                throw new AppException('IPSEC_119');
            }
            $phase1['lifetime'] = $data['lifetime'];

            if(isset($data['rekey_enable']) && 'yes'!=$data['rekey_enable']){
                throw new AppException('IPSEC_120');
            }
            $phase1['rekey_enable'] = $data['rekey_enable'];

            if(isset($data['reauth_enable']) && 'yes'!=$data['reauth_enable']){
                throw new AppException('IPSEC_121');
            }
            $phase1['reauth_enable'] = $data['reauth_enable'];

            if(isset($data['tunnel_isolation']) && 'yes'!=$data['tunnel_isolation']){
                throw new AppException('IPSEC_121');
            }
            $phase1['tunnel_isolation'] = $data['tunnel_isolation'];

            if(!isset($data['nat_traversal']) || !in_array($data['nat_traversal'], Ipsec::NAT_TRAVERSAL)){
                throw new AppException('IPSEC_122');
            }
            $phase1['nat_traversal'] = $data['nat_traversal'];

            if(isset($data['mobike']) && 'on'!=$data['mobike']){
                throw new AppException('IPSEC_123');
            }
            $phase1['mobike'] = $data['mobike'];

            if(isset($data['dpd_enable'])){
                if('yes'!=$data['dpd_enable']) {
                    throw new AppException('IPSEC_124');
                }
                $phase1['dpd_enable'] = $data['dpd_enable'];

                if(!isset($data['dpd_delay']) || !is_numeric($data['dpd_delay'])){
                    throw new AppException('IPSEC_125');
                }
                $data['dpd_delay'] = intval($data['dpd_delay']);
                if($data['dpd_delay']<2 || $data['dpd_delay']>100){
                    throw new AppException('IPSEC_125');
                }
                $phase1['dpd_delay'] = $data['dpd_delay'];

                if(!isset($data['dpd_maxfail']) || !is_numeric($data['dpd_maxfail'])){
                    throw new AppException('IPSEC_126');
                }
                $data['dpd_maxfail'] = intval($data['dpd_maxfail']);
                if($data['dpd_maxfail']<2 || $data['dpd_maxfail']>1000){
                    throw new AppException('IPSEC_126');
                }
                $phase1['dpd_maxfail'] = $data['dpd_maxfail'];
            }
            if(isset($data['p1index'])){
                if(!is_numeric($data['p1index'])){
                    throw new AppException('IPSEC_127');
                }
                if(!isset($data['ikeid']) || !is_numeric($data['ikeid'])){
                    throw new AppException('IPSEC_127');
                }
                if(!isset($config['ipsec']['phase1'][$data['p1index']]) || $config['ipsec']['phase1'][$data['p1index']]['ikeid']!=$data['ikeid']){
                    throw new AppException('IPSEC_127');
                }
                $phase1['p1index'] = $data['p1index'];
                $phase1['ikeid'] = $data['ikeid'];
            }else{
                if(isset($data['ikeid'])){
                    throw new AppException('IPSEC_127');
                }
            }
            if('ikev1' == $phase1['iketype']){
                if(!isset($data['mode']) || !in_array($data['mode'], Ipsec::MODE)){
                    throw new AppException('IPSEC_128');
                }
                $phase1['mode'] = $data['mode'];
            }

            //clean old key
            foreach($config['ca'] as $idx=>$ca){
                if('CSG200P_ipsec_ca_'.$phase1['ikeid'] == $ca['descr']){
                    unset($config['ca'][$idx]);
                    break ;
                }
            }
            foreach($config['cert'] as $idx=>$cert){
                if('CSG2000P_ipsec_cert_'.$phase1['ikeid'] == $cert['descr']){
                    unset($config['cert'][$idx]);
                    break;
                }
            }

            if('rsasig' == $data['authentication_method']){
                $ca = array('refid' => uniqid(),
                    'descr' => 'CSG200P_ipsec_ca_'.$phase1['ikeid'],
                    'serial' => 3,
                    'crt' => $data['ca'],
                    'prv' => '');
                $cert = array('refid'=>uniqid(),
                    'descr'=>'CSG2000P_ipsec_cert_'.$phase1['ikeid'],
                    'crt'=>$data['cert'],
                    'prv'=>$data['prv'],
                    'caref'=>$ca['refid']);

                $config['ca'][] = $ca;
                $config['cert'][] = $cert;
                $phase1['caref'] = $ca['refid'];
                $phase1['certref'] = $cert['refid'];
            }
            if(!isset($phase1['ikeid']) || !isset($phase1['p1index'])){
                $phase1['ikeid'] = self::ipsec_ikeid_next();
                $config['ipsec']['phase1'][] = $phase1;
            }else{
                $p1index = $phase1['p1index'];
                unset($phase1['p1index']);
                $config['ipsec']['phase1'][$p1index] = $phase1;
            }

            self::setEnable();
            self::setFirewall();
            write_config();
            self::configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function delPhase1($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['ikeid']) || !is_numeric($data['ikeid'])){
                throw new AppException('IPSEC_400');
            }
            $deleted = false;
            foreach($config['ipsec']['phase1'] as $p1_idx=>$phase1){
                if($phase1['ikeid'] == $data['ikeid']){
                    if ($phase1['interface'] <> "wan") {
                        /* XXX does this even apply? only use of system.inc at the top! */
                        system_host_route($phase1['remote-gateway'], $phase1['remote-gateway'], true, false);
                    }
                    if(is_array($config['ipsec']['phase2'])){
                        foreach($config['ipsec']['phase2'] as $p2_idx=>$phase2){
                            if($phase2['ikeid'] == $phase1['ikeid']){
                                unset($config['ipsec']['phase2'][$p2_idx]);
                            }
                        }
                    }

                    unset($config['ipsec']['phase1'][$p1_idx]);
                    $deleted = true;
                    break;
                }
            }
            if(!$deleted){
                throw new AppException('IPSEC_401');
            }

            self::setEnable();
            self::setFirewall();
            write_config();
            self::configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getPhase2($data){
        global $config;

        $result =0;
        try{
            if(!isset($data['ikeid']) || !is_numeric($data['ikeid'])){
                throw new AppException('IPSEC_200');
            }
            $phase2s = array();
            foreach($config['ipsec']['phase2'] as $id=>$phase2){
                if($phase2['ikeid'] == $data['ikeid']){
                    if(isset($phase2['disabled']) && '1' == $phase2['disabled']){
                        $phase2['disabled'] = 'yes';
                    }
                    foreach(array('localid', 'remoteid') as $id_type){
                        $phase2[$id_type.'_type'] = $phase2[$id_type]['type'];
                        if(isset($phase2[$id_type]['address'])){
                            $phase2[$id_type.'_address'] = $phase2[$id_type]['address'];
                        }
                        if(isset($phase2[$id_type]['netbits'])){
                            $phase2[$id_type.'_netbits'] = $phase2[$id_type]['netbits'];
                        }
                        unset($phase2[$id_type]);
                    }
                    if(isset($phase2['encryption-algorithm-option'])){
                        $phase2['ealgos'] = array();
                        if(is_array($phase2['encryption-algorithm-option'])){
                            foreach($phase2['encryption-algorithm-option'] as $ealgo){
                                $phase2['ealgos'][] = $ealgo['name'];
                                if(isset($ealgo['keylen'])){
                                    $phase2['keylen_'.$ealgo['name']] = $ealgo['keylen'];
                                }
                            }
                        }
                        unset($phase2['encryption-algorithm-option']);
                    }
                    $phase2s[] = $phase2;
                }
            }

            $result = $phase2s;
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function setPhase2($data)
    {
        global $config;

        $result = 0;
        try {
            $phase2 = array();
            if(isset($data['disabled'])){
                if('yes'!=$data['disabled']){
                    throw new AppException('IPSEC_300');
                }
                $phase2['disabled'] = '1';
            }

            if(!isset($data['mode']) || !in_array($data['mode'], Ipsec::PHASE2MODE)){
                throw new AppException('IPSEC_301');
            }
            $phase2['mode'] = $data['mode'];

            if(!isset($data['descr']) || strlen($data['descr'])>60){
                throw new AppException('IPSEC_302');
            }
            $phase2['descr'] = $data['descr'];

            if(!isset($data['localid_type']) ||
                ('address'!=$data['localid_type'] && 'network'!=$data['localid_type'] && !isset($config['interfaces'][$data['localid_type']]))){
                throw new AppException('IPSEC_303');
            }
            $phase2['localid'] = array('type' => $data['localid_type']);
            if('address' == $data['localid_type'] || 'network' == $data['localid_type']){
                if(!isset($data['localid_address']) || !is_ipaddr($data['localid_address'])){
                    throw new AppException('IPSEC_304');
                }
                $phase2['localid']['address'] = $data['localid_address'];
            }
            if('network' == $data['localid_type']){
                if(!isset($data['localid_netbits']) || !is_numeric($data['localid_netbits'])){
                    throw new AppException('IPSEC_305');
                }
                $phase2['localid']['netbits'] = intval($data['localid_netbits']);
                if($phase2['localid']['netbits']>32 || $phase2['localid']['netbits']<8){
                    throw new AppException('IPSEC_305');
                }
            }

            if(!isset($data['remoteid_type']) ||
                ('address'!=$data['remoteid_type'] && 'network'!=$data['remoteid_type'] && !isset($config['interfaces'][$data['remoteid_type']]))){
                throw new AppException('IPSEC_306');
            }
            $phase2['remoteid'] = array('type' => $data['remoteid_type']);
            if('address' == $data['remoteid_type'] || 'network' == $data['remoteid_type']){
                if(!isset($data['remoteid_address']) || !is_ipaddr($data['remoteid_address'])){
                    throw new AppException('IPSEC_307');
                }
                $phase2['remoteid']['address'] = $data['remoteid_address'];
            }
            if('network' == $data['remoteid_type']){
                if(!isset($data['remoteid_netbits']) || !is_numeric($data['remoteid_netbits'])){
                    throw new AppException('IPSEC_308');
                }
                $phase2['remoteid']['netbits'] = intval($data['remoteid_netbits']);
                if($phase2['remoteid']['netbits']>32 || $phase2['remoteid']['netbits']<8){
                    throw new AppException('IPSEC_308');
                }
            }
            if(!isset($data['protocol']) || !in_array($data['protocol'], Ipsec::PHASE2PROTO)){
                throw new AppException('IPSEC_309');
            }
            $phase2['protocol'] = $data['protocol'];
            if(!isset($data['ealgos']) || !is_array($data['ealgos']) || count($data['ealgos'])<1){
                throw new AppException('IPSEC_310');
            }
            $ealgos_diff = array_diff ($data['ealgos'] , Ipsec::PHASE2EALGOS);
            if(count($ealgos_diff)>0){
                var_dump($ealgos_diff);
                throw new AppException('IPSEC_310');
            }
            $phase2['encryption-algorithm-option'] = array();
            foreach($data['ealgos'] as $ealgos){
                $ealgos_option = array('name'=>$ealgos);
                if(array_key_exists($ealgos, Ipsec::PHASE2EALGOS_KEYLEN)){
                    if(!isset($data['keylen_'.$ealgos]) || !in_array($data['keylen_'.$ealgos], Ipsec::PHASE2EALGOS_KEYLEN[$ealgos])){
                        throw new AppException('IPSEC_311');
                    }
                    $ealgos_option['keylen'] = $data['keylen_'.$ealgos];
                }
                $phase2['encryption-algorithm-option'][] = $ealgos_option;
            }
            if(!isset($data['hash-algorithm-option']) || !is_array($data['hash-algorithm-option']) || count($data['hash-algorithm-option'])<1){
                throw new AppException('IPSEC_312');
            }
            $hash_diff = array_diff($data['hash-algorithm-option'], Ipsec::PHASE2HASH_ALGO);
            if(count($hash_diff)>0){
                throw new AppException('IPSEC_312');
            }
            $phase2['hash-algorithm-option'] = array();
            foreach($data['hash-algorithm-option'] as $hash_algo){
                $phase2['hash-algorithm-option'][] = $hash_algo;
            }
            if(!isset($data['pfsgroup']) || !in_array($data['pfsgroup'], Ipsec::PHASE2_PFSGROUP)){
                throw new AppException('IPSEC_313');
            }
            $phase2['pfsgroup'] = $data['pfsgroup'];
            if(isset($data['lifetime'])){
                if(!is_numeric($data['lifetime'])){
                    throw new AppException('IPSEC_314');
                }
                $phase2['lifetime'] = $data['lifetime'];
            }
            if(isset($data['pinghost'])){
                if(!is_ipaddr($data['pinghost'])){
                    throw new AppException('IPSEC_315');
                }
                $phase2['pinghost'] = $data['pinghost'];
            }
            if(isset($data['spd'])){
                if(!Util::checkCidr($data['spd'], true, 'ipv4')){
                    throw new AppException('IPSEC_316');
                }
                $phase2['spd'] = $data['spd'];
            }
            if(!isset($data['ikeid'])){
                throw new AppException('IPSEC_317');
            }
            if(false == self::getPhase1($data['ikeid'])){
                throw new AppException('IPSEC_317');
            }
            $phase2['ikeid'] = $data['ikeid'];
            if(isset($data['uniqid'])){
                $p2_idx = false;
                foreach($config['ipsec']['phase2'] as $idx=>$tmp_p2){
                    if($tmp_p2['uniqid'] == $data['uniqid'] && $tmp_p2['ikeid'] = $data['ikeid']){
                        $p2_idx = $idx;
                        break ;
                    }
                }
                if(false === $p2_idx){
                    throw new AppException('IPSEC_318');
                }
                $phase2['uniqid'] = $data['uniqid'];
                $config['ipsec']['phase2'][$p2_idx] = $phase2;
            }else{
                $phase2['uniqid'] = uniqid();
                $config['ipsec']['phase2'][] = $phase2;
            }

            self::setEnable();
            self::setFirewall();
            write_config();
            self::configure();
        } catch (AppException $aex) {
            $result = $aex->getMessage();
        } catch (Exception $ex) {
            $result = 100;
        }

        return $result;
    }

    public static function delPhase2($data){
        global $config;

        $result = 0;
        try{
            if(!isset($data['ikeid']) || !is_numeric($data['ikeid'])){
                throw new AppException('IPSEC_500');
            }
            if(!isset($data['uniqid']) || empty($data['uniqid'])){
                throw new AppException('IPSEC_501');
            }
            $deleted = false;
            foreach($config['ipsec']['phase2'] as $p2_idx=>$phase2){
                if($phase2['ikeid'] == $data['ikeid'] && $phase2['uniqid'] == $data['uniqid']){
                    unset($config['ipsec']['phase2'][$p2_idx]);
                    $deleted = true;
                    break;
                }
            }
            if(!$deleted){
                throw new AppException('IPSEC_502');
            }

            self::setEnable();
            self::setFirewall();
            write_config();
            self::configure();
        }catch(AppException $aex){
            $result = $aex->getMessage();
        }catch(Exception $ex){
            $result = 100;
        }

        return $result;
    }

    public static function getStatus(){
        $ipsec_status = json_decode(configd_run("ipsec list status"), true);

        return $ipsec_status;
    }

    public static function connect($data){
        if (!empty($data['connid'])) {
            configd_run("ipsec connect ".$data['connid']);
        }

        return 0;
    }

    public static function disconnect($data){
        if (!empty($data['connid'])) {
            configd_run("ipsec disconnect ".$data['connid']);
        }

        return 0;
    }

    public static function getLogs(){
        exec("/usr/local/sbin/clog /var/log/ipsec.log| grep -v \"CLOG\" | grep -v \"\033\" | /usr/bin/tail -r -n 100", $logarr);
        $logs = implode("\n", $logarr);

        echo $logs;
    }

}