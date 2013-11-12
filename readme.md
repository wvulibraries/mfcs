
# MFCS -- Metadata Form Creation System

MFCS is distributed under the WVU Open Source License. 

The Metadata Form Creation System (MFCS) is WVUs answer for providing an easy to use way for librarians, staff, and students to enter metadata for items in our digital collections. 

MFCS is a delivery agnostic system. That is, it should be able to export data in any format to any digital project system (Hydra, DLXS, etc ... ). Some programming is required to write the export scripts. 

MFCS provides examples on generating statistics for forms or collections of forms. 

MFCS provides 2 interfaces. 

## Form Builder

Allows metadata librarians and adminsitrators to create forms by dragging and dropping fields and defining the behaver of those form fields. Forms can be nested, so that pages can be part of books, or folders in boxes. Forms can also be linked, so that a centralized vocabulary is possible (either for a specific form or across forms and projects). 

Form fields can be any type valid HTML 5 form field. Additionally custom validation is possible using built in checks or regular expressions for more advanced pattern matching (e.g. Custom date formats)

Upload fields can be configured with a large set of options to that the original upload file can be retained as well as exporting options (resize/convert image formats. create thumb nails, combine tiffs into a single OCR pdf, etc ... )

## Metadata Entry

Students, Librarians, etc can then use the forms created in the form builder to enter metadata for digital collections. 

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