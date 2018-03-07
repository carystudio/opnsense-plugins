#!/bin/sh
backDir="/var/backup"
destDir="$backDir/data"
if [ -d "$destDir" ]; then
    rm -rf $destDir
fi
mkdir -p $destDir

if [ -f "$backDir/csgateway.tar.gz" ]; then
    rm -rf "$backDir/csgateway.tar.gz"
fi

#sqlPath="/var/backup/data/csgateway.sql"
user="root"
dbname="csgateway"

cd $backDir
mysqldump -u$user $dbname > data/csgateway.sql

tar -zcvf csgateway.tar.gz data
rm -rf data

#output HTTP header
echo "Pragma: no-cache\n"
echo "Cache-control: no-cache\n"
echo "Content-type: application/octet-stream"
echo "Content-Transfer-Encoding: binary"			#  "\n" make Un*x happy
echo "Content-Disposition: attachment; filename=\"csgateway.dat\""
echo ""

cat csgateway.tar.gz


