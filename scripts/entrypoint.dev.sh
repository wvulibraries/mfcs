#!/bin/bash

# Base PRE Setup
GITDIR="/tmp/git"
LOGDIR="/tmp/log"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="engineAPI-3.x"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/mfcs.lib.wvu.edu"
DOCUMENTROOT="public_html"
# SQLFILES="/vagrant/SQLFiles/migrations/*.sql"

# this should match extension_dir from phpinfo()
PHPMODULES="/usr/local/lib/php/extensions/no-debug-non-zts-20210902"

# cat yum-wof /etc/yum.repos.d/CentOS-Base.repo

# yum -y install \
# 	httpd httpd-devel httpd-manual httpd-tools \
# 	mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server \
# 	mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi \
# 	php php-devel php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql \
# 	php-odbc php-pdo php-pear php-pear-Benchmark php-pecl-apc php-pecl-imagick php-pecl-memcache php-soap php-xml php-xmlrpc \
# 	emacs emacs-common emacs-nox git

# yum -y update

# mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
# /etc/init.d/httpd start

# create $GITDIR if it doesn't exist
if [ ! -d "$GITDIR" ]; then
    mkdir -p $GITDIR
    cd $GITDIR
    git clone -b $ENGINEBRANCH $ENGINEAPIGIT
else
    # update the engineAPI
    cd $GITDIR/engineAPI
    git pull origin $ENGINEBRANCH    
fi

# remove exiting defaultPrivate.php and replace with our custom one
# rm $GITDIR/engineAPI/engine/engineAPI/3.1/config/defaultPrivate.php
# ln -s /home/mfcs.lib.wvu.edu/serverConfiguration/defaultPrivate.php $GITDIR/engineAPI/engine/engineAPI/3.1/config/defaultPrivate.php

# create $SERVERURL/phpincludes/ if it doesn't exist
if [ ! -d "$SERVERURL/phpincludes/" ]; then
    mkdir -p $SERVERURL/phpincludes/
fi

# remove existing engine symbolic link if exists
rm -f $SERVERURL/phpincludes/engine

# create symbolic link to engineAPI
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/

# Application Specific

# remove existing symbolic links if exists
# rm -f $SERVERURL/$DOCUMENTROOT

# create symbolic links to application
# ln -s /home/mfcs.lib.wvu.edu/public_html $SERVERURL/$DOCUMENTROOT

# remove existing symbolic links if exists
# rm -f $SERVERURL/phpincludes/engine/engineAPI/latest

# create symbolic links to application
# ln -s $SERVERURL/phpincludes/engine/engineAPI/3.1 $SERVERURL/phpincludes/engine/engineAPI/latest

# remove existing symbolic links if exists
# rm -f /etc/php.ini
# rm -f /etc/httpd/conf/httpd.conf

# create symbolic links to application
# ln -s /home/mfcs.lib.wvu.edu/serverConfiguration/php.ini /etc/php.ini
# ln -s /home/mfcs.lib.wvu.edu/serverConfiguration/vagrant_httpd.conf /etc/httpd/conf/httpd.conf

mkdir -p $SERVERURL/data/archives/mfcs
mkdir -p $SERVERURL/data/archives/other
mkdir -p $SERVERURL/data/exports
mkdir -p $SERVERURL/data/working/mfcsStaging
mkdir -p $SERVERURL/data/working/tmp
mkdir -p $SERVERURL/data/working/uploads

# link engineAPI JS directory to distribution
# ln -s /tmp/git/engineAPI/engine/template/distribution/public_html/js $SERVERURL/public_html/javascript/distribution

# # remove existing symbolic link to template if exists
# rm -f $GITDIR/engineAPI/engine/template

# # setup the template link
# ln -s /home/mfcs.lib.wvu.edu/template/* $GITDIR/engineAPI/engine/template/

# mkdir -p /home/mfcs.lib.wvu.edu/serverConfiguration/serverlogs
# touch /home/mfcs.lib.wvu.edu/serverConfiguration/serverlogs/error_log
# /etc/init.d/httpd restart
# chkconfig httpd on

# setup emailing support (this is a vagrant requirement) due to symbolic linking
mkdir -p /tmp/git/phpincludes/engine/phpmailer
cp $SERVERURL/phpincludes/engine/phpmailer/*.php /tmp/git/phpincludes/engine/phpmailer/

# Base Post Setup

ln -s $SERVERURL $ENGINEAPIHOME

# remove existing symbolic link if exists
rm -f $SERVERURL/$DOCUMENTROOT/engineIncludes

# create symbolic link to engineAPI
ln -s /tmp/git/engineAPI/public_html/engineIncludes $SERVERURL/$DOCUMENTROOT/engineIncludes

#install 3rd Party dependencies
# cd /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty
# rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

# rm -f /etc/yum.repos.d/remi.repo
# ln -s /home/mfcs.lib.wvu.edu/serverConfiguration/remi.repo /etc/yum.repos.d/remi.repo

# yum -y install libjpeg-devel libpng-devel libtiff-devel SDL-devel agg-devel

# tar -zxf /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty/leptonica-1.69.tar.gz --directory=/tmp
# tar -zxf /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty/tesseract-ocr-3.02.02.tar.gz --directory=/tmp
# tar -zxf /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty/tesseract-ocr-3.02.eng.tar.gz --directory=/tmp
# tar -xf /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty/exact-image-0.8.8.tar --directory=/tmp

# cd /tmp/leptonica-1.69
# ./configure
# make
# make install

# cd /tmp/tesseract-ocr
# ./autogen.sh
# ./configure
# make
# make install

# cp /tmp/tesseract-ocr/tessdata/eng.* /usr/local/share/tessdata

# cd /tmp/exact-image-0.8.8
# ./configure
# make
# make install

# ln -s /usr/local/bin/tesseract /usr/bin/
# ln -s /usr/local/bin/hocr2pdf /usr/bin/

# cd /tmp
# mkdir ffmpeg

# echo "Extracing FFMPEG"
# tar -xvf /home/mfcs.lib.wvu.edu/serverConfiguration/3rdParty/ffmpeg-2.6.8.tar.xz --directory /tmp/ffmpeg/
# cd /tmp/ffmpeg/ffmpeg-2.8.6-64bit-static
# cp ffmpeg /usr/local/bin/
# cp ffmpeg-10bit /usr/local/bin/
# cp ffprobe /usr/local/bin/
# cp ffserver /usr/local/bin/
# cp qt-faststart /usr/local/bin/
# echo "Completed install"

# /sbin/service httpd start

# tail -f /home/mfcs.lib.wvu.edu/serverConfiguration/serverlogs/error_log

# remove existing error.log if exists
rm -f $LOGDIR/error.log
touch $LOGDIR/error.log

# remove existing access.log if exists
rm -f $LOGDIR/access.log
touch $LOGDIR/access.log

# start the apache2 service in the foreground to keep
# the container from closing
exec apache2-foreground             # main execution