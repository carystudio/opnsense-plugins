SET FOREIGN_KEY_CHECKS=0;

DROP DATABASE IF EXISTS `csgateway`;
CREATE DATABASE `csgateway` DEFAULT CHARSET utf8 COLLATE utf8_general_ci;
use csgateway;

CREATE TABLE `AP_UPGRADE` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `csid` varchar(32) DEFAULT NULL,
  `svnnum` int(11) DEFAULT NULL,
  `builddate` varchar(10) DEFAULT NULL,
  `filepath` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `APGROUP` (
  `gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(18) DEFAULT NULL,
  `totalap` int(11) DEFAULT '0',
  `onlineap` int(11) DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `APLIST` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `apname` varchar(32) DEFAULT NULL,
  `apmac` char(17) DEFAULT NULL,
  `ipaddr` varchar(18) DEFAULT NULL,
  `netmask` varchar(18) DEFAULT NULL,
  `gateway` varchar(18) DEFAULT NULL,
  `pridns` varchar(18) DEFAULT NULL,
  `secdns` varchar(18) DEFAULT NULL,
  `apstate` int(11) DEFAULT '0',
  `ledstate` int(11) DEFAULT '1',
  `apkey` varchar(32) DEFAULT 'csapkey2017',
  `csid` varchar(32) DEFAULT NULL,
  `model` varchar(10) DEFAULT NULL,
  `svnnum` int(11) DEFAULT NULL,
  `builddate` varchar(10) DEFAULT NULL,
  `uptime` varchar(20) DEFAULT NULL,
  `softver` varchar(10) DEFAULT NULL,
  `timestamp` varchar(20) DEFAULT '1970-01-01 08:00:00',
  `aptype` int(11) DEFAULT NULL,
  `username` varchar(10) DEFAULT 'admin',
  `password` varchar(10) DEFAULT 'admin',
  `gid` int(11) DEFAULT '1',
  `hftimes` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_apmac` (`apmac`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE `WIFI0_STATUS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT '1',
  `apid` int(11) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `wirelessmode` int(11) DEFAULT '9',
  `htmode` int(11) DEFAULT '0',
  `channel` int(11) DEFAULT '0',
  `txpower` int(11) DEFAULT '100',
  `beacon` int(11) DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `WIFI1_CONFIG` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `wirelessmode` int(11) DEFAULT NULL,
  `htmode` int(11) DEFAULT NULL,
  `channel` int(11) DEFAULT '0',
  `txpower` int(11) DEFAULT '100',
  `beacon` int(11) DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `WIFI1_STATUS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT '1',
  `apid` int(11) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `wirelessmode` int(11) DEFAULT '14',
  `htmode` int(11) DEFAULT '2',
  `channel` int(11) DEFAULT '0',
  `txpower` int(11) DEFAULT '100',
  `beacon` int(11) DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `WLAN_CONFIG` (
  `wid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usefor` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT '1',
  `ssid` varchar(32) DEFAULT NULL,
  `hide` int(11) DEFAULT '0',
  `isolate` int(11) DEFAULT '0',
  `encryption` int(11) DEFAULT '0',
  `passphrase` varchar(64) DEFAULT NULL,
  `stanum` int(11) DEFAULT NULL,
  `vlanid` int(11) DEFAULT '0',
  PRIMARY KEY (`wid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `WLAN_STATUS` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT '1',
  `apid` int(11) DEFAULT NULL,
  `usefor` int(11) DEFAULT '3',
  `ssid` varchar(32) DEFAULT '',
  `hide` int(11) DEFAULT '0',
  `isolate` int(11) DEFAULT '0',
  `encryption` int(11) DEFAULT '0',
  `passphrase` varchar(64) DEFAULT '',
  `stanum` int(11) DEFAULT '32',
  `vlanid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
