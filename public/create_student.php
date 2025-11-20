<?php
// create_student.php — NEW SMART SYSTEM (Minimal info → Complete Profile Later)

if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') {
    die("Access denied.");
}

// FIXED DSN — THIS IS WHY IT WORKS NOW
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "<p style='color:red;'>Username and Password required!</p>";
    } else {
        // Check if username exists
        $check = $pdo->prepare("SELECT id FROM users WHERE lower(username) = lower(?)");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red;'>Username <b>$username</b> already taken!</p>";
        } else {
            // Create user with minimal info
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, 'student', NOW()) 
                RETURNING id
            ");
            if ($stmt->execute([$username, $password])) {
                $user_id = $stmt->fetchColumn();

                // Create EMPTY student record (all fields NULL for now)
                $pdo->prepare("INSERT INTO students (user_id, stuid) VALUES (?, ?)")
                    ->execute([$user_id, 'TEMP-' . $user_id]);

                $message = "<p style='color:green; font-weight:bold;'>
                    Student account created!<br>
                    <b>Username:</b> $username<br>
                    <b>Password:</b> $password<br><br>
                    Student can now <b>LOGIN IMMEDIATELY</b> and complete profile!
                </p>";
            } else {
                $message = "<p style='color:red;'>Failed to create account.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New Student (Quick Method)</h2>
    <p style="color:#27ae60; background:#e8f5e9; padding:15px; border-radius:8px;">
        Only username & password needed.<br>
        Student will complete full profile after first login.
    </p>

    <?php if ($message) echo "<div style='margin:15px 0; padding:15px; background:#f0f0f0; border-radius:8px;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label><strong>Username</strong> (for login)</label>
            <input type="text" name="username" required placeholder="e.g. chioma2025" 
                   style="width:100%; padding:14px; border-radius:8px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Password</strong></label>
            <input type="password" name="password" required placeholder="e.g. chioma123"
                   style="width:100%; padding:14px; border-radius:8px; font-size:1rem;">
        </div>

        <button type="submit" class="btn">Create Student Account</button>
    </form>

    <div style="margin-top:30px; padding:20px; background: #fff3d5afe1a; border-radius:10px; color:#1a3d7c;">
        <strong>How it works:</strong><br>
        1. Admin creates account → Student logs in<br>
        2. First login → sees "Complete Profile" form<br>
        3. After filling → normal student dashboard appears forever
    </div>
</div>

<style>
.form-group { margin: 20px 0; }
label { font-weight: 600; color: #333; margin-bottom: 8px; display: block; }
.btn {
    background: #4CAF50; color: white; padding: 16px 40px; border: none;
    border-radius: 10px; font-weight: bold; font-size: 1.2rem; cursor: pointer;
}
.btn:hover { background: #388E3C; }
</style>