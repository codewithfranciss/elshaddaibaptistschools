<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] !== 'teacher') exit("Access denied.");

$view = $_GET['view'] ?? '';
$classId = $_GET['classid'] ?? '';
$teacherId = $_GET['teacherid'] ?? '';

$dbHost = '127.0.0.200'; $dbPort = '5432'; $dbName = 'elshrwia_EBS_portal_db';
$dbUser = 'elshrwia_postgre'; $dbPass = 'tom123tom123@';
$pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;", $dbUser, $dbPass);

switch ($view) {
  case 'class':
    if (!$classId) { echo "<p>No class assigned.</p>"; break; }
    echo "<div class='section'><h2>Class List - $classId</h2><table><tr><th>ID</th><th>Name</th></tr>";
    $students = $pdo->query("SELECT s.stuid, s.fname, s.lname FROM studentass sa JOIN student s ON sa.studid = s.stuid WHERE sa.classid = '$classId' ORDER BY s.lname")->fetchAll();
    foreach ($students as $s) {
      echo "<tr><td>{$s['stuid']}</td><td>{$s['fname']} {$s['lname']}</td></tr>";
    }
    echo "</table></div>";
    break;

  case 'grades':
    if (!$classId) { echo "<p>No class assigned.</p>"; break; }
    echo "<div class='section'><h2>Assign Grades</h2>";
    echo "<form id='gradeForm'><div class='form-group'><label>Student ID</label><input type='text' name='stuid' required></div>";
    echo "<div class='form-group'><label>Subject</label><input type='text' name='subject' required></div>";
    echo "<div class='form-group'><label>Score (0-100)</label><input type='number' name='score' min='0' max='100' required></div>";
    echo "<input type='hidden' name='classid' value='$classId'>";
    echo "<button type='submit' class='btn'>Save Grade</button></form><div id='gradeMsg'></div></div>";

    echo "<script>
      document.getElementById('gradeForm').onsubmit = function(e) {
        e.preventDefault();
        const form = new FormData(this);
        fetch('teacher-action.php?action=grade', { method: 'POST', body: form })
          .then(res => res.text())
          .then(msg => document.getElementById('gradeMsg').innerHTML = '<p class=\"success\">' + msg + '</p>');
      };
    </script>";
    break;

  case 'attendance':
    if (!$classId) { echo "<p>No class assigned.</p>"; break; }
    echo "<div class='section'><h2>Mark Attendance</h2>";
    echo "<form id='attendForm'><div class='form-group'><label>Student ID</label><input type='text' name='stuid' required></div>";
    echo "<div class='form-group'><label>Date (YYYY-MM-DD)</label><input type='date' name='date' value='" . date('Y-m-d') . "' required></div>";
    echo "<div class='form-group'><label>Status</label><select name='status'><option value='1'>Present</option><option value='0'>Absent</option></select></div>";
    echo "<input type='hidden' name='classid' value='$classId'>";
    echo "<button type='submit' class='btn'>Save</button></form><div id='attendMsg'></div></div>";

    echo "<script>
      document.getElementById('attendForm').onsubmit = function(e) {
        e.preventDefault();
        const form = new FormData(this);
        fetch('teacher-action.php?action=attend', { method: 'POST', body: form })
          .then(res => res.text())
          .then(msg => document.getElementById('attendMsg').innerHTML = '<p class=\"success\">' + msg + '</p>');
      };
    </script>";
    break;

  default:
    echo "<p>Invalid view.</p>";
}
?>