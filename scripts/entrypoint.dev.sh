#!/bin/bash

# Base PRE Setup
GITDIR="/tmp/git"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
# ENGINEBRANCH="engineAPI-3.2-develop"
ENGINEBRANCH="engineAPI-3.x"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/mfcs.lib.wvu.edu"
DOCUMENTROOT="public_html"
# SQLFILES="/vagrant/SQLFiles/migrations/*.sql"

#this should match extension_dir from phpinfo()
PHPMODULES="/usr/lib64/php/modules/"

cat yum-wof /etc/yum.repos.d/CentOS-Base.repo

# yum -y install \
# 	httpd httpd-devel httpd-manual httpd-tools \
# 	mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server \
# 	mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi \
# 	php php-devel php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql \
# 	php-odbc php-pdo php-pear php-pear-Benchmark php-pecl-apc php-pecl-imagick php-pecl-memcache php-soap php-xml php-xmlrpc \
# 	emacs emacs-common emacs-nox git

# yum -y update

# load crontab
crontab $SERVERURL/serverConfiguration/cronjobs.dev

mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
# /etc/init.d/httpd start

mkdir -p $GITDIR
cd $GITDIR
git clone -b $ENGINEBRANCH $ENGINEAPIGIT

# remove exiting defaultPrivate.php and replace with our custom one
rm $GITDIR/engineAPI/engine/engineAPI/3.1/config/defaultPrivate.php
ln -s $SERVERURL/serverConfiguration/defaultPrivate.php $GITDIR/engineAPI/engine/engineAPI/3.1/config/defaultPrivate.php

mkdir -p $SERVERURL/phpincludes/
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/

# Application Specific

# ln -s /vagrant/public_html $SERVERURL/$DOCUMENTROOT

rm -f $SERVERURL/phpincludes/engine/engineAPI/latest
ln -s $SERVERURL/phpincludes/engine/engineAPI/3.1 $SERVERURL/phpincludes/engine/engineAPI/latest

rm -f /etc/php.ini
rm -f /etc/httpd/conf/httpd.conf

ln -s $SERVERURL/serverConfiguration/php.ini /etc/php.ini
ln -s $SERVERURL/serverConfiguration/httpd.conf /etc/httpd/conf/httpd.conf

mkdir -p $SERVERURL/data/archives/mfcs
mkdir -p $SERVERURL/data/archives/other
mkdir -p $SERVERURL/data/exports
mkdir -p $SERVERURL/data/working/mfcsStaging
mkdir -p $SERVERURL/data/working/tmp
mkdir -p $SERVERURL/data/working/uploads

chown apache $SERVERURL/data/ -R

mkdir -p $SERVERURL/public_html/javascript/
ln -s /tmp/git/engineAPI/engine/template/distribution/public_html/js $SERVERURL/public_html/javascript/distribution

# setup the template link
ln -s $SERVERURL/template/* $GITDIR/engineAPI/engine/template/

mkdir -p $SERVERURL/serverConfiguration/serverlogs
touch $SERVERURL/serverConfiguration/serverlogs/error_log
/etc/init.d/httpd restart
chkconfig httpd on

# setup emailing support (this is a vagrant requirement) due to symbolic linking
sudo mkdir -p /tmp/git/phpincludes/engine/phpmailer
sudo cp $SERVERURL/phpincludes/engine/phpmailer/*.php /tmp/git/phpincludes/engine/phpmailer/

# Base Post Setup

ln -s $SERVERURL $ENGINEAPIHOME
ln -s /tmp/git/engineAPI/public_html/engineIncludes $SERVERURL/$DOCUMENTROOT/engineIncludes

#install 3rd Party dependencies
cd $SERVERURL/serverConfiguration/3rdParty
rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

rm -f /etc/yum.repos.d/remi.repo
ln -s $SERVERURL/serverConfiguration/remi.repo /etc/yum.repos.d/remi.repo

# yum -y install libjpeg-devel libpng-devel libtiff-devel SDL-devel agg-devel

tar -zxf $SERVERURL/serverConfiguration/3rdParty/leptonica-1.69.tar.gz --directory=/tmp
tar -zxf $SERVERURL/serverConfiguration/3rdParty/tesseract-ocr-3.02.02.tar.gz --directory=/tmp
tar -zxf $SERVERURL/serverConfiguration/3rdParty/tesseract-ocr-3.02.eng.tar.gz --directory=/tmp
tar -xf $SERVERURL/serverConfiguration/3rdParty/exact-image-0.8.8.tar --directory=/tmp

cd /tmp/leptonica-1.69
./configure
make
make install

cd /tmp/tesseract-ocr
./autogen.sh
./configure
make
make install

cp /tmp/tesseract-ocr/tessdata/eng.* /usr/local/share/tessdata

cd /tmp/exact-image-0.8.8
./configure
make
make install

ln -s /usr/local/bin/tesseract /usr/bin/
ln -s /usr/local/bin/hocr2pdf /usr/bin/

cd /tmp
mkdir ffmpeg

echo "Extracing FFMPEG"
tar -xvf $SERVERURL/serverConfiguration/3rdParty/ffmpeg-2.6.8.tar.xz --directory /tmp/ffmpeg/
cd /tmp/ffmpeg/ffmpeg-2.8.6-64bit-static
cp ffmpeg /usr/local/bin/
cp ffmpeg-10bit /usr/local/bin/
cp ffprobe /usr/local/bin/
cp ffserver /usr/local/bin/
cp qt-faststart /usr/local/bin/
echo "Completed install"

/sbin/service httpd start

tail -f $SERVERURL/serverConfiguration/serverlogs/error_log