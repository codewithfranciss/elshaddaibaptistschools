<?php
session_start();

// Allow only admin
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'admin') {
    die("Access denied. Only admin can create users.");
}

// ===== DATABASE CONNECTION =====
$host = "caboose.proxy.rlwy.net";
$port = "29105";
$dbname = "railway";
$user = "postgres";
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";

// ===== INSERT NEW USER =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role     = trim($_POST["role"]);

    if (empty($username) || empty($password) || empty($role)) {
        $message = "All fields are required.";
    } else {
        // check if user exists
        $check = $pdo->prepare("SELECT id FROM users WHERE LOWER(username)=LOWER(?)");
        $check->execute([$username]);

        if ($check->fetch()) {
            $message = "Username already exists!";
        } else {

            $insert = $pdo->prepare("
                INSERT INTO users (username, password, role, created_at)
                VALUES (?, ?, ?, NOW())
                RETURNING id, username, role
            ");

            $insert->execute([$username, $password, $role]);
            $new = $insert->fetch();

            $message = "User {$new['username']} created successfully as {$new['role']} (ID: {$new['id']})";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>

<h2>Create New User</h2>

<p style="color: red; font-weight: bold;">
    <?= $message ?>
</p>

<form method="POST">
    <label>Username</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password</label><br>
    <input type="text" name="password" required><br><br>

    <label>Role</label><br>
    <select name="role" required>
        <option value="">Select role</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Create User</button>
</form>

</body>
</html>