Everything runs on port 80 on the domain: awesome.dev
The files are in the /awesome/
Checkout the "views", "models", and "tests" folders.  I also worked on the other files located directly in the /awesome/ folder.

Steps:

1.  append to hosts file:
     192.168.56.101      awesome.dev
2. run "vagrant up"

3. run "vagrant ssh"

4. import "setup_db.sql" into the mysql database (usernames and passwords are in puppet\config.yaml file)