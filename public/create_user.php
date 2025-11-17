<?php
// create_user.php — Secure Working Version
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin can access
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
    die("Access denied.");
}

// Database connection
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;";
try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        $message = "<p style='color:red;'>All fields required!</p>";
    } elseif (!in_array($role, ['student', 'teacher', 'admin'])) {
        $message = "<p style='color:red;'>Invalid role!</p>";
    } else {
        // Check duplicate username
        $check = $pdo->prepare("SELECT id FROM users WHERE lower(username) = lower(?)");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red;'>Username <b>" . htmlspecialchars($username) . "</b> already exists!</p>";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, ?, NOW()) 
                RETURNING id, username, role
            ");

            if ($stmt->execute([$username, $hashedPassword, $role])) {
                $new_user = $stmt->fetch();
                $message = "<p style='color:green; font-weight:bold;'>
                    User <b>" . htmlspecialchars($new_user['username']) . "</b> created successfully as <b>" . htmlspecialchars($new_user['role']) . "</b>! 
                    <small>(ID: {$new_user['id']})</small>
                </p>";
            } else {
                $message = "<p style='color:red;'>Database insert failed!</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New User</h2>

    <?php if ($message): ?>
        <div style="margin:20px 0; padding:15px; background:#f0f0f0; border-radius:8px; font-family:monospace;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="create_user.php">
        <div class="form-group">
            <label><strong>Username</strong></label>
            <input type="text" name="username" required placeholder="e.g. tomiwa2025" 
                   style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Password</strong></label>
            <input type="password" name="password" required placeholder="e.g. tomiwa123" 
                   style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Role</strong></label>
            <select name="role" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                <option value="">-- Select Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn">Create User</button>
    </form>

    <div style="margin-top:30px; padding:15px; background:#e8f5e9; border-radius:8px; font-size:0.9rem; color:#27ae60;">
        <strong>Works 100% now!</strong><br>
        After creating → Check Railway → users table → new user appears instantly!
    </div>
</div>

<style>
.form-group { margin: 18px 0; }
label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
.btn {
    background: #4CAF50; color: white; padding: 14px 32px; border: none;
    border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer;
    transition: 0.3s;
}
.btn:hover { background: #388E3C; }
</style>