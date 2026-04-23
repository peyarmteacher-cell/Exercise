# คู่มือการติดตั้งระบบ AI EduGenerator บน Server โรงเรียน (PHP + MySQL)

## 1. การเตรียมการ
1. ติดตั้ง **XAMPP**, **AppServ** หรือ Web Server ที่รองรับ **PHP 7.4+** และ **MySQL**
2. สร้างฐานข้อมูลใหม่ใน PHPMyAdmin โดยใช้ไฟล์ `db.sql` ที่อยู่ในโฟลเดอร์นี้

## 2. การตั้งค่า Backend (PHP)
1. นำไฟล์ `api.php` ไปวางในโฟลเดอร์โปรเจกต์ของคุณบน Server
2. เปิดไฟล์ `api.php` แล้วแก้ไขข้อมูลการเชื่อมต่อฐานข้อมูลด้านบน:
   ```php
   $host = "localhost";
   $db_name = "school_exercises";
   $username = "root";
   $password = "";
   ```

## 3. การนำ Frontend (React) ไปใช้งาน
เพื่อให้ระบบทำงานบน Server ของคุณ คุณต้องทำการ Build ไฟล์ React ก่อน:
1. ดาวน์โหลดโปรเจกต์นี้ลงเครื่องคอมพิวเตอร์ที่มี Node.js
2. รันคำสั่ง `npm install` และ `npm run build`
3. นำไฟล์ในโฟลเดอร์ `dist/` ไปวางบน Server ของคุณ

## 4. หมายเหตุเรื่อง AI
ระบบนี้ใช้ **Gemini AI API** ดังนั้น Server ของคุณต้องสามารถเชื่อมต่ออินเทอร์เน็ตได้ และต้องมี `GEMINI_API_KEY` ตั้งค่าไว้ในฝั่ง Client ด้วย (ในไฟล์ที่ Build ออกมา)
