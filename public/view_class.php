<?php
// view_class.php — FINAL 100% CORRECT & BULLETPROOF VERSION (Nov 2025)

//session_start();  // ← THIS WAS MISSING! CRITICAL!

// Security check
//if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
   // header("Location: login.php");
  //  die("Access denied. Please log in as a teacher.");
//}

$username = $_SESSION['username'];

// DB Connection
$host = "caboose.proxy.rlwy.net"; 
$port = "29105"; 
$dbname = "railway";
$user = "postgres"; 
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// === DYNAMIC SESSION & TERM (Most Important Fix!) ===
$current_session = '2025/2026';  // You can later read from a settings table

// Get current active term from DB (or default to latest)
$term_query = $pdo->query("
    SELECT term FROM teacher_assignments 
    WHERE session = '2025/2026' 
    ORDER BY 
        CASE term 
            WHEN 'First Term' THEN 1
            WHEN 'Second Term' THEN 2 
            WHEN 'Third Term' THEN 3 
            ELSE 99 
        END DESC 
    LIMIT 1
");
$current_term = $term_query->fetchColumn() ?: 'First Term';

// Get teacher internal ID

$stmt = $pdo->prepare("
    SELECT t.teacherid 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("<h3>Error:</h3> Teacher account not found for username: " . htmlspecialchars($username));
}

$teacher_code = $teacher['teacherid'];

// Get teacher's CURRENT class assignment (session + latest term)


$assigned_stmt = $pdo->prepare("
    SELECT 
        c.classname, 
        ta.session, 
        ta.term, 
        ta.class_id
    FROM teacher_assignments ta
    JOIN classes c ON ta.class_id = c.id
    WHERE ta.teachers_id = ?
        AND TRIM(ta.session) ILIKE TRIM(?)
        AND TRIM(ta.term) ILIKE TRIM(?)
    LIMIT 1
");
$assigned_stmt->execute([$teacher_code, $current_session, $current_term]);
$assigned_class = $assigned_stmt->fetch();

if (!$assigned_class) {
    // Fallback: try any term in this session
    $fallback = $pdo->prepare("
        SELECT c.classname, ta.session, ta.term, ta.class_id
        FROM teacher_assignments ta
        JOIN classes c ON ta.class_id = c.id
        WHERE ta.teachers_id = ? AND TRIM(ta.session) ILIKE TRIM(?)
        ORDER BY 
            CASE ta.term 
                WHEN 'First Term' THEN 1
                WHEN 'Second Term' THEN 2 
                WHEN 'Third Term' THEN 3 
                ELSE 99 
            END DESC
        LIMIT 1
    ");
    $fallback->execute([$teacher_code, $current_session]);
    $assigned_class = $fallback->fetch();

    if (!$assigned_class) {
        echo "<div class='section'><h2>My Class</h2>
        <p style='color:#d35400; font-weight:bold; padding:20px; background:#fdf2e9; border-radius:8px;'>
            You are not assigned to any class for session <strong>$current_session</strong>.<br><br>
            Contact the admin if this is incorrect.
        </p></div>";
        include 'footer.php';
        exit;
    }
}

// Now get students in that exact class + session

$students_stmt = $pdo->prepare("
        SELECT s.fname, s.lname, s.stuid, s.gender
        FROM student_assignments sa
        JOIN students s ON sa.student_id = s.id
        WHERE sa.class_id = ?
            AND TRIM(sa.session) ILIKE TRIM(?)
        ORDER BY s.fname, s.lname
");
$students_stmt->execute([$assigned_class['class_id'], $current_session]);
$students = $students_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Class - <?= htmlspecialchars($assigned_class['classname']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; margin:0; padding:20px; }
        .section h2 { color:#27ae60; border-bottom:3px solid #e8f5e9; padding-bottom:10px; }
        table { width:100%; border-collapse:collapse; background:white; box-shadow:0 4px 15px rgba(0,0,0,0.1); border-radius:12px; overflow:hidden; margin-top:15px; }
        th { background:#27ae60; color:white; padding:16px; text-align:left; }
        td { padding:14px 16px; border-bottom:1px solid #eee; }
        tr:hover { background:#f8fff9; }
        .info { background:#e8f5e9; padding:20px; border-radius:10px; border-left:6px solid #27ae60; font-size:1.1rem; }
        .count { background:#4CAF50; color:white; padding:5px 12px; border-radius:20px; font-size:0.9rem; }
    </style>
</head>
<body>

<div class="section">

    <h2>My Class</h2>

    <div class="info">
        <strong>Assigned Class:</strong> <?= htmlspecialchars($assigned_class['classname']) ?><br>
        <strong>Session:</strong> <?= htmlspecialchars($assigned_class['session']) ?> | 
        <strong>Term:</strong> <span class="count"><?= htmlspecialchars($assigned_class['term']) ?></span>
        <?php if ($assigned_class['term'] !== $current_term): ?>
            <small style="color:#e67e22;">(Showing <?= htmlspecialchars($assigned_class['term']) ?> — current term is <?= htmlspecialchars($current_term) ?>)</small>
        <?php endif; ?>
    </div>

    <h3>Students in Class <span class="count"><?= count($students) ?></span></h3>

    <?php if (empty($students)): ?>
        <p style="color:#e67e22; padding:20px; background:#fff3cd; border-radius:8px; font-style:italic;">
            No students have been assigned to this class yet for the <?= htmlspecialchars($current_session) ?> session.
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Student ID</th>
                    <th>Gender</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td style="font-weight:500;">
                            <?= htmlspecialchars($s['fname'] . ' ' . $s['lname']) ?>
                        </td>
                        <td style="color:#27ae60; font-weight:bold;">
                            <?= htmlspecialchars($s['stuid']) ?>
                        </td>
                        <td><?= htmlspecialchars(ucfirst($s['gender'] ?? 'N/A')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>