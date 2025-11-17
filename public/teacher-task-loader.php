<?php
session_start();
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    http_response_code(403);
    exit("Access denied.");
}
// ===== DATABASE CONNECTION =====
$route = $task['route'];
$taskid = $_GET['taskid'] ?? '';
$teacherId = $_SESSION['teachers_id'] ?? '';
$classId   = $_SESSION['classid']   ?? '';

// ---------- SECURITY ----------
if (!$teacherId || !$classId) {
    echo "<p class='error'>Teacher or class not assigned.</p>";
    exit;
}

// ---------- MAP TASK IDs ----------
$allowed_routes = [
    'View_class' => 'view_class.php',        // View Class List
    'upload-assignment' => 'upload-assignment.php', // Upload Assignment
    'take-attendance' => 'take-attendance.php',        // Take Attendance
    'upload-result' => 'upload-result.php',     // Upload Result
    // add more as you create tasks...
];

if (!isset($map[$taskid])) {
    http_response_code(404);
    echo "<p class='error'>Task not found.</p>";
    exit;
}

$filename = $map[$taskid];

// Pass variables to included file
$_TASK = [
    'teacherid' => $teacherId,
    'classid'   => $classId,
    'taskid'    => $taskid
];

include $filename;
?>