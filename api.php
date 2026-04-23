<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json; charset=UTF-8");

// --- Database Configuration ---
$host = "localhost";
$db_name = "schoolos_Exercise";
$username = "schoolos_Exercise";
$password = "!deQn5cB?B7gabu8";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';

// --- Auth Endpoints ---

// Generate AI (Proxy to Gemini)
if ($method === 'POST' && $path === 'generate') {
    $data = json_decode(file_get_contents("php://input"));
    $topic = htmlspecialchars($data->topic);
    $level = htmlspecialchars($data->level);
    $subject = htmlspecialchars($data->subject);
    
    // API KEY - คุณต้องใส่ API Key ของ Google AI Studio ที่นี่
    $api_key = "!deQn5cB?B7gabu8"; // หมายเหตุ: เปลี่ยนเป็น Gemini API Key ของคุณ
    
    $systemInstruction = "คุณคือผู้เชี่ยวชาญด้านการศึกษาสำหรับนักเรียนระดับประถมศึกษา หน้าที่ของคุณคือสร้างแบบฝึกหัดที่เหมาะสมกับระดับชั้นและวิชาที่กำหนด แบบฝึกหัดต้องมีความหลากหลายประกอบด้วย: 1. ปรนัย (multiple_choice) 2. อัตนัย (subjective) 3. จับคู่ (matching) 4. เติมคำ (fill_in_the_blanks) 5. คำนวณคณิตศาสตร์แบบแสดงวิธีทำ (math_show_work) 6. วิเคราะห์แสดงเหตุผล (analysis_reasoning) ให้ตอบกลับในรูปแบบ JSON เท่านั้น";
    
    $fullPrompt = "สร้างแบบฝึกหัดเรื่อง: $topic สำหรับระดับชั้น: $level วิชา: $subject สร้างโจทย์อย่างน้อย 10 ข้อ โดยกระจายประเภทโจทย์ตามที่กำหนด";
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;
    
    $payload = [
        "contents" => [[
            "parts" => [["text" => $fullPrompt]]
        ]],
        "systemInstruction" => [
            "parts" => [["text" => $systemInstruction]]
        ],
        "generationConfig" => [
            "response_mime_type" => "application/json"
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $resData = json_decode($response);
        echo $resData->candidates[0]->content->parts[0]->text;
    } else {
        http_response_code($httpCode);
        echo json_encode(["error" => "AI Generation failed", "details" => json_decode($response)]);
    }
    exit;
}

// Register
if ($method === 'POST' && $path === 'register') {
    $data = json_decode(file_get_contents("php://input"));
    try {
        $stmt = $conn->prepare("INSERT INTO users (id_card, fullname, rank, password, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$data->id_card, $data->fullname, $data->rank, $data->password]);
        echo json_encode(["success" => true, "message" => "ลงทะเบียนสำเร็จ กรุณารอผู้ดูแลอนุมัติ"]);
    } catch(PDOException $e) {
        http_response_code(400);
        echo json_encode(["error" => "หมายเลขบัตรประชาชนนี้ถูกใช้งานแล้ว"]);
    }
    exit;
}

// Login
if ($method === 'POST' && $path === 'login') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_card = ? AND password = ?");
    $stmt->execute([$data->id_card, $data->password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "รหัสประจำตัวหรือรหัสผ่านไม่ถูกต้อง"]);
        exit;
    }
    
    if ($user['status'] !== 'approved') {
        http_response_code(403);
        echo json_encode(["error" => "บัญชีของคุณยังไม่ได้รับการอนุมัติ"]);
        exit;
    }
    
    // Safety: don't send password
    unset($user['password']);
    echo json_encode($user);
    exit;
}

// --- Admin Endpoints ---

if ($method === 'GET' && $path === 'admin/pending-users') {
    $stmt = $conn->prepare("SELECT id, id_card, fullname, rank, created_at FROM users WHERE status = 'pending'");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST' && $path === 'admin/approve-user') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$data->status, $data->id]);
    echo json_encode(["success" => true]);
    exit;
}

// User Management (Super Admin)
if ($method === 'GET' && $path === 'admin/users') {
    $stmt = $conn->prepare("SELECT id, id_card, fullname, rank, status, is_admin, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'DELETE' && preg_match('/admin\/users\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
    exit;
}

if ($method === 'POST' && $path === 'admin/update-user-status') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$data->status, $data->id]);
    echo json_encode(["success" => true]);
    exit;
}

// --- Exercise Endpoints ---

if ($method === 'GET' && $path === 'exercises') {
    $userId = isset($_GET['userId']) ? $_GET['userId'] : null;
    if ($userId) {
        $stmt = $conn->prepare("SELECT * FROM exercises WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM exercises ORDER BY created_at DESC");
        $stmt->execute();
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as &$row) { $row['content'] = json_decode($row['content']); }
    echo json_encode($result);
    exit;
}

if ($method === 'POST' && $path === 'exercises') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO exercises (user_id, title, content, teacher_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data->user_id, $data->title, json_encode($data->content), $data->teacher_name]);
    echo json_encode(["id" => $conn->lastInsertId(), "success" => true]);
    exit;
}

if ($method === 'DELETE' && preg_match('/exercises\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
    $stmt = $conn->prepare("DELETE FROM exercises WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
?>
