#!/bin/bash

cd /vagrant/serverConfiguration/3rdParty
rpm -Uvh --force --quiet remi-release-6*.rpm epel-release-6*.rpm

# yum -y install mod_perl.x86_64 perl.x86_64 perl-Archive-Extract.x86_64 perl-Authen-SASL.noarch perl-Bit-Vector.x86_64 perl-CGI.x86_64 perl-CGI-Session.noarch perl-CPANPLUS.x86_64 perl-Carp-Clan.noarch perl-Compress-Raw-Bzip2.x86_64 perl-Compress-Zlib.x86_64 perl-Crypt-SSLeay.x86_64 perl-DBD-Pg.x86_64 perl-DBD-SQLite.x86_64 perl-DBI.x86_64 perl-DBIx-Simple.noarch perl-Date-Manip.noarch perl-Devel-Symdump.noarch perl-Digest-SHA.x86_64 perl-Digest-SHA1.x86_64 perl-ExtUtils-CBuilder.x86_64 perl-ExtUtils-MakeMaker.x86_64 perl-File-Fetch.x86_64 perl-FreezeThaw.noarch perl-Frontier-RPC.noarch perl-GSSAPI.x86_64 perl-Git.noarch perl-HTML-Parser.x86_64 perl-IO-Compress-Base.x86_64 perl-IO-Compress-Zlib.x86_64 perl-IO-Zlib.x86_64 perl-IPC-Cmd.x86_64 perl-LDAP.noarch perl-Locale-Maketext-Simple.x86_64 perl-Log-Message-Simple.x86_64 perl-Module-CoreList.x86_64 perl-Module-Load-Conditional.x86_64 perl-Module-Pluggable.x86_64 perl-Net-LibIDN.x86_64 perl-Net-SSLeay.x86_64 perl-Newt.x86_64 perl-Object-Accessor.x86_64 perl-Params-Check.x86_64 perl-Parse-CPAN-Meta.x86_64
yum -y install ImageMagick php-pecl-imagick 

rm -f /etc/yum.repos.d/remi.repo
ln -s /vagrant/serverConfiguration/remi.repo /etc/yum.repos.d/remi.repo

yum -y install libjpeg-devel libpng-devel libtiff-devel SDL-devel agg-devel

tar -zxf /vagrant/serverConfiguration/3rdParty/leptonica-1.69.tar.gz --directory=/tmp
tar -zxf /vagrant/serverConfiguration/3rdParty/tesseract-ocr-3.02.02.tar.gz --directory=/tmp
tar -zxf /vagrant/serverConfiguration/3rdParty/tesseract-ocr-3.02.eng.tar.gz --directory=/tmp
tar -xf /vagrant/serverConfiguration/3rdParty/exact-image-0.8.8.tar --directory=/tmp

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