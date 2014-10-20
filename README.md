Everything runs on port 80 on the domain: awesome.dev<br>
The files are in the /awesome/<br>
Checkout the "views", "models", and "tests" folders.  I also worked on the other files located directly in the /awesome/ folder.

Steps:

1.  append to hosts file:<br>
     192.168.56.101      awesome.dev
2. run "vagrant up"

3. run "vagrant ssh"

4. import "setup_db.sql" into the mysql database:<br>
     -- /usr/bin/mysql -u twitter_user -pX7Ev3x7YKR87wVkqEYsUhdr8f9xmzj twitter_fake_db < /var/www/awesome/setup_db.sql
