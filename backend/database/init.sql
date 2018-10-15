CREATE USER 'phpce2018'@'localhost' IDENTIFIED WITH mysql_native_password BY 'secret';
CREATE DATABASE demo COLLATE utf8_general_ci;
GRANT ALL ON demo.* TO 'phpce2018'@'localhost';
