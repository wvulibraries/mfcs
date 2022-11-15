# Webserver container for WVU Lib Engine API
# Using Centos:latest base image
# Version 1

FROM trmccormick/centos6-mfcs
USER root

## Install required packages
RUN yum -y install ImageMagick php-pecl-imagick python-devel \
	perl-ExtUtils-CBuilder.x86_64 perl-ExtUtils-Embed.x86_64 \
    perl-ExtUtils-MakeMaker.x86_64 perl-ExtUtils-ParseXS.x86_64

## Install FFMPEG Dependencies
RUN yum -y install autoconf automake cmake freetype-devel gcc gcc-c++ \
    git libtool make mercurial nasm pkgconfig zlib-devel

## ClamAV
RUN yum -y install clamav clamav-db clamav-devel

ADD . /vagrant

ADD ./scripts/entrypoint.sh /usr/bin/
RUN chmod -v +x /usr/bin/entrypoint.sh

# Start the service
ENTRYPOINT ["/usr/bin/entrypoint.sh"]
