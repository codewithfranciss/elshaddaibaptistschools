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

// Inside teacher-action.php

if ($_POST['action'] === 'upload_text_assignment') {
    session_start();
    if ($_SESSION['status'] !== 'teacher') die("Access denied.");

    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $class_id = (int)$_POST['class_id'];

    // DB connection (same as before)
    $host = "caboose.proxy.rlwy.net"; $port = "29105"; $dbname = "railway";
    $user = "postgres"; $password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=--search_path=public";

    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("INSERT INTO assignments (class_id, title, content, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$class_id, $title, $content, $_SESSION['username']])) {
        echo "Assignment uploaded successfully! Students can now view it.";
    } else {
        echo "Failed to upload assignment.";
    }
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