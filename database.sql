-- =====================================================================
-- CRM Faqih Mart - Skema Database
-- ---------------------------------------------------------------------
-- Cara pakai:
--   mysql -u root -p < database.sql
-- atau impor file ini lewat phpMyAdmin.
--
-- Setelah impor, login awal:
--   URL      : http://localhost/Faqihmart-CRM/login
--   Username : admin
--   Password : admin123
-- WAJIB segera ganti password lewat menu "Ganti Password" setelah login.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

-- ---------------------------------------------------------------------
-- Database
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `faqihmart_crm`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `faqihmart_crm`;

-- ---------------------------------------------------------------------
-- Tabel: admins
-- Hanya admin yang dapat login. Tidak ada registrasi publik.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `name`          VARCHAR(100) NOT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin awal. Password: admin123 (hash bcrypt). Ganti setelah login.
INSERT INTO `admins` (`username`, `password_hash`, `name`) VALUES
('admin', '$2y$10$ki0TNnd1zTcROUZUqUhzlOkD8JGza8milpEiimcVA2ZGiWfUlekny', 'Administrator');

-- ---------------------------------------------------------------------
-- Tabel: customers
-- Soft delete melalui archived_at (data arsip tidak tampil di daftar aktif).
-- location_status:
--   incomplete = informasi alamat wajib belum lengkap
--   unverified = data alamat tersedia, lokasi belum dikonfirmasi
--   verified   = admin sudah memastikan titik sesuai rumah pelanggan
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_code`   VARCHAR(20)  NOT NULL,
  `full_name`       VARCHAR(150) NOT NULL,
  `whatsapp_number` VARCHAR(30)  NOT NULL,
  `housing_name`    VARCHAR(150) NULL,
  `full_address`    TEXT         NULL,
  `block_number`    VARCHAR(50)  NULL,
  `rt_rw`           VARCHAR(20)  NULL,
  `landmark`        VARCHAR(255) NULL,
  `latitude`        DECIMAL(10,7) NULL,
  `longitude`       DECIMAL(10,7) NULL,
  `google_maps_url` VARCHAR(500) NULL,
  `delivery_notes`  TEXT         NULL,
  `location_status` ENUM('incomplete','unverified','verified') NOT NULL DEFAULT 'incomplete',
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived_at`     DATETIME     NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_code` (`customer_code`),
  KEY `idx_customers_whatsapp` (`whatsapp_number`),
  KEY `idx_customers_name` (`full_name`),
  KEY `idx_customers_status` (`location_status`),
  KEY `idx_customers_archived` (`archived_at`),
  CONSTRAINT `chk_customers_latitude`
    CHECK (`latitude` IS NULL OR (`latitude` >= -90 AND `latitude` <= 90)),
  CONSTRAINT `chk_customers_longitude`
    CHECK (`longitude` IS NULL OR (`longitude` >= -180 AND `longitude` <= 180)),
  CONSTRAINT `chk_customers_coordinate_pair`
    CHECK ((`latitude` IS NULL AND `longitude` IS NULL)
        OR (`latitude` IS NOT NULL AND `longitude` IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
