CREATE DATABASE IF NOT EXISTS buildguard_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON buildguard_test.* TO 'buildguard'@'%';
FLUSH PRIVILEGES;
