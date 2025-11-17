<?php
// create_teacher.php
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') die("Access denied.");

$host = "caboose.proxy.rlwy.net"; $port = "29105"; $dbname = "railway";
$user = "postgres"; $password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);
    $fname      = trim($_POST['fname']);
    $lname      = trim($_POST['lname']);
    $teacherid  = strtoupper(trim($_POST['teacherid']));
    $phone      = trim($_POST['phone']);

    if (empty($username) || empty($password) || empty($fname) || empty($teacherid) || empty($phone)) {
        $message = "<p style='color:red;'>All fields are required!</p>";
    } else {
        // Create user account
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher') RETURNING id");
        if ($stmt->execute([$username, $password])) {
            $user_id = $stmt->fetchColumn();

            // Create teacher profile
            $stmt2 = $pdo->prepare("INSERT INTO teachers (user_id, teacherid, fname, lname, phone) VALUES (?, ?, ?, ?, ?)");
            if ($stmt2->execute([$user_id, $teacherid, $fname, $lname, $phone])) {
                $message = "<p style='color:green; font-weight:bold;'>Teacher <strong>$fname $lname</strong> created successfully!</p>";
            } else {
                $message = "<p style='color:red;'>Failed to create teacher profile.</p>";
            }
        } else {
            $message = "<p style='color:red;'>Username already exists!</p>";
        }
    }
}
?>

<div class="section">
    <h2>Create New Teacher</h2>
    <?php if ($message) echo "<div style='padding:15px; background:#f0f0f0; border-radius:8px; margin:15px 0; font-size:1rem;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username (for login)</label>
            <input type="text" name="username" required placeholder="e.g. tomiwa">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Strong password">
        </div>
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="fname" required placeholder="Tomiwa">
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lname" required placeholder="Adebayo">
        </div>
        <div class="form-group">
            <label>Teacher ID</label>
            <input type="text" name="teacherid" required placeholder="e.g. TCH001">
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" required placeholder="08012345678">
        </div>

        <button type="submit" class="btn">Create Teacher</button>
    </form>
</div>

<style>
.form-group { margin: 15px 0; }
.form-group label { display:block; font-weight:600; margin-bottom:6px; color:#333; }
input { width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem; }
.btn { background:#4CAF50; color:white; padding:14px 30px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; font-size:1.1rem; }
.btn:hover { background:#388E3C; }
</style>