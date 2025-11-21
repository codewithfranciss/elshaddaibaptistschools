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

// Get user_id first
$user_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$user_stmt->execute([$username]);
$user = $user_stmt->fetch();

if (!$user) {
    die("User not found in `users` table. Contact admin.");
}

$user_id = $user['id'];

// Check if profile already complete
$profile_complete = false;
if ($role === 'student') {
    $check = $pdo->prepare("SELECT fname FROM students WHERE user_id = ? AND fname IS NOT NULL AND fname != ''");
    $check->execute([$user_id]);
    $profile_complete = $check->fetchColumn() !== false;
} elseif ($role === 'teacher') {
    $check = $pdo->prepare("SELECT fname FROM teachers WHERE user_id = ? AND fname IS NOT NULL AND fname != ''");
    $check->execute([$user_id]);
    $profile_complete = $check->fetchColumn() !== false;
}

if ($profile_complete) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : $role . '.php'));
    exit;
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);

    if ($role === 'student') {
        $stuid  = strtoupper(trim($_POST['stuid']));
        $gender = $_POST['gender'];
        $dob    = $_POST['dob'];

        // UPSERT: Insert or Update
        $sql = "INSERT INTO students (user_id, stuid, fname, lname, gender, dob) 
                VALUES (?, ?, ?, ?, ?, ?) 
                ON CONFLICT (user_id) DO UPDATE 
                SET stuid = EXCLUDED.stuid, 
                    fname = EXCLUDED.fname, 
                    lname = EXCLUDED.lname, 
                    gender = EXCLUDED.gender, 
                    dob = EXCLUDED.dob";
        
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$user_id, $stuid, $fname, $lname, $gender, $dob]);

    } else { // teacher
        $teacherid = strtoupper(trim($_POST['teacherid']));
        $phone = trim($_POST['phone']);

        $sql = "INSERT INTO teachers (user_id, teacherid, fname, lname, phone) 
                VALUES (?, ?, ?, ?, ?) 
                ON CONFLICT (user_id) DO UPDATE 
                SET teacherid = EXCLUDED.teacherid, 
                    fname = EXCLUDED.fname, 
                    lname = EXCLUDED.lname, 
                    phone = EXCLUDED.phone";
        
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$user_id, $teacherid, $fname, $lname, $phone]);
    }

    if ($success) {
        echo "<script>alert('Profile completed successfully!'); location.href='" . ($role === 'admin' ? 'admin.php' : $role . '.php') . "';</script>";
        exit;
    } else {
        $error = "Failed to save profile. Try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; background: #f0f7ff; padding: 20px; }
        .box { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        h2 { color: #1a3d7c; text-align: center; }
        input, select { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        button { background: #4CAF50; color: white; padding: 16px; width: 100%; border: none; border-radius: 10px; font-size: 1.2rem; cursor: pointer; }
        button:hover { background: #388E3C; }
        .error { color: red; background: #ffebee; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p style="text-align:center; color:#555;">Please complete your profile to continue</p>

    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>

        <?php if ($role === 'student'): ?>
            <input type="text" name="stuid" placeholder="Student ID (e.g. STU001)" required>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <input type="date" name="dob" required>
        <?php else: ?>
            <input type="text" name="teacherid" placeholder="Teacher ID (e.g. TCH001)" required>
            <input type="text" name="phone" placeholder="Phone Number (e.g. 08012345678)" required>
        <?php endif; ?>

        <button type="submit">Complete Profile & Continue</button>
    </form>
</div>
</body>
</html>