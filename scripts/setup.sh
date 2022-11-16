#!/bin/bash
SQLFILES="/vagrant/SQLFiles/migrations/*.sql"

# mysql -u root -h $DATABASE_HOST < /SQLFiles/configure-database.sql

# import EngineAPI database
mysql -u root -h $DATABASE_HOST EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

# first value is size in megabytes to load main database
# mysql -u root -h $DATABASE_HOST -Bse "set global max_allowed_packet=1024*1024*1024"; #first value is size in megabytes

# if backup exists import that and do selected migrations
# I was using a older backup and additional migrations needed
# to be run. if using a more current backup the migrations may
# not be required.
if [ -e /vagrant/SQLFiles/mfcs.sql ]
then
  mysql -u root -h $DATABASE_HOST mfcs < /vagrant/SQLFiles/mfcs.sql
  mysql -u root -h $DATABASE_HOST mfcs < /vagrant/SQLFiles/migrations/2016.07.26.0945.sql
else
  echo "No backup found, skipping database import and running migrations"
  mysql -u root -h $DATABASE_HOST mfcs < /vagrant/SQLFiles/baseSnapshot.sql
  for f in $SQLFILES
  do
  	echo "Processing $f ..."
  	mysql -u root -h $DATABASE_HOST mfcs < "$f"
  done
fi

