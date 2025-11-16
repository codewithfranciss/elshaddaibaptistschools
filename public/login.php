<?php
session_start();

// ===== DATABASE CONNECTION =====
$host = "caboose.proxy.rlwy.net";       // Railway public host
$port = "29105";                         // Railway port
$dbname = "railway";                     // Railway database name
$user = "postgres";                      // Railway username
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu"; // Railway password

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // echo "Connected successfully ✅"; // optional debug
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ===== LOGIN LOGIC =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user
    $stmt = $pdo->prepare("SELECT username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check credentials
    if (!$user || $user['password'] !== $password) {
        header("Location: login.html?error=" . urlencode("Wrong username or password"));
        exit;
    }

    // Save session
    $_SESSION['username'] = $user['username'];
    $_SESSION['status'] = strtolower($user['role']);

    // Redirect based on role
    $map = [
        'student' => 'student.php',
        'teacher' => 'teacher.php',
        'bursary' => 'bursary.php',
        'vp'      => 'vp.php',
        'admin'   => 'admin.php'
    ];

    header("Location: " . ($map[$_SESSION['status']] ?? 'dashboard.php'));
    exit;
}
?>