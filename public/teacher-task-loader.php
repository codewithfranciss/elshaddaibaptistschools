<?php


session_start();
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    die("Access denied.");
}

$taskid = $_GET['taskid'] ?? '';
if (!$taskid || !is_numeric($taskid)) {
    echo "<p style='color:red;'>Invalid task ID.</p>";
    exit;
}

// Database Connection
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

// Get task details
$stmt = $pdo->prepare("
    SELECT t.taskname, t.route 
    FROM tasks t
    JOIN role_tasks rt ON t.id = rt.task_id
    WHERE rt.role = 'teacher' AND t.taskid = ?
");
$stmt->execute([$taskid]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo "<p style='color:red;'>Task not found or not assigned to teacher.</p>";
    exit;
}

$route = $task['route'];
$taskname = $task['taskname'];

// Map route to actual content
$allowed_routes = [
    'view_class' => 'view_class.php',        // View Class List
    'upload-assignment' => 'upload-assignment.php', // Upload Assignment
    'take-attendance' => 'take-attendance.php',        // Take Attendance
    'upload-result' => 'upload-result.php',     // Upload Result
    // add more as you create tasks...
];

if (!isset($allowed_routes[$route])) {
    echo "<div class='section'><h2>$taskname</h2><p>Feature coming soon.</p></div>";
    exit;
}

$content_file = $allowed_routes[$route];

$teacherId = $_SESSION['teachers_id'] ?? '';
$classId   = $_SESSION['classid']   ?? '';
if (!$teacherId || !$classId) {
    echo "<p class='error'>Teacher or class not assigned.</p>";
    exit;
}

// Pass variables to included file
$_TASK = [
    'teacherid' => $teacherId,
    'classid'   => $classId,
    'taskid'    => $taskid
];

if (file_exists($content_file)) {
    include $content_file;
} else {
    echo "<div class='section'><h2>$taskname</h2><p>Module not implemented yet.</p></div>";
}
?>