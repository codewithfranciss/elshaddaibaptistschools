<?php
session_start();

// ===== DATABASE CONNECTION (FIXED DSN) =====
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ===== LOGIN LOGIC =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header("Location: login.html?error=" . urlencode("Enter username and password"));
        exit;
    }

    // Fetch user
    $stmt = $pdo->prepare("SELECT username, password, role FROM users WHERE lower(username) = lower(?)");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify user + password
    if (!$user || $user['password'] !== $password) {
        header("Location: login.html?error=" . urlencode("Wrong username or password"));
        exit;
    }

    // SUCCESS: Set session
    $_SESSION['username'] = $user['username'];
    $_SESSION['status']   = strtolower($user['role']);

    // ===== PROFILE COMPLETION CHECK (THIS IS THE MAGIC) =====
    $role = $_SESSION['status'];

    if ($role === 'student' || $role === 'teacher') {
        $table = $role === 'student' ? 'students' : 'teachers';
        $check = $pdo->prepare("
            SELECT fname FROM $table s 
            JOIN users u ON s.user_id = u.id 
            WHERE u.username = ? 
            LIMIT 1
        ");
        $check->execute([$user['username']]);
        $fname = $check->fetchColumn();

        // If fname is empty → profile incomplete
        if (empty($fname)) {
            header("Location: complete_profile.php");
            exit;
        }
    }

    // ===== FINAL REDIRECT BASED ON ROLE =====
    $redirect = [
        'student' => 'student.php',
        'teacher' => 'teacher.php',
        'bursary' => 'bursary.php',
        'vp'      => 'vp.php',
        'admin'   => 'admin.php'
    ];

    $page = $redirect[$_SESSION['status']] ?? 'dashboard.php';
    header("Location: $page");
    exit;
}
?>