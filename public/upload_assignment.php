<?php
// upload_assignment.php — TEXT-BASED ASSIGNMENT (Teacher uploads paragraph)

if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'teacher') {
    die("Access denied.");
}

$host = "caboose.proxy.rlwy.net"; $port = "29105"; $dbname = "railway";
$user = "postgres"; $password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options=--search_path=public";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }

$username = $_SESSION['username'];
$current_session = '2025/2026';

// Get teacher's class
$teacher = $pdo->prepare("SELECT t.id FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.username = ?");
$teacher->execute([$username]);
$teacher_id = $teacher->fetchColumn();

$class = $pdo->query("
    SELECT ta.class_id, c.classname 
    FROM teacher_assignments ta 
    JOIN classes c ON ta.class_id = c.id 
    WHERE ta.teacher_id = $teacher_id AND ta.session = '$current_session'
")->fetch();

if (!$class) {
    echo "<p style='color:red; font-weight:bold;'>You are not assigned to any class this session.</p>";
    exit;
}

$class_id = $class['class_id'];
?>

<div class="section">
    <h2>Upload Assignment</h2>
    <p><strong>Class:</strong> <?= htmlspecialchars($class['classname']) ?> (2025/2026 Session)</p>

    <form id="assignmentForm">
        <div class="form-group">
            <label><strong>Assignment Title</strong></label>
            <input type="text" id="title" required placeholder="e.g. Mathematics Homework Week 5">
        </div>

        <div class="form-group">
            <label><strong>Assignment Content</strong> (Type your full assignment here)</label>
            <div id="editor" style="border:1px solid #ddd; min-height:300px; padding:15px; border-radius:8px; background:white;"></div>
            <textarea name="content" id="content" style="display:none;" required></textarea>
        </div>

        <button type="submit" class="btn">Upload Assignment</button>
    </form>

    <div id="msg" style="margin-top:20px;"></div>
</div>

<!-- Quill.js Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
var quill = new Quill('#editor', {
    theme: 'snow',
    modules: { toolbar: true }
});

document.getElementById('assignmentForm').onsubmit = function(e) {
    e.preventDefault();
    const title = document.getElementById('title').value.trim();
    const content = quill.root.innerHTML;

    if (!title || quill.getText().trim().length < 10) {
        document.getElementById('msg').innerHTML = "<p style='color:red;'>Title and content required (min 10 chars).</p>";
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_text_assignment');
    formData.append('title', title);
    formData.append('content', content);
    formData.append('class_id', '<?= $class_id ?>');

    fetch('teacher-action.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(msg => {
        document.getElementById('msg').innerHTML = '<p style="color:green; font-weight:bold;">' + msg + '</p>';
        document.getElementById('title').value = '';
        quill.setContents([]);
    })
    .catch(() => {
        document.getElementById('msg').innerHTML = '<p style="color:red;">Upload failed. Try again.</p>';
    });
};
</script>

<style>
.form-group { margin: 20px 0; }
label { font-weight: 600; color: #333; display: block; margin-bottom: 8px; }
input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
.btn { background: #4CAF50; color: white; padding: 14px 30px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
.btn:hover { background: #388E3C; }
</style>