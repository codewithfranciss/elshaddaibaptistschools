<?php
session_start();

// Security: Only admin
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
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
    WHERE rt.role = 'admin' AND t.taskid = ?
");
$stmt->execute([$taskid]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo "<p style='color:red;'>Task not found or not assigned to admin.</p>";
    exit;
}

$route = $task['route'];
$taskname = $task['taskname'];

// Map route to actual content
$allowed_routes = [
   'create_student'   => 'create_student.php',
    'create_teacher'   => 'create_teacher.php',
    'manage_users'     => 'manage_users.php',
    'view_reports'     => 'view_reports.php',
    'settings'         => 'settings.php',
    //'create_user'      => 'create_user.php',
    'assign_roles'     => 'assign_roles.php',
    'backup_db'        => 'backup_db.php',
    'system_logs'      => 'system_logs.php',
    'assign_teacher'   => 'assign_teacher.php',
    'assign_student'   => 'assign_student.php',
    

    // Add more as needed
];

if (!isset($allowed_routes[$route])) {
    echo "<div class='section'><h2>$taskname</h2><p>Feature coming soon.</p></div>";
    exit;
}

$content_file = $allowed_routes[$route];

// Include content if file exists
if (file_exists($content_file)) {
    include $content_file;
} else {
    echo "<div class='section'><h2>$taskname</h2><p>Module not implemented yet.</p></div>";
}
?>