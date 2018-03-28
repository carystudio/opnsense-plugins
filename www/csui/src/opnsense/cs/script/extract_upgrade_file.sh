mount | awk '{print $3}' | grep /usb
if [ $? -eq 0 ];then
    echo "umount /usb"
	umount /usb
fi
if [ -e /usb ];then
	rm -rf /usb
fi
mkdir /usb
mount -t msdosfs /dev/da0s1 /usb
if [ $? -ne 0 ];then
	exit 1
fi
if [ -e /upgrade_tmp ];then
    rm -rf /upgrade_tmp
fi
mkdir -p /upgrade_tmp/files
if [ -f /usb/CSG2000P_upgrade.bin ];then
    cd /upgrade_tmp
    dd if=/usb/CSG2000P_upgrade.bin of=fw.img bs=102400 skip=1
    umount /usb
    cd files
    tar zxvf ../fw.img
    mkdir packages
    cd packages
    tar xf ../packages-*-amd64.tar
    tar zxvf Latest/opnsense.txz "+COMPACT_MANIFEST"
    tar zxvf packagesite.txz packagesite.yaml
    cd ..
    rm -f packages-*-amd64.tar
fi
