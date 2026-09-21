-- ============================================================
-- LeadGen Pro — Database Schema
-- MySQL 8+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- 1. Users
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 2. Business Categories
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `business_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'bi-building',
    `variants` JSON DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 3. Countries
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `countries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(3) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_countries_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 4. Cities
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `country_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cities_country` (`country_id`),
    CONSTRAINT `fk_cities_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 5. Areas
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `areas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `city_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_areas_city` (`city_id`),
    CONSTRAINT `fk_areas_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. Search Jobs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `search_jobs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `category_name` VARCHAR(100) NOT NULL,
    `country_name` VARCHAR(100) DEFAULT NULL,
    `city_name` VARCHAR(100) DEFAULT NULL,
    `area_name` VARCHAR(100) DEFAULT NULL,
    `search_query` VARCHAR(500) NOT NULL,
    `search_mode` ENUM('text','radius') NOT NULL DEFAULT 'text',
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `radius_km` DECIMAL(5,2) DEFAULT NULL,
    `max_results` INT NOT NULL DEFAULT 60,
    `status` ENUM('queued','searching','fetching_details','finding_emails','completed','failed') NOT NULL DEFAULT 'queued',
    `total_found` INT NOT NULL DEFAULT 0,
    `new_leads` INT NOT NULL DEFAULT 0,
    `duplicates` INT NOT NULL DEFAULT 0,
    `emails_found` INT NOT NULL DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_searchjobs_user` (`user_id`),
    KEY `idx_searchjobs_status` (`status`),
    KEY `idx_searchjobs_created` (`created_at`),
    CONSTRAINT `fk_searchjobs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 7. Leads (Core Table)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `place_id` VARCHAR(300) NOT NULL,
    `business_name` VARCHAR(500) NOT NULL,
    `primary_category` VARCHAR(200) DEFAULT NULL,
    `types_json` JSON DEFAULT NULL,
    `formatted_address` VARCHAR(500) DEFAULT NULL,
    `national_phone` VARCHAR(50) DEFAULT NULL,
    `international_phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `email_source_url` VARCHAR(500) DEFAULT NULL,
    `website_url` VARCHAR(500) DEFAULT NULL,
    `google_maps_url` VARCHAR(500) DEFAULT NULL,
    `latitude` DECIMAL(10,7) DEFAULT NULL,
    `longitude` DECIMAL(10,7) DEFAULT NULL,
    `rating` DECIMAL(2,1) DEFAULT NULL,
    `review_count` INT DEFAULT NULL,
    `business_status` VARCHAR(50) DEFAULT NULL,
    `search_category` VARCHAR(100) DEFAULT NULL,
    `search_country` VARCHAR(100) DEFAULT NULL,
    `search_city` VARCHAR(100) DEFAULT NULL,
    `search_area` VARCHAR(100) DEFAULT NULL,
    `search_query` VARCHAR(500) DEFAULT NULL,
    `search_job_id` INT UNSIGNED DEFAULT NULL,
    `lead_status` ENUM('new','not_contacted','contacted','follow_up','interested','converted','not_interested','invalid') NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_api_refresh` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_leads_place_id` (`place_id`),
    KEY `idx_leads_phone` (`national_phone`),
    KEY `idx_leads_email` (`email`),
    KEY `idx_leads_website` (`website_url`(191)),
    KEY `idx_leads_category` (`search_category`),
    KEY `idx_leads_city` (`search_city`),
    KEY `idx_leads_area` (`search_area`),
    KEY `idx_leads_status` (`lead_status`),
    KEY `idx_leads_created` (`created_at`),
    KEY `idx_leads_rating` (`rating`),
    KEY `idx_leads_search_job` (`search_job_id`),
    CONSTRAINT `fk_leads_searchjob` FOREIGN KEY (`search_job_id`) REFERENCES `search_jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 8. Lead Emails
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_emails` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `source_url` VARCHAR(500) DEFAULT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('found','not_found','website_unreachable','blocked','pending') NOT NULL DEFAULT 'found',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_emails_lead` (`lead_id`),
    KEY `idx_lead_emails_email` (`email`),
    CONSTRAINT `fk_lead_emails_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 9. Lead Notes
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_notes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `note` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_notes_lead` (`lead_id`),
    CONSTRAINT `fk_lead_notes_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lead_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 10. Lead Activities
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lead_activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_activities_lead` (`lead_id`),
    KEY `idx_lead_activities_created` (`created_at`),
    CONSTRAINT `fk_lead_activities_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 11. Website Crawl Logs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `website_crawl_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lead_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `http_status` INT DEFAULT NULL,
    `emails_found` INT NOT NULL DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_crawl_logs_lead` (`lead_id`),
    CONSTRAINT `fk_crawl_logs_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 12. Settings
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 13. API Usage Logs
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_usage_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `endpoint` VARCHAR(200) NOT NULL,
    `search_job_id` INT UNSIGNED DEFAULT NULL,
    `place_id` VARCHAR(300) DEFAULT NULL,
    `http_status` INT DEFAULT NULL,
    `response_time_ms` INT DEFAULT NULL,
    `request_result` ENUM('success','error','rate_limited','quota_exceeded','timeout') NOT NULL DEFAULT 'success',
    `error_message` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_api_logs_endpoint` (`endpoint`),
    KEY `idx_api_logs_created` (`created_at`),
    KEY `idx_api_logs_result` (`request_result`),
    KEY `idx_api_logs_job` (`search_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
