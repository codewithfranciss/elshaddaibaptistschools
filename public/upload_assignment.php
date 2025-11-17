<?php
// upload_assignment.php — FULLY WORKING TEXT ASSIGNMENT

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

// Get teacher ID
$teacher_id = $pdo->prepare("SELECT t.id FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.username = ?");
$teacher_id->execute([$username]);
$teacher_id = $teacher_id->fetchColumn();

if (!$teacher_id) die("Teacher not found.");

// Get assigned class
$class = $pdo->query("
    SELECT ta.class_id, c.classname 
    FROM teacher_assignments ta 
    JOIN classes c ON ta.class_id = c.id 
    WHERE ta.teacher_id = $teacher_id AND ta.session = '$current_session'
    LIMIT 1
")->fetch();

if (!$class) {
    echo "<div class='section'><h2>Upload Assignment</h2>
          <p style='color:red; font-weight:bold; padding:20px; background:#ffebee; border-radius:8px;'>
          You are not assigned to any class this session (2025/2026).</p></div>";
    exit;
}
?>

<div class="section">
    <h2>Upload Assignment</h2>
    <p style="background:#e8f5e9; padding:12px; border-radius:6px; margin-bottom:20px;">
        <strong>Your Class:</strong> <?= htmlspecialchars($class['classname']) ?> 
        <small>(Session: 2025/2026)</small>
    </p>

    <form id="assignmentForm">
        <div class="form-group">
            <label><strong>Title</strong></label>
            <input type="text" id="title" required placeholder="e.g. English Composition - Week 10" 
                   style="width:100%; padding:12px; border:1px solid #ddd; border-radius:6px; font-size:1rem;">
        </div>

        <div class="form-group">
            <label><strong>Assignment Description</strong> (Type full assignment here)</label>
            <div id="editor" style="height:320px; border:1px solid #ddd; border-radius:8px; background:white;"></div>
            <textarea id="description" name="description" style="display:none;" required></textarea>
        </div>

        <button type="submit" class="btn">Upload Assignment</button>
    </form>

    <div id="msg" style="margin-top:20px; padding:15px; border-radius:8px; font-weight:bold;"></div>
</div>

<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
// Initialize Quill
var quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Submit form
document.getElementById('assignmentForm').onsubmit = function(e) {
    e.preventDefault();

    const title = document.getElementById('title').value.trim();
    const description = quill.root.innerHTML;

    if (!title || quill.getText().trim().length < 10) {
        document.getElementById('msg').innerHTML = "<p style='color:red; background:#ffebee;'>Please enter title and full assignment (min 10 chars).</p>";
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_assignment');
    formData.append('title', title);
    formData.append('description', description);
    formData.append('classid', '<?= $class['class_id'] ?>');
    formData.append('teachers_id', '<?= $teacher_id ?>');

    fetch('teacher-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        document.getElementById('msg').innerHTML = "<p style='color:green; background:#e8f5e9; padding:15px; border-radius:8px;'>" + result + "</p>";
        document.getElementById('title').value = '';
        quill.setContents([]);
    })
    .catch(err => {
        document.getElementById('msg').innerHTML = "<p style='color:red; background:#ffebee;'>Upload failed. Check connection.</p>";
    });
};
</script>

<style>
.form-group { margin: 20px 0; }
label { font-weight: 600; color: #333; display: block; margin-bottom: 10px; }
.btn {
    background: #4CAF50; color: white; padding: 14px 32px; border: none;
    border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer;
    transition: 0.3s;
}
.btn:hover { background: #388E3C; }
#msg { min-height: 50px; }
</style>