<?php
if (!defined('_TASK_INCLUDED')) exit;
$pdo = new PDO("pgsql:host=127.0.0.200;port=5432;dbname=elshrwia_EBS_portal_db", 
               "elshrwia_postgres", "tom123tom123@", 
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Get students
$stmt = $pdo->prepare("
    SELECT s.stuid, s.fname, s.lname
    FROM studentass sa
    JOIN student s ON sa.studid = s.stuid
    WHERE sa.classid = ?
    ORDER BY s.lname
");
$stmt->execute([$_TASK['classid']]);
$students = $stmt->fetchAll();
$today = date('Y-m-d');
?>

<div class="section">
    <h2>Take Attendance – <?= $today ?></h2>
    <form id="attendBulkForm">
        <input type="hidden" name="date" value="<?= $today ?>">
        <table>
            <tr><th>Name</th><th>Present</th><th>Absent</th></tr>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['fname'] . ' ' . $s['lname']) ?></td>
                    <td><input type="radio" name="status[<?= $s['stuid'] ?>]" value="1" checked></td>
                    <td><input type="radio" name="status[<?= $s['stuid'] ?>]" value="0"></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit" class="btn" style="margin-top:15px;">Save All</button>
    </form>
    <div id="attendMsg"></div>
</div>

<script>
document.getElementById('attendBulkForm').onsubmit = function(e) {
    e.preventDefault();
    const form = new FormData(this);
    form.append('action', 'bulk_attendance');
    fetch('teacher-action.php', { method: 'POST', body: form })
        .then(r => r.text())
        .then(msg => {
            document.getElementById('attendMsg').innerHTML = '<p class="success">' + msg + '</p>';
        });
};
</script>