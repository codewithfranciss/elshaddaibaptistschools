<?php
// view_class.php — FOR TEACHERS ONLY
// NO session_start() — already started in teacher.php

if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    die("Access denied.");
}

$username = $_SESSION['username'];

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

$current_session = '2025/2026'; // Change as needed

// Get teacher's internal ID
$teacher_stmt = $pdo->prepare("
    SELECT t.id 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE u.username = ?
");
$teacher_stmt->execute([$username]);
$teacher = $teacher_stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die("Teacher not found.");
}

$teacher_id = $teacher['id'];

// Get assigned class
$class_stmt = $pdo->prepare("
    SELECT c.classname, ta.session, ta.term
    FROM teacher_assignments ta
    JOIN classes c ON ta.class_id = c.id
    WHERE ta.teacher_id = ? AND ta.session = ?
    ORDER BY ta.term DESC LIMIT 1
");
$class_stmt->execute([$teacher_id, $current_session]);
$assigned_class = $class_stmt->fetch(PDO::FETCH_ASSOC);

if (!$assigned_class) {
    $no_class_message = "<p style='color:#d35400; font-weight:bold; padding:15px; background:#fdf2e9; border-radius:8px;'>
        You are not assigned to any class for session <strong>$current_session</strong>.<br>
        Please contact the admin.
    </p>";
}

// Get students in that class
if ($assigned_class) {
    $class_id = $pdo->query("
        SELECT class_id FROM teacher_assignments 
        WHERE teacher_id = $teacher_id AND session = '$current_session'
    ")->fetchColumn();

    $students = $pdo->query("
        SELECT s.fname, s.lname, s.stuid
        FROM student_assignments sa
        JOIN students s ON sa.student_id = s.id
        WHERE sa.class_id = $class_id AND sa.session = '$current_session'
        ORDER BY s.fname, s.lname
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="section">
    <h2>My Class</h2>

    <?php if (isset($no_class_message)): ?>
        <?= $no_class_message ?>
    <?php else: ?>
        <div style="background:#e8f5e9; padding:15px; border-radius:8px; margin-bottom:20px; font-size:1.1rem;">
            <strong>Assigned Class:</strong> <?= htmlspecialchars($assigned_class['classname']) ?><br>
            <strong>Session:</strong> <?= htmlspecialchars($assigned_class['session']) ?> | 
            <strong>Term:</strong> <?= htmlspecialchars($assigned_class['term']) ?>
        </div>

        <h3>Students in Class (<?= count($students) ?>)</h3>

        <?php if (empty($students)): ?>
            <p style="color:#e67e22; font-style:italic;">No students assigned to this class yet.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse; margin-top:10px; background:white;">
                <thead>
                    <tr style="background:#4CAF50; color:white;">
                        <th style="padding:12px; text-align:left;">#</th>
                        <th style="padding:12px; text-align:left;">Full Name</th>
                        <th style="padding:12px; text-align:left;">Student ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $student): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:12px;"><?= $i + 1 ?></td>
                            <td style="padding:12px; font-weight:500;">
                                <?= htmlspecialchars($student['fname'] . ' ' . $student['lname']) ?>
                            </td>
                            <td style="padding:12px; color:#27ae60; font-weight:bold;">
                                <?= htmlspecialchars($student['stuid']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.section h2 {
    color: #27ae60;
    border-bottom: 2px solid #e8f5e9;
    padding-bottom: 8px;
}
table {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
}
tr:hover {
    background: #f8f9fa !important;
}
</style>