-- Fix orders table - add missing columns
-- Run this in phpMyAdmin SQL tab

-- Add created_at column if it doesn't exist
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Add updated_at column if it doesn't exist  
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update existing orders to have timestamps
UPDATE `orders` SET `created_at` = NOW() WHERE `created_at` IS NULL OR `created_at` = '0000-00-00 00:00:00';
UPDATE `orders` SET `updated_at` = NOW() WHERE `updated_at` IS NULL OR `updated_at` = '0000-00-00 00:00:00';
