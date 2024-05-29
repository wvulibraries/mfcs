#!/bin/bash
SQLFILES="/home/mfcs.lib.wvu.edu/SQLFiles/migrations/*.sql"

# import EngineAPI database
mysql -u root -h $DATABASE_HOST EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

# import engineCMS database - engine keeps looking for this database
mysql -u root -h $DATABASE_HOST engineCMS < /home/mfcs.lib.wvu.edu/SQLFiles/engineCMS.sql

# first value is size in megabytes to load main database
mysql -u root -h $DATABASE_HOST -Bse "set global max_allowed_packet=1024*1024*1024"; #first value is size in megabytes

# if backup exists import that and do selected migrations
# I was using a older backup and additional migrations needed
# to be run. if using a more current backup the migrations may
# not be required.
if [ -e /home/mfcs.lib.wvu.edu/SQLFiles/mfcs.sql ]
then
  mysql -u root -h $DATABASE_HOST mfcs < /home/mfcs.lib.wvu.edu/SQLFiles/mfcs.sql
  # only add if we are using a backup from the real server
  mysql -u root -h $DATABASE_HOST mfcs < /home/mfcs.lib.wvu.edu/SQLFiles/migrations/2016.07.26.0945.sql
  # updated one table to adjust fieldtype to LONGTEXT for large data
  # only needed until production server is updated and we get a new backup copy to use
  mysql -u root -h $DATABASE_HOST mfcs < /home/mfcs.lib.wvu.edu/SQLFiles/migrations/2024.05.06.1740.sql
else
  echo "No backup found, skipping database import and running migrations"
  mysql -u root -h $DATABASE_HOST mfcs < /home/mfcs.lib.wvu.edu/SQLFiles/baseSnapshot.sql
  for f in $SQLFILES
  do
  	echo "Processing $f ..."
  	mysql -u root -h $DATABASE_HOST mfcs < "$f"
  done
fi

