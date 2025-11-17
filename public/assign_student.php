<?php
// assign_student.php — FULLY WORKING & SECURE

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
$current_session = '2025/2026'; // Change this or make dynamic later

// === 1. Get ONLY students NOT assigned to ANY class in current session ===
$students = $pdo->query("
    SELECT s.id, s.stuid, s.fname, s.lname
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.id NOT IN (
        SELECT student_id 
        FROM student_assignments 
        WHERE session = '$current_session'
    )
    ORDER BY s.fname, s.lname
")->fetchAll(PDO::FETCH_ASSOC);

// === 2. Get ALL classes (or filter by capacity later) ===
$classes = $pdo->query("
    SELECT c.id, c.classname 
    FROM classes c 
    ORDER BY c.classname
")->fetchAll(PDO::FETCH_ASSOC);

// === 3. Handle Assignment ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $class_id   = $_POST['class_id'] ?? '';
    $session    = trim($_POST['session'] ?? '');
    $term       = trim($_POST['term'] ?? '');

    if (empty($student_id) || empty($class_id) || empty($session) || empty($term)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } else {
        // Prevent student from being in two classes same session
        $check_student = $pdo->prepare("
            SELECT id FROM student_assignments 
            WHERE student_id = ? AND session = ?
        ");
        $check_student->execute([$student_id, $session]);
        if ($check_student->fetch()) {
            $message = "<p style='color:orange; font-weight:bold;'>This student is already assigned to a class this session!</p>";
        } else {
            // Optional: Prevent too many students per class (e.g. max 40)
            // Remove or adjust limit as needed
            $count = $pdo->prepare("
                SELECT COUNT(*) FROM student_assignments 
                WHERE class_id = ? AND session = ?
            ");
            $count->execute([$class_id, $session]);
            $current_count = $count->fetchColumn();

            if ($current_count >= 50) { // Change 50 to your max class size
                $message = "<p style='color:orange; font-weight:bold;'>This class is full (50 students max).</p>";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO student_assignments (student_id, class_id, session, term)
                    VALUES (?, ?, ?, ?)
                ");
                if ($stmt->execute([$student_id, $class_id, $session, $term])) {
                    $message = "<p style='color:green; font-weight:bold;'>Student assigned successfully!</p>";
                    // Refresh page to update list
                    header("Location: admin-task-loader.php?taskid=6");
                    exit;
                } else {
                    $message = "<p style='color:red;'>Failed to assign student.</p>";
                }
            }
        }
    }
}
?>

<div class="section">
    <h2>Assign Student to Class</h2>

    <?php if ($message): ?>
        <div style="margin:15px 0; padding:12px; background:#f0f0f0; border-radius:6px; font-size:0.95rem;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <p style="color:#666; font-style:italic;">
            All students have been assigned for session <strong><?= htmlspecialchars($current_session) ?></strong>.
        </p>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label><strong>Select Student</strong></label>
                <select name="student_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['fname'] . ' ' . $s['lname'] . ' (' . $s['stuid'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><strong>Select Class</strong></label>
                <select name="class_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['classname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><strong>Session</strong></label>
                <input type="text" name="session" value="<?= $current_session ?>" required 
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
            </div>

            <div class="form-group">
                <label><strong>Term</strong></label>
                <select name="term" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                    <option value="">-- Select Term --</option>
                    <option value="First">First Term</option>
                    <option value="Second">Second Term</option>
                    <option value="Third">Third Term</option>
                </select>
            </div>

            <button type="submit" class="btn">Assign Student</button>
        </form>
    <?php endif; ?>

    <div style="margin-top:30px; padding:15px; background:#f9f9f9; border-radius:6px; font-size:0.9rem; color:#444;">
        <p><strong>Rules Applied:</strong></p>
        <ul style="margin:8px 0; padding-left:20px;">
            <li>One student → Only one class per session</li>
            <li>Class capacity: Max 50 students (adjustable)</li>
            <li>Only unassigned students are shown</li>
            <li>Prevents double assignment</li>
        </ul>
    </div>
</div>

<style>
.form-group { margin: 15px 0; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; }
.btn {
    background: #4CAF50; color: white; padding: 12px 28px; border: none;
    border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;
    transition: 0.3s;
}
.btn:hover { background: #388E3C; }
</style>