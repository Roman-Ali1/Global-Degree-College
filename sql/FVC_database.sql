-- ============================================================
-- Future Vision College – Complete Database Schema
-- Database : fvc_db
-- Charset  : utf8mb4_unicode_ci
-- Engine   : InnoDB
-- Author   : FVC Dev Team
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ------------------------------------------------------------
-- Drop tables in reverse dependency order (safe re-import)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `hostel_information`;
DROP TABLE IF EXISTS `scholarships`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `faculty`;
DROP TABLE IF EXISTS `admission_applications`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `admins`;

-- ============================================================
-- TABLE 1: admins
-- Purpose : Admin panel users with role-based access
-- ============================================================
CREATE TABLE `admins` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`       VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `password`        VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
    `role`            ENUM('super_admin','editor','admission_officer') NOT NULL DEFAULT 'editor',
    `profile_photo`   VARCHAR(255) DEFAULT NULL,
    `last_login`      DATETIME DEFAULT NULL,
    `login_attempts`  TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'brute-force counter',
    `locked_until`    DATETIME DEFAULT NULL COMMENT 'account lock expiry',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_email` (`email`),
    INDEX `idx_admins_role` (`role`),
    INDEX `idx_admins_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin panel users';

-- Default super admin (password: Admin@1234 – change immediately)
INSERT INTO `admins` (`full_name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@futurevision.edu.pk', '$2y$12$YourHashWillGoHere.replaceThisWithActualBcryptHash', 'super_admin');


