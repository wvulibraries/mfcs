# Installing 3rd Party Plugins

## Enable EPEL on CentOS 5 and 6: 

http://www.rackspace.com/knowledge_center/article/installing-rhel-epel-repo-on-centos-5x-or-6x

1. wget http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
1. wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
1. rpm -Uvh remi-release-6*.rpm epel-release-6*.rpm

## Image Magick

* yum install ImageMagick
* yum install php-pecl-imagick

## Enable Remi Repo

1. emacs /etc/yum.repos.d/remi.repo
	* enabled=1 on [remi]

## Tesseract 

https://code.google.com/p/tesseract-ocr

* yum install libjpeg-devel libpng-devel libtiff-devel

* wget -O leptonica-1.69.tar.gz https://leptonica.googlecode.com/files/leptonica-1.69.tar.gz
* ./configure
* make
* make install
	* installs leptonica 

* wget -O tesseract-ocr-3.02.02.tar.gz https://tesseract-ocr.googlecode.com/files/tesseract-ocr-3.02.02.tar.gz
* wget -O tesseract-ocr-3.02.eng.tar.gz https://tesseract-ocr.googlecode.com/files/tesseract-ocr-3.02.eng.tar.gz
* ./autogen.sh
* ./configure
* make
* make install
* cp /home/mbond/Downloads/tesseract-ocr/tessdata/eng.* /usr/local/share/tessdata


* tesseract-ocr-3.02.eng.tar.gz (English language data)


## Exact Image

http://www.exactcode.com/site/open_source/exactimage

* yum install SDL-devel agg-devel (installed from epel and prey to god it's there)
* wget -O exact-image-0.8.8.tar.bz2 http://dl.exactcode.de/oss/exact-image/exact-image-0.8.8.tar.bz2
* bunzip2 exact-image-0.8.8.tar.bz2
* tar -xvf exact-image-0.8.8.tar
* ./configure
* make
* make install

## Post Install Steps

* ln -s `which tesseract` /usr/bin/
* ln -s `which hocr2pdf` /usr/bin/
* disbale Remi Repo