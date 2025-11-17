<?php
// upload_assignment.php → TEXT-BASED ASSIGNMENT (WORKS 100%)

if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'teacher') {
    die("Access denied.");
}

// DB Connection (Railway fix)
$dsn = "pgsql:host=caboose.proxy.rlwy.net;port=29105;dbname=railway;sslmode=require;options=--search_path=public";
try {
    $pdo = new PDO($dsn, "postgres", "ubYpfEwCHqwsekeSrBtODAJEohrOiviu", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

$username = $_SESSION['username'];
$session  = '2025/2026';

// Get teacher ID
$tid = $pdo->prepare("SELECT t.id FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.username = ?");
$tid->execute([$username]);
$teacher_id = $tid->fetchColumn();

if (!$teacher_id) die("Teacher account error.");

// Get assigned class
$class = $pdo->query("
    SELECT ta.class_id, c.classname 
    FROM teacher_assignments ta
    JOIN classes c ON ta.class_id = c.id
    WHERE ta.teacher_id = $teacher_id AND ta.session = '$session'
    LIMIT 1
")->fetch();

if (!$class) {
    die("<h2 style='color:red; text-align:center; padding:30px;'>You are not assigned to any class for 2025/2026 session.</h2>");
}
?>

<div class="section">
    <h2>Upload Assignment</h2>
    <div style="background:#e8f5e9;padding:15px;border-radius:8px;margin-bottom:25px;">
        <strong>Class:</strong> <?= htmlspecialchars($class['classname']) ?> | Session: 2025/2026
    </div>

    <form id="assignmentForm">
        <div class="form-group">
            <label><strong>Title</strong></label>
            <input type="text" id="title" required placeholder="e.g. English Composition - My Family"
                   style="width:100%;padding:14px;font-size:16px;border:1px solid #ccc;border-radius:8px;">
        </div>

        <div class="form-group">
            <label><strong>Assignment Description</strong> → Click here and start typing</label>
            <div id="quill-editor"></div>
            <input type="hidden" id="description">
        </div>

        <button type="submit" id="submitBtn" class="btn">
            Upload Assignment Now
        </button>
    </form>

    <div id="result" style="margin-top:20px;padding:15px;border-radius:8px;font-weight:bold;"></div>
</div>

<!-- Quill Editor (Latest Stable) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script>
// Initialize Quill editor
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'Type the full assignment here...',
    modules: {
        toolbar: [
            [{ header: [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Form submit
document.getElementById('assignmentForm').onsubmit = function(e) {
    e.preventDefault();

    const title = document.getElementById('title').value.trim();
    const description = quill.root.innerHTML;

    if (!title || quill.getText().trim().length < 5) {
        document.getElementById('result').innerHTML = 
            "<div style='background:#ffebee;color:red;padding:15px;border-radius:8px;'>Please write title and assignment!</div>";
        return;
    }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = "Uploading...";

    const fd = new FormData();
    fd.append('action', 'upload_assignment');
    fd.append('title', title);
    fd.append('description', description);
    fd.append('classid', '<?= $class['class_id'] ?>');
    fd.append('teachers_id', '<?= $teacher_id ?>');

    fetch('teacher-action.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.text())
    .then(res => {
        document.getElementById('result').innerHTML = 
            "<div style='background:#e8f5e9;color:green;padding:15px;border-radius:8px;'>" + res + "</div>";
        document.getElementById('title').value = '';
        quill.setContents([]);
    })
    .catch(() => {
        document.getElementById('result').innerHTML = 
            "<div style='background:#ffebee;color:red;padding:15px;border-radius:8px;'>Upload failed – check internet.</div>";
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = "Upload Assignment Now";
    });
};
</script>

<style>
.form-group { margin: 22px 0; }
label { font-weight: 600; color: #222; margin-bottom: 10px; display: block; }
.btn {
    background: #4CAF50; color: white; padding: 16px 40px; border: none;
    border-radius: 10px; font-size: 1.2rem; font-weight: bold; cursor: pointer;
    transition: 0.3s;
}
.btn:hover { background: #388E3C; }
.btn:disabled { background: #aaa; cursor: not-allowed; }
</style>