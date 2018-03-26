PLUGIN_NAME=		csui
PLUGIN_VERSION=		0.8
PLUGIN_COMMENT=		Cary Studio style UI
PLUGIN_DEPENDS=		mysql56-server
PLUGIN_MAINTAINER=	lifuqiang@carystudio.com

pwd
/bin/ls /usr/local/opnsense/
echo "exec +POST_INSTALL.post"
d=`/bin/date "+%Y%m%d"`
/usr/bin/sed -i '' 's/^build_date.*$/build_date = '"$d"'/g' /usr/local/opnsense/cs/www/app/config/config.ini
/bin/sleep 30

.include "../../Mk/plugins.mk"

