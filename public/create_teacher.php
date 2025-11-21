<?php
// create_teacher.php — QUICK METHOD (Just like students)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') {
    die("Access denied.");
}

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
        $message = "<p style='color:red; font-weight:bold;'>Username and Password required!</p>";
    } else {
        // Check duplicate
        $check = $pdo->prepare("SELECT id FROM users WHERE lower(username) = lower(?)");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red; font-weight:bold;'>Username <b>" . htmlspecialchars($username) . "</b> already taken!</p>";
        } else {
            // Create user as teacher
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, 'teacher', NOW()) 
                RETURNING id
            ");
            if ($stmt->execute([$username, $password])) {
                $user_id = $stmt->fetchColumn();

                // Create EMPTY teacher record
                $pdo->prepare("INSERT INTO teachers (user_id, teacherid) VALUES (?, ?) 
                               ON CONFLICT (user_id) DO NOTHING")
                    ->execute([$user_id, 'TEMP-' . $user_id]);

                $message = "<div style='background:#e8f5e9; padding:20px; border-radius:10px; border-left:5px solid #4CAF50;'>
                    <h3 style='color:green; margin:0;'>Teacher Account Created!</h3>
                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "<br>
                       <strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                    <p style='color:#27ae60; font-weight:bold;'>
                        Teacher can now login and complete their profile!
                    </p>
                </div>";
            } else {
                $message = "<p style='color:red;'>Failed to create account.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New Teacher (Quick Registration)</h2>
    
    <div style="background:#fff3e0; padding:18px; border-radius:10px; margin-bottom:25px; border-left:5px solid #ff9800;">
        <strong>Only username + password needed!</strong><br>
        Teacher will fill name, ID, phone after first login.
    </div>

    <?php if ($message) echo "<div style='margin:20px 0;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label><strong>Username</strong> (for login)</label>
            <input type="text" name="username" required placeholder="e.g. mradewale" 
                   style="width:100%; padding:14px; border-radius:8px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Password</strong></label>
            <input type="password" name="password" required placeholder="e.g. teacher123"
                   style="width:100%; padding:14px; border-radius:8px; font-size:1rem;">
        </div>

        <button type="submit" class="btn">Create Teacher Account</button>
    </form>

    <div style="margin-top:30px; padding:18px; background:#f5f5f5; border-radius:10px; font-size:0.95rem;">
        Teacher logs in → sees "Complete Profile" → fills details → done!
    </div>
</div>

<style>
.form-group { margin: 22px 0; }
label { font-weight: 600; color: #222; margin-bottom: 8px; display: block; }
.btn {
    background: #FF9800; color: white; padding: 16px 50px; border: none;
    border-radius: 10px; font-weight: bold; font-size: 1.3rem; cursor: pointer;
}
.btn:hover { background: #e68900; }
</style>