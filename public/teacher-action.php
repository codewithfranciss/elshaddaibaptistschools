<?php
session_start();
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    http_response_code(403);
    exit("Access denied.");
}

$action = $_POST['action'] ?? '';
$teacherid = $_SESSION['teacherid'] ?? '';
$classid   = $_SESSION['classid']   ?? '';

// ===== DATABASE CONNECTION =====
$host = "caboose.proxy.rlwy.net";       // Railway public host
$port = "29105";                         // Railway port
$dbname = "railway";                     // Railway database name
$user = "postgres";                      // Railway username
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu"; // Railway password

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

try {
    $pdo = $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// ------------------- UPLOAD ASSIGNMENT -------------------
if ($action === 'upload_assignment') {
    $title = $_POST['title'] ?? '';
    $desc  = $_POST['desc'] ?? '';
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
        echo "File upload failed.";
        exit;
    }

    $file = $_FILES['file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['pdf','doc','docx','txt'];
    if (!in_array(strtolower($ext), $allowed)) {
        echo "Invalid file type.";
        exit;
    }

    $uploadDir = 'uploads/assignments/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = $teacherid . '_' . time() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

    // Optional: save to DB
    $stmt = $pdo->prepare("INSERT INTO assignments (title, description, filepath, classsoon, teacherid, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $desc, $uploadDir.$filename, $classid, $teacherid]);

    echo "Assignment uploaded successfully.";
    exit;
}

// ------------------- BULK ATTENDANCE -------------------
if ($action === 'bulk_attendance') {
    $date = $_POST['date'] ?? '';
    $status = $_POST['status'] ?? [];

    $stmt = $pdo->prepare("INSERT INTO attendancerecord (arid, stuid, date, status, teacherid) VALUES (?, ?, ?, ?, ?)");
    foreach ($status as $stuid => $stat) {
        $arid = $stuid . '_' . strtotime($date);
        $stmt->execute([$arid, $stuid, $date, $stat, $teacherid]);
    }
    echo "Attendance saved for " . count($status) . " students.";
    exit;
}

// ------------------- UPLOAD RESULT (CSV) -------------------
if ($action === 'upload_result') {
    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== 0) {
        echo "CSV upload failed.";
        exit;
    }

    $term = $_POST['term'];
    $session = $_POST['session'];
    $file = $_FILES['csv']['tmp_name'];

    if (($handle = fopen($file, "r")) === false) {
        echo "Cannot read CSV.";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO academicrecord (acadid, stuid, subject, score, term, session, teacherid) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 3) continue;
        [$stuid, $subject, $score] = $row;
        $acadid = $stuid . '_' . $subject . '_' . time() . $count;
        $stmt->execute([$acadid, $stuid, $subject, $score, $term, $session, $teacherid]);
        $count++;
    }
    fclose($handle);
    echo "$count results uploaded.";
    exit;
}

// fallback
echo "Invalid action.";
?>