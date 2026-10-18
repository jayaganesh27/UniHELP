-- ============================================================
--  UniHELP Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS unihelp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE unihelp;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(20)  NOT NULL UNIQUE,
    full_name     VARCHAR(120) NOT NULL,
    email         VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  DEFAULT NULL,
    photo         VARCHAR(255) DEFAULT 'assets/default-avatar.png',
    is_volunteer  TINYINT(1)   NOT NULL DEFAULT 0,
    role          ENUM('student','volunteer') NOT NULL DEFAULT 'student',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Two-Factor Codes ──────────────────────────────────────────
CREATE TABLE two_factor_codes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    code       CHAR(6)      NOT NULL,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Volunteer Skills ──────────────────────────────────────────
CREATE TABLE volunteer_skills (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    skill_type ENUM('academic','technology','new_student','general') NOT NULL,
    UNIQUE KEY uq_user_skill (user_id, skill_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Support Requests ──────────────────────────────────────────
CREATE TABLE support_requests (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id         INT UNSIGNED NOT NULL,
    category           ENUM('academic','technology','new_student','general') NOT NULL,
    description        TEXT         NOT NULL,
    preferred_datetime DATETIME     DEFAULT NULL,
    status             ENUM('open','accepted') NOT NULL DEFAULT 'open',
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Sessions ──────────────────────────────────────────────────
CREATE TABLE sessions (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id         INT UNSIGNED NOT NULL UNIQUE,
    volunteer_id       INT UNSIGNED NOT NULL,
    student_id         INT UNSIGNED NOT NULL,
    scheduled_datetime DATETIME     NOT NULL,
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id)   REFERENCES support_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)   REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Seed Demo Data ────────────────────────────────────────────
INSERT INTO users (student_id, full_name, email, password_hash, phone, is_volunteer, role) VALUES
('S20241001', 'Jayaganesh A/L Palanisamy',  'jaya@university.edu',    '$2y$12$lETRpCnQvDj0Oab6R2n/TOGp14lmkdjmhSGTpbP6aGOQPWPBBE1cS', '+60 12-345 6789', 0, 'student'),
('S20241002', 'Mishalini S. Selvasegaran',       'misha@university.edu',    '$2y$12$lETRpCnQvDj0Oab6R2n/TOGp14lmkdjmhSGTpbP6aGOQPWPBBE1cS', '+60 11-234 5678', 1, 'volunteer'),
('S20241003', 'Charmaine Jayarina Suresh Jayaseelan',      'charm@university.edu',   '$2y$12$lETRpCnQvDj0Oab6R2n/TOGp14lmkdjmhSGTpbP6aGOQPWPBBE1cS', '+60 16-789 0123', 1, 'volunteer'),
('S20241004', 'Marcus Wong',     'marcus.wong@university.edu',  '$2y$12$lETRpCnQvDj0Oab6R2n/TOGp14lmkdjmhSGTpbP6aGOQPWPBBE1cS', NULL,              1, 'volunteer'),
('S20241005', 'Tan Wei Ming',    'wei.ming@university.edu',     '$2y$12$lETRpCnQvDj0Oab6R2n/TOGp14lmkdjmhSGTpbP6aGOQPWPBBE1cS', '+60 13-456 7890', 0, 'student');
-- Local demo password for all seeded users: "UniHelp123!"

INSERT INTO volunteer_skills (user_id, skill_type) VALUES
(2,'academic'),(2,'general'),
(3,'technology'),(3,'academic'),
(4,'new_student'),(4,'general');

INSERT INTO support_requests (student_id, category, description, preferred_datetime, status) VALUES
(5, 'academic',    'Need help with Calculus II — integration by parts is confusing me.', '2026-10-03 14:00:00', 'open'),
(5, 'technology',  'My laptop won\'t boot after a Windows update. Need urgent help!',    '2026-10-04 10:00:00', 'open'),
(1, 'new_student', 'Just arrived on campus, need guidance on registration.',             '2026-10-05 15:00:00', 'open');
