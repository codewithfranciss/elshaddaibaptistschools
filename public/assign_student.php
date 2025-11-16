<?php
// assign_student.php

if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
    die("Access denied.");
}

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

// Load students
$students = $pdo->query("
    SELECT s.id, s.stuid, s.fname, s.lname 
    FROM students s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.fname
")->fetchAll(PDO::FETCH_ASSOC);

// Load classes
$classes = $pdo->query("
    SELECT id, classname FROM classes ORDER BY classname
")->fetchAll(PDO::FETCH_ASSOC);

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $session = trim($_POST['session'] ?? '');
    $term = trim($_POST['term'] ?? '');

    if (empty($student_id) || empty($class_id) || empty($session) || empty($term)) {
        $message = "<p style='color:red;'>All fields required.</p>";
    } else {
        $check = $pdo->prepare("
            SELECT id FROM student_assignments 
            WHERE student_id = ? AND class_id = ? AND session = ? AND term = ?
        ");
        $check->execute([$student_id, $class_id, $session, $term]);
        if ($check->fetch()) {
            $message = "<p style='color:orange;'>Already assigned.</p>";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO student_assignments (student_id, class_id, session, term)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$student_id, $class_id, $session, $term])) {
                $message = "<p style='color:green; font-weight:bold;'>Student assigned!</p>";
            } else {
                $message = "<p style='color:red;'>Failed.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Assign Student to Class</h2>

    <?php if ($message): ?>
        <div style="margin:15px 0; padding:12px; background:#f0f0f0; border-radius:6px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Student</label>
            <select name="student_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['fname'] . ' ' . $s['lname'] . ' (' . $s['stuid'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Class</label>
            <select name="class_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Class --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['classname']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Session</label>
            <input type="text" name…