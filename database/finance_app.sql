-- ============================================================
-- Finance App - Database Schema
-- Import ke phpMyAdmin sebelum menjalankan aplikasi
-- ============================================================

CREATE DATABASE IF NOT EXISTS `finance_app` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `finance_app`;

-- ============================================================
-- Tabel: users
-- ============================================================
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: categories
-- ============================================================
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL COMMENT 'NULL = kategori global/default',
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('income','expense') NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'tag',
  `color` VARCHAR(20) DEFAULT '#6C63FF',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: transactions
-- ============================================================
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `type` ENUM('income','expense') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `transaction_date` DATE NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Data Kategori Default (user_id = NULL → global)
-- ============================================================
INSERT INTO `categories` (`user_id`, `name`, `type`, `icon`, `color`) VALUES
-- Pemasukan
(NULL, 'Gaji',          'income',  'briefcase',    '#10B981'),
(NULL, 'Freelance',     'income',  'laptop',       '#059669'),
(NULL, 'Investasi',     'income',  'trending-up',  '#34D399'),
(NULL, 'Bisnis',        'income',  'shopping-bag', '#6EE7B7'),
(NULL, 'Bonus',         'income',  'gift',         '#A7F3D0'),
(NULL, 'Lainnya',       'income',  'plus-circle',  '#D1FAE5'),
-- Pengeluaran
(NULL, 'Makanan',       'expense', 'coffee',       '#F43F5E'),
(NULL, 'Transport',     'expense', 'truck',        '#EF4444'),
(NULL, 'Belanja',       'expense', 'shopping-cart','#F97316'),
(NULL, 'Tagihan',       'expense', 'file-text',    '#FB923C'),
(NULL, 'Kesehatan',     'expense', 'heart',        '#EC4899'),
(NULL, 'Hiburan',       'expense', 'film',         '#8B5CF6'),
(NULL, 'Pendidikan',    'expense', 'book',         '#3B82F6'),
(NULL, 'Tabungan',      'expense', 'save',         '#06B6D4'),
(NULL, 'Lainnya',       'expense', 'more-horizontal','#94A3B8');

-- ============================================================
-- Contoh User Demo (password: demo1234)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('Demo User', 'demo@finance.app', '$2y$12$9GJJ7k8VCLJlHK4IJkFV8OPcmjIL4ADkdB35R0AJXeUCVqCy4Qjwm');

-- ============================================================
-- Contoh Transaksi Demo (user_id = 1)
-- ============================================================
INSERT INTO `transactions` (`user_id`, `category_id`, `type`, `amount`, `description`, `transaction_date`) VALUES
(1, 1, 'income',  8500000.00, 'Gaji Bulan April',         '2026-04-01'),
(1, 2, 'income',  2500000.00, 'Project Freelance Web',    '2026-04-05'),
(1, 7, 'expense',  450000.00, 'Makan siang & kopi',       '2026-04-02'),
(1, 8, 'expense',  320000.00, 'Bensin & parkir',          '2026-04-03'),
(1, 9, 'expense', 1200000.00, 'Belanja bulanan',          '2026-04-04'),
(1,10, 'expense',  500000.00, 'Listrik & internet',       '2026-04-05'),
(1, 7, 'expense',  380000.00, 'Makan malam keluarga',     '2026-04-06'),
(1,11, 'expense',  250000.00, 'Vitamin & obat',           '2026-04-07'),
(1,12, 'expense',  150000.00, 'Netflix & Spotify',        '2026-04-08'),
(1, 1, 'income',  8500000.00, 'Gaji Bulan Maret',         '2026-03-01'),
(1, 3, 'income',  1200000.00, 'Dividen saham',            '2026-03-10'),
(1, 7, 'expense',  520000.00, 'Makan siang sebulan',      '2026-03-05'),
(1, 8, 'expense',  400000.00, 'Transport Grab/Gojek',     '2026-03-08'),
(1, 9, 'expense', 1500000.00, 'Belanja fashion',          '2026-03-15'),
(1,13, 'expense',  800000.00, 'Kursus online',            '2026-03-20'),
(1, 1, 'income',  8500000.00, 'Gaji Bulan Februari',      '2026-02-01'),
(1, 5, 'income',  3000000.00, 'Bonus Tahunan',            '2026-02-14'),
(1, 7, 'expense',  480000.00, 'Makan siang',              '2026-02-03'),
(1,10, 'expense',  490000.00, 'Tagihan bulanan',          '2026-02-05'),
(1,12, 'expense',  350000.00, 'Bioskop & hiburan',        '2026-02-20');
