/**
 * 全局配置选项配置
 *  
 * @property {bootlean}  debug  是否使用data目录的测试数据
 * @property {bootlean}  ajaxType  当前为真使用get方式提交，为假就是POST方式提交
 * @property {bootlean}  xs  详细说明参考iview手册
 * @property {bootlean}  sm  详细说明参考iview手册
 * @property {bootlean}  md  详细说明参考iview手册
 * @property {bootlean}  lg  详细说明参考iview手册
 * @property {String}  defaultLang  默认值显示中文
 * @property {String}  version  固件版本
 *  支持的系统模式。GW：gateway;BR：bridge;RPT：repeater;WISP：WISP;CLI：client:MESH：mesh;
 * // 桥模式，没有广域网设置和状态信息显示，没有qos和防火墙
 */
var globalConfig = {
    "debug":false,
    "ajaxType":false,
    "cs":"Carystudio",
    "showMenu":true,
    "urlExtension":'.html',
    "showSearch":false,
    "showWechatQR":false,
    "showLanguage":true,
    "showHelp":true,
    "hasMobile":true,
    "labelWith":200,
    "defaultLang":"en",
    "currentMode":"GW",
    "currentModeMenu":{
        "BR":['/internet/wan','/firewall/qos','/firewall','/internet/static_dhcp','/internet/dhcp_detect']
    },
    "xs":24,
    "lg":{span:18,offset:3},
    "version":"V1.0.1bate",
    "helpUrl":"http://www.carystudio.com",
    "wlanSupport" :'2,5',
    "cgiUrl" :'/cgi-bin/cstecgi.cgi',
    "copyright":'Copyright &copy; [date] Carystudio Ltd., All Rights Reserved',
    "iHeader":"Carystudio",
    "m":{
        "url":'/mobile/',
        "showHeader":true,
        "showBottom":true
    }
};

/**
 * 语言切换选项关联配置
 * 
 * @example
 *  {
 *  'cn':'简体中文',
 *  'en':'English'
 *  };
 * 
 */ 
var languages = {
    'cn':'简体中文',
    'en':'English'
};

/**
 * 菜单配置
 *
 * @property {string} menu.href 菜单的路径
 * @property {string} menu.icon 菜单的图标类名（样式类名）
 * @property {string} menu.lang 菜单的文字
 * @property {boolean} menu.display 是否显示菜单
 * @property {boolean} menu.sub 是否有自己。如果有直接设置下面的属性
 * @property {boolean} menu.sub.href 二级菜单的路径
 * @property {boolean} menu.sub.lang 二级菜单的语言
 * @property {boolean} menu.sub.display 是否显示二级菜单
 *
 * @example
 *  {
 *      "href": "/index",
 *      "icon": "cs-icon icon-sytem",
 *      "lang": "status",
 *      "display":true,
 *      "sub": false
 *  },
 * {
 *      "href": "#",
 *      "icon": "cs-icon icon-internet",
 *      "lang": "network",
 *      "display":true,
 *      "sub": [
 *          {
 *              "href": "/internet/wan",
 *              "lang": "net_wan",
 *              "display":true
 *          },
 *          {
 *              "href": "/internet/lan",
 *              "lang": "net_lan",
 *              "display":true
 *          },
 *          {
 *              "href": "/internet/static_dhcp",
 *              "lang": "net_static_dhcp",
 *              "display":true
 *          }
 *      ]
 *  }
 * 
 */
