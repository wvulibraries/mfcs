#!/bin/bash

# Base PRE Setup
GITDIR="/tmp/git"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="engineAPI-3.x"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/mfcs.lib.wvu.edu"
DOCUMENTROOT="public_html"
SQLFILES="/vagrant/SQLFiles/migrations/*.sql"

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

mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
/etc/init.d/httpd start

mkdir -p $GITDIR
cd $GITDIR
git clone -b $ENGINEBRANCH $ENGINEAPIGIT

mkdir -p $SERVERURL/phpincludes/
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/

# Application Specific

ln -s /vagrant/public_html $SERVERURL/$DOCUMENTROOT
ln -s $SERVERURL/phpincludes/engine/engineAPI/3.1 $SERVERURL/phpincludes/engine/engineAPI/latest

rm -f /etc/php.ini
rm -f /etc/httpd/conf/httpd.conf

ln -s /vagrant/serverConfiguration/php.ini /etc/php.ini
ln -s /vagrant/serverConfiguration/vagrant_httpd.conf /etc/httpd/conf/httpd.conf

mkdir -p $SERVERURL/data/archives/mfcs
mkdir -p $SERVERURLdata/archives/other
mkdir -p $SERVERURL/data/exports
mkdir -p $SERVERURL/data/working/mfcsStaging
mkdir -p $SERVERURL/data/working/tmp
mkdir -p $SERVERURL/data/working/uploads

chown apache /home/mfcs.lib.wvu.edu/data/ -R

mkdir -p $SERVERURL/public_html/javascript/
ln -s /tmp/git/engineAPI/engine/template/distribution/public_html/js $SERVERURL/public_html/javascript/distribution

# setup the template link
ln -s /vagrant/template/* $GITDIR/engineAPI/engine/template/

mkdir -p /vagrant/serverConfiguration/serverlogs
touch /vagrant/serverConfiguration/serverlogs/error_log
/etc/init.d/httpd restart
chkconfig httpd on

# setup emailing support (this is a vagrant requirement) due to symbolic linking
sudo mkdir -p /tmp/git/phpincludes/engine/phpmailer
sudo cp $SERVERURL/phpincludes/engine/phpmailer/*.php /tmp/git/phpincludes/engine/phpmailer/

# Base Post Setup

ln -s $SERVERURL $ENGINEAPIHOME
ln -s /tmp/git/engineAPI/public_html/engineIncludes $SERVERURL/$DOCUMENTROOT/engineIncludes

## Setup the EngineAPI Database

/etc/init.d/mysqld start
chkconfig mysqld on

/etc/init.d/mysqld start
chkconfig mysqld on
mysql -u root < /tmp/git/engineAPI/sql/vagrantSetup.sql
mysql -u root EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

# first value is size in megabytes to load main database
mysql -u root -Bse "set global max_allowed_packet=1024*1024*1024"; #first value is size in megabytes

# application Post Setup
mysql -u root < /vagrant/SQLFiles/setup.sql

# mysql -u root "DROP DATABASE IF EXISTS `mfcs`;"
# mysql -u root "CREATE DATABASE IF NOT EXISTS `mfcs`;"
# mysql -u root "GRANT ALL PRIVILEGES ON `mfcs`.* TO '$DATABASE_USER'@'$DATABASE_HOST';"
# mysql -u root "SET GLOBAL max_allowed_packet=1073741824;"
# mysql -u root "USE `mfcs`;"

mysql -u root mfcs < /vagrant/SQLFiles/baseSnapshot.sql

# if backup exists import that and do selected migrations
# I was using a older backup and additional migrations needed
# to be run. if using a more current backup the migrations may
# not be required.
if [ -e /vagrant/SQLFiles/mfcs.sql ]
then
  mysql -u root mfcs < /vagrant/SQLFiles/mfcs.sql
  mysql -u root mfcs < /vagrant/SQLFiles/migrations/2016.07.26.0945.sql
else
  echo "No backup found, skipping database import and running migrations"

  for f in $SQLFILES
  do
  	echo "Processing $f ..."
  	mysql -u root mfcs < "$f"
  done
fi

#install 3rd Party dependencies
cd /vagrant/serverConfiguration/3rdParty
rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

# yum -y install \
# 	ImageMagick php-pecl-imagick python-devel \
# 	perl-ExtUtils-CBuilder.x86_64 perl-ExtUtils-Embed.x86_64 perl-ExtUtils-MakeMaker.x86_64 perl-ExtUtils-ParseXS.x86_64

rm -f /etc/yum.repos.d/remi.repo
ln -s /vagrant/serverConfiguration/remi.repo /etc/yum.repos.d/remi.repo

yum -y install libjpeg-devel libpng-devel libtiff-devel SDL-devel agg-devel

tar -zxf /vagrant/serverConfiguration/3rdParty/leptonica-1.69.tar.gz --directory=/tmp
tar -zxf /vagrant/serverConfiguration/3rdParty/tesseract-ocr-3.02.02.tar.gz --directory=/tmp
tar -zxf /vagrant/serverConfiguration/3rdParty/tesseract-ocr-3.02.eng.tar.gz --directory=/tmp
tar -xf /vagrant/serverConfiguration/3rdParty/exact-image-0.8.8.tar --directory=/tmp

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

# ## Video Dependencies
# ## FFMPEG COMPILE INSTALL
# echo "Installing FFMPEG Dependencies"
# yum -y install autoconf automake cmake freetype-devel gcc gcc-c++ git libtool make mercurial nasm pkgconfig zlib-devel

cd /tmp
mkdir ffmpeg

echo "Extracing FFMPEG"
tar -xvf /vagrant/serverConfiguration/3rdParty/ffmpeg-2.6.8.tar.xz --directory /tmp/ffmpeg/
cd /tmp/ffmpeg/ffmpeg-2.8.6-64bit-static
cp ffmpeg /usr/local/bin/
cp ffmpeg-10bit /usr/local/bin/
cp ffprobe /usr/local/bin/
cp ffserver /usr/local/bin/
cp qt-faststart /usr/local/bin/
echo "Completed install"


## ClamAV
# yum -y install clamav clamav-db clamav-devel
# tar -zxf /vagrant/serverConfiguration/3rdParty/php-clamav_0.15.8.tar.gz --directory=/tmp
# cd /tmp/php-clamav-0.15.8
# phpize
# ./configure --with-clamav
# make
# cp modules/clamav.so $PHPMODULES

/sbin/service httpd restart

tail -f /vagrant/serverConfiguration/serverlogs/error_log
