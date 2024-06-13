#!/bin/bash
SERVERURL="/home/mfcs.lib.wvu.edu"
SQLFILES="$SERVERURL/SQLFiles/migrations/*.sql"

# mysql -u root -h $DATABASE_HOST < /SQLFiles/configure-database.sql

# import EngineAPI database
mysql -u root -h $DATABASE_HOST EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

# import engineCMS database - engine keeps looking for this database
mysql -u root -h $DATABASE_HOST engineCMS < $SERVERURL/SQLFiles/engineCMS.sql

# first value is size in megabytes to load main database
# mysql -u root -h $DATABASE_HOST -Bse "set global max_allowed_packet=1024*1024*1024"; #first value is size in megabytes

# if backup exists import that and do selected migrations
# I was using a older backup and additional migrations needed
# to be run. if using a more current backup the migrations may
# not be required.
if [ -e /home/mfcs.lib.wvu.edu/SQLFiles/mfcs.sql ]
then
  mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/mfcs.sql
  # only add if we are using a backup from the real server and need the docker user
  # mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/migrations/2024.06.12.1509.sql
  # updated one table to adjust fieldtype to LONGTEXT for large data
  # only needed until production server is updated and we get a new backup copy to use
  mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/migrations/2024.05.06.1740.sql
else
  echo "No backup found, skipping database import and running migrations"
  mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/baseSnapshot.sql
  for f in $SQLFILES
  do
  	echo "Processing $f ..."
  	mysql -u root -h $DATABASE_HOST mfcs < "$f"
  done
fi

# This is for objectID=192268
# http://localhost:8080/dataEntry/object.php?objectID=192268
# create path ./data/archives/f/c/1/6/c/0/0/3/e/d/8/a/4/4/6/c/9/9/8/0/1/d/d/9/f/4/8/c/a/f/c/1/fc16c003-ed8a-446c-9980-1dd9f48cafc1
mkdir -p ./data/archives/mfcs/f/c/1/6/c/0/0/3/e/d/8/a/4/4/6/c/9/9/8/0/1/d/d/9/f/4/8/c/a/f/c/1/fc16c003-ed8a-446c-9980-1dd9f48cafc1

# copy am913_*.tif to the above path
cp ./data/testing/archive-download/am913_b27_f03/am913_*.tif ./data/archives/mfcs/f/c/1/6/c/0/0/3/e/d/8/a/4/4/6/c/9/9/8/0/1/d/d/9/f/4/8/c/a/f/c/1/fc16c003-ed8a-446c-9980-1dd9f48cafc1

# This is for objectID=191971
# http://localhost:8080/dataEntry/object.php?objectID=191971

# create path ./data/archives/6/b/8/f/b/4/d/9/d/9/d/6/4/5/5/a/8/e/8/8/f/9/3/f/e/0/a/2/8/c/9/2/6b8fb4d9-d9d6-455a-8e88-f93fe0a28c92/
mkdir -p ./data/archives/mfcs/6/b/8/f/b/4/d/9/d/9/d/6/4/5/5/a/8/e/8/8/f/9/3/f/e/0/a/2/8/c/9/2/6b8fb4d9-d9d6-455a-8e88-f93fe0a28c92

# copy am913_b28_*.tif to the above path
cp ./data/testing/archive-download/am913_b28_f01/am913_*.tif ./data/archives/mfcs/6/b/8/f/b/4/d/9/d/9/d/6/4/5/5/a/8/e/8/8/f/9/3/f/e/0/a/2/8/c/9/2/6b8fb4d9-d9d6-455a-8e88-f93fe0a28c92

# This is for objectID=192379
# http://localhost:8080/dataEntry/object.php?objectID=192379
# create path ./data/archives/f/d/e/d/e/4/0/e/5/a/3/b/4/3/8/4/8/6/2/d/2/3/d/a/f/a/6/d/d/d/f/0/fdede40e-5a3b-4384-862d-23dafa6dddf0
mkdir -p ./data/archives/mfcs/f/d/e/d/e/4/0/e/5/a/3/b/4/3/8/4/8/6/2/d/2/3/d/a/f/a/6/d/d/d/f/0/fdede40e-5a3b-4384-862d-23dafa6dddf0

# copy WVUL_am1500_*.tif to the above path
cp ./data/testing/archive-download/am1500_b7_f01/WVUL_am1500_*.tif ./data/archives/mfcs/f/d/e/d/e/4/0/e/5/a/3/b/4/3/8/4/8/6/2/d/2/3/d/a/f/a/6/d/d/d/f/0/fdede40e-5a3b-4384-862d-23dafa6dddf0

# This is for objectID=172265
# http://localhost:8080/dataEntry/object.php?objectID=172265
# create path ./data/archives/2/5/8/b/f/1/3/6/e/9/5/b/4/8/9/c/9/e/d/3/9/8/d/a/f/c/a/5/e/4/8/2/258bf136-e95b-489c-9ed3-98dafca5e482
mkdir -p ./data/archives/mfcs/2/5/8/b/f/1/3/6/e/9/5/b/4/8/9/c/9/e/d/3/9/8/d/a/f/c/a/5/e/4/8/2/258bf136-e95b-489c-9ed3-98dafca5e482

# copy WVUL_am1500_*.tif to the above path
cp ./data/testing/archive-download/4224.HobbieLeenieFalconeJon/4224.HobbieLeenieFalconeJon.Video.001.2.19.21.mp4 ./data/archives/mfcs/2/5/8/b/f/1/3/6/e/9/5/b/4/8/9/c/9/e/d/3/9/8/d/a/f/c/a/5/e/4/8/2/258bf136-e95b-489c-9ed3-98dafca5e482

