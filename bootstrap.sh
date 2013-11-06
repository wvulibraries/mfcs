#!/bin/bash

# Base Setup

GITDIR="/tmp/git"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="engineAPI-3.x"

SERVERURL="/home/mfcs.lib.wvu.edu"
DOCUMENTROOT="public_html"

yum -y install httpd httpd-devel httpd-manual httpd-tools
yum -y install mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server
yum -y install mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi 
yum -y install phpkk php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql php-odbc php-pdo php-pear php-pear-Benchmark php-pecl-apc php-pecl-imagick php-pecl-memcache php-soap php-xml php-xmlrpc 
yum -y install emacs emacs-common emacs-nox
yum -y install git

mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
/etc/init.d/httpd start

mkdir -p $GITDIR
cd $GITDIR
git clone -b $ENGINEBRANCH $ENGINEAPIGIT

mkdir -p $SERVERURL/phpincludes/
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/

# Application Specific

rmdir $SERVERURL/$DOCUMENTROOT
ln -s /vagrant/public_html $SERVERURL/$DOCUMENTROOT
ln -s $SERVERURL/phpincludes/engine/engineAPI/3.1 $SERVERURL/phpincludes/engine/engineAPI/latest

cp /vagrant/serverConfiguration/php.ini /etc/php.ini
/etc/init.d/httpd

