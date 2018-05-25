(function(obj){
/**
 * 主题函数库列表：
 *
 * @property {Object} getSysStatusCfg 获取系统状态数据 <a href="#getSysStatusCfg">点击查看</a>
 * @property {Object} getEasyWizardCfg 获取Wizrad配置 <a href="#getEasyWizardCfg">点击查看</a>
 * @property {Object} setEasyWizardCfg 提交Wizrad数据 <a href="#setEasyWizardCfg">点击查看</a>
 * @property {Object} getPasswordCfg 获取登录密码 <a href="#getPasswordCfg">点击查看</a>
 * @property {Object} setPasswordCfg 设置登录密码 <a href="#setPasswordCfg">点击查看</a>
 * @property {Object} getRemoteCfg 获取远程管理配置 <a href="#getRemoteCfg">点击查看</a>
 * @property {Object} setRemoteCfg 设置远程管理数据 <a href="#setRemoteCfg">点击查看</a>
 * @property {Object} getNTPCfg 获取NTP配置 <a href="#getNTPCfg">点击查看</a>
 * @property {Object} setNTPCfg 提交NTP数据 <a href="#setNTPCfg">点击查看</a>
 * @property {Object} getDDNSCfg 获取DDNS配置 <a href="#getDDNSCfg">点击查看</a>
 * @property {Object} setDDNSCfg 提交DDNS数据 <a href="#setDDNSCfg">点击查看</a>
 * @property {Object} getRebootScheCfg 获取RebootSche数据 <a href="#getRebootScheCfg">点击查看</a>
 * @property {Object} setRebootScheCfg 提交RebootSche数据 <a href="#setRebootScheCfg">点击查看</a>
 * @property {Object} getMiniUPnPConfig 获取unnp数据 <a href="#getMiniUPnPConfig">点击查看</a> 
 * @property {Object} setMiniUPnPConfig 提交unnp数据 <a href="#setMiniUPnPConfig">点击查看</a>
 * @property {Object} CloudSrvVersionCheck 提交CloudsrvVersion数据 <a href="#CloudSrvVersionCheck">点击查看</a>
 * @property {Object} CloudACMunualUpdate 云检测固件手动升级 <a href="#CloudACMunualUpdate">点击查看</a>
 * @property {Object} setUpgradeFW 提交UpgradeFW数据 <a href="#setUpgradeFW">点击查看</a>
 * @property {Object} FirmwareUpgrade 获取固件信息 <a href="#FirmwareUpgrade">点击查看</a>
 * @property {Object} SystemSettings 获取系统设置数据 <a href="#SystemSettings">点击查看</a>
 * @property {Object} LoadDefSettings 提交恢复出厂配置 <a href="#LoadDefSettings">点击查看</a>
 * @property {Object} RebootSystem 提交系统重启配置 <a href="#RebootSystem">点击查看</a>
 * @property {Object} getSyslogCfg 获取Syslog数据 <a href="#getSyslogCfg">点击查看</a> 
 * @property {Object} setSyslogCfg 提交Syslog数据 <a href="#setSyslogCfg">点击查看</a> 
 * @property {Object} clearSyslog 提交清除Syslog数据 <a href="#clearSyslog">点击查看</a> 
 * @property {Object} showSyslog 获取Syslog数据 <a href="#showSyslog">点击查看</a> 
 * @property {Object} getWiFiBasicConfig 获取无线基础配置 <a href="#getWiFiBasicConfig">点击查看</a>
 * @property {Object} setWiFiBasicConfig 提交无线基础数据 <a href="#setWiFiBasicConfig">点击查看</a>
 * @property {Object} getWiFiMultipleConfig 获取多AP设置数据 <a href="#getWiFiMultipleConfig">点击查看</a>
 * @property {Object} setWiFiMultipleConfig 提交多AP设置数据 <a href="#setWiFiMultipleConfig">点击查看</a>
 * @property {Object} delWiFiMultipleConfig 删除多AP数据 <a href="#delWiFiMultipleConfig">点击查看</a>
 * @property {Object} getWiFiAdvancedConfig 获取无线高级设置数据 <a href="#getWiFiAdvancedConfig">点击查看</a>
 * @property {Object} setWiFiAdvancedConfig 提交无线高级数据 <a href="#setWiFiAdvancedConfig">点击查看</a>
 * @property {Object} getWiFiWpsSetupConfig 获取WPS配置信息 <a href="#getWiFiWpsSetupConfig">点击查看</a>
 * @property {Object} setWiFiWpsSetupConfig 启用WPS <a href="#setWiFiWpsSetupConfig">点击查看</a>
 * @property {Object} getWiFiWpsConfig WPS进行中获取信息 <a href="#getWiFiWpsConfig">点击查看</a>
 * @property {Object} setWiFiWpsConfig 开启WPS <a href="#setWiFiWpsConfig">点击查看</a>
 * @property {Object} getWiFiWdsAddConfig 获取WDS数据 <a href="#getWiFiWdsAddConfig">点击查看</a>
 * @property {Object} setWiFiWdsAddConfig 提交WDS数据 <a href="#setWiFiWdsAddConfig">点击查看</a>
 * @property {Object} setWiFiWdsDeleteConfig 删除WDS数据 <a href="#setWiFiWdsDeleteConfig">点击查看</a>
 * @property {Object} getWiFiAclAddConfig 获取Mac认证数据 <a href="#getWiFiAclAddConfig">点击查看</a>
 * @property {Object} setWiFiAclAddConfig 提交Mac认证数据 <a href="#setWiFiAclAddConfig">点击查看</a>
 * @property {Object} setWiFiAclDeleteConfig 删除Mac认证数据 <a href="#setWiFiAclDeleteConfig">点击查看</a>
 * @property {Object} getWiFiScheduleConfig 获取WiFi调度数据 <a href="#getWiFiScheduleConfig">点击查看</a>
 * @property {Object} setWiFiScheduleConfig 设置WiFi调度数据 <a href="#setWiFiScheduleConfig">点击查看</a>
 * @property {Object} getWiFiApInfo 获取无线状态数据 <a href="#getWiFiApInfo">点击查看</a>
 * @property {Object} getWiFiStaInfo 获取无线客户端连接设备信息 <a href="#getWiFiStaInfo">点击查看</a>
 * @property {Object} getWiFiIpMacTable 获取MAC认证的MAC列表 <a href="#getWiFiIpMacTable">点击查看</a>
 * @property {Object} getWiFiApcliScan 获取扫描AP数据 <a href="#getWiFiApcliScan">点击查看</a>
 * @property {Object} getFirewallType 获取Firewall类型 <a href="#getFirewallType">点击查看</a>
 * @property {Object} setFirewallType 设置Firewall类型 <a href="#setFirewallType">点击查看</a>
 * @property {Object} getVpnPassCfg 获取VPN穿透配置 <a href="#getVpnPassCfg">点击查看</a>
 * @property {Object} setVpnPassCfg 设置VPN穿透数据 <a href="#setVpnPassCfg">点击查看</a>
 * @property {Object} getDMZCfg 获取DMZ数据 <a href="#getDMZCfg">点击查看</a>
 * @property {Object} setDMZCfg 设置DMZ数据 <a href="#setDMZCfg">点击查看</a>
 * @property {Object} getUrlFilterRules 获取URL过滤配置 <a href="#getUrlFilterRules">点击查看</a>
 * @property {Object} setUrlFilterRules 设置URL过滤数据 <a href="#setUrlFilterRules">点击查看</a>
 * @property {Object} delUrlFilterRules 删除URL过滤数据 <a href="#delUrlFilterRules">点击查看</a>
 * @property {Object} getIpPortFilterRules 获取IP端口过滤配置 <a href="#getIpPortFilterRules">点击查看</a>
 * @property {Object} setIpPortFilterRules 设置IP端口过滤数据 <a href="#setIpPortFilterRules">点击查看</a>
 * @property {Object} delIpPortFilterRules 删除IP端口过滤数据 <a href="#delIpPortFilterRules">点击查看</a>
 * @property {Object} getPortForwardRules 获取端口转发配置 <a href="#getPortForwardRules">点击查看</a>
 * @property {Object} setPortForwardRules 提交端口转发数据 <a href="#setPortForwardRules">点击查看</a>
 * @property {Object} delPortForwardRules 删除端口转发数据 <a href="#delPortForwardRules">点击查看</a>
 * @property {Object} getMacFilterRules 获取MAC过滤配置 <a href="#getMacFilterRules">点击查看</a>
 * @property {Object} setMacFilterRules 设置MAC过滤数据 <a href="#setMacFilterRules">点击查看</a>
 * @property {Object} delMacFilterRules 删除MAC过滤数据 <a href="#delMacFilterRules">点击查看</a>
 * @property {Object} setIpQos 提交总QoS数据 <a href="#setIpQos">点击查看</a>
 * @property {Object} getIpQosRules 获取QoS数据 <a href="#getIpQosRules">点击查看</a>
 * @property {Object} setIpQosRules 提交新增QoS数据 <a href="#setIpQosRules">点击查看</a>
 * @property {Object} delIpQosRules 删除QoS数据 <a href="#delIpQosRules">点击查看</a>
 * @property {Object} getScheduleRules 获取时间规则数据 <a href="#getScheduleRules">点击查看</a>
 * @property {Object} setScheduleRules 设置和删除时间规则数据 <a href="#setScheduleRules">点击查看</a>
 * @property {Object} getWanConfig 获取WAN设置信息 <a href="#getWanConfig">点击查看</a>
 * @property {Object} setWanConfig 设置WAN设置信息 <a href="#setWanConfig">点击查看</a>
 * @property {Object} getLanConfig 获取LAN数据 <a href="#getLanConfig">点击查看</a>
 * @property {Object} setLanConfig 设置LAN数据 <a href="#setLanConfig">点击查看</a>
 * @property {Object} getStaticDhcpConfig 获取静态DHCP设置数据 <a href="#getStaticDhcpConfig">点击查看</a>
 * @property {Object} setStaticDhcpConfig 提交静态DHCP设置数据 <a href="#setStaticDhcpConfig">点击查看</a>
 * @property {Object} delStaticDhcpConfig 删除静态DHCP设置数据 <a href="#delStaticDhcpConfig">点击查看</a>
 * @property {Object} getDhcpCliList 获取LAN设置的DHCP列表 <a href="#getDhcpCliList">点击查看</a>
 * @property {Object} getOpMode 获取opmode数据 <a href="#getOpMode">点击查看</a>
 * @property {Object} setOpMode 提交opmode数据 <a href="#setOpMode">点击查看</a>
 * @property {Object} getStationMacByIp 通过ip获得克隆mac <a href="#getStationMacByIp">点击查看</a>
 * @property {Object} getArpTable 获取静态DHCP设置、IP端口过滤、Mac过滤、端口转发的MAC列表 <a href="#getArpTable">点击查看</a>
 * @property {Object} testSQLite 执行sql主题 <a href="#testSQLite">点击查看</a>
 * @property {Object} QuickSetting 集中管理 <a href="#QuickSetting">点击查看</a>
 * @property {Object} getUsbState 获取USB状态 <a href="#getUsbState">点击查看</a>
 * @property {Object} getDhcpSliList 获取DHCP列表 <a href="#getDhcpSliList">点击查看</a>
 * @property {Object} getNetInfo 实时信息 -> 获取外网信息 <a href="#getNetInfo">点击查看</a>
 * @property {Object} getLinksData 实时信息获取链接数 <a href="#getLinksData">点击查看</a>
 * @property {Object} getLoginCfg 获取登录配置 <a href="#getLoginCfg">点击查看</a>
 * @property {Object} acScanAp 集中管理的扫描 <a href="#acScanAp">点击查看</a>
 * @property {Object} NTPSyncWithHost 保存本地时间 <a href="#NTPSyncWithHost">点击查看</a>
 * @property {Object} getssServer 获取SS-Server配置 <a href="#getssServer">点击查看</a>
 * @property {Object} setssServer 设置SS-Server配置 <a href="#setssServer">点击查看</a>
 * @property {Object} setLanguageCfg 设置语言 <a href="#setLanguageCfg">点击查看</a>
 *
 *
 *
 * @property {Object} getStatusInfo 获取系统状态信息 <a href="#getStatusInfo">点击查看</a>
 *
 * @alias uiPost
 * @class
 * @example
 * 封装案例：
 * /**
 *  * 这里写上文档注释
 *  * @param {type} varname description
 *  * param 这里定义为request 参数 
 *  * @property {type} varname description
 *  * property 这里定义为response
 *  * @property 
 *  * @example
 *  * 实际的案例。
 *  * /
 * uiPost.prototype.xxx = function(postVar,callback){
 *    this.topicurl = 'xxx';
 *    // this.async = true; // 默认true。true:异步，false:同步。
 *    if (globalConfig.debug) {
 *        this.url = '/data/wzd.json';
 *    }
 *    return this.post(postVar,callback);
 * };
 * // 把xxx的位置换成对应的主题即可。
 */
function uiPost(){
    this.version = '0.0.1.bate';
    this.author = 'carystudio';
    this.company = 'carystudio';
    this.url = globalConfig.cgiUrl;
    this.type = globalConfig.ajaxType?'GET':'POST';
    this.async = true;
    this.topicurl = '';
    this.post = function(data,callback) {
        var temp_data = null;
        if (data && data instanceof Function) {
            callback = data;
            data = null;
        }
        data = data ? data : {};
        data.topicurl = this.topicurl;
        data = JSON.stringify(data);
        $.ajax({
            url: this.url,
            type: this.type,
            dataType: 'json',
            data: data,
            async:this.async,
            success:function(_data) {
                temp_data = (_data);
                if (callback && callback instanceof Function) {
                    callback(temp_data,data);
                }
            },
            error:function(_data) {
                if (callback && callback instanceof Function) {
                    callback(_data,'error');
                }
            }
        });
    }
}

/**
 * getSysStatusCfg 主题 （获取系统状态信息）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @property {String} portlinkBt	未知
 * @property {String} portLinkStatus	未知
 * @property {String} upTime		系统在线时间
 * @property {String} fmVersion		固件版本
 * @property {String} productName	产品名称
 * @property {String} hardModel		硬件型号
 * @property {String} buildTime		     软件编译时间
 * @property {String} multiLangBt	     多语言支持；1：支持；0：不支持
 * @property {String} languageType	     语言类型
 * @property {String} customerUrl	     官方网站
 * @property {String} apAcBt		     支持APAC, 1：支持；0：不支持
 * @property {String} lanIp			     局域网IP地址
 * @property {String} lanMask		     局域网子网掩码
 * @property {String} lanMac		     局域网MAC地址
 * @property {String} dhcpServer	     dhcp服务器开关；0：关闭；1：开启
 * @property {String} wanMode		     广域网模式，0：静态IP,1：DHCP,3：PPPoE,4：PPTP,6：L2TP
 * @property {String} wanConnStatus	     广域网连接状态：connected：已连接
 * @property {String} wanIp			     广域网IP地址
 * @property {String} wanMask		     广域网子网掩码
 * @property {String} wanGw			     广域网默认网关
 * @property {String} wanMode		     广域网模式
 * @property {String} wanMac		     广域网MAC地址
 * @property {String} priDns		     首选Dns
 * @property {String} secDns		     备选Dns
 * @property {String} wanConnTime	     广域网连接时间
 * @property {String} wifiDualband	     双频支持；1：双频；0：单频
 * @property {String} wifiOff5g		     5G 主SSID无线开关；0：开启；1：关闭
 * @property {String} band5g		     5G 频段
 * @property {String} channel5g		     5G 无线信道
 * @property {String} autoChannel5g	     5G 自动信道
 * @property {String} ssid5g1		     5G 主SSID
 * @property {String} bssid5g1		     5G主SSID MAC地址
 * @property {String} staNum5g1		     5G主SSID 客户端连接数
 * @property {String} ssid5g2		     5G多AP的SSID1
 * @property {String} bssid5g2		     5G多AP MAC地址1
 * @property {String} staNum5g2		     5G多AP的SSID1 客户端连接数
 * @property {String} ssid5g3		     5G多AP的SSID2
 * @property {String} bssid5g3		     5G多AP的SSID2 MAC地址2
 * @property {String} staNum5g3		     5G多AP的SSID2 客户端连接数
 * @property {String} staNum5g		     5G无线最大连接数
 * @property {String} authMode5g	     无线5G加密方式
 * @property {String} encrypType5g	     5G密码类型
 * @property {String} key5g1		     5G 主SSID密码
 * @property {String} key5g2		     5G 多AP的SSID1密码
 * @property {String} key5g3		     5G 多AP的SSID2密码
 * @property {String} wifiOff5g2	     5G多SSID1无线开关；0：开启；1：关闭
 * @property {String} wifiOff5g3	     5G多SSID2无线开关；0：开启；1：关闭
 * @property {String} apcliEnable5g		 5G中继开关，0：开启；1：关闭
 * @property {String} apcliSsid5g		 5G中继上级SSID
 * @property {String} apcliBssid5g		 5G中继上级Mac地址
 * @property {String} apcliAuthMode5g	 5G中继上级加密方式
 * @property {String} apcliEncrypType5g	 5G中继上级密码类型
 * @property {String} apcliKey5g		 5G中继上级密码
 * @property {String} apcliStatus5g		 5G中继状态
 * @property {String} apcliSignal5g		 5G中继信号
 * @property {String} wifiOff		     2.4G无线开关：0 启用，1 禁用
 * @property {String} bssidNum		     2.4G无线Mac位数
 * @property {String} band			     2.4G无线频段
 * @property {String} channel		     2.4G无线信道
 * @property {String} autoChannel	     2.4G自动信道
 * @property {String} ssid1			     2.4G主SSID
 * @property {String} bssid1		     2.4G主SSID的MAC地址
 * @property {String} staNum1		     2.4G主SSID的客户端连接数
 * @property {String} ssid2			     2.4G多AP的SSID1
 * @property {String} bssid2		     2.4G多AP的SSID1的MAC
 * @property {String} staNum2		     2.4G多AP的SSID1的客户连接数
 * @property {String} ssid3			     2.4G多AP的SSID2
 * @property {String} bssid3		     2.4G多AP的SSID2的MAC
 * @property {String} staNum3		     2.4G多AP的SSID2的客户端连接数
 * @property {String} staNum		     2.4G无线最大连接数
 * @property {String} authMode		     2.4G SSID加密方式
 * @property {String} encrypType	     2.4G SSID加密类型
 * @property {String} key1			     2.4G 主SSID密码
 * @property {String} key2			     2.4G 多AP的SSID1密码
 * @property {String} key3			     2.4G 多AP的SSID2密码
 * @property {String} wifiOff2		     2.4G多SSID1无线开关；0：开启；1：关闭
 * @property {String} wifiOff3		     2.4G多SSID2无线开关；0：开启；1：关闭
 * @property {String} apcliEnable	     2.4G中继开关；0：开启；1：关闭
 * @property {String} apcliSsid		     2.4G中继上级SSID
 * @property {String} apcliBssid	     2.4G中继上级Mac地址
 * @property {String} apcliAuthMode		 2.4G中继上级加密方式
 * @property {String} apcliEncrypType	 2.4G中继上级加密类型
 * @property {String} apcliKey			 2.4G中继上级加密密码
 * @property {String} apcliStatus		 2.4G中继连接状态
 * @property {String} apcliSignal		 2.4G中继信号强度
 * @property {String} operationMode		 系统模式 上网模式。0：网关模式，1：桥模式，2：中继模式，3：wisp模式
 * @property {String} wanTx		         wan 口发送速率
 * @property {String} wanRx		         wan 口接收速率
 * @property {String} lanTx		         lan 口发送速率
 * @property {String} lanRx		         lan 口接收速率
 * @property {String} wlanTx5g	         无线5G发送速率
 * @property {String} wlanRx5g	         无线5G接收速率
 * @property {String} wlanTx	         无线2.4G发送速率
 * @property {String} wlanRx	         无线2.4G接收速率
 *
 * @example
 * request:
 * {
*       "topicurl" : "getSysStatusCfg",
* }
 * response:
 * {
 *   "portlinkBt":	1,
 *   "portLinkStatus":	"0,1,0,0,1,",
 *   "upTime":	"9;7;20;30",
 *   "fmVersion":	"V5.9c.680",
 *   "productName":	"A3000RU",
 *   "hardModel":	"04325",
 *   "buildTime":	"Sep 13 2017 16:13:07",
 *   "multiLangBt":	0,
 *   "languageType":	"vn",
 *   "customerUrl":	"www.totolink.net",
 *   "apAcBt":	"0",
 *   "lanIp":	"192.168.0.5",
 *   "lanMask":	"255.255.255.0",
 *   "lanMac":	"F4:28:54:00:40:E2",
 *   "dhcpServer":	2,
 *   "staticIp":	"192.168.1.5",
 *   "staticMask":	"255.255.255.0",
 *   "staticGw":	"192.168.1.1",
 *   "wanMode":	1,
 *   "wanConnStatus":	"connected",
 *   "wanIp":	"192.168.1.4",
 *   "wanMask":	"255.255.255.0",
 *   "wanGw":	"192.168.1.1",
 *   "wanMac":	"F4:28:54:00:40:E3",
 *   "priDns":	"192.168.1.1",
 *   "secDns":	"0.0.0.0",
 *   "wanConnTime":	"0;0;0;45",
 *   "wifiDualband":	1,
 *   "wifiOff5g":	0,
 *   "band5g":	14,
 *   "channel5g":	161,
 *   "autoChannel5g":	161,
 *   "ssid5g1":	"TOTOLINK_A3000RU_5G",
 *   "bssid5g1":	"F4:28:54:00:40:E2",
 *   "staNum5g1":	0,
 *   "ssid5g2":	"111111111",
 *   "bssid5g2":	"F4:28:54:00:40:E4",
 *   "staNum5g2":	0,
 *   "ssid5g3":	"22222222222",
 *   "bssid5g3":	"F4:28:54:00:40:E5",
 *   "staNum5g3":	0,
 *   "staNum5g":	0,
 *   "authMode5g":	"WPAPSKWPA2PSK;WPAPSKWPA2PSK;NONE",
 *   "encrypType5g":	"TKIPAES;TKIPAES;NONE",
 *   "key5g1":	"12345678",
 *   "wifiOff5g2":	0,
 *   "wifiOff5g3":	0,
 *   "apcliEnable5g":	0,
 *   "apcliSsid5g":	"Extender",
 *   "apcliBssid5g":	"00:00:00:00:00:00",
 *   "apcliAuthMode5g":	"NONE",
 *   "apcliEncrypType5g":	"NONE",
 *   "apcliKey5g":	"",
 *   "apcliStatus5g":	"fail",
 *   "apcliSignal5g":	"null",
 *   "wifiOff":	0,
 *   "bssidNum":	1,
 *   "band":	9,
 *   "channel":	0,
 *   "autoChannel":	3,
 *   "ssid1":	"TOTOLINK_0040E2",
 *   "bssid1":	"F4:28:54:00:40:E6",
 *   "staNum1":	0,
 *   "ssid2":	"111111111111111",
 *   "bssid2":	"F4:28:54:00:40:E7",
 *   "staNum2":	0,
 *   "ssid3":	"3333333333",
 *   "bssid3":	"F4:28:54:00:40:E8",
 *   "staNum3":	0,
 *   "staNum":	0,
 *   "authMode":	"WPAPSKWPA2PSK;NONE;WPAPSKWPA2PSK",
 *   "encrypType":	"TKIPAES;NONE;TKIPAES",
 *   "key1":	"12345678",
 *   "wifiOff2":	0,
 *   "wifiOff3":	0,
 *   "apcliEnable":	1,
 *   "apcliSsid":	"Extender",
 *   "apcliBssid":	"00:00:00:00:00:00",
 *   "apcliAuthMode":	"NONE",
 *   "apcliEncrypType":	"NONE",
 *   "apcliKey":	"",
 *   "apcliStatus":	"fail",
 *   "apcliSignal":	"null",
 *   "operationMode":	0,
 *   "wanTx":	617,
 *   "wanRx":	714,
 *   "lanTx":	973,
 *   "lanRx":	1059,
 *   "wlanTx5g":	71,
 *   "wlanRx5g":	148,
 *   "wlanTx":	158,
 *   "wlanRx":	3357
 * }
 */
uiPost.prototype.getSysStatusCfg = function(postVar,callback){
    this.topicurl = 'getSysStatusCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/syscfg.json';
    }
    return this.post(postVar,callback);
};

/**
 * getEasyWizardCfg 主题
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-27
 * @property {String} wanMode       连接模式。0：静态IP，1：DHCP，2：PPPOE拨号                  
 * @property {String} staticIp          静态 IP地址      
 * @property {String} staticMask        静态 子网掩码      
 * @property {String} staticGw          静态 网关      
 * @property {String} priDns         首选dns地址      
 * @property {String} secDns         备用dns地址     
 * @property {String} wanConnStatus   连接状态   
 * @property {String} ssid5g        wifi 5g 账号密码
 * @property {String} wpakey5g      wifi 5g 账号密码
 * @property {String} ssid          wifi 2.4g 账号密码
 * @property {String} wpakey        wifi 2.4g 账号密码
 * 
 * @property {String} xxxxx 其他的参数(还有很多参数不知道意思,但是你们要写对咯。)
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getEasyWizardCfg"
 * }
 * response:
 * {
 *    "wanMode":  1,
 *    "staticIp": "172.1.1.1",
 *    "staticMask":  "255.255.255.0",
 *    "staticGw": "172.1.1.254",
 *    "priDns":  "114.114.114.114",
 *    "secDns":  "0.0.0.0",
 *    "wanConnStatus":  "disconnected",
 *    "l2tpMode": 0,
 *    "l2tpFlag": 0,
 *    "pptpMode": 0,
 *    "pptpFlag": 0,
 *    "pptpMppe": 0,
 *    "pptpMppc": 0,
 *    "lanIp":  "192.168.0.1",
 *    "l2tpServerIp": "172.1.1.1",
 *    "l2tpIp": "172.1.1.2",
 *    "l2tpMask":  "255.255.255.0",
 *    "l2tpGw": "0.0.0.0",
 *    "pptpServerIp": "172.1.1.1",
 *    "pppoeUser":  "",
 *    "pppoePass":  "",
 *    "pptpIp": "172.1.1.2",
 *    "pptpMask":  "255.255.255.0",
 *    "pptpGw": "0.0.0.0",
 *    "l2tpUser": "",
 *    "l2tpPass": "",
 *    "l2tpServer": "",
 *    "pptpUser": "",
 *    "pptpPass": "",
 *    "pptpServer": "",
 *    "multiLangBt": 1,
 *    "helpBt":  1,
 *    "languageType": "cn",
 *    "productName":  "A800RE",
 *    "fmVersion":  "V5.9c.821",
 *    "title":  "TOTOLINK",
 *    "helpUrl":  "www.totolink.cn",
 *    "hardModel":  "",
 *    "wanIp":  "0.0.0.0",
 *    "wanMask":  "0.0.0.0",
 *    "wanGw":  "0.0.0.0",
 *    "wanAutoDetectBt":  0,
 *    "pptpBt": 0,
 *    "l2tpBt": 0,
 *    "wifiDualband": 1,
 *    "ssid5g": "TOTOLINK_A800RE_5G",
 *    "wpakey5g": "22222222",
 *    "ssid": "TOTOLINK_A800RE",
 *    "wpakey": "33333333",
 *  }
 */
uiPost.prototype.getEasyWizardCfg = function(postVar,callback){
    this.topicurl = 'getEasyWizardCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 一键设置的保存数据    
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-11-02
 * @example
 * request:
 * {
 *  "topicurl" : "setEasyWizardCfg",
 *  "wanMode" : 1,
 *  "ssid" : "TOTOLINK_A800RE",
 *  "ssid5g" : "TOTOLINK_A800RE_5G",
 *  "wpakey" : "33333333",
 *  "wpakey5g" : "22222222",
 * }
 * response:
 * {
 *   成功的提示？？？
 * }
 */
uiPost.prototype.setEasyWizardCfg = function(postVar,callback){
    this.topicurl = 'setEasyWizardCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getPasswordCfg     获取登录密码
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-03
 * @property {String} admuser       管理员的用户名
 * @property {String} admpass       管理员的密码
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getPasswordCfg"
 * }
 * response:
 *{
 *	
 *	 "admuser":"admin",
     "admpass":"admin"
 *	
 *}
 */
uiPost.prototype.getPasswordCfg = function(postVar,callback){
    this.topicurl = 'getPasswordCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/changepwd_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setPasswordCfg     设置登录密码   
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} admuser          管理员的用户名
 * @param {String} admpass          管理员的新密码
 * @property {String} success       响应状态：true：响应成功，false：响应失败
 * @property {String} error         错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime         等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *  "topicurl" : "setPasswordCfg",
 *  "admuser":"admin",
 *  "admpass":"admin"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setPasswordCfg = function(postVar,callback){
    this.topicurl = 'setPasswordCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getRemoteCfg    获取远程管理配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 * @param    {String} topicurl       主题
 * @property {String} remoteEnabled   远程管理开关 【0：禁用，1：启用】
 * @property {String} port       远程管理访问端口
 * @property {String} csid       软件CSID
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getRemoteCfg"
 * }
 * response:
 * {
 *   "remoteEnabled":	1,
 *   "port":	6555,
 *   "csid":	"CS18FR"
 * }
* */
uiPost.prototype.getRemoteCfg = function(postVar,callback){
    this.topicurl = 'getRemoteCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/remoteinfo.json';
    }
    return this.post(postVar,callback);
};

/**
 * setRemoteCfg 主题  （设置远程管理配置）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 *
 * @param {String} remoteEnabled  远程管理开关 【0：禁用，1：启用】
 * @param {String} port           远程管理端口号
 *
 * @property {String} success     响应状态：true：响应成功，false：响应失败
 * @property {String} error       错误
 * @property {String} lan_ip      局域网IP
 * @property {String} wtime       等待时间
 * @property {String} reserv      返回页面（参数未知）
 *
 * @example
 * request:
 * {
 *   "remoteEnabled":1,
 *   "port":6555,
 *   "topicurl":"setRemoteCfg"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setRemoteCfg = function(postVar,callback){
    this.topicurl = 'setRemoteCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/remoteinfo.json';
    }
    return this.post(postVar,callback);
};

/**
 * getNTPCfg 主题
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} currentTime		当前时间
 * @property {String} tz				时区
 * @property {String} ntpServerIp		ntp服务器
 * @property {String} ntpClientEnabled	自动同步ntp：0：不勾选，1：勾选
 * @property {String} ntpHostFlag	 	是否使用本机本机时间
 * @property {String} languageType		语言类型
 * @property {String} operationMode		工作模式  
 * @property {String} apAcBt		是否是AP    
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getNTPCfg"
 * }
 * response:
 * {
 *   "tz":	"GMT_000",
	 "ntpServerIp":	"time.nist.gov",
	 "ntpClientEnabled":	1,
	 "ntpHostFlag":	0,
	 "currentTime":	"Fri Nov 3 00:48:18 GMT 2017",
	 "languageType":"cn",
	 "operationMode":	0,
	 "apAcBt":	0
 * }
* */  
uiPost.prototype.getNTPCfg = function(postVar,callback){
    this.topicurl = 'getNTPCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/time_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setNTPCfg 主题 <时间规则的保存数据>   
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} tz				时区
 * @param {String} ntpServerIp		ntp服务器
 * @param {String} ntpClientEnabled	自动同步ntp：0：不勾选，1：勾选  
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *  "topicurl" : "setNTPCfg",
 *  "tz":"GMT_000",
 * 	"ntpServerIp":"time.nist.gov",
 * 	"ntpClientEnabled":"1"}
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setNTPCfg = function(postVar,callback){
    this.topicurl = 'setNTPCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getDDNSCfg  	获取ddns配置
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-03
 * @property {String} topicurl			主题
 * @property {String} ddnsEnabled		DDNS的开关：0：禁用，1：启用
 * @property {String} ddnsProvider		DDNS的供应商， 0：DynDNS, 1：noip, 2：3322.org
 * @property {String} ddnsDomain		DDNS的域名
 * @property {String} ddnsAccount		DDNS的用户名
 * @property {String} ddnsPassword		DDNS的密码
 
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getDDNSCfg"
 * }
 * response:
 * {
 * "ddnsEnabled":	0,
 * "ddnsProvider":	2,
 * "ddnsDomain":	"host.dyndns.org",
 * "ddnsAccount":	" ",
 * "ddnsPassword":	" "
 * }
