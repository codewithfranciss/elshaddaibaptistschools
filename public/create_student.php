<?php
// create_student.php — FINAL 100% WORKING (Minimal + Complete Profile Flow)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') {
    die("Access denied.");
}

// CORRECT DSN — THIS IS CRITICAL!
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
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = "<p style='color:red; font-weight:bold;'>Username and Password are required!</p>";
    } else {
        // Check if username already exists (case-insensitive)
        $check = $pdo->prepare("SELECT id FROM users WHERE lower(username) = lower(?)");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red; font-weight:bold;'>Username <b>" . htmlspecialchars($username) . "</b> is already taken!</p>";
        } else {
            // STEP 1: Create user account
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, 'student', NOW()) 
                RETURNING id
            ");

            if ($stmt->execute([$username, $password])) {
                $user_id = $stmt->fetchColumn();

                // STEP 2: Create EMPTY student profile (so complete_profile.php can find it)
                $insert_student = $pdo->prepare("
                    INSERT INTO students (user_id, stuid) 
                    VALUES (?, ?) 
                    ON CONFLICT (user_id) DO NOTHING
                ");
                $insert_student->execute([$user_id, 'TEMP-' . $user_id]);

                $message = "<div style='background:#e8f5e9; padding:20px; border-radius:10px; border-left:5px solid #4CAF50;'>
                    <h3 style='color:green; margin-top:0;'>Student Created Successfully!</h3>
                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "<br>
                       <strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                    <p style='font-weight:bold; color:#27ae60;'>
                        Student can now <u>LOGIN IMMEDIATELY</u> and complete their profile!
                    </p>
                </div>";
            } else {
                $message = "<p style='color:red; font-weight:bold;'>Failed to create account. Try again.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New Student (Quick Registration)</h2>
    
    <div style="background:#e3f2fd; padding:18px; border-radius:10px; margin-bottom:25px; border-left:5px solid #2196F3;">
        <strong>Only username + password needed!</strong><br>
        Student will fill full details (name, ID, DOB, etc.) after first login.
    </div>

    <?php if ($message) echo "<div style='margin:20px 0; font-size:1rem;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label><strong>Username</strong> (for login)</label>
            <input type="text" name="username" required placeholder="e.g. chioma2025" 
                   style="width:100%; padding:14px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Password</strong></label>
            <input type="password" name="password" required placeholder="e.g. chioma123"
                   style="width:100%; padding:14px; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
        </div>

        <button type="submit" class="btn">
            Create Student Account
        </button>
    </form>

    <div style="margin-top:35px; padding:20px; background:#f9f9f9; border-radius:10px; font-size:0.95rem; line-height:1.6;">
        <strong>How it works:</strong><br>
        1. Admin creates account → Student logs in<br>
        2. First login → sees "Complete Your Profile" form<br>
        3. After filling → normal student dashboard appears forever<br><br>
        <small style="color:#27ae60;">This is how modern school portals work!</small>
    </div>
</div>

<style>
.form-group { margin: 22px 0; }
label { font-weight: 600; color: #222; margin-bottom: 8px; display: block; }
.btn {
    background: #4CAF50; color: white; padding: 16px 50px; border: none;
    border-radius: 10px; font-weight: bold; font-size: 1.3rem; cursor: pointer;
    transition: 0.3s; margin-top: 10px;
}
.btn:hover { background: #388E3C; }
</style>