#!/bin/sh

if [ ! -f /usr/local/opnsense/cs/conf/mcode.txt ];then
    /sbin/dmesg|/usr/bin/grep "Ethernet address"|/usr/bin/awk '{print $4}'|/usr/bin/head -2|/sbin/md5>/usr/local/opnsense/cs/conf/mcode.txt.tmp
    /bin/dd if=/usr/local/opnsense/cs/conf/mcode.txt.tmp of=/usr/local/opnsense/cs/conf/mcode.txt bs=32 count=1
    /bin/rm -f /usr/local/opnsense/cs/conf/mcode.txt.tmp
fi
md5="`/bin/cat /usr/local/opnsense/cs/conf/mcode.txt`"
if [ "32" = "${#md5}" ];then
	echo -n $md5
else
	/sbin/ifconfig|/sbin/md5>/usr/local/opnsense/cs/conf/mcode.txt
	/bin/cat /usr/local/opnsense/cs/conf/mcode.txt
fi
