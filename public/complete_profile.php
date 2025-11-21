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

// Get user_id
$user_id = $pdo->prepare("SELECT id FROM users WHERE username = ?")->execute([$username])
    ? $pdo->fetchColumn() : die("User not found.");

// Auto-create empty profile row if missing (GENIUS FIX)
if ($role === 'student') {
    $pdo->prepare("INSERT INTO students (user_id, stuid) VALUES (?, ?) ON CONFLICT (user_id) DO NOTHING")
        ->execute([$user_id, 'TEMP-' . $user_id]);
} elseif ($role === 'teacher') {
    $pdo->prepare("INSERT INTO teachers (user_id, teacherid) VALUES (?, ?) ON CONFLICT (user_id) DO NOTHING")
        ->execute([$user_id, 'TEMP-' . $user_id]);
}

// Check if profile is already complete
$is_complete = false;
if ($role === 'student') {
    $is_complete = $pdo->prepare("SELECT fname FROM students WHERE user_id = ? AND fname IS NOT NULL AND fname != ''")
        ->execute([$user_id]) && $pdo->fetchColumn();
} elseif ($role === 'teacher') {
    $is_complete = $pdo->prepare("SELECT fname FROM teachers WHERE user_id = ? AND fname IS NOT NULL AND fname != ''")
        ->execute([$user_id]) && $pdo->fetchColumn();
}

if ($is_complete) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : $role . '.php'));
    exit;
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);

    $success = false;

    if ($role === 'student') {
        $stuid  = strtoupper(trim($_POST['stuid']));
        $gender = $_POST['gender'];
        $dob    = $_POST['dob'];

        $stmt = $pdo->prepare("
            INSERT INTO students (user_id, stuid, fname, lname, gender, dob)
            VALUES (?, ?, ?, ?, ?, ?)
            ON CONFLICT (user_id) DO UPDATE SET
                stuid = EXCLUDED.stuid,
                fname = EXCLUDED.fname,
                lname = EXCLUDED.lname,
                gender = EXCLUDED.gender,
                dob = EXCLUDED.dob
        ");
        $success = $stmt->execute([$user_id, $stuid, $fname, $lname, $gender, $dob]);

    } elseif ($role === 'teacher') {
        $teacherid = strtoupper(trim($_POST['teacherid']));
        $phone = trim($_POST['phone']);

        $stmt = $pdo->prepare("
            INSERT INTO teachers (user_id, teacherid, fname, lname, phone)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT (user_id) DO UPDATE SET
                teacherid = EXCLUDED.teacherid,
                fname = EXCLUDED.fname,
                lname = EXCLUDED.lname,
                phone = EXCLUDED.phone
        ");
        $success = $stmt->execute([$user_id, $teacherid, $fname, $lname, $phone]);
    }

    if ($success) {
        echo "<script>
            alert('Profile completed successfully! Welcome, " . htmlspecialchars($fname) . "!');
            location.href = '" . ($role === 'admin' ? 'admin.php' : $role . '.php') . "';
        </script>";
        exit;
    } else {
        $error = "Failed to save profile. Please try again.";
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
        .box { max-width: 520px; margin: 50px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); text-align: center; }
        h2 { color: #1a3d7c; font-size: 2rem; margin-bottom: 10px; }
        p { color: #555; font-size: 1.1rem; }
        input, select { width: 100%; padding: 16px; margin: 12px 0; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 1.1rem; transition: 0.3s; }
        input:focus, select:focus { border-color: #667eea; outline: none; }
        button { background: #667eea; color: white; padding: 18px; width: 100%; border: none; border-radius: 12px; font-size: 1.3rem; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        button:hover { background: #5a6fd8; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102,126,234,0.3); }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin: 15px 0; }
    </style>
</head>
<body>
<div class="box">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p>Let's complete your profile</p>

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