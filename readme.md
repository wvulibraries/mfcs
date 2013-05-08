Install 3rd Party Plugins

Tesseract (https://code.google.com/p/tesseract-ocr)
-yum install libjpeg-devel libpng-devel libtiff-devel
-leptonica (https://code.google.com/p/leptonica)
-tesseract-ocr-3.02.eng.tar.gz (English language data)
-ln -s `which tesseract` /usr/bin/
-ln -s `which hocr2pdf` /usr/bin/

Dependancies needed for exact-image (http://www.exactcode.com/site/open_source/exactimage)
-yum install SDL-devel agg-devel (installed from epel and prey to god it's there)

(Enable EPEL on CentOS 5 and 6: http://www.rackspace.com/knowledge_center/article/installing-rhel-epel-repo-on-centos-5x-or-6x)