* */  
uiPost.prototype.getDDNSCfg = function(postVar,callback){
    this.topicurl = 'getDDNSCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/ddns_info.json';
    }
    return this.post(postVar,callback);
};
/**
 * getDDNSStatus 主题    获取ddns配置
 * @Author   amy       <Amy_wei@carystudio.com>
 * @DateTime 2017-11-03
 * @property {String} topicurl          主题
 * @property {String} ddnsIp          DDNS的公共地址
 * @property {String} ddnsStatus      DDNS的连接状态，success：成功，fail:失败
 
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getDDNSStatus"
 * }
 * response:
 * {
 * "ddnsIp":   "",
 * "ddnsStatus":  "fail",
 * }
* */  
uiPost.prototype.getDDNSStatus = function(postVar,callback){
    this.topicurl = 'getDDNSStatus';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/ddns_status.json';
    }
    return this.post(postVar,callback);
};

/**
 * setDDNSCfg 主题    
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} ddnsEnabled		DDNS的开关：0：禁用，1：启用
 * @param {String} ddnsProvider		DDNS的供应商， 0：DynDNS, 1：noip, 2：3322.org
 * @param {String} ddnsDomain		DDNS的域名
 * @param {String} ddnsAccount		DDNS的用户名
 * @param {String} ddnsPassword		DDNS的密码
 * @property {String} success       响应状态：true：响应成功，false：响应失败
 * @property {String} error         错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime         等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *  "topicurl" : "setDDNSCfg",
 *  "ddnsEnabled":	0,
 *  "ddnsProvider":	2,
 *  "ddnsDomain":	"host.dyndns.org",
 *  "ddnsAccount":	" ",
 *  "ddnsPassword":	" "
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
 uiPost.prototype.setDDNSCfg = function(postVar,callback){
    this.topicurl = 'setDDNSCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getRebootScheCfg 主题 获取RebootSche配置
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} topicurl			主题
 * @property {String} scheEn			状态：0：禁用，1：启用
 * @property {String} scheWeek			周
 * @property {String} scheHour			小时
 * @property {String} scheMin			分钟
 * @property {String} ntpEnabled		启用自动同步
 * @example
 * request:
 * {
 *    topicurl:"getRebootScheCfg"
 * }
 * response:
 * {
 *   "scheEn":	0,
 *   "scheWeek":	0,
 *   "scheHour":	0,
 *   "scheMin":	0,
 *   "ntpEnabled":	1
 * }
* */
uiPost.prototype.getRebootScheCfg = function(postVar,callback){
    this.topicurl = 'getRebootScheCfg';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = "/data/schedule_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * setRebootScheCfg  主题 <定时重启的保存数据>
 * @Author   Bob     <Bob_huang@carystudio>
 * @DateTime 2017-11-02
 * @property {String} topicurl			主题
 * @property {String} scheEn			状态：0：禁用，1：启用
 * @property {String} scheWeek			周
 * @property {String} scheHour			小时
 * @property {String} scheMin			分钟
 * @example
 * request:
 * {
 *   "topicurl":"setRebootScheCfg"
 *   "scheEn":	1,
 *   "scheWeek":	0,
 *   "scheHour":	0,
 *   "scheMin":	0
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
 uiPost.prototype.setRebootScheCfg = function(postVar,callback){
    this.topicurl = 'setRebootScheCfg';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = "/data/schedule_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * getMiniUPnPConfig  主题 （获取miniunp表格信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-08
 *
 * @property {String} upnpEnabled       upnp启用/禁用。0：禁用，1：启用
 * @property {String} getUpnpTable      unpn表格信息  
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getMiniUPnPConfig"
 * }
 * response:
 *{
 *	"upnpEnabled":	"1",
 *	"getUpnpTable":	"none"
 *}
 */
uiPost.prototype.getMiniUPnPConfig = function(postVar,callback){
    this.topicurl = 'getMiniUPnPConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setMiniUPnPConfig  主题 （获取miniunp表格信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-08
 * @param {String} upnpEnabled       upnp启用/禁用。0：禁用，1：启用
 * @property {String} success      响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip       局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv       返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"setMiniUPnPConfig"
 *    "upnpEnabled":"1"
 * }
 * response:
 *{
 *	 "success":	true,
 *	 "error":	null,
 *	 "lan_ip":	"192.168.133.1",
 *	 "wtime":	"0",
 *	 "reserv":	"reserv"
 *}
 */
uiPost.prototype.setMiniUPnPConfig = function(postVar,callback){
    this.topicurl = 'setMiniUPnPConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * CloudSrvVersionCheck  主题   （检测云升级信息）    
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-07
 * @property {String} cloudFwStatus    检测云升级。New：已是最新版本，UnNet:没有网络，Update：有可更新版本
 * @property {String} newVersion       新的固件版本
 * @example
 * request:
 * {
 *  "topicurl" : "CloudSrvVersionCheck",
 *
 * }
 * response:
 * {
 *    "cloudFwStatus":"New",
 *    "newVersion":"V6.2c.464"
 * }
 */
uiPost.prototype.CloudSrvVersionCheck = function(postVar,callback){
    this.topicurl = 'CloudSrvVersionCheck';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/firmware_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * CloudACMunualUpdate  主题   云检测固件手动升级  
 * @Author   amy       <amy@carystudio.com>
 * @DateTime 2017-11-07
 * @property {String} Flags         带配置升级的标志，1：带，0：不带
 * @property {String} FileName      固件名称
 * @example
 * request:
 * {
 *  "topicurl" : "CloudACMunualUpdate",
 *  
 * }
 * response:
 * {
 *    "Flags":"1",
 *    "FileName":""
 * }
 */
uiPost.prototype.CloudACMunualUpdate = function(postVar,callback){
    this.topicurl = 'CloudACMunualUpdate';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setUpgradeFW  主题   （获取升级信息）    
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-07
 * @param {String} FileName            文件名 
 * @param {String} ContentLength        内容大小
 * @property {String} upgradeStatus     上传状态。0：上传失败，1：上传成功
 * @property {String} upgradeERR        错误信息
 * @example
 * request:
 * {
 *  "topicurl" : "setUpgradeFW",
 *  "FileName":"",
 *  "ContentLength":""
 * }
 * response:
 * {
 *	"upgradeStatus":"1",
 *	"upgradeERR":"MM_FwFileInvalid"
 * }
 */
uiPost.prototype.setUpgradeFW = function(postVar,callback){
    this.topicurl = 'setUpgradeFW';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * FirmwareUpgrade   主题   获取固件信息
 * add maxSize and upgreadeAction by Yexk @2018-1-23
 * @Author   karen       <karen@carystudio.com>
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-11-07
 * @param    {Function} topicurl              主题
 * @property {String} fmVersion      当前软件版本
 * @property {String} buildTime      当前软件发布时间
 * @property {String} cloudFw        是否支持云升级。0:no， 1:yes
 * @property {String} cloudFwStatus        云状态 
 * @property {String} flashSize       flash固件的大小
 * @property {String} hardModel           硬件型号
 * @property {String} lanIp           IP地址
 * @property {String} maxSize          校验文件最大值。单位：kb,以1000位进制数。
 * @property {String} upgradeAction      返回当前升级固件的url，返回完整的URL
 * @property {String} setUpgradeFW      设置升级检测主题。默认：0调用的是CloudSrvVersionCheck主题。如果1则调用setUpgradeFW主题
 * @example
 * request:
 * {
 *    topicurl:"FirmwareUpgrade"
 *    
 * }
 * response:
 *  {
 *   "fmVersion":"0",
 *   "buildTime":"Oct 17 2017 10:34:08",
 *   "cloudFw":"0",
 *	 "cloudFwStatus":"",
 *   "flashSize":"16",
 *   "hardModel":"CS182R",
 *   "lanIp":"192.168.0.1",
 *   "maxSize":"10000",
 *   "upgradeAction":"/cgi-bin/cstecgi.cgi?action=upload&setUpgradeFW",
 *   "setUpgradeFW":1
 *  }
 */
uiPost.prototype.FirmwareUpgrade = function(postVar,callback){
    this.topicurl = 'FirmwareUpgrade';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/firmware_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * SystemSettings 主题  获取系统设置数据
 * add 导入导出的路径配置，最大size by Yexk @2018-1-23
 * @Author   amy       <amy@carystudio.com>
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-12-16
 * @property {String} operationMode        系统模式。0：网关，1：桥模式，2：中继，3：wisp模式
 * @property {String} hardModel        固件
 * @property {String} meshEnabled        mesh的开关
 * @property {String} exportAction        导出的配置路径。
 * @property {String} exportAction        导入的配置路径。
 * @property {String} maxSize           校验文件最大值。单位：kb,以1000位进制数。
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"SystemSettings"
 * }
 * response:
 *{
 *  
 *   "operationMode": 0,
 *   "hardModel":   "",
 *   "meshEnabled":  0,
 *   "exportAction":  '/cgi-bin/ExportSettings.sh',
 *   "importAction":  '/cgi-bin/cstecgi.cgi?action=upload&setting/setUploadSetting',
 *   "maxSize": "100000"
 *}
 */
uiPost.prototype.SystemSettings = function(postVar,callback){
    this.topicurl = 'SystemSettings';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/SystemSettings.json"
    }
    return this.post(postVar,callback);
};    

/**
 * LoadDefSettings 主题  （获取恢复出厂设置信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-07
 * @property {String} success       响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"LoadDefSettings"
 * }
 * response:
 *{
 *	
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   100,
 *   "reserv":  "reserv"
 *	
 *}
 */
uiPost.prototype.LoadDefSettings = function(postVar,callback){
    this.topicurl = 'LoadDefSettings';
    this.async = true; // true:异步，false:同步。
    this.url = "/webapi";
    return this.post(postVar,callback);
};

/**
 * RebootSystem 主题  （获取重启信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-07
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"RebootSystem"
 * }
 * response:
 *{	
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   100,
 *   "reserv":  "reserv"
 *}
 */
uiPost.prototype.RebootSystem = function(postVar,callback){
    this.topicurl = 'RebootSystem';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getSyslogCfg  主题   （提交系统日志状态）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-04
 * @param {String} syslogEnabled     开启日志。1：启用，0：禁用
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getSyslogCfg"
 *    "syslogEnabled":"1"
 * }
 * response:
 *{
 *	
 *}
 */
uiPost.prototype.getSyslogCfg = function(postVar,callback){
    this.topicurl = 'getSyslogCfg';
    this.async = true; // true:异步，false:同步。
	 if (globalConfig.debug) {
        this.url = "/data/syslog_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * setSyslogCfg  主题  （获取系统日志状态）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-04
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"setSyslogCfg"
 * }
 * response:
 *{
 *	 "success":true,
 *   "error":null,
 *   "lan_ip":"192.168.0.1",
 *   "wtime":"0",
 *   "reserv":"reserv"
 *}
 */
uiPost.prototype.setSyslogCfg = function(postVar,callback){
    this.topicurl = 'setSyslogCfg';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = "/data/syslog_info.json";
    }
    return this.post(postVar,callback);
};	

/**
 * clearSyslog  主题   （清除系统日志信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-04
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"clearSyslog"
 * }
 * response:
 *{
 *	 "success":true,
 *   "error":null,
 *   "lan_ip":"192.168.0.1",
 *   "wtime":"0",
 *   "reserv":"reserv"
 *}
 */
uiPost.prototype.clearSyslog = function(postVar,callback){
    this.topicurl = 'clearSyslog';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};	

/**
 * showSyslog  主题   （获取系统日志信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-04
 * @property {String} syslog        日志信息
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"showSyslog"
 * }
 * response:
 *{
 *	 "syslog":"xxxxxx"
 *}
 */
uiPost.prototype.showSyslog = function(postVar,callback){
    this.topicurl = 'showSyslog';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
      //  this.url = "/data/syslog_info.json";
    }
    return this.post(postVar,callback);
};

/**
 * getWiFiBasicConfig 获取WiFiBasic数据
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-04
 * @param    {String} topicurl		主题
 * @param    {String} wifiIdx		无线信息：0:5G 1:2.4G
 * @property {String} wifiOff		状态：0：启用，1：禁用
 * @property {String} channel		信道
 * @property {String} hssid		广播SSID
 * @property {String} regDomain		未知
 * @property {String} bw			频宽
 * @property {String} ntpEnabled	ntp时间同步是否开启
 * @property {String} wifiSchEnabled 未知
 * @property {String} wpakey		WPAPSK加密方式下的密码输入
 * @property {String} countryStr	未知
 * @property {String} ssid			SSID
 * @property {String} band	频段
 * @property {String} authMode		加密方式。0：禁用，1：WEP-开放系统，2：WEP-共享密钥，3：WPA-PSK， 4：WPA2-PSK， 5：WPA/WPA2-PSK
 * @property {String} encrypType	加密类型。
 * @property {String} keyFormat		密码类型。0：Hex，1：ASCII
 * @property {String} wepkey		WEPKEY加密方式下的密码输入
 * @property {String} apcliEnable	未知
 * @property {String} channelDfs	未知
 * @property {String} wifiDualband	未知
 * @property {String} countryBt 未知
 * @property {String} hardModel		未知
 * @property {String} apAcBt	是否支持ApAc
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getWiFiBasicConfig" ,
 *    "wifiIdx":  "0"
 * }
 * response:
 * {
 *  "wifiOff":	0,
 *  "channel":	149,
 *  "hssid":	0,
 *  "regDomain":	13,
 *  "bw":	1,
 *  "ntpEnabled":	1,
 *  "wifiSchEnabled":	1,
 *  "wpakey":	12345678,
 *  "countryStr":	"US",
 *  "ssid":	"TOTOLINK_A800R_5G",
 *  "band":	14,
 *  "authMode":	"WPAPSKWPA2PSK",
 *  "encrypType":	"TKIPAES",
 *  "keyFormat":	0,
 *  "wepkey":	"TOTOLINK_A800R_5G",
 *  "apcliEnable":	0,
 *  "channelDfs":	1,
 *  "wifiDualband":	1,
 *  "countryBt":	0,
 *  "hardModel":	"",
 *  "apAcBt":	0
 * }
* */
uiPost.prototype.getWiFiBasicConfig = function(postVar,callback){
    this.topicurl = 'getWiFiBasicConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setWiFiBasicConfig  设置WiFiBasic配置
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-04
 * @param {String} ssid			SSID
 * @param {String} band			频段
 * @param {String} channel		信道
 * @param {String} hssid		广播SSID
 * @param {String} authMode		加密方式。0：禁用，1：WEP-开放系统，2：WEP-共享密钥，3：WPA-PSK， 4：WPA2-PSK， 5：WPA/WPA2-PSK
 * @param {String} encrypType	加密类型。
 * @param {String} keyFormat	密码类型。0：Hex，1：ASCII
 * @param {String} wepkey		WEPKEY加密方式下的密码输入
 * @param {String} wpakey		WPAPSK加密方式下的密码输入
 * @param {String} bw			频宽
 * @param {String} countryStr	未知
 * @param {String} wscEnabled	未知
 * @param {String} addEffect	未知
 * @param {String} wifiIdx		无线信息：0:5G 1:2.4G
 * @property {String} success
 * @property {String} error
 * @property {String} lan_ip
 * @property {String} wtime
 * @property {String} reserv
 * @example
 * request:
 * {
* 	"topicurl":"setWiFiBasicConfig",
* 	"ssid":"TOTOLINK_A800R_5G",
* 	"band":"14",
* 	"channel":"149",
* 	"hssid":0,
* 	"authMode":"NONE",
* 	"encrypType":"NONE",
* 	"keyFormat":1,
* 	"wepkey":"",
* 	"wpakey":"",
* 	"bw":1,
* 	"countryStr":"US",
* 	"wscEnabled":0,
* 	"addEffect":0,
* 	"wifiIdx":0
* }
 * response:
 * {
*  "success":	"true",
*  "error":	null,
*  "lan_ip":	"192.168.0.1",
*  "wtime":	"10",
*  "reserv":	"reserv"
* }
 */
uiPost.prototype.setWiFiBasicConfig = function(postVar,callback){
    this.topicurl = 'setWiFiBasicConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiMultipleConfig 主题  （获取添加AP的信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx            wifi类型。0：2.4G,1：5G
 * @property {String} wifiOff         wifii的启用。1 : Disabled Wlan, 0 : Enalbe Wlan
 * @property {String} bssidNum         当前ssid数目
 * @property {String} multiApNum          支持添加多AP的数目
 * @property {String} operationMode              上网模式。0：bridge，1：repeater，2：wisp，3：gateway，4：mesh，5：client
 * @property {Number} ssidOff             1 : Disabled ssid, 0 : Enable
 * @property {Number} ssid                SSID配置
 * @property {Number} vlanId              无线vlan的标识符。 range：0-4094, 0： disable
 * @property {Number} hssid             隐藏ssid  1 : don't hide, 0 : hide.
 * @property {Number} authMode          加密方式。 支持{NONE, OPEN, SHARED, WPAPSK, WPA2PSK, WPAPSKWPA2PSK}, AP和CPE类产品只支持 {WPAPSKWPA2PSK,NONE}
 * @property {Number} encrypType          加密类型。 支持{NONE,WEP,AES,TKIP,TKIPAES}
 * @property {Number} keyFormat             密码类型。  1 : ACII code, 0 : hex
 * @property {Number} key                 密码
 * @return   {Object}
 * @example
 * request:
 * {
 *     topicurl:"getWiFiMultipleConfig"
 *	   "wifiIdx":"0"
 * }
 * response:
 *{
 *	 "wifiOff":  "0",
 *   "bssidNum": "2",
 *   "multiApNum":   "2",
 *   "operationMode":   "0",
 *   "ssids":    [{
 *          "ssidOff":  "0",
 *           "ssid": "mytest",
 *           "vlanId":   "0",
 *           "hssid": "0",
 *           "authMode":   "NONE",
 *           "encrypType":   "NONE",
 *           "keyFormat":  "1",
 *           "key":  ""
 *       }],
 *
 *}
 */
uiPost.prototype.getWiFiMultipleConfig = function(postVar,callback){
    this.topicurl = 'getWiFiMultipleConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setWiFiMultipleConfig  主题   （提交添加AP的信息）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx       wifi类型。0：2.4G,1：5G
 * @param {String} ssidIdx         修改的索引
 * @param {String} bssidNum          当前ssid数目
 * @param {String} ssid        SSID配置
 * @param {String} hssid        隐藏ssid  1 : don't hide, 0 : hide.
 * @param {String} vlanId        无线vlan的标识符。 range：0-4094, 0： disable
 * @param {String} authMode        加密方式。 支持{NONE, OPEN, SHARED, WPAPSK, WPA2PSK, WPAPSKWPA2PSK}, AP和CPE类产品只支持 {WPAPSKWPA2PSK,NONE}
 * @param {String} encrypType         加密类型。 支持{NONE,WEP,AES,TKIP,TKIPAES}
 * @param {String} keyFormat        密码类型。  1 : ACII code, 0 : hex
 * @param {String} key        密码
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "topicurl" : "setWiFiMultipleConfig",
 *   "wifiIdx" : "0"
 *   "ssidIdx":  "0",
 *   "bssidNum": "2",
 *   "ssid": "mytest",
 *   "hssid": "0",
 *   "vlanId":   "0",
 *   "authMode":   "NONE",
 *   "encrypType":   "NONE",
 *   "keyFormat":  "1",
 *   "key":  ""
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiMultipleConfig = function(postVar,callback){
    this.topicurl = 'setWiFiMultipleConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delWiFiMultipleConfig 主题  （删除多AP列表）
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} wifiIdx       wifi状态。0 : 2.4G ,1 : 5G
 * @param {String} ssidNo        删除指定的AP
 * @param {String} bssidNum       删除之后的多AP数目
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知） 
 * @example
 * request:
 * {
 *  "topicurl" : "delWiFiMultipleConfig",
 *   "wifiIdx" : "0",
 *   "ssidNo" : "1,2",
 *   "bssidNum" : "1"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delWiFiMultipleConfig = function(postVar,callback){
    this.topicurl = 'delWiFiMultipleConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiAdvancedConfig 主题 （获取 无线设置->高级设置 中的配置）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} wifiIdx       无线信息：0:5G 1:2.4G
 * @property {String} bgProtection               BG保护模式
 * @property {String} beaconPeriod               信标
 * @property {String} htBSSCoexistence               20/40M共存
 * @property {String} dtimPeriod               数据标率
 * @property {String} fragThreshold               分片域值
 * @property {String} rtsThreshold               RTS域值
 * @property {String} txPreamble               前导帧类型
 * @property {String} wmmCapable               Wi-Fi多媒体(WMM)
 * @property {String} noForwarding               AP隔离
 * @property {String} txPower               发射功率
 * @property {String} band               无线模式：9和6:20/40M 功能显示（具体作用未知）
 * @property {String} wifiDualband               双频标志：1：双频 （其他参数未知）
 *
 * @example
 * request:
 * {
 *      "wifiIdx":1,
 *      "topicurl":"getWiFiAdvancedConfig"
 *  }
 * response:
 * {
 *    "bgProtection":	1,
 *    "beaconPeriod":	100,
 *    "htBSSCoexistence":	0,
 *    "dtimPeriod":	1,
 *    "fragThreshold":	2346,
 *    "rtsThreshold":	2347,
 *    "txPreamble":	1,
 *    "wmmCapable":	1,
 *    "noForwarding":	0,
 *    "txPower":	0,
 *    "band":	14,
 *    "wifiDualband":	1
 * }
 */
uiPost.prototype.getWiFiAdvancedConfig = function(postVar,callback){
    this.topicurl = 'getWiFiAdvancedConfig';
    this.async = true; // true:异步，false:同步。
    if(postVar.wifiIdx == 1){   //1: 2.4G
    }else{  //0: 5G
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiAdvancedConfig 主题 （设置 高级设置 配置）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} bgProtection       BG保护模式
 * @param {String} beaconPeriod       信标
 * @param {String} dtimPeriod       数据标率
 * @param {String} txPreamble       前导帧类型
 * @param {String} fragThreshold       分片域值
 * @param {String} rtsThreshold       RTS域值
 * @param {String} txPower       发射功率
 * @param {String} noForwarding       AP隔离
 * @param {String} htBSSCoexistence       20/40M共存
 * @param {String} wmmCapable       Wi-Fi多媒体(WMM)
 * @param {String} wifiIdx       无线信息：0: 5G，1: 2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 *
 * @example
 * request:
 * {
 *      "bgProtection":1,
 *      "beaconPeriod":100,
 *      "dtimPeriod":1,
 *      "txPreamble":1,
 *      "fragThreshold":2346,
 *      "rtsThreshold":2347,
 *      "txPower":0,
 *      "noForwarding":0,
 *      "htBSSCoexistence":0,
 *      "wmmCapable":1,
 *      "wifiIdx":1,
 *      "topicurl":"setWiFiAdvancedConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiAdvancedConfig = function(postVar,callback){
    this.topicurl = 'setWiFiAdvancedConfig';
    this.async = true; // true:异步，false:同步。
    if(postVar.wifiIdx == 1){   //0: 2.4G
    }else{  //0: 5G
    }
    return this.post(postVar,callback);
};

/**
 * getWiFiWpsSetupConfig 主题 （获取WPS配置信息）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx       无线信息：0：5G , 1：2.4G
 * @property {String} wifiOff       未知
 * @property {String} wscEnabledFlag       WPS标志位
 * @property {String} wscEnabled       WPS状态：0：禁用，1：启用
 * @property {String} wscMode       未知
 * @property {String} wscPinMode       未知
 * @property {String} wscPin       未知
 *
 * @example
 * request:
 * {
 *      "topicurl":"getWiFiWpsSetupConfig",
 *      "wifiIdx":"0"
 * }
 * response:
 * {
*   "wifiOff":	0,
*   "wscEnabledFlag":	0,
*   "wscEnabled":	1,
*   "wscMode":	0,
*   "wscPinMode":	0,
*   "wscPin":	"21595684"
* }
 */
uiPost.prototype.getWiFiWpsSetupConfig = function(postVar,callback){
    this.topicurl = 'getWiFiWpsSetupConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/wps_getconf.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiWpsSetupConfig 主题 （启用WPS）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx       无线信息：0：5G , 1：2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 *
 * @example
 * request:
 * {
*      "wifiIdx":  1,
*      "topicurl":"setWiFiWpsSetupConfig",
* }
 * response:
 * {
*   "success": true,
*   "error":   null,
*   "lan_ip":  "192.168.0.5",
*   "wtime":   0,
*   "reserv":  "reserv"
* }
 */
uiPost.prototype.setWiFiWpsSetupConfig = function(postVar,callback){
    this.topicurl = 'setWiFiWpsSetupConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiWpsConfig 主题 （WPS进行中）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx       无线信息：0：5G , 1：2.4G
 * @property {String} wscConfigured       未知
 * @property {String} wscSsid       SSID
 * @property {String} wscAuthMode       加密方式
 * @property {String} wscEncrypType       加密类型
 * @property {String} wscKeyIdx       未知
 * @property {String} wscKey       未知
 * @property {String} wscStatus       WPS状态：Start WSC Process/Send M2(未知)：进行中，Not used：未使用, Idle:空闲, WSC Fail:WPS失败
 * @property {String} wscResult       结果
 * @property {String} wscStatusIdx       未知
 *
 * @example
 * request:
 * {
*      "wifiIdx":  1,
*      "topicurl":"getWiFiWpsConfig",
* }
 * response:
 * {
*       "wscConfigured":	"1",
*       "wscSsid":	"TOTOLINK_A3000RU_5G8888",
*       "wscAuthMode":	"WPA2-PSK",
*       "wscEncrypType":	"AES",
*       "wscKeyIdx":	"1",
*       "wscKey":	"",
*       "wscStatus":	"Start WSC Process",
*       "wscResult":	"0",
*       "wscStatusIdx":	"0"
* }
 */
uiPost.prototype.getWiFiWpsConfig = function(postVar,callback){
    this.topicurl = 'getWiFiWpsConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/wps_conf.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiWpsConfig 主题 （打开WPS）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx       无线信息：0：5G , 1：2.4G
 * @param {String} wscMode       作用未知
 * @param {String} wscPinMode       作用未知
 * @param {String} pin       		作用未知
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 *
 * @example
 * request:
 * {
 *      "wifiIdx":  1,
 *      "wscMode":2,
 *      "wscPinMode":0,
 *      "pin":"",
 *      "topicurl":"setWiFiWpsConfig",
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiWpsConfig = function(postVar,callback){
    this.topicurl = 'setWiFiWpsConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiWdsAddConfig 获取WDS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param    {String} wifiIdx        无线信息：0：5G , 1：2.4G
 * @property {String} wifiOff    无线状态。1：禁用，0：启用
 * @property {String} wdsEnable     状态。0：禁用，1：启用 
 * @property {String} wdsList        添加WDS的列表    
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    "wifiIdx":  0,
 *    topicurl:"getWiFiWdsAddConfig"
 * }
 * response:
 * {
 *   "wifiOff":0,
 *   "wdsEnable":"1",
 *   "wdsList":	"F4:28:54:00:37:45;FE:28:54:00:37:EE;F4:28:54:00:37:89",
 * }
* */
uiPost.prototype.getWiFiWdsAddConfig = function(postVar,callback){
    this.topicurl = 'getWiFiWdsAddConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/wds_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiWdsAddConfig  提交WDS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} addEffect   添加的状态  
 * @param {String} wdsEnable    状态。0：禁用，1：启用
 * @param {String} wdsList     添加WDS的列表 
 * @param {String} wifiIdx    无线信息：0：5G , 1：2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "addEffect":	"0",
 *   "wdsEnable":	"1",
 *   "wdsList":	"F4:28:54:00:37:45",
 *   "wifiIdx": "0",
 *   "topicurl":"setWiFiWdsAddConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiWdsAddConfig = function(postVar,callback){
    this.topicurl = 'setWiFiWdsAddConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setWiFiWdsDeleteConfig  删除WDS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} DR0    删除第一条规则
 * @param {String} wifiIdx    无线信息：0：5G , 1：2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "DR0":   0,
 *   "wifiIdx":  "0",
 *   "topicurl":"setWiFiWdsDeleteConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiWdsDeleteConfig = function(postVar,callback){
    this.topicurl = 'setWiFiWdsDeleteConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 

/**
 * getWiFiAclAddConfig 获取Mac认证配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param    {String} wifiIdx   无线信息：0：5G，1:2.4G
 * @property {String} authMode    认证模式。0：禁用，1：白名单，2：黑名单
 * @property {String} authList       添加的认证规则     
 * @return   {Object}
 * @example
 * request:
 * {
 *    "wifiIdx":"0",
 *    topicurl:"getWiFiAclAddConfig"
 * }
 * response:
 * {
 *   "authMode":	2,
 *   "authList":	"F4:28:56:34:51:44;F4:28:56:34:51:22"
 * }
* */
uiPost.prototype.getWiFiAclAddConfig = function(postVar,callback){
    this.topicurl = 'getWiFiAclAddConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/acl_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiAclAddConfig  提交Mac认证数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} authMode    认证模式。0：禁用，1：白名单，2：黑名单
 * @param {String} addEffect       添加的状态
 * @param {String} wifiIdx       无线信息：0：5G，1：2.4G 
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "authMode":  "2",
 *   "addEffect":	"1",

 *   "wifiIdx": "0"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiAclAddConfig = function(postVar,callback){
    this.topicurl = 'setWiFiAclAddConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * setWiFiAclDeleteConfig  删除Mac认证数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} DR0    删除第一条规则
 * @param {String} wifiIdx    无线信息：0：5G，1：2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "DR0":  0,
 *   "wifiIdx":  "0",
 *   "topicurl":"setWiFiAclDeleteConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setWiFiAclDeleteConfig = function(postVar,callback){
    this.topicurl = 'setWiFiAclDeleteConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiScheduleConfig  获取WiFi调度配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-04
 * @property {String} wifiScheduleEn     WiFi调度状态：0：禁用，1：启用
 * @property {String} wifiScheduleNum   WiFi调度规则总个数
 * @property {String} wifiScheduleRule0    第一条规则
 * @property {String} wifiScheduleRule1    第二条规则
 * @property {String} wifiScheduleRule2    第三条规则
 * @property {String} wifiScheduleRule3    第四条规则
 * @property {String} wifiScheduleRule4    第五条规则
 * @property {String} wifiScheduleRule5    第六条规则
 * @property {String} wifiScheduleRule6    第七条规则
 * @property {String} wifiScheduleRule7    第八条规则
 * @property {String} wifiScheduleRule8    第九条规则
 * @property {String} wifiScheduleRule9    第十条规则
 * @example
 * request:
 * {
*   "topicurl":"getWiFiScheduleConfig"
* }
 * response:
 * {
 *	"wifiScheduleEn":	1,
 *	"wifiScheduleNum":	10,
 *	"wifiScheduleRule0":	"1,1,1,0,3,19",
 *	"wifiScheduleRule1":	"0,0,0,0,0,0",
 *	"wifiScheduleRule2":	"0,0,0,0,0,0",
 *	"wifiScheduleRule3":	"0,5,16,9,23,12",
 *	"wifiScheduleRule4":	"0,0,0,0,0,0",
 *	"wifiScheduleRule5":	"0,0,0,0,0,0",
 *	"wifiScheduleRule6":	"0,0,0,0,0,0",
 *	"wifiScheduleRule7":	"0,0,0,0,0,0",
 *	"wifiScheduleRule7":	"0,0,0,0,0,0",
 *	"wifiScheduleRule9":	"0,3,10,58,20,43"
 * }
 */
uiPost.prototype.getWiFiScheduleConfig = function(postVar,callback){
    this.topicurl = 'getWiFiScheduleConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/wifischedule.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWiFiScheduleConfig  设置WiFi调度数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-04
 * @param {String} enable0    第1条规则的状态 0：禁用，1：启用
 * @param {String} week0       第1条规则的周  1：周一，2：周二 以此类推
 * @param {String} startHour0    第1条规则的起始时
 * @param {String} startMinute0    第1条规则的起始分
 * @param {String} endHour0    第1条规则的结束时
 * @param {String} endMinute0    第1条规则的结束分
 * @param {String} wifiScheduleEn   功能总开关， 0：禁用，1：启用
 * @param {String} addEffect   添加的状态
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
*   "enable0":"1",
*   "week0":   "1",
*   "startHour0":"1",
*   "startMinute0":"0",
*   "endHour0":"3",
*   "endMinute0":"19",
*   "enable1":"0"
*   "addEffect":"0",
*   "wifiScheduleEn":"1",
*   "topicurl":"setWiFiScheduleConfig"
* }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
* }
 */
uiPost.prototype.setWiFiScheduleConfig = function(postVar,callback){
    this.topicurl = 'setWiFiScheduleConfig';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
        this.url = '/data/wifischedule.json';
    }
    return this.post(postVar,callback);
};

/**
 * getWiFiApInfo 主题  （获取WIFI信息）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx           无线信息标志：0 ：5G , 1: 2.4G
 * @property {String} operationMode       系统模式：0：网关模式
 * @property {String} channel      信道
 * @property {String} autoChannel      自动信道
 * @property {String} band      频段
 * @property {String} wifiOff1      主SSID状态：0：启用，1：禁用
 * @property {String} ssid1      主SSID
 * @property {String} bssid1      主SSID 的MAC地址
 * @property {String} key1      wifi密码
 * @property {String} staNum1      主SSID客户端连接数
 * @property {String} wifiOff2      多SSID2(多SSID中的第一个)状态：0：启用，1：禁用
 * @property {String} ssid2      ssid2
 * @property {String} bssid2      ssid2 的MAC地址
 * @property {String} key2      ssid2 密码
 * @property {String} staNum2      ssid2 客户端连接数
 * @property {String} wifiOff3      多SSID3(多SSID中的第二个)状态：0：启用，1：禁用
 * @property {String} ssid3      ssid3
 * @property {String} bssid3      ssid3 的MAC地址
 * @property {String} key3      ssid3 密码
 * @property {String} staNum3      ssid3 客户端连接数
 * @property {String} authMode      加密方式：字符串以“;”隔开，第一个是主SSID的加密方式，第二个SSID2,第三个SSID3
 * @property {String} encrypType      加密类型：字符串以“;”隔开，第一个是主SSID的加密类型，第二个SSID2,第三个SSID3
 * @property {String} bssidNum      未知
 * @property {String} apcliEnable      未知
 * @property {String} apcliSsid      未知
 * @property {String} apcliAuthMode      未知
 * @property {String} apcliEncrypType      未知
 * @property {String} apcliKey      未知
 * @property {String} apcliBssid      未知
 * @property {String} apcliStatus      未知
 *
 * @example
 * request:
 * {
*       "wifiIdx":1,
*       "topicurl":"getWiFiApInfo"
* }
 * response:
* {
*     "operationMode":	0,
*     "channel":	161,
*     "autoChannel":	161,
*     "band":	14,
*     "wifiOff1":	0,
*     "ssid1":	"TOTOLINK_A3000RU_5G8888",
*     "bssid1":	"F4:28:54:00:40:E2",
*     "key1":	"12345678",
*     "staNum1":	0,
*     "wifiOff2":	0,
*     "ssid2":	"111111111",
*     "bssid2":	"F4:28:54:00:40:E4",
*     "key2":	"12345678",
*     "staNum2":	0,
*     "wifiOff3":	1,
*     "ssid3":	"TOTOLINK 5G VAP2",
*     "bssid3":	"00:E0:4C:81:86:86",
*     "key3":	"",
*     "staNum3":	0,
*     "authMode":	"WPAPSKWPA2PSK;WPAPSKWPA2PSK;NONE",
*     "encrypType":	"TKIPAES;TKIPAES;NONE",
*     "bssidNum":	3,
*     "apcliEnable":	0,
*     "apcliSsid":	"Extender",
*     "apcliAuthMode":	"NONE",
*     "apcliEncrypType":	"NONE",
*     "apcliKey":	"",
*     "apcliBssid":	"00:00:00:00:00:00",
*     "apcliStatus":	"fail"
* }
 */
uiPost.prototype.getWiFiApInfo = function(postVar,callback){
    this.topicurl = 'getWiFiApInfo';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWiFiStaInfo 主题  （获取连接客户端信息）
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-03
 * @param {String} wifiIdx           无线信息标志：0 ：5G , 1: 2.4G
 * @property {String} 0.0.0.0;14:F6:5A:B3:EC:77;11n;40M;18;98;300;TOTOLINK_A3000RU_5G;68$       返回参数为一个字符串，不是json格式，以"$"符号为一台设备的结束信息
 * @property {String} 第一个参数（0.0.0.0）    未知
 * @property {String} 第二个参数（14:F6:5A:B3:EC:77）    设备MAC
 * @property {String} 第三个参数（11n）    模式
 * @property {String} 第四个参数（40M）    频宽
 * @property {String} 第五个参数（18）    未知
 * @property {String} 第六个参数（98）    信号
 * @property {String} 第七个参数（300）    未知
 * @property {String} 第八个参数（TOTOLINK_A3000RU_5G）    SSID
 * @property {String} 第九个参数（68）    未知
 *
 * @example
 * request:
 * {
*       "wifiIdx":1,
*       "topicurl":"getWiFiStaInfo"
* }
 * response:
 * {
 *     "0.0.0.0;14:F6:5A:B3:EC:77;11n;40M;18;98;300;TOTOLINK_A3000RU_5G;68$"
 * }
 */
uiPost.prototype.getWiFiStaInfo = function(postVar,callback){
    this.topicurl = 'getWiFiStaInfo';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 获取MAC认证的MAC列表
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-07
 * @return   {null}
 * @property {String} enable   原来的意思是是否开启？还是什么？新版本中建议去掉
 *
 * @property {String} obj       一个对象带表一条数据
 * @property {String} obj.table   标题
 * @property {String} obj.mac   MAC地址
 * @property {String} obj.ip    IP地址
 * @example
 * request:
 * {
*    topicurl:"getWiFiIpMacTable"
*
* }
 * response:
 *  [
 *      {
 *          "table":"rules"
 *      },
 *      {
 *          "mac":"14:F6:5A:B3:EC:77",
 *          "ip":"0.0.0.0"
 *      }
 *  ]
 */
uiPost.prototype.getWiFiIpMacTable = function(postVar,callback){
    this.topicurl = 'getWiFiIpMacTable';
    this.async = true; // true:异步，false:同步。
	if (globalConfig.debug) {
		this.url = '/data/aclscanlist.json';
	}
    return this.post(postVar,callback);
};

/**
 * getWiFiApcliScan 获取AP列表
 * @Author   Felix       <amy@carystudio.com>
 * @DateTime 2017-12-20 
 * @property {String} ssid      SSID
 * @property {String} bssid     MAC
 * @property {String} channel   信道 
 * @property {String} encrypt   加密方式  
 * @property {String} cipher    加密算法
 * @property {String} band		频段
 * @property {String} signal    信号强度
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getWiFiApcliScan"
 * }
 * response:
 * {
 *  [
 *   {
 *       "ssid":"a1",
 *       "bssid":"F4:11:22:33:44:44",
 *       "channel":"11",
 *       "encrypt":"NONE",
 *       "cipher":"",
 *       "band":"B",
 *       "signal":"78"
 *   },
 *   {
 *       "ssid":"a2",
 *       "bssid":"F4:22:22:33:44:44",
 *       "channel":"6",
 *       "encrypt":"WPA2PSK",
 *       "cipher":"AES",
 *       "band":"B/G/N",
 *       "signal":"88"
 *  }
 * ]
 * }
* */
uiPost.prototype.getWiFiApcliScan = function(postVar,callback){
    this.topicurl = 'getWiFiApcliScan';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/getWiFiApcliScan.json';
    }
    return this.post(postVar,callback);
};

uiPost.prototype.setWiFiRepeaterConfig = function(postVar,callback){
    this.topicurl = 'setWiFiRepeaterConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};


/**
 * getFirewallType   获取Firewall类型
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-08
 * @param    {String} topicurl			主题
 * @property {String} firewallType		防火墙类型。 0:白名单，1:黑名单
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getFirewallType" ,
 * }
 * response:
 * {
 *  "firewallType":	0
 * }
* */
uiPost.prototype.getFirewallType = function(postVar,callback){
    this.topicurl = 'getFirewallType';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/firewall_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setFirewallType  设置Firewall类型
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-08
 * @param {String} firewallType		防火墙类型。 0:白名单，1:黑名单
 * @property {String} success
 * @property {String} error
 * @property {String} lan_ip
 * @property {String} wtime
 * @property {String} reserv
 * @example
 * request:
 * {
* 	"topicurl":"setFirewallType",
* 	"firewallType":	0
* }
 * response:
 * {
*  "success":	"true",
*  "error":	null,
*  "lan_ip":	"192.168.0.1",
*  "wtime":	"10",
*  "reserv":	"reserv"
* }
 */
uiPost.prototype.setFirewallType = function(postVar,callback){
    this.topicurl = 'setFirewallType';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getVpnPassCfg  获取VPN穿透配置
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-06
 * @param    {String} topicurl			主题
 * @property {String} wanPingFilter		Ping Access on WAN；0：禁用，1：启用
 * @property {String} l2tpPassThru		L2TP穿透。 0：禁用，1：启用
 * @property {String} pptpPassThru		PPTP穿透。 0：禁用，1：启用
 * @property {String} ipsecPassThru		IPSec穿透。0：禁用，1：启用
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getVpnPassCfg" ,
 * }
 * response:
 * {
 *  "wanPingFilter":	1,
 *  "l2tpPassThru":	1,
 *  "pptpPassThru":	1,
 *  "ipsecPassThru":	1
 * }
* */
uiPost.prototype.getVpnPassCfg = function(postVar,callback){
    this.topicurl = 'getVpnPassCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/vpnpass_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setVpnPassCfg  设置VpnPass配置
 * @Author   Bob       <Bob_huang@carystudio.com>
 * @DateTime 2017-11-06
 * @param {String} wanPingFilter	允许从WAN口PING, 0：禁用，1：启用
 * @param {String} l2tpPassThru		L2TP穿透, 0：禁用，1：启用
 * @param {String} pptpPassThru		PPTP穿透, 0：禁用，1：启用
 * @param {String} ipsecPassThru	IPSec穿透, 0：禁用，1：启用
 * @property {String} success
 * @property {String} error
 * @property {String} lan_ip
 * @property {String} wtime
 * @property {String} reserv
 * @example
 * request:
 * {
* 	"topicurl":"setVpnPassCfg",
* 	"wanPingFilter":"1",
* 	"l2tpPassThru":"1",
* 	"pptpPassThru":"1",
* 	"ipsecPassThru":"1"
* }
 * response:
 * {
*  "success":	"true",
*  "error":	null,
*  "lan_ip":	"192.168.0.1",
*  "wtime":	"10",
*  "reserv":	"reserv"
* }
 */
uiPost.prototype.setVpnPassCfg = function(postVar,callback){
    this.topicurl = 'setVpnPassCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getDMZCfg      获取DMZ数据
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} dmzEnabled       DMZ开关, 0：禁用，1：启用
 * @property {String} dmzAddress       DMZ域名地址
 * @property {String} lanIp            局域网IP地址
 * @property {String} lanNetmask       局域网的子网掩码
 * @property {String} stationIp        计算机连接的IP地址
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getDMZCfg"
 * }
 * response:
 * {
 *     "dmzEnabled":	"1",
 *     "dmzAddress":	"192.168.0.8",
 *     "lanIp":	"192.168.0.5",
 *     "lanNetmask":	"255.255.255.0",
 *     "stationIp":	"192.168.0.6"
 * }
 * */
uiPost.prototype.getDMZCfg = function(postVar,callback){
    this.topicurl = 'getDMZCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/dmz_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setDMZCfg    设置DMZ配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} dmzEnabled      DMZ开关, 0：禁用，1：启用
 * @param {String} dmzAddress      DMZ域名地址
 * @property {String} success      响应状态, true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip       局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv       返回页面（参数未知）
 *
 * @example
 * request:
 * {
*     "dmzEnabled":1,
*     "dmzAddress":"192.168.0.8",
*     "topicurl":"setDMZCfg"
* }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setDMZCfg = function(postVar,callback){
    this.topicurl = 'setDMZCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getUrlFilterRules 获取URL过滤配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} enable    URL过滤开关,0：禁用，1：启用
 * @property {String} idx       规则序号
 * @property {String} url       过滤url关键词  
 * @property {String} delRuleName   删除规则索引         
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getUrlFilterRules"
 * }
 * response:
 * {
 *   [
 *		{
 *			"enable":"1"
 *		},
 *		{
 *			"idx":"1",
 *			"url":"www.qq.comxx:xx:xx:xx:xx:xx",
 *			"delRuleName":"delRule0"
 *		},
 *		{
 *			"idx":"2",
 *			"url":"www.baidu.comxx:xx:xx:xx:xx:xx",
 *			"delRuleName":"delRule1"
 *		}
 *	 ]
 * }
* */
uiPost.prototype.getUrlFilterRules = function(postVar,callback){
    this.topicurl = 'getUrlFilterRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/url_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setUrlFilterRules  设置URL过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} enable       URL过滤开关, 0：禁用，1：启用 
 * @param {String} addEffect    0：设置url过滤开关；1：设置过滤规则
 * @param {String} url          过滤url关键词  
 * @property {String} success   响应状态 , true：响应成功，false：响应失败 
 * @property {String} error     错误
 * @property {String} lan_ip    局域网IP
 * @property {String} wtime     等待时间
 * @property {String} reserv    返回页面（参数未知）
 * @example
 * request:
 * {
 *   "enable":	1,
 *   "addEffect":	0,
 *   "url":	"www.qq.com",
 *   "topicurl":"setUrlFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setUrlFilterRules = function(postVar,callback){
    this.topicurl = 'setUrlFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delUrlFilterRules  删除URL过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} delRule0      删除指定规则
 * @property {String} success    响应状态, true：响应成功，false：响应失败 
 * @property {String} error      错误
 * @property {String} lan_ip     局域网IP
 * @property {String} wtime      等待时间
 * @property {String} reserv     返回页面（参数未知）
 * @example
 * request:
 * {
 *   "delRule0":	0,
 *   "topicurl":"delUrlFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delUrlFilterRules = function(postVar,callback){
    this.topicurl = 'delUrlFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getIpPortFilterRules  获取IP端口过滤配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} enable      IP端口过滤开关 , 1：启用; 0：禁用 
 * @property {String} lanNetmask  局域网子网掩码
 * @property {String} lanIp       局域网IP地址 
 * @property {String} idx         规则序号
 * @property {String} ip          过滤的IP地址 
 * @property {String} proto       过滤规则协议 
 * @property {String} portRange   端口范围 
 * @property {String} comment     描述 
 * @property {String} delRuleName   删除制定规则      
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getIpPortFilterRules"
 * }
 * response:
 * {
 * [
 *  {
 *     "enable":"1",
 *     "lanNetmask":"255.255.255.0",
 *     "lanIp":"192.168.0.1"
 *  },
 *  {
 *      "idx":"1",
 *      "ip":"192.168.0.100",
 *      "proto":"TCP+UDP",
 *      "portRange":"1223-12312",
 *      "comment":"wwwwwww",
 *      "delRuleName":"delRule0"
 *  },
 *  {
 *      "idx":"2",
 *      "ip":"192.168.0.200",
 *      "proto":"TCP+UDP",
 *      "portRange":"123",
 *      "comment":"",
 *      "delRuleName":"delRule1"
 *  }
 *]
 * }
* */
uiPost.prototype.getIpPortFilterRules = function(postVar,callback){
    this.topicurl = 'getIpPortFilterRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/ipf_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setIpPortFilterRules  设置IP端口过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} enable      IP端口过滤开关, 1：启用; 0：禁用
 * @param {String} addEffect   0：设置IP端口过滤开关；1：设置过滤规则
 * @param {String} ipAddress   过滤的IP地址  
 * @param {String} dFromPort   过滤的起始端口
 * @param {String} dToPort     过滤的结束端口 
 * @param {String} protocol    过滤规则协议设置
 * @param {String} comment     描述
 * @property {String} success  响应状态, true：响应成功，false：响应失败
 * @property {String} error    错误
 * @property {String} lan_ip   局域网IP
 * @property {String} wtime    等待时间
 * @property {String} reserv   返回页面（参数未知）
 * @example
 * request:
 * {
 *   "enable":	1,
 *   "addEffect":	0,
 *   "ipAddress":	"192.168.0.12",
 *   "dFromPort":	345,
 *   "dToPort":	  5432,
 *   "protocol":	"TCP/UDP",
 *   "comment":	   "wwwwww",
 *   "topicurl":"setIpPortFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setIpPortFilterRules = function(postVar,callback){
    this.topicurl = 'setIpPortFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delIpPortFilterRules  删除IP端口过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param    {String} delRule0    删除指定规则
 * @property {String} success     响应状态, true：响应成功，false：响应失败
 * @property {String} error       错误
 * @property {String} lan_ip      局域网IP
 * @property {String} wtime       等待时间
 * @property {String} reserv      返回页面（参数未知）
 * @example
 * request:
 * {
 *   "delRule0":	0,
 *   "topicurl":"delIpPortFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delIpPortFilterRules = function(postVar,callback){
    this.topicurl = 'delIpPortFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 

/**
 * getPortForwardRules 获取端口转发配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} enable    端口转发开关, 0：禁用，1：启用
 * @property {String} lanNetmask  局域网子网掩码
 * @property {String} lanIp       局域网IP地址 
 * @property {String} idx       规则序号
 * @property {String} ip        转发的目的IP地址 
 * @property {String} proto     协议 
 * @property {String} wanPortFrom   外部开始端口
 * @property {String} wanPortTo		外部结束端口
 * @property {String} lanPortFrom   内部开始端口
 * @property {String} lanPortTo 	内部结束端口
 * @property {String} comment       规则描述 
 * @property {String} delRuleName   删除规则的索引         
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getPortForwardRules"
 * }
 * response:
 * {
 *  [
 *   {
 *     "enable":"1",
 *     "lanNetmask":"255.255.255.0",
 *     "lanIp":"192.168.0.1"
 *   },
 *   {
 *       "idx":"1",
 *       "ip":"192.168.0.54",
 *       "proto":"TCP+UDP",
 *       "wanPortFrom":"999",
 *       "wanPortTo":"999",
 *       "lanPortFrom":"788",
 *       "lanPortTo":"788",
 *       "comment":"",
 *       "delRuleName":"delRule0"
 *   },
 *   {
 *       "idx":"2",
 *       "ip":"192.168.0.123",
 *       "proto":"TCP+UDP",
 *       "wanPortFrom":"8888",
 *       "wanPortTo":"8888",
 *       "lanPortFrom":"888",
 *       "lanPortTo":"888",
 *       "comment":"",
 *       "delRuleName":"delRule1"
 *  }
 * ]
 * }
* */
uiPost.prototype.getPortForwardRules = function(postVar,callback){
    this.topicurl = 'getPortForwardRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/portfwd_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setPortForwardRules  提交端口转发数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} enable          端口转发, 0：禁用，1：启用
 * @param {String} addEffect       0：设置端口转发开关；1：设置转发规则
 * @param {String} wanPortFrom     外部开始端口
 * @param {String} wanPortTo       外部结束端口 
 * @param {String} ipAddress       转发的目的IP地址  
 * @param {String} lanPortFrom     内部开始端口
 * @param {String} lanPortTo       内部结束端口 
 * @param {String} protocol        设置规则协议 
 * @param {String} comment         规则描述
 * @property {String} success      响应状态, true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip       局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv       返回页面（参数未知）
 * @example
 * request:
 * {
 *   "enable":	1,
 *   "addEffect":	0,
 *   "wanPortFrom":	12345,
 *   "wanPortTo":	  12345,
 *   "ipAddress":	"192.168.0.12",
 *   "lanPortFrom":	12331,
 *   "lanPortTo":	  12331,
 *   "protocol":	"TCP+UDP",
 *   "comment":	   "That's the first rule .",
 *   "topicurl":"setPortForwardRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setPortForwardRules = function(postVar,callback){
    this.topicurl = 'setPortForwardRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delPortForwardRules  删除端口转发数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} delRule0    删除指定规则
 * @property {String} success  响应状态  【true：响应成功，false：响应失败】
 * @property {String} error    错误
 * @property {String} lan_ip   局域网IP
 * @property {String} wtime    等待时间
 * @property {String} reserv   返回页面（参数未知） 
 * @example
 * request:
 * {
 *   "delRule0":	0,
 *   "topicurl":"delPortForwardRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delPortForwardRules = function(postVar,callback){
    this.topicurl = 'delPortForwardRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 

/**
 * getMacFilterRules   获取MAC过滤配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} enable    MAC过滤开关, 0：禁用，1：启用
 * @property {String} idx       规则序号
 * @property {String} mac       过滤规则中的Mac地址 
 * @property {String} comment   描述 
 * @property {String} delRuleName    删除规则的索引       
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getMacFilterRules"
 * }
 * response:
 * {
 *   [
 *		{
 *			"enable":"1"
 *		},
 *		{
 *			"idx":"1",
 *			"mac":"f4:28:54:00:37:89",
 *			"comment":"",
 *			"delRuleName":"delRule0"
 *		},
 *		{
 *			"idx":"2",
 *			"mac":"3c:97:0e:61:35:b9",
 *			"comment":"wwww",
 *			"delRuleName":"delRule1"
 *		}
 *	]
 * }
* */
uiPost.prototype.getMacFilterRules = function(postVar,callback){
    this.topicurl = 'getMacFilterRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/mac_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setMacFilterRules  设置MAC过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} enable        MAC过滤开关, 0：禁用，1：启用
 * @param {String} addEffect     0：设置Mac过滤开关；1：设置过滤规则
 * @param {String} macAddress    过滤规则中的MAC地址  
 * @param {String} comment       描述
 * @property {String} success    响应状态, true：响应成功，false：响应失败
 * @property {String} error      错误
 * @property {String} lan_ip     局域网IP
 * @property {String} wtime      等待时间
 * @property {String} reserv     返回页面（参数未知）
 * @example
 * request:
 * {
 *   "enable":	1,
 *   "addEffect":	0,
 *   "macAddress":	F4:28:54:00:37:22,
 *   "comment":	   "That's the first rule .",
 *   "topicurl":"setMacFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setMacFilterRules = function(postVar,callback){
    this.topicurl = 'setMacFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delMacFilterRules  删除MAC过滤数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} delRule0      删除指定规则
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "delRule0":	0,
 *   "topicurl":"delMacFilterRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delMacFilterRules = function(postVar,callback){
    this.topicurl = 'delMacFilterRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 

/**
 * setIpQos  提交总QoS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} enabled     状态。0：禁用，1：启用
 * @param {String} manualUplinkSpeed      总上传带宽
 * @param {String} manualDownlinkSpeed    总下载带宽
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知） 
 * @example
 * request:
 * {
 *   "enabled":"1",
 *   "manualUplinkSpeed":"99999",
 *   "manualDownlinkSpeed":"99999"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
* }
 */
uiPost.prototype.setIpQos = function(postVar,callback){
    this.topicurl = 'setIpQos';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getIpQosRules 获取QoS数据
 * @Author   Amy       <amy@carystudio.com>
 * @Author   Yexk      <yexk@carystudio.com>
 * @DateTime 2017-11-02
 * @param    {String} wifiIdx        无线信息：0：5G , 1：2.4G
 * @property {String} enable    状态。0：禁用，1：启用
 * @property {String} manualUpSpeed     总上传带宽 
 * @property {String} manualDwSpeed     总下载带宽
 * @property {String} gigaBitBt		未知
 * @property {String} wanGigabitBt	未知
 * @property {String} lanIp      局域网IP
 * @property {String} lanNetmask      局域网子网掩码
 * @property {String} idx     列表ID号 
 * @property {String} ip     IP地址
 * @property {String} upBandwidth     上传宽带   
 * @property {String} dwBandwidth     下载宽带 
 * @property {String} comment          描述
 * @property {String} limitMode         限速模式 ， 0 系统自动，1 手动
 * @property {String} delRuleName      第几条规则   
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getIpQosRules"
 * }
 * response:
 * {
 *   [{
 *       "enable":"1",
 *       "manualUpSpeed":"99999",
 *       "manualDwSpeed":"99999",
 *		 "gigaBitBt":"1",
 *		 "wanGigabitBt":"1",
 *       "lanIp":"192.168.0.1",
 *       "lanNetmask":"255.255.255.0"
 *   },
 *   {
 *       "idx":"1",
 *       "ip":"192.168.0.100",
 *       "upBandwidth":"100000",
 *       "dwBandwidth":"100000",
 *       "comment":"",
 *       "limitMode":"0",
 *       "delRuleName":"delRule0"
 *   },
 *   {
 *       "idx":"2",
 *       "ip":"192.168.0.222",
 *       "upBandwidth":"11111",
 *       "dwBandwidth":"22222",
 *       "comment":"",
 *       "limitMode":"1",
 *       "delRuleName":"delRule1"
 *   }]
 * }
* */
uiPost.prototype.getIpQosRules = function(postVar,callback){
    this.topicurl = 'getIpQosRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/qos_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setIpQosRules  提交新增QoS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} ipStart     起始IP
 * @param {String} ipEnd       结束IP
 * @param {String} upBandwidth   上传带宽  
 * @param {String} downBandwidth    下载带宽
 * @param {String} comment    描述
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "ipStart":"192.168.0.100",
 *   "ipEnd":"192.168.0.100",
 *   "upBandwidth":"100000",
 *   "downBandwidth":"100000",
 *   "comment":""
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
* }
 */
uiPost.prototype.setIpQosRules = function(postVar,callback){
    this.topicurl = 'setIpQosRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delIpQosRules  删除QoS数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} delRule0    删除第一条规则
 * @param {String} wifiIdx    无线信息：0：5G , 1：2.4G
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "delRule0":   0,
 *   "wifiIdx":  "0",
 *   "topicurl":"delIpQosRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
* }
 */
uiPost.prototype.delIpQosRules = function(postVar,callback){
    this.topicurl = 'delIpQosRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 
    
/**
 * getScheduleRules  获取时间规则配置
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-1-24
 * @property {String} itemList        当前规则列表
 * @property {String} firewallMode    防火墙类型 值选项：1、IPPORT，2、MAC
 * @property {String} ip              规则的IP地址  ，防火墙类型为 IPPORT 时候才有这个值。
 * @property {String} proto           协议类型 ， 防火墙类型为 IPPORT 时候才有这个值。
 * @property {String} portRange       端口范围 ， 防火墙类型为 IPPORT 时候才有这个值。
 * @property {String} week            防火墙调度规则日期 格式：'Tue Wed'(星期用英文缩写，以空格隔开)
 * @property {String} time            防火墙调度规则时间 格式：HH:mm-HH:mm（开始-结束）
 * @property {String} mac             规则的MAC地址 ，防火墙类型为Mac时候才有这个值。
 * @property {String} delRuleName     规则的索引 格式：'防火墙类型，索引'
 * @example
 * request:
 * {
 *   "topicurl":"getScheduleRules"
 * }
 * response:
 * [
 *   {
 *     "firewallMode": "IPPORT",
 *     "ip": "192.168.0.2",
 *     "proto": "TCP+UDP",
 *     "portRange": "3456-4456",
 *     "week": "Tue Wed",
 *     "time": "5:15-6:54",
 *     "delRuleName": "1,0"
 *   },
 *   {
 *     "firewallMode": "MAC",
 *     "mac": "00:E0:4C:07:04:0C",
 *     "week": "Tue",
 *     "time": "12:12-12:34",
 *     "delRuleName": "2,0"
 *   }
 * ]
 */
uiPost.prototype.getScheduleRules = function(postVar,callback){
    this.topicurl = 'getScheduleRules';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/time_rule.json';
    }
    return this.post(postVar,callback);
};

/**
 * setScheduleRules  设置WiFi调度数据（增删）
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-1-24
 * @param {String} action             操作类型，add:添加，del：删除 （当操作为删除的时候week和time默认是全选,也就是demo2）
 * @param {String} list               操作规则列表数据
 * @param {String} list.week          周。格式:'1,1,1,1,1,1,1'，分别代表为'星期一,星期二,星期三,星期四,星期五,星期六,星期日,' 1代表选中，0代表未选择
 * @param {String} list.time          时间。 格式：HH:mm-HH:mm（开始-结束）
 * @param {String} list.delRuleName   操作规则的索引,格式：'防火墙类型，索引' (来源于getScheduleRules主题的delRuleName索引)
 * 
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error          错误
 * @property {String} lan_ip         局域网IP
 * @property {String} wtime          等待时间
 * @property {String} reserv         返回页面（参数未知）
 * @example
 * request:
 * {
 *   "action": "add",
 *   "list": [
 *     {
 *       "week": "1,1,0,0,0,0,0",
 *       "time": "05:01-05:01",
 *       "delRuleName": "2,0"
 *     }
 *   ],
 *   "topicurl": "setScheduleRules"
 * }
 * // demo2 del action   
 * {
 *   "action": "del",
 *   "list": [
 *     {
 *       "week": "1,1,1,1,1,1,1",
 *       "time": "00:00-23:59",
 *       "delRuleName": "2,0"
 *     }
 *   ],
 *   "topicurl": "setScheduleRules"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.1",
 *   "wtime":   0,
 *   "reserv":  "reserv"
* }
 */
uiPost.prototype.setScheduleRules = function(postVar,callback){
    this.topicurl = 'setScheduleRules';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getWanConfig  获取wan信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-06
 * @property {String} wanMode  连接模式
 * @property {String} dnsMode  DNS模式
 * @property {String} pptpMode PPTP模式
 * @property {String} l2tpMode L2TP模式
 * @property {String} staticMtu    静态MTU
 * @property {String} dhcpMtu    DHCP MTU
 * @property {String} pppoeMtu    PPPoE MTU
 * @property {String} pptpMtu    PPTP MTU
 * @property {String} l2tpMtu    L2TP MTU
 * @property {String} pppoeSpecType    特殊策略
 * @property {String} pppoeTime    未知
 * @property {String} pptpTime    未知
 * @property {String} l2tpTime    未知
 * @property {String} pptpMppe    未知
 * @property {String} pptpMppc    未知
 * @property {String} l2tpDomainFlag    未知
 * @property {String} pptpDomainFlag    未知
 * @property {String} pppoeOpMode    未知
 * @property {String} pptpOpMode    未知
 * @property {String} l2tpOpMode    未知
 * @property {String} lanIp    局域网IP
 * @property {String} staticIp    wan静态 IP
 * @property {String} staticMask    wan静态 子网掩码
 * @property {String} staticGw    wan默认网关
 * @property {String} pptpIp    PPTP IP
 * @property {String} pptpMask    PPTP 子网掩码
 * @property {String} pptpGw     PPTP 默认网关
 * @property {String} pptpServerIp     PPTP 服务器IP
 * @property {String} l2tpIp     L2TP IP
 * @property {String} l2tpMask    L2TP 子网掩码
 * @property {String} l2tpGw    L2TP 默认网关
 * @property {String} l2tpServerIp    L2TP 服务器IP
 * @property {String} hostName    主机名
 * @property {String} pppoeUser    PPPoE 用户账号
 * @property {String} pppoePass    PPPoE 用户密码
 * @property {String} pptpUser       PPTP 用户账号
 * @property {String} pptpPass     PPTP 用户密码
 * @property {String} l2tpUser    L2TP 用户账号
 * @property {String} l2tpPass    L2TP 用户密码
 * @property {String} l2tpServerDomain    未知
 * @property {String} pptpServerDomain    未知
 * @property {String} wanConnStatus    广域网连接状态
 * @property {String} wanDefMac    默认MAC
 * @property {String} macCloneMac    克隆MAC
 * @property {String} macCloneEnabled    克隆MAC标志 ,0 缺省mac，1 克隆mac
 * @property {String} operationMode    系统模式 
 * @property {String} priDns    首选DNS
 * @property {String} secDns    备选DNS
 * @property {String} wanAutoDetectBt    未知
 * @property {String} pppoeSpecBt    未知
 * @property {String} pptpBt    未知
 * @property {String} l2tpBt    未知
 *
 * @example
 * request:
 * {
* 	"topicurl":"getWanConfig",
* }
 * response:
 * {
 *  "wanMode":	3,
 *  "dnsMode":	0,
 *  "pptpMode":	0,
 *  "l2tpMode":	0,
 *  "staticMtu":	1500,
 *  "dhcpMtu":	1492,
 *  "pppoeMtu":	1492,
 *  "pptpMtu":	1460,
 *  "l2tpMtu":	1460,
 *  "pppoeSpecType":	0,
 *  "pppoeTime":	300,
 *  "pptpTime":	300,
 *  "l2tpTime":	300,
 *  "pptpMppe":	0,
 *  "pptpMppc":	0,
 *  "l2tpDomainFlag":	0,
 *  "pptpDomainFlag":	0,
 *  "pppoeOpMode":	0,
 *  "pptpOpMode":	0,
 *  "l2tpOpMode":	0,
 *  "lanIp":	"192.168.0.5",
 *  "staticIp":	"192.168.1.5",
 *  "staticMask":	"255.255.255.0",
 *  "staticGw":	"192.168.1.1",
 *  "pptpIp":	"172.1.1.2",
 *  "pptpMask":	"255.255.255.0",
 *  "pptpGw":	"0.0.0.0",
 *  "pptpServerIp":	"172.1.1.1",
 *  "l2tpIp":	"172.1.1.2",
 *  "l2tpMask":	"255.255.255.0",
 *  "l2tpGw":	"0.0.0.0",
 *  "l2tpServerIp":	"172.1.1.1",
 *  "hostName":	"totolink",
 *  "pppoeUser":	"root",
 *  "pppoePass":	"root",
 *  "pptpUser":	"",
 *  "pptpPass":	"",
 *  "l2tpUser":	"",
 *  "l2tpPass":	"",
 *  "l2tpServerDomain":	"",
 *  "pptpServerDomain":	"",
 *  "wanConnStatus":	"connected",
 *  "wanDefMac":	"F4:28:54:00:40:E3",
 *  "macCloneMac":	"00:00:00:00:00:00",
 *  "macCloneEnabled":	0,
 *  "operationMode":	0,
 *  "priDns":	"192.168.1.1",
 *  "secDns":	"0.0.0.0",
 *  "wanAutoDetectBt":	0,
 *  "pppoeSpecBt":	0,
 *  "pptpBt":	1,
 *  "l2tpBt":	1
 * }
 */
uiPost.prototype.getWanConfig = function(postVar,callback){
    this.topicurl = 'getWanConfig';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/wan.json';
    }
    return this.post(postVar,callback);
};

/**
 * setWanConfig  设置WAN配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-06
 * @param {String} wanMode	    WAN连接类型 0：静态IP，1：DCHP ，3：PPPoE
 * @param {String} hostName		未知（只看到存在于DHCP）
 * @param {String} dhcpMtu		DHCP MTU
 * @param {String} dnsMode		DNS模式 0：自动，1：手动
 * @param {String} macCloneEnabled		克隆标志 0 缺省mac，1 克隆mac
 * @param {String} macCloneMac		克隆MAC，根据 克隆/缺省 选择MAC
 * @param {String} staticIp		静态IP
 * @param {String} staticMask		静态子网掩码
 * @param {String} staticGw		静态默认网关
 * @param {String} staticMtu		静态MTU
 * @param {String} priDns		首选DNS
 * @param {String} secDns		备选DNS
 * @param {String} staticMtu		静态MTU
 * @param {String} pppoeUser		PPPOE用户账号
 * @param {String} pppoePass		PPPOE用户密码
 * @param {String} pppoeSpecType		特殊策略
 * @param {String} pppoeMtu		PPPOE MTU
 * @param {String} pppoeOpMode		拨号模式
 * @param {String} pppoeTime		未知
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 *
 * @example
 * request:
 * {
 *     //DHCP模式
 * 	    "wanMode":"1",
 *      "hostName":"",
 *      "dhcpMtu":1492,
 *      "dnsMode":0,
 *      "macCloneEnabled":0,
 *      "macCloneMac":"00:00:00:00:00:00",
 *      "topicurl":"setWanConfig"
 *      //静态IP
 *      "wanMode":"0",
 *      "staticIp":"192.168.1.5",
 *      "staticMask":"255.255.255.0",
 *      "staticGw":"192.168.1.1",
 *      "staticMtu":1500,
 *      "dnsMode":"1",
 *      "priDns":"192.168.1.1",
 *      "secDns":"0.0.0.0",
 *      "macCloneEnabled":0,
 *      "macCloneMac":"00:00:00:00:00:00",
 *      "topicurl":"setWanConfig"
 *      //PPPoE
 *      "wanMode":3,
 *      "pppoeUser":"root",
 *      "pppoePass":"root",
 *      "pppoeSpecType":0,
 *      "pppoeMtu":1492,
 *      "pppoeOpMode":0,
 *      "pppoeTime":300,
 *      "dnsMode":0,
 *      "macCloneEnabled":0,
 *      "macCloneMac":"00:00:00:00:00:00",
 *      "topicurl":"setWanConfig"
* }
 * response:
 * {
*  "success":	"true",
*  "error":	null,
*  "lan_ip":	"192.168.0.1",
*  "wtime":	"10",
*  "reserv":	"reserv"
* }
 */
uiPost.prototype.setWanConfig = function(postVar,callback){
    this.topicurl = 'setWanConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getLanConfig  获取局域网配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-04
 * @property {String} lanIp         局域网IP
 * @property {String} lanNetmask         子网掩码
 * @property {String} dhcpServer         DHCP状态：0：禁用，2：启用
 * @property {String} dhcpStart         DHCP起始IP地址
 * @property {String} dhcpEnd         DHCP结束IP地址
 * @property {String} dhcpLease     租约时间
 * @property {String} br0Ip         br0Ip
 * @property {String} br0Netmask         br0子网掩码
 * @property {String} operationMode         系统模式
 * @property {String} wanIp         广域网IP
 * @property {String} languageType         语言
 *
 * @example
 * request:
 * {
*   "topicurl":"getLanConfig"
* }
 * response:
 * {
 *	"lanIp":	"192.168.0.5",
 *	"lanNetmask":	"255.255.255.0",
 *	"dhcpServer":	2,
 *	"dhcpStart":	"192.168.0.6",
 *	"dhcpEnd":	"192.168.0.254",
 *	"dhcpLease":	300,
 *	"br0Ip":	"192.168.0.5",
 *	"br0Netmask":	"255.255.255.0",
 *	"operationMode":	0,
 *	"wanIp":	"192.168.1.4",
 *	"languageType":	"vn"
 * }
 */
uiPost.prototype.getLanConfig = function(postVar,callback){
    this.topicurl = 'getLanConfig';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/lan_info.json';
    }
    return this.post(postVar,callback);
};

/**
 * setLanConfig  设置局域网配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2017-11-04
 * @param {String} lanIp    局域网IP
 * @param {String} lanNetmask    子网掩码
 * @param {String} dhcpServer    无线信息：0：5G , 1：2.4G
 * @param {String} dhcpStart    删除第一条规则
 * @param {String} dhcpEnd    局域网IP
 * @param {String} dhcpLease    租约时间
 * @property {String} 无返回值
 * @example
 * request:
 * {
*   "lanIp":"192.168.0.5",
*   "lanNetmask":"255.255.255.0",
*   "dhcpServer":0,
*   "dhcpStart":"192.168.0.6",
*   "dhcpEnd":"192.168.0.254",
*   "dhcpLease":300,
*   "topicurl":"setLanConfig"
* }
 * response:
 * {
*       无（倒计时显示）
* }
 */
uiPost.prototype.setLanConfig = function(postVar,callback){
    this.topicurl = 'setLanConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * getStaticDhcpConfig 获取静态DHCP设置数据
 * modify by york 2018-1-16
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @property {String} enable    状态。0：禁用，1：1启用
 * @property {String} lanIp   lan口的ip地址
 * @property {String} lanNetmask   lan口的子网掩码
 * @property {String} idx       列表ID号
 * @property {String} ip        IP地址 
 * @property {String} mac        MAC地址 
 * @property {String} comment       描述 
 * @property {String} delRuleName      第几条规则
 *
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getStaticDhcpConfig"
 * }
 * response:
 *   [
 *		{
 *			"enable":"1",
 *          "lanIp" : "192.168.0.2",
 *          "lanNetmask" : "255.255.255.0"
 *		},
 *		{
 *			"idx":"1",
 *			"ip":"192.168.0.123",
 *			"mac":"F4:28:54:00:37:66",
 *			"comment":"",
 *			"delRuleName":"delRule0"
 *		},
 *		{
 *			"idx":"2",
 *			"ip":"192.168.0.200",
 *			"mac":"3C:97:0E:61:35:B9",
 *			"comment":"",
 *			"delRuleName":"delRule1"
 *		}
 *	]
 *
 */
uiPost.prototype.getStaticDhcpConfig = function(postVar,callback){
    this.topicurl = 'getStaticDhcpConfig';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/static_dhcp.json";
    }
    return this.post(postVar,callback);
};

/**
 * setStaticDhcpConfig  提交静态DHCP设置数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} addEffect      添加的状态
 * @param {String} enable    状态。0：禁用，1：1启用
 * @param {String} ipAddress     IP地址 
 * @param {String} macAddress    MAC地址  
 * @param {String} delRuleName    新增的第几条规则
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @example
 * request:
 * {
 *   "addEffect":	0,
 *   "enable":	1,
 *   "ipAddress":	"192.168.0.123",
 *   "macAddress":	F4:28:54:00:37:22,
 *   "delRuleName":"delRule0",
 *   "topicurl":"setStaticDhcpConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setStaticDhcpConfig = function(postVar,callback){
    this.topicurl = 'setStaticDhcpConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * delStaticDhcpConfig  删除静态DHCP设置数据
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2017-11-02
 * @param {String} delRule0    删除第一条规则
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知） 
 * @example
 * request:
 * {
 *   "delRule0":	0,
 *   "topicurl":"delStaticDhcpConfig"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.delStaticDhcpConfig = function(postVar,callback){
    this.topicurl = 'delStaticDhcpConfig';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
}; 

/**
 * 获取DHCP客户端列表
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-11-06
 * @return   {null}        
 * @property {String} enable   原来的意思是是否开启？还是什么？新版本中建议去掉
 * 
 * @property {String} obj       一个对象带表一条数据
 * @property {String} obj.idx   信道的标识 0 2.4g  1 5g ？ 
 * @property {String} obj.ip    IP地址
 * @property {String} obj.mac   MAC地址
 * @example
 * request:
 * {
 *    topicurl:"getDhcpCliList"
 *    
 * }
 * response:
 * [
 *  {
 *    "enable": "1"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.0.3",
 *    "mac": "88:51:fb:4a:dc:2c"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.1.3",
 *    "mac": "88:51:fb:5a:dc:3c"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.1.34",
 *    "mac": "88:51:fb:1a:dc:3c"
 *  }
 * ]
 */
uiPost.prototype.getDhcpCliList = function(postVar,callback){
    this.topicurl = 'getDhcpCliList';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * getOpMode  获取opmode信息
 * add OpModeSupport by Yexk@2018-1-22
 * @Author   karen       <karen@carystudio.com>
 * @Author   Yexk        <yexk@carystudio.com>
 * @DateTime 2017-11-04
 *
 * @property {String} operationMode          上网模式。0：bridge，1：repeater，2：wisp，3：gateway，4：mesh，5：client
 * @property {String} wispInface    wisp内部接口。0:5G，1:2.4G
 * @property {String} wifiOff      2.4g客户端。0：开启，1：关闭
 * @property {String} wifiOff5g   5g客户端。0：开启，1：关闭
 * @property {String} wifiDualband     支持双频。0：不支持，1：支持
 * @property {String} hardModel   硬件版本
 * @property {String} OpModeSupport   支持的系统模式。GW：gateway;BR：bridge;RPT：repeater;WISP：WISP;CLI：client:MESH：mesh;
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"getOpMode"
 * }
 * response:
 *{
 *	"operationMode":"1",
 *  "wispInface":"0",
 *  "wifiOff":"0",
 *  "wifiOff5g":"0",
 *  "wifiDualband":"1",
 *  "OpModeSupport":"GW;BR;RPT;WISP",
 *  "hardModel":""
 *}
 */
uiPost.prototype.getOpMode = function(postVar,callback){
    this.topicurl = 'getOpMode';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/opmode.json';
    }
    return this.post(postVar,callback);
};

/**
 * setOpMode  获取opmode信息
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2017-11-04
 * @param {String} operationMode          上网模式。0：bridge，1：repeater，2：wisp，3：gateway，4：mesh，5：client
 * @param {String} wispInface   wisp内部接口。0:5G，1:2.4G
 * @property {String} success      响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip       局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv       返回页面（参数未知）
 * @return   {Object}
 * @example
 * request:
 * {
 *    topicurl:"setOpMode"
 *	  "operationMode":"0",
 *    "wispInface":""
 * }
 * response:
 *{
 *	 "success":true,
 *   "error":null,
 *   "lan_ip":"192.168.0.1",
 *   "wtime":"0",
 *   "reserv":"reserv"
 *}
 */
uiPost.prototype.setOpMode = function(postVar,callback){
    this.topicurl = 'setOpMode';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 获取静态DHCP设置、IP端口过滤、Mac过滤、端口转发的MAC列表
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-11-07
 * @return   {null}        
 * @property {String} enable   原来的意思是是否开启？还是什么？新版本中建议去掉
 * 
 * @property {String} obj       一个对象带表一条数据
 * @property {String} obj.idx   信道的标识 0 2.4g  1 5g ？ 
 * @property {String} obj.ip    IP地址
 * @property {String} obj.mac   MAC地址
 * @example
 * request:
 * {
 *    topicurl:"getArpTable"
 * }
 * response:
 * [
 *  {
 *    "enable": "1"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.0.3",
 *    "mac": "88:51:fb:4a:dc:2c"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.1.3",
 *    "mac": "88:51:fb:5a:dc:3c"
 *  },
 *  {
 *    "idx": "0",
 *    "ip": "192.168.1.34",
 *    "mac": "88:51:fb:1a:dc:3c"
 *  }
 * ]
 */
uiPost.prototype.getArpTable = function(postVar,callback){
    this.topicurl = 'getArpTable';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/dhcpclient.json";
    }
    return this.post(postVar,callback);
};

/**
 * getStationMacByIp  通过ip获得克隆mac
 * @Author   amy       <amy@carystudio.com>
 * @DateTime 2017-11-06
 * @param {String} stationIp     station ip
 * @property {String} stationMac       station mac
 *
 * @example
 * request:
 * {
 *     "stationIp":"192.168.15.200",
 * }
 * response:
 * {
 *  "stationMac":   "c8:1f:66:17:ae:b7",
 * }
 */
uiPost.prototype.getStationMacByIp = function(postVar,callback){
    this.topicurl = 'getStationMacByIp';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/getStationMacByIp.json';
    }
    return this.post(postVar,callback);
};

/**
 * <b style="color:red">[new]</b> 页面初始化数据
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-11-08
 * @property {String} currentMode       <b>必须</b>, 当前路由器的模式，默认为GW。系统模式可选值。GW：gateway;BR：bridge;RPT：repeater;WISP：WISP;CLI：client:MESH：mesh;
 * @property {Boolean} debug            调试模式，默认为true ,true为调试模式，false为产品模式
 * @property {String} defaultLang       设置当前语言。默认'en'. 可选值'cn':'中文','en':'English'
 * @return   {Object}                         
 * @example
 * request:
 * {
 *   "topicurl":"getInitConfig",
 * }
 * 如果属性不需要改变其默认值的可以不传递
 * response:
 * {
 *  "defaultLang":   "en",
 *  "currentMode":   "GW"
 * }
 */
uiPost.prototype.getInitConfig = function(postVar,callback){
    this.topicurl = 'getInitConfig';
    this.async = false; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/init.json';
    }
    return this.post(postVar,callback);
};

/**
 * 通过sql语句获取数据
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-18
 * @param {String} one           1 集中管理 -> 内网扫描操作 （发送数据）
 * @param {Array}  one.SqlCmd                sql语句
 * @property {String} one        1 集中管理 -> 内网扫描操作 (响应数据)
 * @property {String} one.id             ID
 * @property {String} one.apMac              AP的MAC地址，不允许AP有相同MAC地址存储
 * @property {String} one.status             1：正常，2：升级中
 * @property {String} one.onlieStatus        在线状态  1 在线。0 离线
 * @property {String} one.ledCtlStatus       led灯状态 2，开启 1 结束        
 * @property {String} one.apType             0:不支持无线；1：2.4G频，2：5G单频，3：2.4+5G
 * @property {String} one.csid               内部型号, 即配置中的ProductName，唯一，内部使用
 * @property {String} one.fmVersion          设备软件版本  
 * @property {String} one.svn                 设备软件当前svn
 * @property {String} one.softModel           设备外部型号   
 * @property {String} one.schdReboot          定时重启参数   
 * @property {String} one.ipaddr             设备IP地址
 * @property {String} one.mask               设备子网掩码
 * @property {String} one.gateway            设备网关
 * @property {String} one.dns1               设备第一DNS
 * @property {String} one.dns2               设备第二DNS
 * @property {String} one.ssid2g             2.4G SSID
 * @property {String} one.wlanKey2g           2.4G 密码   
 * @property {String} one.channel2g           2.4G 信道   
 * @property {String} one.staNum2g           2.4G 客户端总数   
 * @property {String} one.txPower2g          2.4G TxPower    
 * @property {String} one.ssid5g             5G SSID
 * @property {String} one.wlanKey5g           5G 密码   
 * @property {String} one.channel5g          5G 信道    
 * @property {String} one.staNum5g           5G 客户端总数    
 * @property {String} one.txPower5g           5G txPower   
 * @property {String} one.protocol            协议版本，当前为24   
 * @property {String} one.sameNetwork         设备与AC是否处于同一网段，0为不同网段，1为相同网段。   
 * @property {String} one.password            AP管理密码   
 * @property {String} one.name                AP名称
 * @property {String} one.fwSvn              固件的SVN号
 * @property {String} one.fwUrl              固件路径
 * @property {String} one.fwMd5              固件的MD5值
 *
 * @return   {Object}                         
 * @example
 * request:
 * // (1)
 * {
 *   "SqlCmd": "select f.id,f.apMac,f.status,f.onlieStatus,f.ledCtlStatus,f.apType,f.csid,f.fmVersion,f.svn,f.softModel,f.schdReboot,f.ipaddr,f.mask,f.gateway,f.dns1,f.dns2,f.ssid2g,f.wlanKey2g,f.channel2g,f.staNum2g,f.txPower2g,f.ssid5g,f.wlanKey5g,f.channel5g,f.staNum5g,f.txPower5g,f.protocol,f.sameNetwork,o.password,o.name,w.fwSvn,w.fwUrl,w.fwMd5  from (BASICINFO f LEFT JOIN OTHERINFO o on f.apMac:o.mac ) LEFT JOIN FWINFO w on f.csid : w.fwCsid",
 *   "topicurl": "testSQLite"
 * }
 * response:
 * // (1)
 * [ {
 *   "id": "1",
 *   "apMac": "F4:28:53:00:32:C8",
 *   "status": "",
 *   "onlieStatus": "1",
 *   "ledCtlStatus": "0",
 *   "apType": "1",
 *   "csid": "C8B810A-K7AP202",
 *   "fmVersion": "V6.2c",
 *   "svn": "753",
 *   "softModel": "AP202",
 *   "schdReboot": "0",
 *   "ipaddr": "192.168.1.170",
 *   "mask": "255.255.255.0",
 *   "gateway": "192.168.1.1",
 *   "dns1": "192.168.1.1",
 *   "dns2": "192.168.1.1",
 *   "ssid2g": "@KuaiQi_32C8",
 *   "wlanKey2g": "",
 *   "channel2g": "0",
 *   "staNum2g": "0",
 *   "txPower2g": "100",
 *   "ssid5g": "",
 *   "wlanKey5g": "",
 *   "channel5g": "",
 *   "staNum5g": "",
 *   "txPower5g": "",
 *   "protocol": "24",
 *   "sameNetwork": "1",
 *   "password": "admin",
 *   "name": "",
 *   "fwSvn": "",
 *   "fwUrl": "",
 *   "fwMd5": ""
 * },
 * { ... } ]
 */
uiPost.prototype.testSQLite = function(postVar,callback){
    this.topicurl = 'testSQLite';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        if (postVar.central){
            this.url = '/data/central.json';
        }
    }
    return this.post(postVar,callback);
};

/**
 * 集中管理
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-18
 * @param {String} one   1（集中管理：QuickSetting）
 * @param {String} one.Action                   操作名称 值：QuickSetting
 * @param {Array}  one.APMACLIST                选中的mac地址组
 * @param {Array}  one.APIPLIST                 选中的ip地址组
 * @param {Array}  one.NETLIST                  网段组 ，0为不同网段，1为相同网段。
 * @param {Object} one.postData                 
 * @param {String} one.postData.AuthPassword    管理员密码
 * @param {String} one.postData.ModifyPassword  修改密码 如果修改密码为空将不会传递次参数
 * @param {String} one.postData.Ipaddr          ip地址
 * @param {String} one.postData.Gateway         网关
 * @param {String} one.postData.Dns1            DNS
 * @param {String} one.postData.Ssid2g          2.4G SSID
 * @param {String} one.postData.WlanKey2g       2.4G ssid密码
 * @param {String} one.postData.Channel2g       2.4G 信道
 * @param {String} one.postData.TxPower2g       2.4G 发射功率
 * @param {String} one.postData.Ssid5g          5G SSID
 * @param {String} one.postData.WlanKey5g       5G ssid密码
 * @param {String} one.postData.Channel5g       5G 信道
 * @param {String} one.postData.TxPower5g       5G 发射功率
 * @param {String} one.postData.APName          AP名称
 * 
 * @param {String} two     2（灯控制：setledState）
 * @param {String} two.Action                   操作名称 值：setledState
 * @param {String} two.Ledstate                 值选项：2 开启， 1 结束
 * @param {String} two.AuthPassword             管理员密码
 * @param {Array}  two.APMACLIST                选中的mac地址组
 * @param {Array}  two.APIPLIST                 选中的ip地址组
 * 
 * @param {String} three   3（设备重启：RebootSystem）
 * @param {String} three.Action                   操作名称 值：RebootSystem
 * @param {String} three.AuthPassword             管理员密码
 * @param {Array}  three.APMACLIST                选中的mac地址组
 * @param {Array}  three.APIPLIST                 选中的ip地址组
 * 
 * @param {String} four   4（定时重启：QuickSetting）
 * @param {String} four.Action                   操作名称 值：QuickSetting
 * @param {Array}  four.APMACLIST                选中的mac地址组
 * @param {Array}  four.APIPLIST                 选中的ip地址组
 * @param {Array}  four.NETLIST                  网段组 ，0为不同网段，1为相同网段。
 * @param {Object} four.postData
 * @param {String} four.postData.AuthPassword    管理员密码
 * @param {String} four.postData.SchdReboot      重启规则 ： -1：默认，0：禁用，3 , 4 ,5 , 6
 * 
 * @param {String} five   5（恢复出厂：QuickSetting）
 * @param {String} five.Action                   操作名称 值：LoadDefSettings
 * @param {String} five.AuthPassword             管理员密码
 * @param {Array}  five.APMACLIST                选中的mac地址组
 * @param {Array}  five.APIPLIST                 选中的ip地址组
 * 
 * @param {String} six   6（固件升级：QuickSetting）
 * @param {String} six.Action                   操作名称 值：RemoteUpgradeFW
 * @param {String} six.AuthPassword             管理员密码
 * @param {String} six.Port                     端口 值:80
 * @param {String} six.ProxyEnabled             域 值：0
 * @param {String} six.AuthPassword             管理员密码
 * @param {Array}  six.APMACLIST                选中的mac地址组
 * @param {Array}  six.APIPLIST                 选中的ip地址组
 * @param {Array}  six.URLLIST                  选中的URL组
 * @param {Array}  six.MD5LIST                  选中的MD5组
 * 
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return {Object}
 * @example
 * request:
 * // (1)
 * {
 *   "APIPLIST": ["192.168.1.170"],
 *   "APMACLIST": ["F4:28:53:00:32:C8"],
 *   "Action": "QuickSetting",
 *   "NETLIST": ["1"],
 *   "postData": {
 *     "APName": "test",
 *     "Action": "QuickSetting",
 *     "AuthPassword": "admin",
 *     "Channel2g": "0",
 *     "Channel5g": "",
 *     "Dns1": "192.168.1.1",
 *     "Gateway": "192.168.1.1",
 *     "Ipaddr": "192.168.1.170",
 *     "ModifyPassword": "password",
 *     "Ssid2g": "@KuaiQi_32C8",
 *     "Ssid5g": "",
 *     "TxPower2g": "100",
 *     "TxPower5g": "",
 *     "WlanKey2g": "121",
 *     "WlanKey5g": ""
 *   },
 *   "topicurl": "QuickSetting"
 * }
 * // (2)
 * {
 *   "topicurl": "QuickSetting",
 *   "AuthPassword": "admin",
 *   "Ledstate": 2,
 *   "Action": "setledState",
 *   "APMACLIST": ["F4:28:53:00:32:C8"],
 *   "APIPLIST": [ "192.168.1.170" ]
 * }
 * // (3)
 * {
 *   "topicurl": "QuickSetting",
 *   "AuthPassword": "admin",
 *   "Action": "RebootSystem",
 *   "APMACLIST": [ "F4:28:53:00:32:C8" ],
 *   "APIPLIST": [ "192.168.1.170" ]
 * }
 * // (4)
 * {
 *   "topicurl": "QuickSetting",
 *   "Action": "QuickSetting",
 *   "APMACLIST": ["F4:28:53:00:32:C8"],
 *   "APIPLIST": ["192.168.1.170"],
 *   "NETLIST": ["1"],
 *   "postData": {
 *     "Action": "QuickSetting",
 *     "AuthPassword": "admin",
 *     "SchdReboot": "4"
 *   }
 * }
 * // (5)
 * {
 *   "topicurl": "QuickSetting",
 *   "AuthPassword": "admin",
 *   "Action": "LoadDefSettings",
 *   "APMACLIST": [ "F4:28:53:00:32:C8" ],
 *   "APIPLIST": [ "192.168.1.170" ]
 * }
 * // (6)
 * {
 *   "topicurl": "QuickSetting",
 *   "AuthPassword": "admin",
 *   "Action": "RemoteUpgradeFW",
 *   "Port": "80",
 *   "ProxyEnabled": "0",
 *   "APMACLIST": ["F4:28:53:00:32:C8"],
 *   "APIPLIST": ["192.168.1.170"],
 *   "URLLIST": [""],
 *   "MD5LIST": [""]
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.QuickSetting = function(postVar,callback){
    this.topicurl = 'QuickSetting';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 待定
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-18
 * @property {String} usbState usb状态 ，0：没有插入USB，1：插入USB 
 * @return   {Object}                    
 * @example
 * request:
 * {
 *   "topicurl":"getUsbState",
 * }
 * response:
 * {
 *  "usbState": "1"
 * }
 */
uiPost.prototype.getUsbState = function(postVar,callback){
    this.topicurl = 'getUsbState';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/getUsbState.json";
    }
    return this.post(postVar,callback);
};

/**
 * getDhcpSliList	获取DHCP列表
 * @Author   karen       <karen@carystudio.com>
 * @DateTime 2018-1-19
 * @property {Boolean} ip       IP地址
 * @property {Boolean} mac  	MAC地址
 * @return   {Object}                         
 * @example
 * request:
 * {
 *   "topicurl":"getDhcpSliList",
 * }
 * 
 * response:
 * {
 *  	"ip":"192.168.1.1",
 *		"mac":"11:22:33:44:55:66"
 * }
 */
uiPost.prototype.getDhcpSliList = function(postVar,callback){
    this.topicurl = 'getDhcpSliList';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/data/getDhcpSliList.json";
    }
    return this.post(postVar,callback);
};

/**
 * 实时信息 -> 获取外网信息
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-19
 * @property {String}  type            上网类型，1 => DHCP ,2 => static , 3 => PPPoE    
 * @property {String}  ip              ip地址 
 * @property {String}  gateway         网关地址
 * @property {String}  timestamp       时间戳。（单位毫秒）
 * @property {String}  up              上行数据 (KB/s) 
 * @property {String}  down            下行数据 (KB/s)
 * @return   {Object}
 * @example
 * // request:
 * {
 *     "topicurl":"setting/getNetInfo"
 * }
 * // response:
 * {
 *     "type":"1",
 *     "ip":"192.168.0.253",
 *     "gateway":"192.168.0.1",
 *     "timestamp":"1512959106",
 *     "up":"13",
 *     "down":"156" 
 * }
 */
uiPost.prototype.getNetInfo = function(postVar,callback){
    this.topicurl = 'getNetInfo';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/newui/data/getNetInfo.json";
    }else{
        this.url = "/webapi";
    }
    return this.post(postVar,callback);
};

/**
 * 实时信息获取链接数
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-19
 * @property {String} connections                       连接数
 * @property {String} connections.protocol              协议
 * @property {String} connections.src                   源IP地址
 * @property {String} connections.count_download        累计下载 (单位：MB)
 * @property {String} connections.count_upload          累计上传 (单位：MB)
 * @property {String} connections.download              下载速度 (单位：KB/s)
 * @property {String} connections.upload                上传速度 (单位：KB/s)
 * @property {String} connections.connect_count         连接速度
 * @property {String} connections.data                  每条的数据
 * @property {String} connections.data.dport             目标IP地址端口
 * @property {String} connections.data.dst               目标IP地址
 * @property {String} connections.data.net               网口
 * @property {String} connections.data.protocol          协议
 * @property {String} connections.data.sport               源IP地址端口
 * @property {String} connections.data.src               源IP地址
 * @property {String} connections.data.time               时间,格式：YYYY-mm-dd HH:ii:ss
 * @property {String} statistics    tcp和udp数据
 * @property {String} statistics.tcp    tcp数据
 * @property {String} statistics.udp    udp数据
 * @return   {Object}
 * @example
 * // request:
 * {
 *     "topicurl":"setting/getLinksData"
 * }
 * // response:
 * {
 *     connections:[
 *         {
 *             protocol : "TCP",
 *             src : "192.168.10.18",
 *             count_download:"11", 
 *             count_upload:"10", 
 *             download:"123", 
 *             upload:"150", 
 *             connect_count:"20",
 *             data:[
 *                 {
 *                     net : "WAN1",
 *                     dport : "80",
 *                     dst : "192.168.0.253",
 *                     protocol : "tcp",
 *                     sport :    "3210",
 *                     src :  "192.168.0.2",
 *                     time : "2017-12-19 15:50:21"
 *                 },{
 *                     net : "WAN1",
 *                     dport : "80",
 *                     dst : "192.168.0.253",
 *                     protocol : "tcp",
 *                     sport :    "3210",
 *                     src :  "192.168.0.2",
 *                     time : "2017-12-19 15:50:21"
 *                 },{
 *                     ...
 *                 },
 *             ]
 *         },{
 *             protocol : "TCP",
 *             src : "192.168.10.18",
 *             count_download:"11", 
 *             count_upload:"10", 
 *             download:"123", 
 *             upload:"150", 
 *             connect_count:"20", 
 *             data:[
 *                 {
 *                     net : "WAN1",
 *                     dport : "80",
 *                     dst : "192.168.0.253",
 *                     protocol : "tcp",
 *                     sport :    "3210",
 *                     src :  "192.168.0.2",
 *                     time : "2017-12-19 15:50:21"
 *                 },{
 *                     net : "WAN1",
 *                     dport : "80",
 *                     dst : "192.168.0.253",
 *                     protocol : "tcp",
 *                     sport :    "3210",
 *                     src :  "192.168.0.2",
 *                     time : "2017-12-19 15:50:21"
 *                 },{
 *                     ...
 *                 },
 *             ]
 *         },
 *         {  ...  },
 *     ],
 *     statistics:
 *     {
 *         "tcp":"45",
 *         "udp":"46" 
 *     }
 * }
 */
uiPost.prototype.getLinksData = function(postVar,callback){
    this.topicurl = 'getLinksData';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = "/newui/data/getLinksData.json";
    }else{
        this.url = "/webapi";
    }
    return this.post(postVar,callback);
};

/**
 * getLoginCfg  获取登录配置
 * @Author   amy       <amy@carystudio.com>
 * @DateTime 2018-1-19
 * @property {String} loginIp       登录IP地址
 * @property {String} loginUser     登录的用户名
 * @property {String} loginPass     登录的密码
 * @property {String} loginFlag     登录标志，0是当前登录的用户名和密码。
 *
 * @example
 * request:
 * {
 *     topicurl:"getLoginCfg"
 * }
 * response:
 * {
 *  "loginIp":      "192.168.0.1",
 *  "loginUser":    "admin",
 *  "loginPass":    "admin",
 *  "loginFlag":     0
 * }
 */
uiPost.prototype.getLoginCfg = function(postVar,callback){
    this.topicurl = 'getLoginCfg';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/getLoginCfg.json';
    }
    return this.post(postVar,callback);
};

/**
 * 集中管理的扫描 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-20
 * 
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {object}
 * @example
 * request:
 * {
 *     topicurl:"acScanAp"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.acScanAp = function(postVar,callback){
    this.topicurl = 'acScanAp';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 获取微信管理的链接
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-20
 * @property {String} static     1 - 网络接入正常，crp服务运行正常，url为生成二维码的链接 0 - crp服务异常
 * @property {String} url        微信URL。前缀：http://www.carystudio.com/router/wechatmanage/routerurl?url=
  后缀：设备远程访问地址http://f42854000666.d.carystudio.com:9080/的urlencode
  其中f42854000666为设备bridge iface的MAC地址去掉':'号的小写，做为设备的ID
 * @return   {Object}
 * @example
 * request:
 * {
 *     topicurl:"getCrpcConfig"
 * }
 * response:
 * {
 *     "status":   "1",
 *     "url":  "http://www.carystudio.com/router/wechatmanage/routerurl?url=http%3a%2f%2ff42854000666.d.carystudio.com%3a9080%2f"
 * }
 * 
 */
uiPost.prototype.getCrpcConfig = function(postVar,callback){
    this.topicurl = 'getCrpcConfig';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    if (globalConfig.debug) {
        this.url = '/newui/data/getCrpcConfig.json';
    }else{
        this.url = '/webapi'
    }
    return this.post(postVar,callback);
};

/**
 * 保存本地时间
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-20
 * @param {String} host_time  当前的时间，格式：yyyy-MM-dd HH:mm:ss
 * 
 * @property {String} success        响应状态：true：响应成功，false：响应失败
 * @property {String} error        错误
 * @property {String} lan_ip        局域网IP
 * @property {String} wtime        等待时间
 * @property {String} reserv        返回页面（参数未知）
 * @return   {object}
 * @example
 * request:
 * {
 *     topicurl:"NTPSyncWithHost"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.NTPSyncWithHost = function(postVar,callback){
    this.topicurl = 'NTPSyncWithHost';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 获取SS-Server配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2018-01-26
 * @property {String} enable    服务器配置的开关，0:关，1：开 
 * @property {String} server    服务器本机IP地址，一般为0.0.0.0
 * @property {String} server_port    服务器监听端口号，小于6535
 * @property {String} timeout        超时时间(秒)，默认60
 * @property {String} password       服务器设置的密码
 * @property {String} encrypt_method 加密方式，aes-128-cfb,aes-128-ctr,aes-128-gcm,aes-192-cfb, aes-192-ctr,aes-192-gcm,aes-256-cfb,aes-256-ctr,aes-256-gcm,bf-cfb,camellia-128-cfb,camellia-192-cfb,camellia-256-cfb,chacha20,chacha20-ietf,chacha20-ietf-poly1305,xchacha20-ietf-poly1305,rc4-md5,salsa20
 * @return   {object}
 * @example
 * request:
 * {
 *     topicurl:"getssServer"
 * }
 * response:
 * {
 *   "enable":   true,
 *   "server":   "192.168.0.5",
 *   "server_port":  "65535",
 *   "timeout":  "60",
 *   "password":   "12345678",
 *   "encrypt_method":  "rc4-md5"
 * }
 */
uiPost.prototype.getssServer = function(postVar,callback){
    this.topicurl = 'getssServer';
    this.async = true; // true:异步，false:同步。
    if (globalConfig.debug) {
        this.url = '/data/getssServer.json';
    }
    return this.post(postVar,callback);
};

/**
 * 设置SS-Server配置
 * @Author   Amy       <amy@carystudio.com>
 * @DateTime 2018-01-26
 * @param {String} enable    服务器配置的开关，0:关，1：开 
 * @param {String} server    服务器本机IP地址，一般为0.0.0.0
 * @param {String} server_port    服务器监听端口号，小于6535
 * @param {String} timeout        超时时间(秒)，默认60 
 * @param {String} password       服务器设置的密码
 * @param {String} encrypt_method 加密方式，aes-128-cfb,aes-128-ctr,aes-128-gcm,aes-192-cfb, aes-192-ctr,aes-192-gcm,aes-256-cfb,aes-256-ctr,aes-256-gcm,bf-cfb,camellia-128-cfb,camellia-192-cfb,camellia-256-cfb,chacha20,chacha20-ietf,chacha20-ietf-poly1305,xchacha20-ietf-poly1305,rc4-md5,salsa20
 * 
 * @property {String} success     响应状态：true：响应成功，false：响应失败
 * @property {String} error       错误
 * @property {String} lan_ip      局域网IP
 * @property {String} wtime       等待时间
 * @property {String} reserv      返回页面（参数未知）
 * @return   {object}
 * @example
 * request:
 * {
 *     topicurl:"setssServer",
 *     "enable":   true,
 *     "server":   "192.168.0.5",
 *     "server_port":  "65535",
 *     "timeout":  "60",
 *     "password":   "12345678",
 *     "encrypt_method":  "rc4-md5"
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setssServer = function(postVar,callback){
    this.topicurl = 'setssServer';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 设置语言
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   lang               设置语言。值有：'cn'：中文，'en': 英文
 * 
 * @property {String} success     响应状态：true：响应成功，false：响应失败
 * @property {String} error       错误
 * @property {String} lan_ip      局域网IP
 * @property {String} wtime       等待时间
 * @property {String} reserv      返回页面（参数未知）
 * @return   {object}
 * @example
 * request:
 * {
 *     topicurl:"setssServer",
 *     "lang":   'cn',
 * }
 * response:
 * {
 *   "success": true,
 *   "error":   null,
 *   "lan_ip":  "192.168.0.5",
 *   "wtime":   0,
 *   "reserv":  "reserv"
 * }
 */
uiPost.prototype.setLanguageCfg = function(postVar,callback){
    this.topicurl = 'setLanguageCfg';
    this.async = true; // true:异步，false:同步。
    return this.post(postVar,callback);
};

/**
 * 设置语言
 * @Author   Jeff       <yexk@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   lang               设置语言。值有：'cn'：中文，'en': 英文
 *
 * @property {String} success     响应状态：true：响应成功，false：响应失败
 * @property {String} error       错误
 * @property {String} lan_ip      局域网IP
 * @property {String} wtime       等待时间
 * @property {String} reserv      返回页面（参数未知）
 * @return   {object}
 * @example
 * request:
 * {
*     action:"getStatusInfo",
*     data:{},
* }
 * response:
* {
*     "rescode":0,
*     "data":{
*         "system":{
*             "Uptime":533627,
*             "FirmwareVersion":"V15.0.1b",
*             "ReleaseDate":20170412
*         },
*         "niclist":{
*             "em0":{
*                 "up":true,
*                 "ipaddr":"192.168.10.1",
*                 "mac":"00:0c:29:7f:11:ee",
*                 "dmesg":"",
*                 "friendly":"lan"
*             },
*             "em1":{
*                 "up":true,
*                 "ipaddr":"192.168.11.133",
*                 "mac":"00:0c:29:7f:11:f8",
*                 "dmesg":"",
*                 "friendly":"wan"
*             },
*             "em2":{
*                 "up":false,
*                 "ipaddr":null,
*                 "mac":"00:0c:29:7f:11:02",
*                 "dmesg":"",
*                 "friendly":"opt1"
*             }
*         },
*         "interface":{
*             "wan1":{
*                 "Interface":"wan1",
*                 "Mtu":"1500",
*                 "Enable":"1",
*                 "Protocol":"dhcp",
*                 "Nic":"em1",
*                 "Dns":"192.168.11.38",
*                 "Monitor":"",
*                 "Tier":"1",
*                 "Weight":"1",
*                 "Status":{
*                     "if":"em1",
*                     "status":"up",
*                     "capabilities":[
*                         "rxcsum",
*                         "txcsum",
*                         "vlan_mtu",
*                         "vlan_hwtagging",
*                         "vlan_hwcsum",
*                         "vlan_hwfilter",
*                         "netmap"
*                     ],
*                     "options":[
*                         "vlan_mtu",
*                         "vlan_hwtagging",
*                         "vlan_hwcsum",
*                         "performnud",
*                         "accept_rtadv",
*                         "auto_linklocal"
*                     ],
*                     "macaddr":"00:0c:29:7f:11:f8",
*                     "ipv4":[
*                         {
*                             "ipaddr":"192.168.11.133",
*                             "subnetbits":24
*                         }
*                     ],
*                     "ipv6":[
*                         {
*                             "ipaddr":"fe80::20c:29ff:fe7f:11f8",
*                             "subnetbits":64,
*                             "link-local":true
*                         }
*                     ],
*                     "mtu":"1500",
*                     "media":"1000baseT <full-duplex>",
*                     "linklocal":"fe80::20c:29ff:fe7f:11f8",
*                     "ipaddr":"192.168.11.133",
*                     "subnet":"255.255.255.0",
*                     "inerrs":"0",
*                     "outerrs":"0",
*                     "collisions":"0",
*                     "dhcplink":"up",
*                     "gateway":"192.168.11.38",
*                     "gatewayv6":null
*                 }
*             },
*             "lan1":{
*                 "Interface":"lan1",
*                 "Mtu":"1500",
*                 "Enable":"1",
*                 "Protocol":"static",
*                 "Nic":"em0",
*                 "Ip":"192.168.10.1",
*                 "Netmask":"255.255.255.0",
*                 "DhcpSvrEnable":"1",
*                 "DhcpStart":"192.168.10.2",
*                 "DhcpEnd":"192.168.10.254",
*                 "DhcpLeasetime":7200,
*                 "Status":{
*                     "if":"em0",
*                     "status":"up",
*                     "capabilities":[
*                         "rxcsum",
*                         "txcsum",
*                         "vlan_mtu",
*                         "vlan_hwtagging",
*                         "vlan_hwcsum",
*                         "vlan_hwfilter",
*                         "netmap"
*                     ],
*                     "options":[
*                         "vlan_mtu",
*                         "vlan_hwtagging",
*                         "vlan_hwcsum",
*                         "performnud",
*                         "auto_linklocal"
*                     ],
*                     "macaddr":"00:0c:29:7f:11:ee",
*                     "ipv4":[
*                         {
*                             "ipaddr":"192.168.10.1",
*                             "subnetbits":24
*                         }
*                     ],
*                     "ipv6":[
*                         {
*                             "ipaddr":"fe80::1:1",
*                             "subnetbits":64,
*                             "link-local":true
*                         }
*                     ],
*                     "mtu":"1500",
*                     "media":"1000baseT <full-duplex>",
*                     "linklocal":"fe80::1:1",
*                     "ipaddr":"192.168.10.1",
*                     "subnet":"255.255.255.0",
*                     "inerrs":"0",
*                     "outerrs":"0",
*                     "collisions":"0"
*                 }
*             },
 *            "wan2":{
 *                "Interface":"wan2",
 *                "Mtu":"1500",
 *                "Enable":"0",
 *                "Protocol":"",
 *                "Nic":"em2",
 *                "Dns":"",
 *                "Monitor":"",
 *                "Tier":"1",
 *                "Weight":"1",
 *                "Status":{
 *                    "if":"em2",
 *                    "status":"down",
 *                    "capabilities":[
 *                        "rxcsum",
 *                        "txcsum",
 *                        "vlan_mtu",
 *                        "vlan_hwtagging",
 *                        "vlan_hwcsum",
 *                        "vlan_hwfilter",
 *                        "netmap"
 *                    ],
 *                    "options":[
 *                        "rxcsum",
 *                        "txcsum",
 *                        "vlan_mtu",
 *                        "vlan_hwtagging",
 *                        "vlan_hwcsum",
 *                        "performnud",
 *                        "auto_linklocal"
 *                    ],
 *                    "macaddr":"00:0c:29:7f:11:02",
 *                    "ipv4":[
 *
 *                    ],
 *                    "ipv6":[
 *
 *                    ],
 *                    "mtu":"1500",
 *                    "media":"1000baseT <full-duplex>",
 *                    "inerrs":"0",
 *                    "outerrs":"0",
 *                    "collisions":"0",
 *                    "subnet":"0.0.0.0"
 *                }
 *            }
 *        }
 *    }
 *}
 */
uiPost.prototype.getStatusInfo = function(postVar,callback){
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};


/**
 * 获取WAN信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   action       主题
 * @param    {Object}   data        {}，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*     action:"getWanInfo",
*     "data":   {},
* }
 * response:
 * {
 *
* }
 */
uiPost.prototype.getWanInfo = function(postVar,callback){
    this.topicurl = 'getWanInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除多WAN
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   action       主题
 * @param    {Object}   data        {}，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*     action:"delInterfaceBind",
*     "data":   {},
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delInterfaceBind = function(postVar,callback){
    this.topicurl = 'delInterfaceBind';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除多WAN
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   action       主题
 * @param    {Object}   data
 * @param    {String}   data.Interface  绑定对象
 * @param    {String}   data.Nic  绑定的网卡
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*     action:"setInterfaceBind",
*     "data":   {
*           Interface:  "wan",
*           Nic:    "em2"
*     },
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setInterfaceBind = function(postVar,callback){
    this.topicurl = 'setInterfaceBind';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置多WAN
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-01-27
 * @param    {String}   action       主题
 * @param    {Object}   data
 * @param    {String}   data.Interface  绑定对象
 * @param    {String}   data.Tier  优先级
 * @param    {String}   data.Weight  权重
 * @param    {String}   data.Nic  绑定的网卡
 * @param    {String}   data.Protocol  连接方式
 * @param    {String}   data.Mtu  MTU
 * @param    {String}   data.Dns  NDS
 * @param    {String}   data.Monitor  网络连通监视IP
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"setWanInfo",
 *       "data":{
 *           "Interface":"wan1",
 *           "Tier":"1",
 *           "Weight":"1",
 *           "Nic":"em1",
 *           "Protocol":"dhcp",
 *           "Mtu":"1500",
 *           "Dns":"8.8.8.8",
 *           "Monitor":""
 *       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setWanInfo = function(postVar,callback){
    this.topicurl = 'setWanInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};


/**
 * 获取LAN信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    {}，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*     action:"getLanInfo",
*     "data":   {
*
*     }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getLanInfo = function(postVar,callback){
    this.topicurl = 'getLanInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置LAN信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {String}   data.Ip    IP地址
 * @param    {String}   data.Netmask    子网掩码
 * @param    {String}   data.DhcpSvrEnable    DHCP服务器开关 1：开 0：关
 * @param    {String}   data.DhcpStart    DHCP起始IP地址
 * @param    {String}   data.DhcpEnd    DHCP结束IP地址
 * @param    {String}   data.DhcpReleaseTime    租约时间
 * @param    {String}   data.Interface    lan口
 * @param    {String}   data.Nic       网卡
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"setLanInfo",
 *       "data":{
 *           "Ip":"192.168.10.1",
 *           "Netmask":"255.255.255.0",
 *           "DhcpSvrEnable":"1",
 *           "DhcpStart":"192.168.10.2",
 *           "DhcpEnd":"192.168.10.254",
 *           "DhcpReleaseTime":"3600",
 *           "Interface":"lan1",
 *           "Nic":"em0"
 *       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setLanInfo = function(postVar,callback){
    this.topicurl = 'setLanInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取静态DHCP信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    {}，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getStaticDhcp",
*       "data":{
*
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getStaticDhcp = function(postVar,callback){
    this.topicurl = 'getStaticDhcp';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取静态DHCP信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Array}   Mac    需要删除的MAC数组
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delStaticDhcp",
*       "data":{
*            "Mac":[
*                "00:0C:29:53:11:55",
*                "00:0C:29:7F:11:11"
*            ]
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delStaticDhcp = function(postVar,callback){
    this.topicurl = 'delStaticDhcp';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取静态DHCP信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    数据,孔=空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getArpInfo",
*       "data":{
*
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getArpInfo = function(postVar,callback){
    this.topicurl = 'getArpInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取静态DHCP信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-01
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {String}   data.Ip    IP
 * @param    {String}   data.Mac    MAC
 * @param    {String}   data.Descr    描述
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addStaticDhcp",
*        "data":{
*            "Ip":"192.168.10.144",
*            "Mac":"00:0C:29:7F:11:11",
*            "Descr":"11"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addStaticDhcp = function(postVar,callback){
    this.topicurl = 'addStaticDhcp';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取静态路由表信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题
 * @param    {Object}   data    数据空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getStaticRoute",
*        "data":{
*
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getStaticRoute = function(postVar,callback){
    this.topicurl = 'getStaticRoute';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除静态路由规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题
 * @param    {Object}   data    数据空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delStaticRoute",
*        "data":{
*            "Interface":"lan1",
*            "Ip":"192.168.54.123",
*            "Netmask":"255.255.255.0"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delStaticRoute = function(postVar,callback){
    this.topicurl = 'delStaticRoute';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 添加静态路由规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {String}   data.Interface    接口
 * @param    {Object}   data.Ip    IP地址
 * @param    {Object}   data.Netmask    子网掩码
 * @param    {Object}   data.Gateway    默认网关
 * @param    {Object}   data.Descr    描述
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addStaticRoute",
*       "data":{
*            "Interface":"lan1",
*            "Ip":"192.168.54.123",
*            "Netmask":"255.255.255.0",
*            "Gateway":"192.168.10.55",
*            "Descr":"55"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addStaticRoute = function(postVar,callback){
    this.topicurl = 'addStaticRoute';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取PPTP用户配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题
 * @param    {Object}   data    数据,空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getPptpdUsers",
*       "data":{
*
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getPptpdUsers = function(postVar,callback){
    this.topicurl = 'getPptpdUsers';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取L2PTP用户配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题
 * @param    {Object}   data    数据,空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getL2tpUsers",
*       "data":{
*
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getL2tpUsers = function(postVar,callback){
    this.topicurl = 'getL2tpUsers';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 添加账号管理用户信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题，addL2tpUser：L2tp账号， addPptpdUser：PPTP账号
 * @param    {Object}   data    数据,空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addL2tpUser",
*       "data":{
*
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addlocalServerUser = function(postVar,callback){
    this.topicurl = 'addlocalServerUser';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除账号管理用户信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-02
 * @param    {String}   action       主题，delL2tpUser：L2tp账号， delPptpdUser：PPTP账号
 * @param    {Object}   data    数据,空
 * @param    {Object}   data.Username    用户名
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addL2tpUser",
*       "data":{
*              "Username":"user"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delLocalServerUsers = function(postVar,callback){
    this.topicurl = 'delLocalServerUsers';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取QOS状态信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据,空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getQosStatus",
*       "data":{
*
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getQosStatus = function(postVar,callback){
    this.topicurl = 'getQosStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置QOS
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Object}   data.Type    限速类型，0 关闭（无上传，下载带宽），1 智能限速 ，2 自定义限速
 * @param    {Object}   data.Up    上传带宽
 * @param    {Object}   data.Down    下载带宽
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setQos",
*        "data":{
*            "Type":"2",
*            "Up":"125000",
*            "Down":"121500"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setQos = function(postVar,callback){
    this.topicurl = 'setQos';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除QOS规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Object}   data.Ip    要删除的IP地址
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delQosCustom","
*       data":{
*           "Ip":"192.168.10.120"
*           }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delQosCustom = function(postVar,callback){
    this.topicurl = 'delQosCustom';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 新增QOS规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Object}   data.Ip    要删除的IP地址
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addQosCustom","
*       data":{
*           "Ip":"192.168.10.120"
*           }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addQosCustom = function(postVar,callback){
    this.topicurl = 'addQosCustom';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取防火墙信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getFilterStatus",
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getFilterStatus = function(postVar,callback){
    this.topicurl = 'getFilterStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 新增IP/端口过滤规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Object}   data.Type    接口类型，1 WAN , 2 LAN1
 * @param    {Object}   data.Protocol    协议
 * @param    {Object}   data.Ip    Ip地址
 * @param    {Object}   data.PortStart    起始端口
 * @param    {Object}   data.PortEnd    结束端口
 * @param    {Object}   data.comment    描述
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addFilter",
*        "data":{
*            "Type":"2",
*            "Protocol":"tcp",
*            "Ip":"192.168.10.1",
*            "PortStart":"3",
*            "PortEnd":"22",
*            "comment":"22"
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addFilter = function(postVar,callback){
    this.topicurl = 'addFilter';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除IP/端口过滤规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-03
 * @param    {String}   action       主题
 * @param    {Object}   data    数据
 * @param    {Array}   data.Id    要删除的规则ID
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*        "action":"delFilter",
*        "data":{
*           "Id":[
*               "k24dskbgeu",
*               "lljxiw45p1"
*           ]
*        }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delFilter = function(postVar,callback){
    this.topicurl = 'delFilter';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取端口转发信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"postPatData"
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.postPatData = function(postVar,callback){
    this.topicurl = 'postPatData';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 新增端口转发规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addPatItem"
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addPatItem = function(postVar,callback){
    this.topicurl = 'addPatItem';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除端口转发规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delPatItem"
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delPatItem = function(postVar,callback){
    this.topicurl = 'delPatItem';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取DNS转发信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"getDnsForwardStatus"
 * }
 * response:
 * {
 *
 * }
 */
uiPost.prototype.getDnsForwardStatus = function(postVar,callback){
    this.topicurl = 'getDnsForwardStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置DNS配置状态
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Enable       1：开启 0：关闭
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setDnsForwardStatus",
*       "data":{
*           "Enable":"1"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setDnsForwardStatus = function(postVar,callback){
    this.topicurl = 'setDnsForwardStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除DNS转发规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Domain       域名
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"delDnsOverwrite",
 *       "data":{
 *          "Domain":"csg200p.test"
 *       }
 * }
 * response:
 * {
 *
 * }
 */
uiPost.prototype.delDnsOverwrite = function(postVar,callback){
    this.topicurl = 'delDnsOverwrite';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 添加DNS转发规则
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Descr       描述
 * @param    {String}   data.Domain       域名
 * @param    {String}   data.Ip       IP地址
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"addDnsOverwrite",
 *       "data":{
 *          "Descr":"ddd",
 *          "Domain":"csg200p.test",
 *          "Ip":"192.168.11.55"
 *       }
 * }
 * response:
 * {
 *
 * }
 */
uiPost.prototype.addDnsOverwrite = function(postVar,callback){
    this.topicurl = 'addDnsOverwrite';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取portal认证配置状态
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getPortalStatus",
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getPortalStatus = function(postVar,callback){
    this.topicurl = 'getPortalStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置portal认证配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Enable       portal使能标志：1 开启  0：关闭
 * @param    {String}   data.Type       portal认证类型：local 本地，server 云服务
 *
 * @param    {String}   data.Type == ‘local’        本地服务器参数
 * @param    {String}   data.IdleTimeout       空闲退出时间
 * @param    {String}   data.SessionTimeout       强制退出时间
 * @param    {String}   data.AllowedIp       IP白名单
 * @param    {String}   data.AllowedMac       MAC白名单
 * @param    {String}   data.LoginPageStatus       登录页面
 * @param    {String}   data.appId       APPID
 * @param    {String}   data.shop_id       SHOPID
 * @param    {String}   data.ssid       SSID
 * @param    {String}   data.secretkey       secretkey
 * @param    {String}   data.attention       强制关注   0：强制 1：不强制
 *
 * @param    {String}   data.Type == ‘server’        云服务器参数
 * @param    {String}   data.GatewayId       热点ID
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *       "action":"setPortalStatus",
 *       "data":{
 *          //开启
 *          "Enable":"1",
 *
 *          //本地服务器
 *          "Type":"local",
 *          "IdleTimeout":"10",
 *          "SessionTimeout":"600",
 *          "AllowedIp":"",
 *          "AllowedMac":"",
 *          "LoginPageStatus":"0",
 *          "appId":"wxc477b56984d37818",
 *          "shop_id":"3617584",
 *          "ssid":"totolink66666",
 *          "secretkey":"d165eef2f597c417bb08d1756d1591e2",
 *          "attention":"0"
 *
 *          //云服务器
 *          "GatewayId":"totolink66666"
 *
 *          //关闭
 *          "Enable":"0",
 *      }
 * }
 * response:
 * {
 *
 * }
 */
uiPost.prototype.setPortalStatus = function(postVar,callback){
    this.topicurl = 'setPortalStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取Portal本地账号信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.pagenum       当前页
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getPortalUsers",
*       "data":{
*           "pagenum":"1"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getPortalUsers = function(postVar,callback){
    this.topicurl = 'getPortalUsers';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 添加Portal本地账号
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Username       用户名
 * @param    {String}   data.Password       密码
 * @param    {String}   data.ExpireTime       登录有效截止时间
 * @param    {String}   data.RemainTime       可登录时间
 * @param    {String}   data.MutilLogins       是否可重复登录
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"addPortalUser",
*       "data":{
*           "Username":"test",
*           "Password":"test12",
*           "ExpireTime":"2018-02-07 14:32:08",
*           "RemainTime":"111",
*           "MutilLogins":"0"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.addPortalUser = function(postVar,callback){
    this.topicurl = 'addPortalUser';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 更新Portal本地账号信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Username       用户名
 * @param    {String}   data.Password       密码
 * @param    {String}   data.ExpireTime       登录有效截止时间
 * @param    {String}   data.RemainTime       可登录时间
 * @param    {String}   data.MutilLogins       是否可重复登录
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"updatePortalUser",
*       "data":{
*           "Username":"test",
*           "Password":"test12",
*           "ExpireTime":"2018-02-07 14:32:08",
*           "RemainTime":"111",
*           "MutilLogins":"0"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.updatePortalUser = function(postVar,callback){
    this.topicurl = 'updatePortalUser';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 删除Portal本地账号
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Username       用户名
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delPortalUser",
*       "data":{
*           "Username":"test",
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delPortalUser = function(postVar,callback){
    this.topicurl = 'delPortalUser';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 强制Portal用户下线
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Sessionid       Sessionid
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"delPortalSession", 
*       "data":{
*           "Sessionid":"11",
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.delPortalSession = function(postVar,callback){
    this.topicurl = 'delPortalSession';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取Portal在线用户
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   action       主题
 * @param    {object}   data       数据（参数还不清楚）
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getPortalSessions",
*       "data":{
*       
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getPortalSessions = function(postVar,callback){
    this.topicurl = 'getPortalSessions';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 修改管理员密码
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.NewPassword       新密码
 * @param    {String}   data.OldPassword       原密码
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"changePassword",
*       "data":{
*          "NewPassword":"admin",
*          "OldPassword":"admin"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.changePassword = function(postVar,callback){
    this.topicurl = 'changePassword';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取网关时间
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.NewPassword       新密码
 * @param    {String}   data.OldPassword       原密码
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getGwTime",
*       "data":{
*          "NewPassword":"admin",
*          "OldPassword":"admin"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getGwTime = function(postVar,callback){
    this.topicurl = 'getGwTime';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置网关时间
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Time       时间
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setGwTime",
*       "data":{
*           "Time":"201802081609"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setGwTime = function(postVar,callback){
    this.topicurl = 'setGwTime';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取远程配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getRemoteAccess",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getRemoteAccess = function(postVar,callback){
    this.topicurl = 'getRemoteAccess';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置远程配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setRemoteAccess",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setRemoteAccess = function(postVar,callback){
    this.topicurl = 'setRemoteAccess';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取固件配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getFirmwareInfo",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getFirmwareInfo = function(postVar,callback){
    this.topicurl = 'getFirmwareInfo';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 升级固件
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"upgradeFirmware",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.upgradeFirmware = function(postVar,callback){
    this.topicurl = 'upgradeFirmware';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 重启系统
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"reboot",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.sysReboot = function(postVar,callback){
    this.topicurl = 'reboot';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取定时重启系统
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据，空
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getRebootSched",
*       "data":{}
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getRebootSched = function(postVar,callback){
    this.topicurl = 'getRebootSched';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置定时重启系统
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-08
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Enable      状态：0 禁用,   1 启用
 * @param    {String}   data.TimeMonth      月份
 * @param    {String}   data.TimeMonth      日期
 * @param    {String}   data.TimeMonth      星期
 * @param    {String}   data.TimeMonth      小时
 * @param    {String}   data.TimeMonth      分钟
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setRebootSched",
*       "data":{
*           "Enable":"0",
*           "TimeMonth":"",
*           "TimeDate":"",
*           "TimeWeek":"",
*           "TimeHour":"0",
*           "TimeMin":"0"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setRebootSched = function(postVar,callback){
    this.topicurl = 'setRebootSched';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取L2TP服务器配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-23
 * @param    {String}   action       主题
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getL2tpStatus"
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getL2tpStatus = function(postVar,callback){
    this.topicurl = 'getL2tpStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置L2TP服务器配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-23
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Enable       状态：1 启用，0 禁用
 * @param    {String}   data.LocalIp       服务器IP
 * @param    {String}   data.ClientIpStart       起始IP地址
 * @param    {String}   data.ClientIpEnd       结束IP地址
 * @param    {String}   data.Dns1       首选DNS
 * @param    {String}   data.Dns2       备选DNS
 * @param    {String}   data.Wins       Wins服务器地址
 * @param    {String}   data.AuthType       认证类型
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setL2tpStatus",
*       "data":{
*           "Enable":"1",
*           "LocalIp":"10.10.10.1",
*           "ClientIpStart":"10.10.10.2",
*           "ClientIpEnd":"10.10.10.254",
*           "Dns1":"8.8.8.8",
*           "Dns2":"",
*           "Wins":"",
*           "AuthType":"chap"
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setL2tpStatus = function(postVar,callback){
    this.topicurl = 'setL2tpStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 获取PPTP服务器配置信息
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-24
 * @param    {String}   action       主题
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"getPptpdStatus"
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.getPptpdStatus = function(postVar,callback){
    this.topicurl = 'getPptpdStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 设置PPTP服务器配置
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-24
 * @param    {String}   action       主题
 * @param    {object}   data       数据
 * @param    {String}   data.Enable       状态 1：启用 0：禁用
 * @param    {String}   data.ClientIpStart       起始IP地址
 * @param    {String}   data.ClientIpEnd       结束IP地址
 * @param    {String}   data.Encrypt       MPPE数据加密
 * @param    {String}   data.LocalIp       服务器地址
 * @param    {String}   data.Wins       Wins服务器地址
 * @param    {String}   data.Dns1       首选DNS
 * @param    {String}   data.Dns2       备选DNS
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
*       "action":"setPptpdStatus",
*       "data":{
*           "Enable":"1",
*           "ClientIpStart":"10.10.10.2",
*           "ClientIpEnd":"10.10.10.254",
*           "Encrypt":"0",
*           "LocalIp":"10.10.10.1",
*           "Wins":"",
*           "Dns1":"8.8.8.8",
*           "Dns2":""
*       }
* }
 * response:
 * {
*
* }
 */
uiPost.prototype.setPptpdStatus = function(postVar,callback){
    this.topicurl = 'setPptpdStatus';
    this.async = true; // true:异步，false:同步。
    this.url = '/webapi';
    return this.post(postVar,callback);
};

/**
 * 用户登录
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-24
 * @param    {String}   action       主题
 *
 * @param    {String}   username       用户名
 * @param    {String}   password        密码
 * @param    {String}   newui        新UI登录
 *
 * @property {String}
 * @return   {object}
 * @example
 * request:
 * {
 *      "username":"admin"
 *      "password":"password"
 *      "newui":"new"
 * }
 * response:
 * {
 *
 * }
 */
uiPost.prototype.doLogin = function(postVar,callback){
    this.topicurl = 'doLogin';
    this.async = true; // true:异步，false:同步。
    this.url = '/login/newdologin';
    return this.post(postVar,callback);
};


    /*AC管理主题*/
    uiPost.prototype.SaveAcConfig = function(postVar,callback){
        this.topicurl = 'SaveAcConfig';     /*保存数据库文件*/
        this.async = true; // true:异步，false:同步。
        return this.post(postVar,callback);
    };
    uiPost.prototype.AcRestore = function(postVar,callback){
        this.topicurl = 'AcRestore';        /*恢复数据库出厂设置*/
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.scanAp = function(postVar,callback){
        this.topicurl = 'acScanAp';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApIp = function(postVar,callback){
        this.topicurl = 'setApIp';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApReboot = function(postVar,callback){
        this.topicurl = 'setApReboot';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApName = function(postVar,callback){
        this.topicurl = 'setApName';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApLedState = function(postVar,callback){
        this.topicurl = 'setApLedState';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApRestore = function(postVar,callback){
        this.topicurl = 'setApRestore';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setApUpgrade = function(postVar,callback){
        this.topicurl = 'setApUpgrade';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getApStatusConfig = function(postVar,callback){
        this.topicurl = 'getApStatusConfig';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setQuick = function(postVar,callback){
        this.topicurl = 'setQuick';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
	uiPost.prototype.getApUpgradeInfo = function(postVar,callback){
        this.topicurl = 'getApUpgradeInfo';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delApUpgradeFile = function(postVar,callback){
        this.topicurl = 'delApUpgradeFile';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getLangConfig = function(postVar,callback){
        this.topicurl = 'getLangConfig';
        this.async = true; // true:异步，false:同步。
        this.url = '/index/getlangcfg';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setLangConfig = function(postVar,callback){
        this.topicurl = 'setLangConfig';
        this.async = true; // true:异步，false:同步。
        this.url = '/index/setlangcfg';
        return this.post(postVar,callback);
    };
    uiPost.prototype.saveConfigFile = function(postVar,callback){
        this.topicurl = 'saveConfigFile';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.updataConfig = function(postVar,callback){
        this.topicurl = 'updataConfig';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnSvrStatus = function(postVar,callback){
        this.topicurl = 'getOpenvpnSvrStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setIpsecPhase1 = function(postVar,callback){
        this.topicurl = 'setIpsecPhase1';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setOpenvpnSvrStatus = function(postVar,callback){
        this.topicurl = 'setOpenvpnSvrStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getIpsecPhase1 = function(postVar,callback){
        this.topicurl = 'getIpsecPhase1';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnEncryInfo = function(postVar,callback){
        this.topicurl = 'getOpenvpnEncryInfo';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getIpsecPhase2 = function(postVar,callback){
        this.topicurl = 'getIpsecPhase2';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getLanWanInf = function(postVar,callback){
        this.topicurl = 'getLanWanInf';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setIpsecPhase2 = function(postVar,callback){
        this.topicurl = 'setIpsecPhase2';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getIpsecStatus = function(postVar,callback){
        this.topicurl = 'getIpsecStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delIpsecPhase1 = function(postVar,callback){
        this.topicurl = 'delIpsecPhase1';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delIpsecPhase2 = function(postVar,callback){
        this.topicurl = 'delIpsecPhase2';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.ipsecConnect = function(postVar,callback){
        this.topicurl = 'ipsecConnect';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.ipsecDisconnect = function(postVar,callback){
        this.topicurl = 'ipsecDisconnect';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getDyndnsList = function(postVar,callback){
        this.topicurl = 'getDyndnsList';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setDyndns = function(postVar,callback){
        this.topicurl = 'setDyndns';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delDyndns = function(postVar,callback){
        this.topicurl = 'delDyndns';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnUsers = function(postVar,callback){
        this.topicurl = 'getOpenvpnUsers';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.exportOvpnClientConf = function(postVar,callback){
        this.topicurl = 'exportOvpnClientConf';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnClientStatus = function(postVar,callback){
        this.topicurl = 'getOpenvpnClientStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setOpenvpnClientStatus = function(postVar,callback){
        this.topicurl = 'setOpenvpnClientStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getProxy = function(postVar,callback){
        this.topicurl = 'getProxy';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setProxy = function(postVar,callback){
        this.topicurl = 'setProxy';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnStatus = function(postVar,callback){
        this.topicurl = 'getOpenvpnStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getOpenvpnLogs = function(postVar,callback){
        this.topicurl = 'getOpenvpnLogs';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getIpsecLogs = function(postVar,callback){
        this.topicurl = 'getIpsecLogs';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.startCapturePacket = function(postVar,callback){
        this.topicurl = 'startCapturePacket';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getCaptureStatus = function(postVar,callback){
        this.topicurl = 'getCaptureStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getCaptureView = function(postVar,callback){
        this.topicurl = 'getCaptureView';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delCapturePacket = function(postVar,callback){
        this.topicurl = 'delCapturePacket';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getFrStatus = function(postVar,callback){
        this.topicurl = 'getFrStatus';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setFrGeneral = function(postVar,callback){
        this.topicurl = 'setFrGeneral';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getFrUser = function(postVar,callback){
        this.topicurl = 'getFrUser';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setFrUser = function(postVar,callback){
        this.topicurl = 'setFrUser';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delFrUser = function(postVar,callback){
        this.topicurl = 'delFrUser';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getClientConfig = function(postVar,callback){
        this.topicurl = 'getClientConfig';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delClientCfg = function(postVar,callback){
        this.topicurl = 'delClientCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setFrClientCfg = function(postVar,callback){
        this.topicurl = 'setFrClientCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getTraShaCfg = function(postVar,callback){
        this.topicurl = 'getTraShaCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delTraShaPipeCfg = function(postVar,callback){
        this.topicurl = 'delTraShaPipeCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delTraShaQueueCfg = function(postVar,callback){
        this.topicurl = 'delTraShaQueueCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delTraShaRulesCfg = function(postVar,callback){
        this.topicurl = 'delTraShaRulesCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setTraShaPipeCfg = function(postVar,callback){
        this.topicurl = 'setTraShaPipeCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setTraShaQueueCfg = function(postVar,callback){
        this.topicurl = 'setTraShaQueueCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.setTraShaRuleCfg = function(postVar,callback){
        this.topicurl = 'setTraShaRuleCfg';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.getWolEntryList = function(postVar,callback){
        this.topicurl = 'getWolEntryList';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.addWolEntry = function(postVar,callback){
        this.topicurl = 'addWolEntry';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.wolWakeup = function(postVar,callback){
        this.topicurl = 'wolWakeup';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delWolEntry = function(postVar,callback){
        this.topicurl = 'delWolEntry';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.addLanInterface = function(postVar,callback){
        this.topicurl = 'addLanInterface';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };
    uiPost.prototype.delLanInterface = function(postVar,callback){
        this.topicurl = 'delInterface';
        this.async = true; // true:异步，false:同步。
        this.url = '/webapi';
        return this.post(postVar,callback);
    };

	obj.uiPost = new uiPost();
})(window);