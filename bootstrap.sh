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

yum -y install \
	httpd httpd-devel httpd-manual httpd-tools \
	mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server \
	mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi \
	php php-devel php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql \
	php-odbc php-pdo php-pear php-pear-Benchmark php-pecl-apc php-pecl-imagick php-pecl-memcache php-soap php-xml php-xmlrpc \
	emacs emacs-common emacs-nox git \
	http://www.webmin.com/download/rpm/webmin-current.rpm


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

mkdir -p /home/mfcs.lib.wvu.edu/data/archives/mfcs
mkdir -p /home/mfcs.lib.wvu.edu/data/archives/other
mkdir -p /home/mfcs.lib.wvu.edu/data/exports
mkdir -p /home/mfcs.lib.wvu.edu/data/working/mfcsStaging
mkdir -p /home/mfcs.lib.wvu.edu/data/working/tmp
mkdir -p /home/mfcs.lib.wvu.edu/data/working/uploads

chown apache /home/mfcs.lib.wvu.edu/data/ -R

mkdir -p /home/mfcs.lib.wvu.edu/public_html/javascript/
ln -s /tmp/git/engineAPI/engine/template/distribution/public_html/js /home/mfcs.lib.wvu.edu/public_html/javascript/distribution

# setup the template link
ln -s /vagrant/template/* $GITDIR/engineAPI/engine/template/

mkdir -p /vagrant/serverConfiguration/serverlogs
touch /vagrant/serverConfiguration/serverlogs/error_log
/etc/init.d/httpd restart
chkconfig httpd on

# setup emailing support (this is a vagrant requirement) due to symbolic linking
sudo mkdir -p /tmp/git/phpincludes/engine/phpmailer
sudo cp /home/mfcs.lib.wvu.edu/phpincludes/engine/phpmailer/*.php /tmp/git/phpincludes/engine/phpmailer/

# Base Post Setup

ln -s $SERVERURL $ENGINEAPIHOME
ln -s /tmp/git/engineAPI/public_html/engineIncludes $SERVERURL/$DOCUMENTROOT/engineIncludes

## Setup the EngineAPI Database

/etc/init.d/mysqld start
chkconfig mysqld on
mysql -u root < /tmp/git/engineAPI/sql/vagrantSetup.sql
mysql -u root EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

# application Post Setup

mysql -u root < /vagrant/SQLFiles/setup.sql
mysql -u root mfcs < /vagrant/SQLFiles/baseSnapshot.sql

for f in $SQLFILES
do
	echo "Processing $f ..."
	mysql -u root mfcs < "$f"
done

#install 3rd Party dependencies
cd /vagrant/serverConfiguration/3rdParty
rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

yum -y install \
	ImageMagick php-pecl-imagick python-devel \
	perl-ExtUtils-CBuilder.x86_64 perl-ExtUtils-Embed.x86_64 perl-ExtUtils-MakeMaker.x86_64 perl-ExtUtils-ParseXS.x86_64

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

## Video Dependencies
rpm -Uvh http://download.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-5.noarch.rpm
rpm -Uhv http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
yum -y install --nogpgcheck http://download1.rpmfusion.org/free/el/updates/6/i386/rpmfusion-free-release-6-1.noarch.rpm http://download1.rpmfusion.org/nonfree/el/updates/6/i386/rpmfusion-nonfree-release-6-1.noarch.rpm

yum -y install mplayer
yum -y install ffmpeg-devel ffmpeg ffmpeg-libs

## ClamAV
yum -y install clamav clamav-db clamav-devel
# tar -zxf /vagrant/serverConfiguration/3rdParty/php-clamav_0.15.8.tar.gz --directory=/tmp
# cd /tmp/php-clamav-0.15.8
# phpize
# ./configure --with-clamav
# make
# cp modules/clamav.so $PHPMODULES

/sbin/service httpd restart

