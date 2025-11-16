<?php
session_start();

// --- Security: Only student ---
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'student') {
    header("Location: login.html");
    exit;
}
$username = $_SESSION['username'];

// ===== DATABASE CONNECTION =====
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

// --- Get Student Info (using correct column names & joins) ---
$stmt = $pdo->prepare("
    SELECT 
        s.id AS student_id,
        s.fname, 
        s.lname, 
        s.stuid, 
        c.classname AS classid,
        NULL AS pixpath
    FROM users u
    JOIN students s ON u.id = s.user_id
    LEFT JOIN student_assignments sa ON s.id = sa.student_id 
        AND sa.session = (SELECT MAX(session) FROM student_assignments WHERE student_id = s.id)
    LEFT JOIN classes c ON sa.class_id = c.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found. Check if '$username' exists in `users` and is linked to `students` via `user_id`.");
}

$fullName = $student['fname'] . ' ' . $student['lname'];
$class = $student['classid'] ?? 'Not Assigned';
$photo = 'assets/default-avatar.png'; // No pix table
$student_id = $student['student_id']; // Now safely defined

// --- Get Recent Grades (using student_id) ---
$grades = $pdo->prepare("
    SELECT 
        sub.subname AS subject, 
        ar.score, 
        ar.term, 
        ar.session 
    FROM academic_records ar
    JOIN subjects sub ON ar.subject_id = sub.id
    WHERE ar.student_id = ? 
    ORDER BY ar.session DESC, ar.term DESC 
    LIMIT 5
");
$grades->execute([$student_id]);
$recentGrades = $grades->fetchAll(PDO::FETCH_ASSOC);

// --- Get Student Tasks (from tasks + role_tasks) ---
$tasks = $pdo->query("
    SELECT t.taskid, t.taskname 
    FROM tasks t
    JOIN role_tasks rt ON t.id = rt.task_id
    WHERE rt.role = 'student'
    ORDER BY t.taskid
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Portal</title>
  <style>
    :root {
      --green: #4CAF50;
      --light-green: #E8F5E9;
      --dark-green: #388E3C;
      --gray: #f9f9f9;
    }
    * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: var(--gray); display:flex; min-height:100vh; }

    .sidebar {
      width: 260px; background: var(--green); color: white; padding: 20px;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1); overflow-y: auto;
    }
    .logo { text-align: center; margin-bottom: 20px; }
    .logo img { width: 70px; height: 70px; border-radius: 50%; border: 3px solid white; }
    
    .student-info {
      text-align: center; margin-bottom: 30px; padding: 15px;
      background: rgba(255,255,255,0.1); border-radius: 8px;
    }
    .student-info img {
      width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px; object-fit: cover;
    }
    .student-info h3 { font-size: 1.1rem; margin: 8px 0; }
    .student-info p { font-size: 0.9rem; opacity: 0.9; }

    .menu h4 { margin: 20px 0 10px; font-size: 0.95rem; text-transform: uppercase; opacity: 0.8; }
    .menu ul { list-style: none; }
    .menu ul li { margin: 8px 0; }
    .menu ul li a {
      color: white; text-decoration: none; display: block; padding: 12px 15px;
      border-radius: 6px; transition: 0.3s; font-weight: 500; cursor: pointer;
    }
    .menu ul li a:hover, .menu ul li a.active {
      background: var(--dark-green); transform: translateX(5px);
    }

    .main { flex: 1; padding: 30px; display: flex; flex-direction: column; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .header h1 { color: var(--dark-green); }
    .logout-btn {
      background: #d32f2f; color: white; padding: 10px 20px; border: none;
      border-radius: 6px; text-decoration: none; font-weight: bold;
    }
    .logout-btn:hover { background: #b71c1c; }

    .content-area {
      flex: 1; background: white; border-radius: 10px; padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-y: auto;
      transition: all 0.4s ease;
    }
    .content-area.loading { opacity: 0.6; pointer-events: none; }

    .section { margin-bottom: 30px; }
    .section h2 {
      color: var(--green); font-size: 1.4rem; margin-bottom: 15px;
      border-bottom: 2px solid var(--light-green); padding-bottom: 8px;
    }
    table {
      width: 100%; border-collapse: collapse; margin-top: 10px;
    }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: var(--light-green); color: var(--dark-green); }

    @media (max-width: 768px) {
      body { flex-direction: column; }
      .sidebar { width: 100%; padding: 15px; }
      .main { padding: 15px; }
    }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="logo">
      <img src="img/EBC.jpg" alt="School Logo">
    </div>
    <div class="student-info">
      <img src="<?= htmlspecialchars($photo) ?>" alt="Profile">
      <h3><?= htmlspecialchars($fullName) ?></h3>
      <p>Class: <?= htmlspecialchars($class) ?></p>
    </div>

    <div class="menu">
      <h4>Menu</h4>
      <ul>
        <li><a class="task-link" data-id="profile">View Profile</a></li>
        <li><a class="task-link" data-id="results">View Results</a></li>
        <li><a class="task-link" data-id="assignments">View Assignments</a></li>
        <li><a class="task-link" data-id="payment">Make Payment</a></li>
        <?php foreach ($tasks as $task): ?>
          <li><a class="task-link" data-id="task_<?= $task['taskid'] ?>">
            <?= htmlspecialchars($task['taskname']) ?>
          </a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main">
    <div class="header">
      <h1>Student Portal</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="content-area" id="contentArea">
      <p><em>Select an option from the left menu.</em></p>
    </div>
  </div>

  <script>
    document.querySelectorAll('.task-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const contentArea = document.getElementById('contentArea');

        document.querySelectorAll('.task-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        contentArea.classList.add('loading');
        contentArea.innerHTML = '<p>Loading...</p>';

        const url = id.startsWith('task_') 
          ? `student-task-loader.php?taskid=${id.split('_')[1]}`
          : `student-content.php?view=${id}&student_id=<?= $student_id ?>&username=<?= urlencode($username) ?>`;

        fetch(url)
          .then(res => res.text())
          .then(html => {
            contentArea.innerHTML = html;
            contentArea.classList.remove('loading');
          })
          .catch(() => {
            contentArea.innerHTML = '<p style="color:red;">Error loading content.</p>';
            contentArea.classList.remove('loading');
          });
      });
    });

    // Auto-load Profile on start
    document.querySelector('.task-link[data-id="profile"]')?.click();
  </script>

</body>
</html>