#!/bin/bash

SERVERURL="/home/mfcs.lib.wvu.edu"
SQLFILES="$SERVERURL/SQLFiles/migrations/*.sql"
APP_ENV=${APP_ENV:-development}

if [ "$APP_ENV" = "production" ]; then
    read -p "WARNING: You are about to overwrite the production database. Are you sure you want to continue? (y/n) " -r
    echo

    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborting database overwrite."
        exit 1
    fi
fi

echo "Creating New EngineAPI database"

import EngineAPI database
mysql -u root -h $DATABASE_HOST EngineAPI < /tmp/git/engineAPI/sql/EngineAPI.sql

if [ -e $SERVERURL/SQLFiles/mfcs.sql ]
then
  echo "Importing mfcs database backup"
  mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/mfcs.sql
else
  echo "No backup found, skipping database import and running migrations"
  mysql -u root -h $DATABASE_HOST mfcs < $SERVERURL/SQLFiles/baseSnapshot.sql
  for f in $SQLFILES
  do
  	echo "Processing $f ..."
  	mysql -u root -h $DATABASE_HOST mfcs < "$f"
  done
fi
