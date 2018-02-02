cd /
tar zxf /upgrade_tmp/files/base-17.1.3_1-amd64.txz
while read FILE; do
  rm -f ${FILE}
done < /upgrade_tmp/files/base-17.1.3_1-amd64.obsolete

tar zxf /upgrade_tmp/files/kernel-17.1.3_1-amd64.txz
echo Y|pkg install /upgrade_tmp/files/packages/Latest/opnsense.txz
while read FILE; do
	echo "install $FILE..."
	echo Y|pkg install /upgrade_tmp/files/packages/Latest/${FILE}.txz
done < /upgrade_tmp/files/packages/upgrade_package.tmp
echo Y|pkg install /upgrade_tmp/files/packages/Latest/os-csui.txz
echo "Upgrade completed. "
echo "Restart..."
shutdown -r now

