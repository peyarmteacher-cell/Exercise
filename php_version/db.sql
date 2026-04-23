CREATE DATABASE IF NOT EXISTS school_exercises;
USE school_exercises;

-- 1. ตารางสำหรับเก็บข้อมูลสมาชิก (คุณครู และ Super Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_card VARCHAR(20) UNIQUE NOT NULL, -- ใช้เลขบัตรประชาชนเป็น Username
    fullname VARCHAR(255) NOT NULL,
    rank VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- สถานะการอนุมัติ
    is_admin TINYINT(1) DEFAULT 0, -- 1 คือ Super Admin, 0 คือ ครูทั่วไป
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. ตารางสำหรับเก็บแบบฝึกหัด
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- เชื่อมโยงกับเจ้าของใบงาน
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL, -- เก็บเป็น JSON ของคำถามทั้งหมด
    teacher_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- ถ้าลบ User ให้ลบใบงานทั้งหมดด้วย
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 3. บัญชี Super Admin เริ่มต้น
-- หมายเหตุ: เมื่อติดตั้งบน Server จริง ควรเปลี่ยนรหัสผ่านทันที
INSERT INTO users (id_card, fullname, rank, password, status, is_admin) 
VALUES ('admin', 'Super Admin ผู้ดูแลระบบ', 'ผู้ดูแลระบบสูงสุด', 'admin123', 'approved', 1)
ON DUPLICATE KEY UPDATE id_card=id_card;