-- ============================================================
-- TABLE 2: students
-- Purpose : Student master profiles (linked to applications)
-- ============================================================
CREATE TABLE `students` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_code`    VARCHAR(20) NOT NULL COMMENT 'e.g. FVC-2024-0001',
    `full_name`       VARCHAR(100) NOT NULL,
    `father_name`     VARCHAR(100) NOT NULL,
    `cnic_bform`      VARCHAR(20) NOT NULL COMMENT 'CNIC or B-Form number',
    `date_of_birth`   DATE NOT NULL,
    `gender`          ENUM('male','female','other') NOT NULL,
    `email`           VARCHAR(150) DEFAULT NULL,
    `mobile`          VARCHAR(20) NOT NULL,
    `address`         TEXT NOT NULL,
    `city`            VARCHAR(80) DEFAULT NULL,
    `photo`           VARCHAR(255) DEFAULT NULL COMMENT 'path under /uploads/admissions/',
    `course_id`       INT UNSIGNED DEFAULT NULL COMMENT 'FK → courses.id',
    `enrollment_year` YEAR DEFAULT NULL,
    `status`          ENUM('active','inactive','graduated','expelled') NOT NULL DEFAULT 'active',
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_students_code`     (`student_code`),
    UNIQUE KEY `uq_students_cnic`     (`cnic_bform`),
    INDEX `idx_students_course`       (`course_id`),
    INDEX `idx_students_status`       (`status`),
    INDEX `idx_students_year`         (`enrollment_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registered student profiles';


-- ============================================================
-- TABLE 3: courses
-- Purpose : Programs/degrees offered by the college
-- ============================================================
CREATE TABLE `courses` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(150) NOT NULL COMMENT 'e.g. FSc Pre-Medical',
    `slug`            VARCHAR(170) NOT NULL COMMENT 'URL-friendly identifier',
    `short_code`      VARCHAR(20) NOT NULL COMMENT 'e.g. FSC-MED',
    `description`     TEXT DEFAULT NULL,
    `duration`        VARCHAR(50) NOT NULL COMMENT 'e.g. 2 Years',
    `eligibility`     TEXT DEFAULT NULL COMMENT 'Entry requirements',
    `total_seats`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `fee_per_month`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `admission_fee`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `icon`            VARCHAR(100) DEFAULT NULL COMMENT 'Font Awesome class e.g. fa-flask',
    `cover_image`     VARCHAR(255) DEFAULT NULL,
    `sort_order`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_courses_slug`      (`slug`),
    UNIQUE KEY `uq_courses_code`      (`short_code`),
    INDEX `idx_courses_active`        (`is_active`),
    INDEX `idx_courses_order`         (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Programs offered by the college';

INSERT INTO `courses`
    (`title`, `slug`, `short_code`, `description`, `duration`, `eligibility`, `total_seats`, `fee_per_month`, `admission_fee`, `icon`, `sort_order`)
VALUES
('FSc Pre-Medical',     'fsc-pre-medical',     'FSC-MED', 'Prepare for MBBS, BDS and allied health sciences.', '2 Years', 'Matric (Science) with minimum 60% marks', 120, 1500.00, 5000.00, 'fa-heartbeat', 1),
('FSc Pre-Engineering', 'fsc-pre-engineering', 'FSC-ENG', 'Gateway to engineering universities nationwide.',    '2 Years', 'Matric (Science) with minimum 60% marks', 120, 1500.00, 5000.00, 'fa-cogs',      2),
('ICS',                 'ics',                 'ICS',     'Computer Science with Mathematics and Physics.',    '2 Years', 'Matric (Science) with minimum 55% marks', 80,  1400.00, 4500.00, 'fa-laptop-code',3),
('FA',                  'fa',                  'FA',      'Faculty of Arts — literature, history, civics.',   '2 Years', 'Matric (Arts/Science) minimum 45% marks',  80,  1200.00, 4000.00, 'fa-book-open',  4),
('I.Com',               'icom',                'ICOM',    'Commerce with Accounting, Economics and Stats.',   '2 Years', 'Matric (Any group) minimum 45% marks',     80,  1200.00, 4000.00, 'fa-chart-line', 5);


-- ============================================================
-- TABLE 4: admission_applications
-- Purpose : Online admission form submissions
-- ============================================================
CREATE TABLE `admission_applications` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `app_number`        VARCHAR(30) NOT NULL COMMENT 'e.g. APP-2024-00001',
    `student_name`      VARCHAR(100) NOT NULL,
    `father_name`       VARCHAR(100) NOT NULL,
    `cnic_bform`        VARCHAR(20) NOT NULL,
    `date_of_birth`     DATE NOT NULL,
    `gender`            ENUM('male','female','other') NOT NULL,
    `email`             VARCHAR(150) DEFAULT NULL,
    `mobile`            VARCHAR(20) NOT NULL,
    `address`           TEXT NOT NULL,
    `city`              VARCHAR(80) DEFAULT NULL,
    `previous_school`   VARCHAR(200) NOT NULL,
    `previous_class`    VARCHAR(80) NOT NULL COMMENT 'e.g. Matric',
    `obtained_marks`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `total_marks`       SMALLINT UNSIGNED NOT NULL DEFAULT 1100,
    `percentage`        DECIMAL(5,2) GENERATED ALWAYS AS
                            (ROUND((`obtained_marks` / `total_marks`) * 100, 2)) STORED,
    `course_id`         INT UNSIGNED NOT NULL COMMENT 'FK → courses.id',
    `photo`             VARCHAR(255) DEFAULT NULL,
    `documents`         VARCHAR(255) DEFAULT NULL COMMENT 'Zipped docs or JSON path list',
    `status`            ENUM('pending','under_review','accepted','rejected','waitlisted')
                            NOT NULL DEFAULT 'pending',
    `reviewed_by`       INT UNSIGNED DEFAULT NULL COMMENT 'FK → admins.id',
    `review_notes`      TEXT DEFAULT NULL,
    `reviewed_at`       DATETIME DEFAULT NULL,
    `ip_address`        VARCHAR(45) DEFAULT NULL COMMENT 'IPv4 or IPv6',
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_app_number`            (`app_number`),
    INDEX `idx_app_course`                (`course_id`),
    INDEX `idx_app_status`                (`status`),
    INDEX `idx_app_reviewed_by`           (`reviewed_by`),
    INDEX `idx_app_created`               (`created_at`),
    INDEX `idx_app_cnic`                  (`cnic_bform`),

    CONSTRAINT `fk_app_course`
        FOREIGN KEY (`course_id`)    REFERENCES `courses` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_app_reviewer`
        FOREIGN KEY (`reviewed_by`) REFERENCES `admins`  (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Online admission form submissions';


-- ============================================================
-- TABLE 5: faculty
-- Purpose : Teaching and administrative staff
-- ============================================================
CREATE TABLE `faculty` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`       VARCHAR(100) NOT NULL,
    `designation`     VARCHAR(100) NOT NULL COMMENT 'e.g. Senior Lecturer',
    `department`      VARCHAR(100) DEFAULT NULL COMMENT 'e.g. Science Dept',
    `qualification`   VARCHAR(200) DEFAULT NULL COMMENT 'e.g. M.Phil Chemistry',
    `experience_years`TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `email`           VARCHAR(150) DEFAULT NULL,
    `phone`           VARCHAR(20) DEFAULT NULL,
    `bio`             TEXT DEFAULT NULL,
    `photo`           VARCHAR(255) DEFAULT NULL,
    `course_id`       INT UNSIGNED DEFAULT NULL COMMENT 'Primary course taught',
    `sort_order`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `show_on_home`    TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Feature on homepage',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_faculty_course`    (`course_id`),
    INDEX `idx_faculty_home`      (`show_on_home`),
    INDEX `idx_faculty_active`    (`is_active`),

    CONSTRAINT `fk_faculty_course`
        FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Teaching and administrative staff';


-- ============================================================
-- TABLE 6: news
-- Purpose : News articles / announcements
-- ============================================================
CREATE TABLE `news` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(280) NOT NULL,
    `excerpt`         VARCHAR(500) DEFAULT NULL COMMENT 'Short summary for listing cards',
    `body`            LONGTEXT NOT NULL,
    `featured_image`  VARCHAR(255) DEFAULT NULL,
    `category`        VARCHAR(80) DEFAULT NULL COMMENT 'e.g. Announcement, Result, Achievement',
    `tags`            VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated',
    `author_id`       INT UNSIGNED DEFAULT NULL COMMENT 'FK → admins.id',
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `views`           INT UNSIGNED NOT NULL DEFAULT 0,
    `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at`    DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_news_slug`         (`slug`),
    INDEX `idx_news_status`           (`status`),
    INDEX `idx_news_featured`         (`is_featured`),
    INDEX `idx_news_published`        (`published_at`),
    INDEX `idx_news_author`           (`author_id`),
    FULLTEXT INDEX `ft_news_search`   (`title`, `excerpt`, `body`),

    CONSTRAINT `fk_news_author`
        FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='News articles and announcements';


-- ============================================================
-- TABLE 7: events
-- Purpose : College events, seminars, exams
-- ============================================================
CREATE TABLE `events` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(280) NOT NULL,
    `description`     TEXT DEFAULT NULL,
    `event_date`      DATE NOT NULL,
    `event_time`      TIME DEFAULT NULL,
    `end_date`        DATE DEFAULT NULL,
    `venue`           VARCHAR(255) DEFAULT NULL,
    `organizer`       VARCHAR(150) DEFAULT NULL,
    `featured_image`  VARCHAR(255) DEFAULT NULL,
    `type`            ENUM('academic','sports','cultural','seminar','exam','other')
                          NOT NULL DEFAULT 'academic',
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `status`          ENUM('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    `created_by`      INT UNSIGNED DEFAULT NULL COMMENT 'FK → admins.id',
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_events_slug`       (`slug`),
    INDEX `idx_events_date`           (`event_date`),
    INDEX `idx_events_status`         (`status`),
    INDEX `idx_events_type`           (`type`),
    INDEX `idx_events_featured`       (`is_featured`),

    CONSTRAINT `fk_events_creator`
        FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='College events, seminars and examinations';


-- ============================================================
-- TABLE 8: gallery
-- Purpose : Image gallery with categories
-- ============================================================
CREATE TABLE `gallery` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(200) NOT NULL,
    `description`     VARCHAR(500) DEFAULT NULL,
    `filename`        VARCHAR(255) NOT NULL COMMENT 'Stored filename under /uploads/gallery/',
    `category`        VARCHAR(80) NOT NULL DEFAULT 'General'
                          COMMENT 'e.g. Campus, Events, Sports, Labs',
    `sort_order`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `uploaded_by`     INT UNSIGNED DEFAULT NULL COMMENT 'FK → admins.id',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_gallery_category`  (`category`),
    INDEX `idx_gallery_active`    (`is_active`),
    INDEX `idx_gallery_order`     (`sort_order`),

    CONSTRAINT `fk_gallery_uploader`
        FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Image gallery with categories';


-- ============================================================
-- TABLE 9: testimonials
-- Purpose : Student and parent reviews
-- ============================================================
CREATE TABLE `testimonials` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100) NOT NULL,
    `designation`     VARCHAR(150) DEFAULT NULL COMMENT 'e.g. FSc Student 2023, Parent',
    `photo`           VARCHAR(255) DEFAULT NULL,
    `message`         TEXT NOT NULL,
    `rating`          TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1-5 stars',
    `course_id`       INT UNSIGNED DEFAULT NULL COMMENT 'FK → courses.id',
    `status`          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `sort_order`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `show_on_home`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_testimonials_status`   (`status`),
    INDEX `idx_testimonials_home`     (`show_on_home`),

    CONSTRAINT `fk_testimonials_course`
        FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Student and parent testimonials';


-- ============================================================
-- TABLE 10: contact_messages
-- Purpose : Public contact form submissions
-- ============================================================
CREATE TABLE `contact_messages` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100) NOT NULL,
    `email`           VARCHAR(150) NOT NULL,
    `phone`           VARCHAR(20) DEFAULT NULL,
    `subject`         VARCHAR(255) NOT NULL,
    `message`         TEXT NOT NULL,
    `ip_address`      VARCHAR(45) DEFAULT NULL,
    `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
    `read_at`         DATETIME DEFAULT NULL,
    `is_replied`      TINYINT(1) NOT NULL DEFAULT 0,
    `replied_at`      DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_contact_read`      (`is_read`),
    INDEX `idx_contact_created`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Public contact form submissions';


-- ============================================================
-- TABLE 11: scholarships
-- Purpose : Scholarship programs offered
-- ============================================================
CREATE TABLE `scholarships` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(200) NOT NULL,
    `slug`            VARCHAR(220) NOT NULL,
    `description`     TEXT NOT NULL,
    `eligibility`     TEXT DEFAULT NULL,
    `amount`          DECIMAL(10,2) DEFAULT NULL COMMENT 'Monthly or one-time amount',
    `amount_type`     ENUM('monthly','one_time','percentage','full_waiver') NOT NULL DEFAULT 'monthly',
    `seats`           SMALLINT UNSIGNED DEFAULT NULL,
    `deadline`        DATE DEFAULT NULL,
    `cover_image`     VARCHAR(255) DEFAULT NULL,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_scholarship_slug` (`slug`),
    INDEX `idx_scholarship_active`   (`is_active`),
    INDEX `idx_scholarship_deadline` (`deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Scholarship programs';

INSERT INTO `scholarships` (`title`, `slug`, `description`, `eligibility`, `amount`, `amount_type`, `seats`) VALUES
('Merit Scholarship',    'merit-scholarship',    'Awarded to top performers in entry test.',        'Minimum 85% marks in Matric', 500.00,  'monthly',    20),
('Need-Based Aid',       'need-based-aid',       'Financial support for deserving students.',       'Family income below PKR 30,000/month', 1000.00, 'monthly', 15),
('Hafiz-e-Quran Grant',  'hafiz-e-quran-grant',  'Full fee waiver for Hafiz-e-Quran students.',    'Valid Sanad required', NULL, 'full_waiver', 10);


-- ============================================================
-- TABLE 12: hostel_information
-- Purpose : Hostel facilities and room types
-- ============================================================
CREATE TABLE `hostel_information` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `hostel_name`     VARCHAR(150) NOT NULL,
    `gender`          ENUM('male','female','mixed') NOT NULL DEFAULT 'male',
    `address`         TEXT DEFAULT NULL,
    `total_rooms`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `capacity`        SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total student capacity',
    `fee_per_month`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `room_types`      TEXT DEFAULT NULL COMMENT 'JSON: single, double, quad with rates',
    `facilities`      TEXT DEFAULT NULL COMMENT 'JSON array of facility names',
    `warden_name`     VARCHAR(100) DEFAULT NULL,
    `warden_contact`  VARCHAR(20) DEFAULT NULL,
    `cover_image`     VARCHAR(255) DEFAULT NULL,
    `map_embed`       TEXT DEFAULT NULL COMMENT 'Google Maps embed URL',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_hostel_gender`  (`gender`),
    INDEX `idx_hostel_active`  (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Hostel facilities and room information';

INSERT INTO `hostel_information`
    (`hostel_name`, `gender`, `total_rooms`, `capacity`, `fee_per_month`, `facilities`, `warden_name`, `warden_contact`)
VALUES
('Al-Farabi Boys Hostel',   'male',   40, 160, 3500.00,
 '["WiFi","Hot Water","Generator Backup","Cafeteria","CCTV Security","Study Hall"]',
 'Mr. Saleem Khan', '0300-1234567'),
('Rabia Girls Hostel',      'female', 30, 90,  4000.00,
 '["WiFi","Hot Water","Generator Backup","Cafeteria","CCTV Security","Study Hall","Separate Block"]',
 'Mrs. Nadia Bibi',  '0300-7654321');


-- ============================================================
-- TABLE 13: settings
-- Purpose : Key-value site configuration (editable from admin)
-- ============================================================
CREATE TABLE `settings` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`         VARCHAR(100) NOT NULL,
    `value`       TEXT DEFAULT NULL,
    `label`       VARCHAR(150) DEFAULT NULL COMMENT 'Human-readable label for admin UI',
    `group`       VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'Group for admin panel tabs',
    `type`        ENUM('text','textarea','email','url','tel','number','image','toggle')
                      NOT NULL DEFAULT 'text',
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`),
    INDEX `idx_settings_group`   (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Key-value site configuration store';

INSERT INTO `settings` (`key`, `value`, `label`, `group`, `type`) VALUES
-- General
('site_name',           'Global Degree College',            'Site Name',            'general',  'text'),
('site_tagline',        'Shaping Leaders of Tomorrow',      'Site Tagline',         'general',  'text'),
('site_logo',           'assets/images/logo/logo.png',      'Site Logo',            'general',  'image'),
('site_favicon',        'assets/images/logo/favicon.ico',   'Favicon',              'general',  'image'),
('admission_open',      '1',                                'Admission Open',       'general',  'toggle'),
-- Contact
('contact_address',     'University Road, Peshawar, KPK',   'College Address',      'contact',  'textarea'),
('contact_phone',       '+92-91-1234567',                   'Phone Number',         'contact',  'tel'),
('contact_mobile',      '0300-1234567',                     'Mobile Number',        'contact',  'tel'),
('contact_email',       'info@futurevision.edu.pk',         'Email Address',        'contact',  'email'),
('contact_whatsapp',    '923001234567',                     'WhatsApp Number',      'contact',  'tel'),
-- Social Media
('social_facebook',     'https://facebook.com/fvc',         'Facebook URL',         'social',   'url'),
('social_instagram',    'https://instagram.com/fvc',        'Instagram URL',        'social',   'url'),
('social_youtube',      'https://youtube.com/@fvc',         'YouTube URL',          'social',   'url'),
('social_twitter',      '',                                 'Twitter / X URL',      'social',   'url'),
-- SEO
('meta_description',    'Global Degree College – Premier intermediate college in Peshawar offering FSc, ICS, FA and I.Com programs.', 'Meta Description', 'seo', 'textarea'),
('meta_keywords',       'college peshawar, fsc pre medical, ics, icom, intermediate college',  'Meta Keywords', 'seo', 'text'),
('google_analytics',    '',                                 'Google Analytics ID',  'seo',      'text'),
-- Map
('map_embed_url',       '',                                 'Google Map Embed URL', 'contact',  'url');


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- ============================================================
