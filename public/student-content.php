<?php
session_start();

// Security
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'student') {
    die("Access denied.");
}

$view = $_GET['view'] ?? '';
$student_id = $_GET['student_id'] ?? '';
$username = $_SESSION['username'];

// Database
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

if ($view === 'profile') {
    // Get student info
    $stmt = $pdo->prepare("
        SELECT s.fname, s.lname, s.stuid, s.gender, s.dob, c.classname
        FROM users u
        JOIN students s ON u.id = s.user_id
        LEFT JOIN student_assignments sa ON s.id = sa.student_id AND sa.session = (SELECT MAX(session) FROM student_assignments WHERE student_id = s.id)
        LEFT JOIN classes c ON sa.class_id = c.id
        WHERE u.username = ?
    ");
    $stmt->execute([$username]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="section">
        <h2>Profile</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($info['fname'] . ' ' . $info['lname']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($info['stuid']) ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($info['gender'] ?? 'Not Set') ?></p>
        <p><strong>Date of Birth:</strong> <?= htmlspecialchars($info['dob'] ?? 'Not Set') ?></p>
        <p><strong>Class:</strong> <?= htmlspecialchars($info['classname'] ?? 'Not Assigned') ?></p>
    </div>
    <?php
} elseif ($view === 'results') {
    // Get results
    $stmt = $pdo->prepare("
        SELECT sub.subname, ar.score, ar.term, ar.session
        FROM academic_records ar
        JOIN subjects sub ON ar.subject_id = sub.id
        WHERE ar.student_id = ?
        ORDER BY ar.session DESC, ar.term DESC
    ");
    $stmt->execute([$student_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="section">
        <h2>Academic Results</h2>
        <?php if (empty($results)): ?>
            <p>No results found.</p>
        <?php else: ?>
            <table>
                <tr><th>Subject</th><th>Score</th><th>Term</th><th>Session</th></tr>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['subname']) ?></td>
                        <td><?= htmlspecialchars($r['score']) ?></td>
                        <td><?= htmlspecialchars($r['term']) ?></td>
                        <td><?= htmlspecialchars($r['session']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php
} else {
    echo "<p>Feature coming soon.</p>";
}
?>