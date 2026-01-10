-- Create separate admin table for admin login
-- This table is completely separate from the users table

CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If you want to migrate existing admin users from users table to admin table, run this:
-- INSERT INTO admin (username, email, password) 
-- SELECT username, email, password FROM users WHERE role = 'admin';

