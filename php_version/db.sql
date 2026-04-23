CREATE DATABASE IF NOT EXISTS school_exercises;
USE school_exercises;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_card VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    rank VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    teacher_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- แทรกบัญชี Admin เริ่มต้น (รหัสผ่านคือ admin123)
-- หมายเหตุ: ในระบบจริงควรใช้ password_hash() ใน PHP เมื่อสมัครสมาชิก
INSERT INTO users (id_card, fullname, rank, password, status, is_admin) 
VALUES ('admin', 'ระบบผู้ดูแล', 'ผู้ดูแลระบบ', 'admin123', 'approved', 1);
