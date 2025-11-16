<?php
// assign_teacher.php — FULLY WORKING VERSION

//if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
  //  die("Access denied.");
//}

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

// === 1. Get ONLY teachers NOT assigned to ANY class (in current session) ===
$current_session = '2025/2026';  // Change this or make it dynamic later

$teachers = $pdo->query("
    SELECT t.id, t.teacherid, t.fname, t.lname 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE t.id NOT IN (
        SELECT teacher_id 
        FROM teacher_assignments 
        WHERE session = '$current_session'
    )
    ORDER BY t.fname
")->fetchAll(PDO::FETCH_ASSOC);

// === 2. Get ONLY classes WITHOUT a teacher (in current session) ===
$classes = $pdo->query("
    SELECT c.id, c.classname 
    FROM classes c
    WHERE c.id NOT IN (
        SELECT class_id 
        FROM teacher_assignments 
        WHERE session = '$current_session'
    )
    ORDER BY c.classname
")->fetchAll(PDO::FETCH_ASSOC);

// === 3. Handle Assignment ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $session = trim($_POST['session'] ?? '');
    $term = trim($_POST['term'] ?? '');

    if (empty($teacher_id) || empty($class_id) || empty($session) || empty($term)) {
        $message = "<p style='color:red;'>All fields are required.</p>";
    } else {
        // Check if teacher already assigned in this session
        $check_teacher = $pdo->prepare("
            SELECT id FROM teacher_assignments 
            WHERE teacher_id = ? AND session = ?
        ");
        $check_teacher->execute([$teacher_id, $session]);
        if ($check_teacher->fetch()) {
            $message = "<p style='color:orange; font-weight:bold;'>Teacher already assigned to a class this session!</p>";
        }
        // Check if class already has a teacher
        elseif ($check_class = $pdo->prepare("
            SELECT id FROM teacher_assignments 
            WHERE class_id = ? AND session = ?
        ");
         $check_class->execute([$class_id, $session]), $check_class->fetch()) {
            $message = "<p style='color:orange; font-weight:bold;'>Class already assigned a teacher this session!</p>";
        } 
        else {
            $stmt = $pdo->prepare("
                INSERT INTO teacher_assignments (teacher_id, class_id, session, term)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$teacher_id, $class_id, $session, $term])) {
                $message = "<p style='color:green; font-weight:bold;'>Teacher assigned successfully!</p>";
                // Refresh lists after success
                header("Location: admin-task-loader.php?taskid=5");
                exit;
            } else {
                $message = "<p style='color:red;'>Database error.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Assign Teacher to Class</h2>

    <?php if ($message): ?>
        <div style="margin:15px 0; padding:12px; background:#f0f0f0; border-radius:6px; font-size:0.95rem;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (empty($teachers)): ?>
        <p style="color:#666;"><em>No unassigned teachers available for session <strong><?= htmlspecialchars($current_session) ?></strong>.</em></p>
    <?php elseif (empty($classes)): ?>
        <p style="color:#666;"><em>All classes already have teachers for this session.</em></p>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label><strong>Select Teacher</strong></label>
                <select name="teacher_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
                    <option value="">-- Choose Teacher --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['fname'] . ' ' . $t['lname'] . ' (' . $t['teacherid'] . ')') ?>
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

            <button type="submit" class="btn">Assign Teacher</button>
        </form>
    <?php endif; ?>

    <div style="margin-top:30px; padding:15px; background:#f9f9f9; border-radius:6px; font-size:0.9rem; color:#444;">
        <p><strong>Rules:</strong></p>
        <ul style="margin:8px 0; padding-left:20px;">
            <li>One teacher → One class per session</li>
            <li>One class → One teacher per session</li>
            <li>Unassigned teachers/classes only shown</li>
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