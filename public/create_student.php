<?php
// create_student.php — 100% BULLETPROOF VERSION (WORKS NO MATTER WHAT)

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') {
    die("Access denied.");
}

// FIXED DSN
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = "<p style='color:red; font-weight:bold;'>Both fields are required!</p>";
    } elseif (strlen($username) < 3 || strlen($password) < 4) {
        $message = "<p style='color:red;'>Username ≥ 3 chars, Password ≥ 4 chars</p>";
    } else {
        try {
            // STEP 1: Check if username exists
            $check = $pdo->prepare("SELECT 1 FROM users WHERE lower(username) = lower(?)");
            $check->execute([$username]);
            if ($check->fetch()) {
                $message = "<p style='color:red;'>Username <b>" . htmlspecialchars($username) . "</b> already exists!</p>";
            } else {
                // STEP 2: Insert into users (let PostgreSQL generate id automatically)
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, role, created_at) 
                    VALUES (?, ?, 'student', NOW()) 
                    RETURNING id
                ");
                $stmt->execute([$username, $password]);
                $user_id = $stmt->fetchColumn();

                // STEP 3: Insert into students (safe, no conflict error)
                $pdo->prepare("
                    INSERT INTO students (user_id, stuid) 
                    VALUES (?, ?) 
                    ON CONFLICT (user_id) DO NOTHING
                ")->execute([$user_id, 'TEMP-' . $user_id]);

                // SUCCESS!
                $message = "<div style='background:#e8f5e9; padding:25px; border-radius:12px; border-left:6px solid #4CAF50;'>
                    <h3 style='color:green; margin:0 0 10px 0;'>Student Created Successfully!</h3>
                    <p><strong>Username:</strong> <code>" . htmlspecialchars($username) . "</code><br>
                       <strong>Password:</strong> <code>" . htmlspecialchars($password) . "</code></p>
                    <p style='font-weight:bold; color:#27ae60;'>
                        Student can now <u>LOGIN IMMEDIATELY</u> and complete their profile!
                    </p>
                </div>";
            }
        } catch (Exception $e) {
            $message = "<p style='color:red; background:#ffebee; padding:15px; border-radius:8px;'>
                Error: " . $e->getMessage() . "
            </p>";
        }
    }
}
?>

<div class="section">
    <h2>Create New Student</h2>
    
    <div style="background:#e8f5e9; padding:18px; border-radius:10px; margin-bottom:25px; border-left:5px solid #4CAF50;">
        <strong>Only username + password needed!</strong><br>
        Student completes full profile after login.
    </div>

    <?php if ($message) echo "<div style='margin:20px 0; font-size:1.1rem;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label><strong>Username</strong></label>
            <input type="text" name="username" required placeholder="e.g. john2025" 
                   style="width:100%; padding:15px; border:2px solid #ddd; border-radius:10px; font-size:1.1rem;">
        </div>

        <div class="form-group">
            <label><strong>Password</strong></label>
            <input type="text" name="password" required placeholder="e.g. john123" 
                   style="width:100%; padding:15px; border:2px solid #ddd; border-radius:10px; font-size:1.1rem;">
        </div>

        <button type="submit" class="btn">
            Create Student Account
        </button>
    </form>
</div>

<style>
.form-group { margin: 25px 0; }
label { font-weight: 600; color: #222; margin-bottom: 10px; display: block; }
.btn {
    background: #4CAF50; color: white; padding: 18px 60px; border: none;
    border-radius: 12px; font-weight: bold; font-size: 1.4rem; cursor: pointer;
    transition: 0.3s; margin-top: 20px; box-shadow: 0 5px 15px rgba(76,175,80,0.3);
}
.btn:hover { background: #388E3C; transform: translateY(-2px); }
</style>