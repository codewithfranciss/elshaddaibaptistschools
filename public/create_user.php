<?php
// create_user.php — DO NOT CALL session_start() AGAIN!
// It's already started in admin.php

// Security: Only admin
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
    die("Access denied.");
}

// Database Connection
$host = "caboose.proxy.rlwy.net";
$port = "29105";
$dbname = "railway";
$user = "postgres";
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? '';

    // Validation
    if (empty($username) || empty($password) || empty($role)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } elseif (!in_array($role, ['student', 'teacher', 'admin'])) {
        $message = "<p style='color:red;'>Invalid role.</p>";
    } else {
        // Check if username exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red;'>Username <b>$username</b> already exists!</p>";
        } else {
            // INSERT USER
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            if ($stmt->execute([$username, $password, $role])) {
                $message = "<p style='color:green; font-weight:bold;'>User <b>$username</b> created as <b>$role</b>!</p>";
                // DEBUG: Show SQL proof
                $check_insert = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $check_insert->execute([$username]);
                $row = $check_insert->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $message .= "<pre style='background:#eee; padding:8px; border-radius:4px;'>" . print_r($row, true) . "</pre>";
                } else {
                    $message .= "<p style='color:red;'>User not found after insert!</p>";
                }
            } else {
                $message = "<p style='color:red;'>Failed to insert into database.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New User</h2>
    
    <?php if ($message): ?>
        <div style="margin:15px 0; padding:12px; border-radius:6px; background:#f0f0f0;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="e.g. john123" 
                   style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password" 
                   style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required 
                    style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn">
            Create User
        </button>
    </form>

    <div style="margin-top:25px; padding:15px; background:#f9f9f9; border-radius:6px; font-size:0.9rem; color:#555;">
        <p><strong>After creating:</strong></p>
        <ul style="margin:8px 0; padding-left:20px;">
            <li>Check <code>users</code> table in Railway</li>
            <li>Login with new credentials</li>
            <li>Student/Teacher needs extra info later</li>
        </ul>
    </div>
</div>

<style>
.btn {
    background: #4CAF50; color: white; padding: 12px 24px; border: none;
    border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px;
}
.btn:hover { background: #388E3C; }
.form-group { margin: 15px 0; }
</style>