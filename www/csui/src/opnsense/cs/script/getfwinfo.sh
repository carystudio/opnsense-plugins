mount | awk '{print $3}' | grep /usb
if [ $? -eq 0 ];then
	umount /usb
fi
if [ -e /usb ];then
	rm -rf /usb
fi
mkdir /usb
mount -t msdosfs /dev/da0s1 /usb
if [ $? -nq 0 ];then
	exit 1
fi

if [ -f /usb/CSG2000P_upgrade.bin ];then
	dd if=/usb/CSG2000P_upgrade.bin of=/usr/local/opnsense/cs/tmp/fw_version.tmp bs=16 count=1
	dd if=/usb/CSG2000P_upgrade.bin of=/usr/local/opnsense/cs/tmp/fw_builddate.tmp bs=16 count=1 skip=1
	dd if=/usb/CSG2000P_upgrade.bin of=/usr/local/opnsense/cs/tmp/fw_md5.tmp bs=32 count=1 skip=1
	version="`cat /usr/local/opnsense/cs/tmp/fw_version.tmp`"
	builddate="`cat /usr/local/opnsense/cs/tmp/fw_builddate.tmp`"
	md5="`cat /usr/local/opnsense/cs/tmp/fw_md5.tmp`"
	echo -n "$version||$builddate||$md5">/usr/local/opnsense/cs/tmp/fw_info.tmp
else
	if [ -f /usr/local/opnsense/cs/tmp/fw_info.tmp ];then
		rm -f /usr/local/opnsense/cs/tmp/fw_info.tmp
	fi
fi
