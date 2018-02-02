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
rm -rf /upgrade_tmp
if [ -f /usr/local/opnsense/cs/tmp/upgrade_config.ini ];then
	rm -f /usr/local/opnsense/cs/tmp/upgrade_config.ini
fi
mkdir /upgrade_tmp
if [ -f /usb/CSG2000P_upgrade.bin ];then
cd /upgrade_tmp
dd if=/usb/CSG2000P_upgrade.bin of=len.tmp bs=1 count=2

a=$(od -A d -d len.tmp | head -1 | awk '{print $2}')
rm len.tmp
echo $a
a=$((a+2))
dd if=/usb/CSG2000P_upgrade.bin ibs=$a skip=1 obs=10240 of=CSG2000P_upgrade.img
umount /usb
mkdir files
cd files
tar zxvf ../CSG2000P_upgrade.img
version="`cat version`"
mkdir packages
cd packages
tar xf ../packages-${version}-amd64.tar
tar zxvf Latest/opnsense.txz "+COMPACT_MANIFEST"
tar zxvf Latest/os-csui.txz /usr/local/opnsense/cs/www/app/config/config.ini
tar zxvf packagesite.txz packagesite.yaml
mv usr/local/opnsense/cs/www/app/config/config.ini /usr/local/opnsense/cs/tmp/upgrade_config.ini
cd ..
rm -f packages-${version}-amd64.tar
fi
