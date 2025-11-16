<?php
// assign_teacher.php — NO session_start() — already in admin.php

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

// Load teachers (not assigned yet or show all)
$teachers = $pdo->query("
    SELECT t.id, t.teacherid, t.fname, t.lname 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.fname
")->fetchAll(PDO::FETCH_ASSOC);

// Load classes
$classes = $pdo->query("
    SELECT id, classname 
    FROM classes 
    ORDER BY classname
")->fetchAll(PDO::FETCH_ASSOC);

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $session = trim($_POST['session'] ?? '');
    $term = trim($_POST['term'] ?? '');

    if (empty($teacher_id) || empty($class_id) || empty($session) || empty($term)) {
        $message = "<p style='color:red;'>All fields required.</p>";
    } else {
        // Check if already assigned
        $check = $pdo->prepare("
            SELECT id FROM teacher_assignments 
            WHERE teacher_id = ? AND class_id = ? AND session = ? AND term = ?
        ");
        $check->execute([$teacher_id, $class_id, $session, $term]);
        if ($check->fetch()) {
            $message = "<p style='color:orange;'>Already assigned in this session/term.</p>";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO teacher_assignments (teacher_id, class_id, session, term)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$teacher_id, $class_id, $session, $term])) {
                $message = "<p style='color:green; font-weight:bold;'>Teacher assigned successfully!</p>";
            } else {
                $message = "<p style='color:red;'>Failed to assign.</p>";
            }
        }
    }
}
?>

<div class="section">
    <h2>Assign Teacher to Class</h2>

    <?php if ($message): ?>
        <div style="margin:15px 0; padding:12px; background:#f0f0f0; border-radius:6px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Teacher</label>
            <select name="teacher_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Teacher --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['fname'] . ' ' . $t['lname'] . ' (' . $t['teacherid'] . ')') ?>
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
            <label>Session (e.g. 2025/2026)</label>
            <input type="text" name="session" required placeholder="2025/2026" 
                   style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <div class="form-group">
            <label>Term</label>
            <select name="term" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">-- Select Term --</option>
                <option value="First">First Term</option>
                <option value="Second">Second Term</option>
                <option value="Third">Third Term</option>
            </select>
        </div>

        <button type="submit" class="btn">Assign Teacher</button>
    </form>
</div>

<style>
.form-group { margin: 15px 0; }
.btn {
    background: #4CAF50; color: white; padding: 12px 24px; border: none;
    border-radius: 6px; font-weight: bold; cursor: pointer;
}
.btn:hover { background: #388E3C; }
</style>