var menu = [
    {
  	"id":'1',
        "href": "/newui/index",
        "icon": "ios-home",
        "lang": "status",
        "display":true,
        "sub": false
    },
    {
    	"id":"2",
        "href": "/newui/opmode",
        "icon": "more",
        "lang": "opmode",
        "display":false,
        "sub": false
    },
    {
	"id":"3",
        "href": "/newui/internet",
        "icon": "ios-world",
        "lang": "network",
        "display":true,
        "sub": [
            {
		"id":"3-1",
                "href": "/newui/internet/wan",
                "lang": "wan",
                "display":true
            },
            {
		"id":"3-2",
                "href": "/newui/internet/lan",
                "lang": "lan",
                "display":true
            },
            {
		"id":"3-3",
                "href": "/newui/internet/static_dhcp",
                "lang": "static_dhcp",
                "display":true
            },
	    {
		"id":"3-4",
	    	"href": "/newui/internet/dhcp_detect",
                "lang": "dhcp_detect",
                "display":false
	    },
            {
	    	"id":"3-5",
                "href": "/newui/internet/route",
                "lang": "route",
                "display":true
            }
        ]
    },
    {
	    "id":"4",
        "href": "/newui/wireless",
        "icon": "wifi",
        "lang": "wireless",
        "display":false,
        "sub": [
            {
		    "id":"4-1",
                "href": "/newui/wireless/status",
                "lang": "wifi_status",
                "display":true
            },
            {
            "id":"4-2",
                "href": "/newui/wireless/wifi",
                "lang": "basic",
                "display":true
            },
            {
	    	    "id":"4-3",
                "href": "/newui/wireless/multiap",
                "lang": "multiap",
                "display":true
            },
            {
	        	"id":"4-4",
                "href": "/newui/wireless/acl",
                "lang": "acl",
                "display":true
            },
            {
		        "id":"4-5",
                "href": "/newui/wireless/wds",
                "lang": "wds",
                "display":true
            },
            {
		        "id":"4-6",
                "href": "/newui/wireless/wps",
                "lang": "wps",
                "display":true
            },
            {
		        "id":"4-7",
                "href": "/newui/wireless/advanced",
                "lang": "advanced",
                "display":true
            }
        ]
    },
    {
        "id":"5",
        "href": "/wireless?5g",
        "icon": "wifi",
        "lang": "wireless_5g",
        "display":false,
        "sub": [
            {
                "id":"5-1",
                "href": "/wireless/status?5g",
                "lang": "wifi_status",
                "display":true
            },
            {
                "id":"5-2",
                "href": "/wireless/wifi?5g",
                "lang": "basic",
                "display":true
            },
            {
                "id":"5-3",
                "href": "/wireless/multiap?5g",
                "lang": "multiap",
                "display":true
            },
            {
                "id":"5-4",
                "href": "/wireless/acl?5g",
                "lang": "acl",
                "display":true
            },
            {
                "id":"5-5",
                "href": "/wireless/wds?5g",
                "lang": "wds",
                "display":true
            },
            {
                "id":"5-6",
                "href": "/wireless/wps?5g",
                "lang": "wps",
                "display":true
            },
            {
                "id":"5-7",
                "href": "/wireless/advanced?5g",
                "lang": "advanced",
                "display":true
            }
        ]
    },
    {
        "id":"6",
        "href": "/newui/server",
        "icon": "gear-b",
        "lang": "server",
        "display":true,
        "sub": [
            {
                "id":"6-1",
                "href": "/newui/server/pptp",
                "lang": "pptp",
                "display":true
            },
            {
                "id":"6-2",
                "href": "/newui/server/l2tp",
                "lang": "l2tp",
                "display":true
            },
            {
                "id":"6-3",
                "href": "/newui/server/account",
                "lang": "account",
                "display":true
            }
        ]
    },
    {
        "id":"7",
        "href": "/newui/firewall/qos",
        "icon": "cube",
        "lang": "qos",
        "display":true,
        "sub": false
    },
    {
        "id":"14",
        "href": "/newui/vpn",
        "icon": "paper-airplane",
        "lang": "vpn",
        "display":true,
        "sub": [
            {
                "id":"14-1",
                "href": "/newui/vpn/openservers",
                "lang": "openservers",
                "display":true
            },
            {
                "id":"14-2",
                "href": "/newui/vpn/openclients",
                "lang": "openclients",
                "display":true
            },
            {
                "id":"14-3",
                "href": "/newui/vpn/ipsec",
                "lang": "ipsec",
                "display":true
            },
            {
                "id":"14-4",
                "href": "/newui/vpn/account",
                "lang": "account",
                "display":true
            }
        ]
    },
    {
        "id":"15",
        "href": "/newui/service",
        "icon": "ios-cog",
        "lang": "service",
        "display":true,
        "sub": [
            {
                "id":"15-1",
                "href": "/newui/service/dynamicdns",
                "lang": "dynamicdns",
                "display":true
            }
        ]
    },
    {
        "id":"8",
        "href": "/newui/firewall",
        "icon": "fireball",
        "lang": "firewall",
        "display":true,
        "sub": [
            {
		        "id":"8-1",
                "href": "/newui/firewall/firewall",
                "lang": "firewall_type",
                "display":false
            },
            {
		        "id":"8-2",
                "href": "/newui/firewall/ipf",
                "lang": "ipf",
                "display":true
            },
            {
		        "id":"8-3",
                "href": "/newui/firewall/macf",
                "lang": "macf",
                "display":false
            },
            {
		        "id":"8-4",
                "href": "/newui/firewall/urlf",
                "lang": "urlf",
                "display":false
            },
            {
		        "id":"8-5",
                "href": "/newui/firewall/portfwd",
                "lang": "portfwd",
                "display":true
            },
            {
	    	    "id":"8-6",
                "href": "/newui/firewall/dnsfwd",
                "lang": "dnsfwd",
                "display":true
            },
            {
	    	    "id":"8-7",
                "href": "/newui/firewall/vpnpass",
                "lang": "vpnpass",
                "display":false
            },
            {
                "id":"8-8",
                "href": "/newui/firewall/dmz",
                "lang": "dmz",
                "display":false
            },
            {
                "id":"8-9",
                "href": "/newui/firewall/time_rule",
                "lang": "time_rule",
                "display":false
            }
        ]
    }, {
        "id":"9",
        "href": "/newui/portal",
        "icon": "wifi",
        "lang": "portal",
        "display":true,
        "sub": [
            {
                "id":"9-1",
                "href": "/newui/portal/portal",
                "lang": "portal",
                "display":true
            },
            {
                "id":"9-2",
                "href": "/newui/portal/localaccount",
                "lang": "localaccount",
                "display":true
            },
            {
                "id":"9-3",
                "href": "/newui/portal/addaccounts",
                "lang": "addaccounts",
                "display":true
            },
            {
                "id":"9-4",
                "href": "/newui/portal/loginuser",
                "lang": "loginuser",
                "display":true
            }
        ]
    },
    {
        "id":"13",
        "href": "/newui/ac",
        "icon": "android-options",
        "lang": "ac",
        "display":true,
        "sub": [
            {
                "id":"13-1",
                "href": "/newui/ac/central",
                "lang": "central",
                "display":true
            },
            {
                "id":"13-2",
                "href": "/newui/ac/database",
                "lang": "database",
                "display":true
            }
        ]
    },
    {
        "id":"10",
        "href": "/newui/adm",
        "icon": "gear-a",
        "lang": "management",
        "display":true,
        "sub": [
            {
                "id":"10-1",
                "href": "/newui/adm/changepwd",
                "lang": "changepwd",
                "display":true
            },
            {
                "id":"10-2",
                "href": "/newui/adm/time",
                "lang": "time",
                "display":true
            },
            {
                "id":"10-3",
                "href": "/newui/adm/ddns",
                "lang": "ddns",
                "display":false
            },
            {
                "id":"10-4",
                "href": "/newui/adm/remote",
                "lang": "remote",
                "display":true
            },
            {
                "id":"10-5",
                "href": "/newui/adm/upnp",
                "lang": "upnp",
                "display":false
            },
            {
                "id":"10-6",
                "href": "/newui/adm/firmware",
                "lang": "firmware",
                "display":true
            },
            {
                "id":"10-7",
                "href": "/newui/adm/config",
                "lang": "config",
                "display":true
            },
            {
                "id":"10-8",
                "href": "/newui/adm/syslog",
                "lang": "syslog",
                "display":false
            },
            {
                "id":"10-9",
                "href": "/newui/adm/schedule",
                "lang": "reboot_schedule",
                "display":true
            },
            {
                "id":"10-10",
                "href": "/newui/wireless/schedule",
                "lang": "wifi_schedule",
                "display":false
            },
            {
                "id":"10-11",
                "href": "javascript:logout();",
                "lang": "logout",
                "display":true
            }
        ]
    },
    {
        "id":"11",
        "href": "/newui/central",
        "icon": "android-options",
        "lang": "central",
        "display":false,
        "sub": false
    },
    {
        "id":"12",
        "href": "/newui/net",
        "icon": "ios-paperplane",
        "lang": "net",
        "display":false,
        "sub": [
            {
                "id":"12-1",
                "href": "/newui/net/ssserver",
                "lang": "ss_server",
                "display":false
            }
        ]
    }
];

var mobileMenu = [{
    id: "status",
    icon: "ios-speedometer-outline",
    href: "index",
    display:true,
    text: "status"
},/* {
    id: "setting",
    icon: "ios-world-outline",
    href: "setting",
    display:true,
    text: "network"
}, {
    id: "network",
    icon: "wifi",
    href: "network",
    display:false,
    text: "wireless"
}, */{
    id: "management",
    icon: "ios-settings",
    href: "management",
    display:true,
    text: "management"
}];

var DEVICE = {
    'csid':'CSG2000P',
    'model':'Gateway'
};

