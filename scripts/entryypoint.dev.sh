#!/bin/bash

# Base PRE Setup
GITDIR="/tmp/git"
LOGDIR="/tmp/log"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="engineAPI-3.2-develop"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/mfcs.lib.wvu.edu"
DOCUMENTROOT="public_html"

# this should match extension_dir from phpinfo()
PHPMODULES="/usr/local/lib/php/extensions/no-debug-non-zts-20210902"

# create $GITDIR if it doesn't exist
if [ ! -d "$GITDIR" ]; then
    mkdir -p $GITDIR
fi

# if the engineAPI directory doesn't exist, clone it
if [ ! -d "$GITDIR/engineAPI" ]; then
    # clone the engineAPI
    cd $GITDIR
    git clone -b $ENGINEBRANCH $ENGINEAPIGIT
else
    # update the engineAPI
    cd $GITDIR/engineAPI
    git pull origin $ENGINEBRANCH    
fi

# remove exiting defaultPrivate.php and replace with our custom one
rm $GITDIR/engineAPI/engine/engineAPI/3.2/config/defaultPrivate.php
ln -s $SERVERURL/serverConfiguration/defaultPrivate.php $GITDIR/engineAPI/engine/engineAPI/3.2/config/defaultPrivate.php

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
rm -f $SERVERURL/phpincludes/engine/engineAPI/latest

# create symbolic links to application
ln -s $SERVERURL/phpincludes/engine/engineAPI/3.2 $SERVERURL/phpincludes/engine/engineAPI/latest

mkdir -p $SERVERURL/data/archives/mfcs
mkdir -p $SERVERURL/data/archives/other
mkdir -p $SERVERURL/data/exports
mkdir -p $SERVERURL/data/working/mfcsStaging
mkdir -p $SERVERURL/data/working/tmp
mkdir -p $SERVERURL/data/working/uploads

# link engineAPI JS directory to distribution
ln -s /tmp/git/engineAPI/engine/template/distribution/public_html/js $SERVERURL/public_html/javascript/distribution

# remove existing symbolic link to template if exists
rm -f $GITDIR/engineAPI/engine/template

# setup the template link
ln -s $SERVERURL/template/* $GITDIR/engineAPI/engine/template/

# setup emailing support (this is a vagrant requirement) due to symbolic linking
mkdir -p /tmp/git/phpincludes/engine/phpmailer
cp $SERVERURL/phpincludes/engine/phpmailer/*.php /tmp/git/phpincludes/engine/phpmailer/

# Base Post Setup

ln -s $SERVERURL $ENGINEAPIHOME

# remove existing symbolic link if exists
rm -f $SERVERURL/$DOCUMENTROOT/engineIncludes

# create symbolic link to engineAPI
ln -s /tmp/git/engineAPI/public_html/engineIncludes $SERVERURL/$DOCUMENTROOT/engineIncludes

# remove existing error.log if exists
rm -f $LOGDIR/error.log
touch $LOGDIR/error.log

# remove existing access.log if exists
rm -f $LOGDIR/access.log
touch $LOGDIR/access.log

# load crontab from file
crontab /home/mfcs.lib.wvu.edu/serverConfiguration/crontab.dev

# remove /etc/ImageMagick-6/policy.xml and replace with our custom one
rm /etc/ImageMagick-6/policy.xml
ln -s /config/policy.xml /etc/ImageMagick-6/policy.xml

# start the apache2 service in the foreground to keep
# the container from closing
exec apache2-foreground             # main execution