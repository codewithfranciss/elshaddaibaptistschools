<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['status'] ?? '';

// DB Connection
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";
try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// GET USER ID SAFELY
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) die("User not found.");
$user_id = $user['id'];

// CHECK IF PROFILE IS COMPLETE
$is_complete = false;
if ($role === 'student') {
    $check = $pdo->prepare("SELECT fname FROM students WHERE user_id = ? AND fname IS NOT NULL AND fname != ''");
    $check->execute([$user_id]);
    $is_complete = $check->fetchColumn() ? true : false;
} elseif ($role === 'teacher') {
    $check = $pdo->prepare("SELECT fname FROM teachers WHERE user_id = ? AND fname IS NOT NULL AND fname != ''");
    $check->execute([$user_id]);
    $is_complete = $check->fetchColumn() ? true : false;
}

if ($is_complete) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : $role . '.php'));
    exit;
}

// HANDLE FORM SUBMISSION — NO ON CONFLICT! SAFE METHOD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);

    try {
        if ($role === 'student') {
            $stuid  = strtoupper(trim($_POST['stuid']));
            $gender = $_POST['gender'];
            $dob    = $_POST['dob'];

            // First: Try UPDATE
            $update = $pdo->prepare("
                UPDATE students 
                SET stuid = ?, fname = ?, lname = ?, gender = ?, dob = ? 
                WHERE user_id = ?
            ");
            $updated = $update->execute([$stuid, $fname, $lname, $gender, $dob, $user_id]);

            // If no rows updated → INSERT
            if ($update->rowCount() === 0) {
                $insert = $pdo->prepare("
                    INSERT INTO students (user_id, stuid, fname, lname, gender, dob)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([$user_id, $stuid, $fname, $lname, $gender, $dob]);
            }

        } elseif ($role === 'teacher') {
            $teacherid = strtoupper(trim($_POST['teacherid']));
            $phone = trim($_POST['phone']);

            $update = $pdo->prepare("
                UPDATE teachers 
                SET teacherid = ?, fname = ?, lname = ?, phone = ? 
                WHERE user_id = ?
            ");
            $updated = $update->execute([$teacherid, $fname, $lname, $phone, $user_id]);

            if ($update->rowCount() === 0) {
                $insert = $pdo->prepare("
                    INSERT INTO teachers (user_id, teacherid, fname, lname, phone)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insert->execute([$user_id, $teacherid, $fname, $lname, $phone]);
            }
        }

        echo "<script>
            alert('Profile completed successfully! Welcome, " . htmlspecialchars($fname) . "!');
            location.href = '" . ($role == 'admin' ? 'admin.php' : $role . '.php') . "';
        </script>";
        exit;

    } catch (Exception $e) {
        $error = "Error saving profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .box { max-width: 520px; margin: 60px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); text-align: center; }
        h2 { color: #1a3d7c; font-size: 2rem; margin-bottom: 10px; }
        input, select { width: 100%; padding: 16px; margin: 12px 0; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 1.1rem; }
        input:focus, select:focus { border-color: #667eea; outline: none; }
        button { background: #667eea; color: white; padding: 18px; width: 100%; border: none; border-radius: 12px; font-size: 1.3rem; font-weight: bold; cursor: pointer; margin-top: 20px; }
        button:hover { background: #5a6fd8; transform: translateY(-3px); }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin: 15px 0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p>Complete your profile to access your dashboard</p>

    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>

        <?php if ($role === 'student'): ?>
            <input type="text" name="stuid" placeholder="Student ID (e.g. STU001)" required>
            <select name="gender" required>
                <option value="">Gender</option>
                <option>Male</option>
                <option>Female</option>
            </select>
            <input type="date" name="dob" required>
        <?php else: ?>
            <input type="text" name="teacherid" placeholder="Teacher ID (e.g. TCH001)" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
        <?php endif; ?>

        <button type="submit">Complete Profile</button>
    </form>
</div>
</body>
</html>