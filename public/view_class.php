<?php
// view_class.php — FINAL 100% WORKING VERSION

if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    die("Access denied.");
}

$username = $_SESSION['username'];

$host = "caboose.proxy.rlwy.net"; $port = "29105"; $dbname = "railway";
$user = "postgres"; $password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// CURRENT SESSION — MAKE IT BULLETPROOF
$current_session = '2025/2026';

// Get teacher's internal ID

$stmt = $pdo->prepare("
    SELECT t.id 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!$teacher) {
    echo "<pre>DEBUG: No teacher found for username $username</pre>";
    die("Teacher account not found.");
}

// Get assigned class (with TRIM + ILIKE for safety)

$assigned_stmt = $pdo->prepare("
    SELECT c.classname, ta.session, ta.term, ta.class_id
    FROM teacher_assignments ta
    JOIN classes c ON ta.class_id = c.id
    WHERE ta.teacher_id = ?
      AND TRIM(ta.session) ILIKE TRIM(?)
    ORDER BY ta.term DESC LIMIT 1
");
$assigned_stmt->execute([$teacher['id'], $current_session]);
$assigned_class = $assigned_stmt->fetch();

if (!$assigned_class) {
    echo "<pre>DEBUG: No class assigned for teacher_id {$teacher['id']} and session $current_session</pre>";
    echo "<div class='section'><h2>My Class</h2>
    <p style='color:#d35400; font-weight:bold; padding:20px; background:#fdf2e9; border-radius:8px;'>
        You are not assigned to any class for session <strong>2025/2026</strong>.<br><br>
        <small>Check with admin or verify session is exactly '2025/2026'</small>
    </p></div>";
    exit;
}

// Get students

$students_stmt = $pdo->prepare("
        SELECT s.fname, s.lname, s.stuid
        FROM student_assignments sa
        JOIN students s ON sa.student_id = s.id
        WHERE sa.class_id = ?
            AND TRIM(sa.session) ILIKE TRIM(?)
        ORDER BY s.fname, s.lname
");
$students_stmt->execute([$assigned_class['class_id'], $current_session]);
$students = $students_stmt->fetchAll();
?>

<div class="section">
    <h2>My Class</h2>

    <div style="background:#e8f5e9; padding:18px; border-radius:10px; margin-bottom:25px; font-size:1.1rem; border-left:5px solid #4CAF50;">
        <strong>Assigned Class:</strong> <?= htmlspecialchars($assigned_class['classname']) ?><br>
        <strong>Session:</strong> <?= htmlspecialchars($assigned_class['session']) ?> | 
        <strong>Term:</strong> <?= htmlspecialchars($assigned_class['term']) ?>
    </div>

    <h3>Students in Class (<?= count($students) ?>)</h3>

    <?php if (empty($students)): ?>
        <p style="color:#e67e22; font-style:italic; padding:15px; background:#fff3cd; border-radius:8px;">
            No students assigned to this class yet.
        </p>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; background:white; box-shadow:0 3px 10px rgba(0,0,0,0.1); border-radius:10px; overflow:hidden;">
            <thead>
                <tr style="background:#4CAF50; color:white;">
                    <th style="padding:15px; text-align:left;">#</th>
                    <th style="padding:15px; text-align:left;">Full Name</th>
                    <th style="padding:15px; text-align:left;">Student ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:15px;"><?= $i + 1 ?></td>
                        <td style="padding:15px; font-weight:500;">
                            <?= htmlspecialchars($s['fname'] . ' ' . $s['lname']) ?>
                        </td>
                        <td style="padding:15px; color:#27ae60; font-weight:bold;">
                            <?= htmlspecialchars($s['stuid']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.section h2 { color:#27ae60; border-bottom:3px solid #e8f5e9; padding-bottom:10px; margin-bottom:20px; }
tr:hover { background:#f8f9fa !important; }
</style>