#!/bin/bash

# rename existing backup
if [ -e /vagrant/SQLFiles/mfcs.sql ]
then
  mv /vagrant/SQLFiles/mfcs.sql /vagrant/SQLFiles/mfcs-`date +%Y%m%d%H%M%S`.sql
fi

mysqldump -u root -h $DATABASE_HOST mfcs > /vagrant/SQLFiles/mfcs.sql