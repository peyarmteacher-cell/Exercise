import express from "express";
import { createServer as createViteServer } from "vite";
import path from "path";
import cors from "cors";
import Database from "better-sqlite3";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function startServer() {
  const app = express();
  const PORT = 3000;

  // Database setup
  const db = new Database("exercises.db");
  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      id_card TEXT UNIQUE NOT NULL,
      fullname TEXT NOT NULL,
      rank TEXT NOT NULL,
      password TEXT NOT NULL,
      status TEXT DEFAULT 'pending',
      is_admin INTEGER DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS exercises (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER,
      title TEXT NOT NULL,
      content TEXT NOT NULL,
      teacher_name TEXT NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY(user_id) REFERENCES users(id)
    );
  `);

  // Hardcode first admin if no users exist (for demo purposes)
  const userCount = db.prepare("SELECT count(*) as count FROM users").get();
  // @ts-ignore
  if (userCount.count === 0) {
    db.prepare("INSERT INTO users (id_card, fullname, rank, password, status, is_admin) VALUES (?, ?, ?, ?, ?, ?)")
      .run("admin", "ระบบผู้ดูแล", "ผู้ดูแลระบบ", "admin123", "approved", 1);
  }

  app.use(cors());
  app.use(express.json());

  // --- Auth Routes ---
  app.post("/api/register", (req, res) => {
    const { id_card, fullname, rank, password } = req.body;
    try {
      db.prepare("INSERT INTO users (id_card, fullname, rank, password, status) VALUES (?, ?, ?, ?, ?)")
        .run(id_card, fullname, rank, password, 'pending');
      res.json({ success: true, message: "ลงทะเบียนสำเร็จ กรุณารอผู้ดูแลอนุมัติ" });
    } catch (error) {
      res.status(400).json({ error: "หมายเลขบัตรประชาชนนี้ถูกใช้งานแล้ว" });
    }
  });

  app.post("/api/login", (req, res) => {
    const { id_card, password } = req.body;
    const user = db.prepare("SELECT * FROM users WHERE id_card = ? AND password = ?").get(id_card, password);
    if (!user) {
      return res.status(401).json({ error: "รหัสประจำตัวหรือรหัสผ่านไม่ถูกต้อง" });
    }
    // @ts-ignore
    if (user.status !== 'approved') {
      return res.status(403).json({ error: "บัญชีของคุณยังไม่ได้รับการอนุมัติ หรือถูกระงับ" });
    }
    res.json(user);
  });

  // --- Admin Routes ---
  app.get("/api/admin/pending-users", (req, res) => {
    const users = db.prepare("SELECT id, id_card, fullname, rank, created_at FROM users WHERE status = 'pending'").all();
    res.json(users);
  });

  app.post("/api/admin/approve-user", (req, res) => {
    const { id, status } = req.body; // status: 'approved' or 'rejected'
    db.prepare("UPDATE users SET status = ? WHERE id = ?").run(status, id);
    res.json({ success: true });
  });

  app.get("/api/admin/all-users", (req, res) => {
    const users = db.prepare("SELECT id, id_card, fullname, rank, status, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC").all();
    res.json(users);
  });

  app.delete("/api/admin/users/:id", (req, res) => {
    db.prepare("DELETE FROM users WHERE id = ?").run(req.params.id);
    res.json({ success: true });
  });

  app.post("/api/admin/update-user-status", (req, res) => {
    const { id, status } = req.body;
    db.prepare("UPDATE users SET status = ? WHERE id = ?").run(status, id);
    res.json({ success: true });
  });

  // --- Exercise Routes (Filtered by user) ---
  app.get("/api/exercises", (req, res) => {
    const userId = req.query.userId;
    try {
      let rows;
      if (userId) {
        rows = db.prepare("SELECT * FROM exercises WHERE user_id = ? ORDER BY created_at DESC").all(userId);
      } else {
        rows = db.prepare("SELECT * FROM exercises ORDER BY created_at DESC").all();
      }
      res.json(rows);
    } catch (error) {
      res.status(500).json({ error: "Failed to fetch exercises" });
    }
  });

  app.post("/api/exercises", (req, res) => {
    const { title, content, teacher_name, user_id } = req.body;
    try {
      const stmt = db.prepare("INSERT INTO exercises (user_id, title, content, teacher_name) VALUES (?, ?, ?, ?)");
      const info = stmt.run(user_id, title, JSON.stringify(content), teacher_name);
      res.json({ id: info.lastInsertRowid });
    } catch (error) {
      res.status(500).json({ error: "Failed to save exercise" });
    }
  });

  app.get("/api/exercises/:id", (req, res) => {
    try {
      const row = db.prepare("SELECT * FROM exercises WHERE id = ?").get(req.params.id);
      if (row) {
        // @ts-ignore
        row.content = JSON.parse(row.content);
        res.json(row);
      } else {
        res.status(404).json({ error: "Exercise not found" });
      }
    } catch (error) {
      res.status(500).json({ error: "Failed to fetch exercise" });
    }
  });

  app.delete("/api/exercises/:id", (req, res) => {
    try {
      db.prepare("DELETE FROM exercises WHERE id = ?").run(req.params.id);
      res.json({ success: true });
    } catch (error) {
      res.status(500).json({ error: "Failed to delete exercise" });
    }
  });

  // Vite middleware for development
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
