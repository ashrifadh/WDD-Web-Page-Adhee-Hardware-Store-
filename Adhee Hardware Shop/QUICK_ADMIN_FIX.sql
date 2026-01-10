-- Quick Admin Access Fix
-- Run this in phpMyAdmin to fix admin access issues

-- Step 1: Add role column if it doesn't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `role` VARCHAR(20) DEFAULT 'customer';

-- Step 2: Make user ID 1 an admin (change the ID if needed)
UPDATE `users` SET `role` = 'admin' WHERE `id` = 1;

-- Step 3: Check which users are admins
SELECT id, username, email, role FROM users WHERE role = 'admin';

-- Step 4: To make a specific user admin (replace USERNAME with actual username)
-- UPDATE `users` SET `role` = 'admin' WHERE `username` = 'USERNAME';

