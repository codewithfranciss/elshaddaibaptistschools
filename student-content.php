<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'student') {
    http_response_code(403); exit("Access denied.");
}

$view = $_GET['view'] ?? 'profile';
$username = $_SESSION['username'];

$dbHost = '127.0.0.200'; $dbPort = '5432'; $dbName = 'elshrwia_EBS_portal_db';
$dbUser = 'elshrwia_postgres'; $dbPass = 'tom123tom123@';
$pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;", $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Get student ID
$stuid = $pdo->query("SELECT stuid FROM studlogin WHERE username = '$username'")->fetchColumn();

switch ($view) {
  case 'profile':
    $s = $pdo->query("SELECT * FROM student WHERE stuid = '$stuid'")->fetch(PDO::FETCH_ASSOC);
    echo "<div class='section'><h2>Full Profile</h2>";
    echo "<p><strong>Name:</strong> {$s['fname']} {$s['lname']}</p>";
    echo "<p><strong>DOB:</strong> {$s['dob']}</p>";
    echo "<p><strong>Gender:</strong> {$s['gender']}</p>";
    echo "<p><strong>Entry Session:</strong> {$s['sessionofentry']}</p>";
    echo "</div>";
    break;

  case 'results':
    echo "<div class='section'><h2>Recent Results</h2><table><tr><th>Subject</th><th>Score</th><th>Term</th><th>Session</th></tr>";
    $grades = $pdo->query("SELECT subject, score, term, session FROM academicrecord WHERE stuid = '$stuid' ORDER BY session DESC, term DESC LIMIT 10")->fetchAll();
    foreach ($grades as $g) {
      echo "<tr><td>{$g['subject']}</td><td>{$g['score']}</td><td>{$g['term']}</td><td>{$g['session']}</td></tr>";
    }
    echo "</table></div>";
    break;

  case 'assignments':
    echo "<div class='section'><h2>Assignments</h2>";
    echo "<p><em>No assignments uploaded yet.</em></p>";
    echo "</div>";
    break;

  case 'payment':
    echo "<div class='section'><h2>Make Payment</h2>";
    echo "<p>Click below to proceed with payment.</p>";
    echo "<a href='payment.php?stuid=$stuid' class='pay-btn'>Go to Payment</a>";
    echo "</div>";
    break;

  default:
    echo "<p>Invalid view.</p>";
}
?>