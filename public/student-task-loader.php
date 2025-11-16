<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'student') exit("Access denied.");

$taskId = $_GET['taskid'] ?? 0;
if (!is_numeric($taskId)) exit("Invalid task.");
// ===== DATABASE CONNECTION =====
$host = "caboose.proxy.rlwy.net";       // Railway public host
$port = "29105";                         // Railway port
$dbname = "railway";                     // Railway database name
$user = "postgres";                      // Railway username
$password = "ubYpfEwCHqwsekeSrBtODAJEohrOiviu"; // Railway password

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;";

try {
    $pdo = $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

$task = $pdo->query("SELECT taskname FROM userstatustask WHERE taskid = $taskId")->fetchColumn();
if (!$task) { echo "<p>Task not found.</p>"; exit; }

echo "<div class='section'><h2>" . htmlspecialchars($task) . "</h2>";
echo "<p><em>Details for this task will appear here.</em></p>";
echo "</div>";
?>