-- ispconfig Autodiscover Module - Database Schema

-- Autodiscover Configuration Table
CREATE TABLE IF NOT EXISTS `autodiscover_config` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `domain_id` INT NOT NULL,
  `domain` VARCHAR(255) NOT NULL,
  `enabled` ENUM('y','n') NOT NULL DEFAULT 'y',
  `imap_host` VARCHAR(255) NOT NULL DEFAULT 'mail.example.com',
  `imap_port` INT NOT NULL DEFAULT 993,
  `imap_security` ENUM('SSL','STARTTLS','NONE') NOT NULL DEFAULT 'SSL',
  `smtp_host` VARCHAR(255) NOT NULL DEFAULT 'mail.example.com',
  `smtp_port` INT NOT NULL DEFAULT 465,
  `smtp_security` ENUM('SSL','STARTTLS','NONE') NOT NULL DEFAULT 'SSL',
  `smtp_auth_required` ENUM('y','n') NOT NULL DEFAULT 'y',
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `domain_id` (`domain_id`),
  KEY `domain` (`domain`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add custom field to domain table (if not exists)
ALTER TABLE `domain` ADD COLUMN `autodiscover` ENUM('y','n') NOT NULL DEFAULT 'n' AFTER `active`;
