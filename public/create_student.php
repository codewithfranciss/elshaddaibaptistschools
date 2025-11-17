<?php
// create_student.php
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
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $fname    = trim($_POST['fname']);
    $lname    = trim($_POST['lname']);
    $stuid    = strtoupper(trim($_POST['stuid']));
    $gender   = $_POST['gender'];
    $dob      = $_POST['dob'];

    if (empty($username) || empty($password) || empty($fname) || empty($stuid)) {
        $message = "<p style='color:red;'>Required fields missing!</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student') RETURNING id");
        if ($stmt->execute([$username, $password])) {
            $user_id = $stmt->fetchColumn();

            $stmt2 = $pdo->prepare("INSERT INTO students (user_id, stuid, fname, lname, gender, dob) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt2->execute([$user_id, $stuid, $fname, $lname, $gender, $dob])) {
                $message = "<p style='color:green; font-weight:bold;'>Student <strong>$fname $lname ($stuid)</strong> created!</p>";
            }
        } else {
            $message = "<p style='color:red;'>Username already taken!</p>";
        }
    }
}
?>

<div class="section">
    <h2>Create New Student</h2>
    <?php if ($message) echo "<div style='padding:15px; background:#f0f0f0; border-radius:8px; margin:15px 0;'>$message</div>"; ?>

    <form method="POST">
        <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <div class="form-group"><label>First Name</label><input type="text" name="fname" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="lname" required></div>
        <div class="form-group"><label>Student ID</label><input type="text" name="stuid" required placeholder="STU001"></div>
        <div class="form-group"><label>Gender</label>
            <select name="gender" required style="width:100%; padding:12px; border-radius:6px;">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" required></div>

        <button type="submit" class="btn">Create Student</button>
    </form>
</div>

<style>
.form-group { margin: 15px 0; }
.form-group label { display:block; font-weight:600; margin-bottom:6px; color:#333; }
input, select { width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem; }
.btn { background:#4CAF50; color:white; padding:14px 30px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; margin-top:10px; }
.btn:hover { background:#388E3C; }
</style>