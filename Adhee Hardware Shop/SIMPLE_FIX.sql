-- SIMPLE FIX - Run this in phpMyAdmin to make orders work
-- Just copy and paste this into the SQL tab

-- Add timestamp columns to orders table
ALTER TABLE `orders` 
ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- That's it! Now orders will work perfectly.
