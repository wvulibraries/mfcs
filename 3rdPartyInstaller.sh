#!/bin/bash

cd /vagrant/serverConfiguration/3rdParty
rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

yum -y install ImageMagick php-pecl-imagick 
yum -y install perl-ExtUtils-CBuilder.x86_64 perl-ExtUtils-Embed.x86_64 perl-ExtUtils-MakeMaker.x86_64 perl-ExtUtils-ParseXS.x86_64 
yum -y install python-devel

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