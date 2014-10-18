USE twitter_fake_db;

-- create tables
DROP TABLE IF EXISTS user;
CREATE TABLE user (id INT NOT NULL auto_increment PRIMARY KEY, username VARCHAR(55) NOT NULL, password VARCHAR(255) NOT NULL);
DROP TABLE IF EXISTS message;
CREATE TABLE message (id INT NOT NULL auto_increment PRIMARY KEY, user_id INT NOT NULL, message VARCHAR(140) NOT NULL);

-- seed database
INSERT INTO user(username, password) VALUES('david','$2y$10$DVbX36HAPhgAhN5AcdLA.OxY2qBKGRklbQkNNFPXDeM.OsuqKaFhu'); -- "password"
INSERT INTO message(user_id, message) VALUES(1,'Hello World');
INSERT INTO message(user_id, message) VALUES(2,'Message 2');
INSERT INTO message(user_id, message) VALUES(3,'Some extra message');
INSERT INTO message(user_id, message) VALUES(4,'final message');