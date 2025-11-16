<?php
session_start();

// --- Security ---
if (!isset($_SESSION['username']) || strtolower($_SESSION['status']) !== 'admin') {
    header("Location: login.html");
    exit;
}
$username = $_SESSION['username'];

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

// --- Get Admin Name ---
$stmt = $pdo->prepare("SELECT a.username FROM admin a WHERE a.adminid = (SELECT adminid FROM adminlogin WHERE username = ?)");
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$adminName = $admin['username'] ?? $username;

// --- Get Tasks ---
$tasks = $pdo->query("
    SELECT t.taskid, t.taskname, s.statusname 
    FROM userstatustask t
    LEFT JOIN createuserstatus s ON t.statusid = s.statusid
    ORDER BY t.taskid
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <style>
    :root {
      --green: #4CAF50;
      --light-green: #E8F5E9;
      --dark-green: #388E3C;
      --gray: #f4f4f4;
    }
    * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }
    body { background: var(--gray); display:flex; min-height:100vh; }

    .sidebar {
      width: 260px;
      background: var(--green);
      color: white;
      padding: 20px;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      overflow-y: auto;
    }
    .logo { text-align: center; margin-bottom: 30px; }
    .logo img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; }
    .admin-info { text-align: center; margin-bottom: 30px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; }
    .admin-info h3 { margin: 8px 0; font-size: 1.1rem; }
    .admin-info p { font-size: 0.9rem; opacity: 0.9; }

    .menu h4 { margin: 20px 0 10px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
    .menu ul { list-style: none; }
    .menu ul li { margin: 8px 0; }
    .menu ul li a {
      color: white; text-decoration: none; display: block; padding: 12px 15px;
      border-radius: 6px; transition: 0.3s; font-weight: 500; cursor: pointer;
    }
    .menu ul li a:hover, .menu ul li a.active {
      background: var(--dark-green); transform: translateX(5px);
    }
    .menu .status { float: right; font-size: 0.8rem; background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 4px; }

    .main {
      flex: 1; padding: 30px; display: flex; flex-direction: column;
    }
    .header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
    }
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
    .content-area.loading {
      opacity: 0.6; pointer-events: none;
    }
    .task-title { color: var(--green); font-size: 1.5rem; margin-bottom: 15px; border-bottom: 2px solid var(--light-green); padding-bottom: 8px; }
    .task-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-completed { background: #d4edda; color: #155724; }

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
      <img src="assets/logo.png" alt="School Logo">
    </div>
    <div class="admin-info">
      <h3><?= htmlspecialchars($adminName) ?></h3>
      <p>System Administrator</p>
    </div>

    <div class="menu">
      <h4>Tasks</h4>
      <ul>
        <?php foreach ($tasks as $task): ?>
          <li>
            <a class="task-link" data-id="<?= $task['taskid'] ?>">
              <?= htmlspecialchars($task['taskname']) ?>
              <?php if ($task['statusname']): ?>
                <span class="status"><?= htmlspecialchars($task['statusname']) ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
        <?php if (empty($tasks)): ?>
          <li><em>No tasks assigned.</em></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main">
    <div class="header">
      <h1>Admin Dashboard</h1>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="content-area" id="contentArea">
      <p><em>Select a task from the left to view details.</em></p>
    </div>
  </div>

  <script>
    document.querySelectorAll('.task-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const taskId = this.dataset.id;
        const contentArea = document.getElementById('contentArea');

        // Highlight active
        document.querySelectorAll('.task-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        // Show loading
        contentArea.classList.add('loading');
        contentArea.innerHTML = '<p>Loading task details...</p>';

        // Fetch via AJAX
        fetch(`task-loader.php?taskid=${taskId}`)
          .then(res => res.text())
          .then(html => {
            contentArea.innerHTML = html;
            contentArea.classList.remove('loading');
          })
          .catch(err => {
            contentArea.innerHTML = '<p style="color:red;">Error loading task.</p>';
            console.error(err);
            contentArea.classList.remove('loading');
          });
      });
    });

    // Optional: Load first task automatically
    // document.querySelector('.task-link')?.click();
  </script>

</body>
</html>