<?php
// complete_profile.php — AUTO REDIRECT IF PROFILE INCOMPLETE

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$role = $_SESSION['status'];
$username = $_SESSION['username'];

$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";
$pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check if profile is complete
if ($role === 'student') {
    $data = $pdo->prepare("SELECT fname, lname, stuid, gender, dob FROM students s JOIN users u ON s.user_id = u.id WHERE u.username = ?");
} else {
    $data = $pdo->prepare("SELECT fname, lname, teacherid, phone FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.username = ?");
}
$data->execute([$username]);
$profile = $data->fetch();

$is_complete = !empty($profile['fname']) && !empty($profile['lname']);

if ($is_complete) {
    // Profile complete → go to dashboard
    header("Location: " . ($role === 'admin' ? 'admin.php' : $role . '.php'));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Profile</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; padding: 40px; }
        .box { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; }
        button { background: #4CAF50; color: white; padding: 14px; width: 100%; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <p>Please complete your profile to continue.</p>

    <form method="POST">
        <input type="text" name="fname" placeholder="First Name" required><br>
        <input type="text" name="lname" placeholder="Last Name" required><br>

        <?php if ($role === 'student'): ?>
            <input type="text" name="stuid" placeholder="Student ID (e.g. STU001)" required><br>
            <select name="gender" required><option value="">Gender</option><option>Male</option><option>Female</option></select><br>
            <input type="date" name="dob" required><br>
        <?php else: ?>
            <input type="text" name="teacherid" placeholder="Teacher ID (e.g. TCH001)" required><br>
            <input type="text" name="phone" placeholder="Phone Number" required><br>
        <?php endif; ?>

        <button type="submit">Complete Profile & Continue</button>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];

    if ($role === 'student') {
        $stuid = $_POST['stuid'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $pdo->prepare("UPDATE students SET fname=?, lname=?, stuid=?, gender=?, dob=? WHERE user_id=(SELECT id FROM users WHERE username=?)")
            ->execute([$fname, $lname, $stuid, $gender, $dob, $username]);
    } else {
        $teacherid = $_POST['teacherid'];
        $phone = $_POST['phone'];
        $pdo->prepare("UPDATE teachers SET fname=?, lname=?, teacherid=?, phone=? WHERE user_id=(SELECT id FROM users WHERE username=?)")
            ->execute([$fname, $lname, $teacherid, $phone, $username]);
    }

    echo "<script>alert('Profile completed! Welcome!'); location.href='" . ($role === 'admin' ? 'admin.php' : $role . '.php') . "';</script>";
}
?>
</body>
</html>