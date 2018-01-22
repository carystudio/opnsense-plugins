/bin/rm -f /usr/local/opnsense/cs/tmp/upgrade_config.ini
cd /usr/local/opnsense/cs/tmp/
/usr/bin/tar xvf /mnt/packages-17.1.3_33-OpenSSL-amd64.tar All/os-csui-0.8.txz
cd All
/usr/bin/tar zxvf os-csui-0.8.txz /usr/local/opnsense/cs/www/app/config/config.ini
/bin/cp usr/local/opnsense/cs/www/app/config/config.ini /usr/local/opnsense/cs/tmp/upgrade_config.ini
cd /usr/local/opnsense/cs/tmp
/bin/rm -rf All




cd /
tar zxvf /mnt/base-17.1.3_1-amd64.txz
while read FILE; do
  rm -f ${FILE}
done < /mnt/base-17.1.3_1-amd64.obsolete
tar zxvf /mnt/kernel-17.1.3_1-amd64.txz
mkdir /mnt/package
cd /mnt/package
tar xvf /mnt/packages-17.1.3_33-OpenSSL-amd64.tar
