<?php
// create_user.php
session_start();

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
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validation
    if (empty($username) || empty($password) || empty($role)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } elseif (!in_array($role, ['student', 'teacher', 'admin'])) {
        $message = "<p style='color:red;'>Invalid role selected.</p>";
    } else {
        // Check if username already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "<p style='color:red;'>Username already exists.</p>";
        } else {
            // Insert into users table
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            if ($stmt->execute([$username, $password, $role])) {
                $message = "<p style='color:green; font-weight:bold;'>User '$username' created successfully as $role!</p>";
            } else {
                $message = "<p style='color:red;'>Failed to create user.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Create New User</h2>
    
    <?php if ($message): ?>
        <div style="margin:15px 0; padding:10px; border-radius:6px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required 
                   placeholder="e.g. tomiwa" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   placeholder="Enter secure password" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required 
                    style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn" 
                style="background:var(--green); color:white; padding:12px 24px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
            Create User
        </button>
    </form>

    <div style="margin-top:30px; padding:15px; background:#f9f9f9; border-radius:6px; font-size:0.9rem;">
        <p><strong>Tips:</strong></p>
        <ul style="margin:10px 0; padding-left:20px;">
            <li>Use strong passwords</li>
            <li>Student/Teacher will need additional info later (in their modules)</li>
            <li>Admin users get full access</li>
        </ul>
    </div>
</div>