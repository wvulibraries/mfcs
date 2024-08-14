#!/bin/bash

# rename existing backup
if [ -e /SQLFiles/mfcs.sql ]
then
  mv /SQLFiles/mfcs.sql /SQLFiles/mfcs-`date +%Y%m%d%H%M%S`.sql
fi

mysqldump -u root -h $DATABASE_HOST mfcs > /SQLFiles/mfcs.sql