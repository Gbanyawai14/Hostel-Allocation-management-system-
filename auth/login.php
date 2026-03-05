 <?php
// ============================================================
// auth/login.php
// ============================================================
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';
    $db   = getDB();
    $user = trim($_POST['username']);
    $pass = md5(trim($_POST['password']));

    $stmt = $db->prepare(
        "SELECT user_id, username, role FROM UserAccount
         WHERE username = ? AND password = ? LIMIT 1"
    );
    $stmt->bind_param('ss', $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id']  = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];
        header('Location: ../index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Login</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&family=IBM+Plex+Sans:wght@300;400;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:      #0d1117;
    --surface: #161b22;
    --border:  #30363d;
    --accent:  #58a6ff;
    --green:   #3fb950;
    --text:    #e6edf3;
    --muted:   #8b949e;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2.5rem;
    width: 100%;
    max-width: 400px;
  }
  .logo {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 0.25rem;
  }
  .subtitle { color: var(--muted); font-size: 0.85rem; margin-bottom: 2rem; }
  label { display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 0.4rem; }
  input {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text);
    padding: 0.65rem 0.9rem;
    font-size: 0.95rem;
    margin-bottom: 1.1rem;
    font-family: inherit;
    transition: border-color .2s;
  }
  input:focus { outline: none; border-color: var(--accent); }
  .btn {
    width: 100%;
    background: var(--accent);
    color: #000;
    border: none;
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
  }
  .btn:hover { opacity: .85; }
  .error {
    background: rgba(248,81,73,.12);
    border: 1px solid #f8514940;
    border-radius: 6px;
    padding: .65rem .9rem;
    color: #f85149;
    font-size: .85rem;
    margin-bottom: 1rem;
  }
  .hint {
    margin-top: 1.5rem;
    font-size: .78rem;
    color: var(--muted);
    font-family: 'IBM Plex Mono', monospace;
  }
  .hint span { color: var(--green); }
</style>
</head>
<body>
<div class="card">
  <div class="logo">HAMS</div>
  <div class="subtitle">Hostel Allocation &amp; Management System</div>

  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Username</label>
    <input type="text" name="username" placeholder="Enter username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit" class="btn">Sign In</button>
  </form>

  <div class="hint">
    Default accounts:<br>
    admin / <span>admin123</span><br>
    warden1 / <span>warden123</span><br>
    student001 / <span>student123</span>
  </div>
</div>
</body>
</html>
