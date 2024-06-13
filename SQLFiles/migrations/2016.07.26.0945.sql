#Set Permissions on the Vagrant Account to ADMIN for testing
#This line below fails to update the vagrant user
#Where does the vagrant user get added

#UPDATE users SET status='ADMIN', active=1, formCreator=1 WHERE username='vagrant';

# INSERT INTO users (username, status, active, formCreator) VALUES ('vagrant','ADMIN',1,1);
INSERT INTO users (username, status, active, formCreator) VALUES ('admin','ADMIN',1,1);