<?php
session_start();
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'teacher') {
    http_response_code(403);
    exit("Access denied.");
}

$taskid = $_GET['taskid'] ?? '';
$teacherId = $_SESSION['teacherid'] ?? '';
$classId   = $_SESSION['classid']   ?? '';

// ---------- SECURITY ----------
if (!$teacherId || !$classId) {
    echo "<p class='error'>Teacher or class not assigned.</p>";
    exit;
}

// ---------- MAP TASK IDs ----------
$map = [
    '1' => 'task-class-list.php',        // View Class List
    '2' => 'task-upload-assignment.php', // Upload Assignment
    '3' => 'task-attendance.php',        // Take Attendance
    '4' => 'task-upload-result.php',     // Upload Result
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