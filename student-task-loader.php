<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'student') exit("Access denied.");

$taskId = $_GET['taskid'] ?? 0;
if (!is_numeric($taskId)) exit("Invalid task.");

$dbHost = '127.0.0.200'; $dbPort = '5432'; $dbName = 'elshrwia_EBS_portal_db';
$dbUser = 'elshrwia_postgres'; $dbPass = 'tom123tom123@';
$pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;", $dbUser, $dbPass);

$task = $pdo->query("SELECT taskname FROM userstatustask WHERE taskid = $taskId")->fetchColumn();
if (!$task) { echo "<p>Task not found.</p>"; exit; }

echo "<div class='section'><h2>" . htmlspecialchars($task) . "</h2>";
echo "<p><em>Details for this task will appear here.</em></p>";
echo "</div>";
?>