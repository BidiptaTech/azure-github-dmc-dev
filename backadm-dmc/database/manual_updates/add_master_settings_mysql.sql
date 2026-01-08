-- Manual SQL to add DMC-level columns to the users table (MySQL Version)
-- Run this SQL script if you want to manually alter the table instead of using Laravel migration
-- Date: 2025-01-31
-- Description: Adds group_pax and markup_service columns to users table
-- These settings are specific to each DMC user (user_type = 2 or DMC roles)
-- Note: markup_type and markup_price columns already exist in users table

-- For MySQL Database

-- Add group_pax column (integer, nullable)
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `group_pax` INT NULL AFTER `role_id`;

-- Add markup_service column (varchar, nullable)
-- Options: all_service, hotels_only, others_only
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `markup_service` VARCHAR(255) NULL AFTER `markup_type`;

-- Note: markup_type and markup_price already exist in the users table
-- markup_type: Used for markup calculation type
-- markup_price: Used for markup value/percentage

-- Verify the columns were added
SHOW COLUMNS FROM `users` WHERE `Field` IN ('group_pax', 'markup_service', 'markup_type', 'markup_price');

