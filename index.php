<?php
// ============================================================
// index.php — Main Entry Point / Dashboard
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}
require_once 'config/db.php';
$db   = getDB();
$role = $_SESSION['role'];
$user = $_SESSION['username'];

// Dashboard Stats
$stats = [];
$stats['hostels']    = $db->query("SELECT COUNT(*) c FROM Hostel")->fetch_assoc()['c'];
$stats['rooms']      = $db->query("SELECT COUNT(*) c FROM Room")->fetch_assoc()['c'];
$stats['students']   = $db->query("SELECT COUNT(*) c FROM Student")->fetch_assoc()['c'];
$stats['active']     = $db->query("SELECT COUNT(*) c FROM Allocation WHERE status='ACTIVE'")->fetch_assoc()['c'];
$stats['revenue']    = $db->query("SELECT IFNULL(SUM(amount),0) c FROM Payment WHERE status='PAID'")->fetch_assoc()['c'];
$stats['pending_pay']= $db->query("SELECT COUNT(*) c FROM Payment WHERE status='PENDING'")->fetch_assoc()['c'];
$stats['maintenance']= $db->query("SELECT COUNT(*) c FROM MaintenanceRequest WHERE status='PENDING'")->fetch_assoc()['c'];

// Recent Audit Log
$audit = $db->query("SELECT * FROM AuditLog ORDER BY action_time DESC LIMIT 8");

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&family=IBM+Plex+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:      #0d1117;
    --surface: #161b22;
    --border:  #30363d;
    --accent:  #58a6ff;
    --green:   #3fb950;
    --yellow:  #d29922;
    --red:     #f85149;
    --purple:  #bc8cff;
    --text:    #e6edf3;
    --muted:   #8b949e;
  }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { background:var(--bg); color:var(--text); font-family:'IBM Plex Sans',sans-serif; display:flex; min-height:100vh; }

  /* SIDEBAR */
  .sidebar {
    width: 230px; min-height:100vh;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex; flex-direction:column;
    padding: 1.5rem 0;
    position: fixed; top:0; left:0; bottom:0;
  }
  .logo {
    font-family:'IBM Plex Mono',monospace;
    font-size:1.4rem; font-weight:700;
    color:var(--accent);
    padding: 0 1.5rem 1.5rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 1rem;
  }
  .logo small { display:block; font-size:.65rem; font-weight:400; color:var(--muted); margin-top:.15rem; }
  .nav-section { padding: .5rem 1rem .25rem; font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }
  .nav-item {
    display:flex; align-items:center; gap:.65rem;
    padding: .55rem 1.5rem;
    color:var(--muted); text-decoration:none;
    font-size:.88rem; transition: all .15s;
    border-left: 3px solid transparent;
  }
  .nav-item:hover, .nav-item.active {
    color:var(--text);
    background: rgba(88,166,255,.08);
    border-left-color: var(--accent);
  }
  .nav-item .icon { font-size:1rem; width:18px; text-align:center; }
  .sidebar-footer {
    margin-top:auto;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    font-size:.8rem;
  }
  .role-badge {
    display:inline-block;
    background:rgba(88,166,255,.15);
    color:var(--accent);
    border:1px solid rgba(88,166,255,.3);
    border-radius:4px;
    padding:.15rem .5rem;
    font-size:.7rem;
    font-family:'IBM Plex Mono',monospace;
  }
  .sidebar-footer .name { color:var(--text); font-weight:600; margin-bottom:.25rem; }
  .logout-btn {
    display:block; margin-top:.75rem; text-align:center;
    padding:.4rem; background:rgba(248,81,73,.1);
    color:var(--red); border:1px solid rgba(248,81,73,.3);
    border-radius:6px; text-decoration:none; font-size:.8rem;
    transition:.2s;
  }
  .logout-btn:hover { background:rgba(248,81,73,.2); }

  /* MAIN */
  .main { margin-left:230px; padding:2rem; flex:1; width:calc(100% - 230px); }
  .page-header { margin-bottom:1.75rem; }
  .page-header h1 { font-size:1.4rem; font-weight:700; }
  .page-header p { color:var(--muted); font-size:.87rem; margin-top:.25rem; }

  /* STAT CARDS */
  .stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:1rem; margin-bottom:2rem; }
  .stat-card {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:10px;
    padding:1.25rem;
    position:relative; overflow:hidden;
  }
  .stat-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background: var(--card-color, var(--accent));
  }
  .stat-label { font-size:.75rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.4rem; }
  .stat-value { font-size:2rem; font-weight:700; font-family:'IBM Plex Mono',monospace; color:var(--text); }
  .stat-sub { font-size:.75rem; color:var(--muted); margin-top:.25rem; }

  /* CONTENT GRID */
  .content-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
  @media(max-width:900px){ .content-grid{grid-template-columns:1fr;} }
  .panel {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:10px;
    overflow:hidden;
  }
  .panel-header {
    padding:1rem 1.25rem;
    border-bottom:1px solid var(--border);
    display:flex; justify-content:space-between; align-items:center;
  }
  .panel-header h2 { font-size:.95rem; font-weight:600; }
  .panel-header a { font-size:.78rem; color:var(--accent); text-decoration:none; }
  table { width:100%; border-collapse:collapse; font-size:.83rem; }
  th { padding:.65rem 1rem; text-align:left; color:var(--muted); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--border); }
  td { padding:.65rem 1rem; border-bottom:1px solid rgba(48,54,61,.6); }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background:rgba(88,166,255,.04); }
  .badge {
    display:inline-block; padding:.2rem .55rem;
    border-radius:4px; font-size:.72rem; font-family:'IBM Plex Mono',monospace;
  }
  .badge-green  { background:rgba(63,185,80,.15);  color:var(--green);  border:1px solid rgba(63,185,80,.3); }
  .badge-yellow { background:rgba(210,153,34,.15); color:var(--yellow); border:1px solid rgba(210,153,34,.3); }
  .badge-red    { background:rgba(248,81,73,.15);  color:var(--red);    border:1px solid rgba(248,81,73,.3); }
  .badge-blue   { background:rgba(88,166,255,.15); color:var(--accent); border:1px solid rgba(88,166,255,.3); }
  .empty { padding:2rem; text-align:center; color:var(--muted); font-size:.85rem; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="logo">HAMS <small>Hostel Management System</small></div>

  <div class="nav-section">Overview</div>
  <a href="index.php" class="nav-item active"><span class="icon">📊</span> Dashboard</a>

  <?php if ($role === 'ADMIN' || $role === 'WARDEN'): ?>
  <div class="nav-section">Management</div>
  <a href="admin/add_hostel.php"    class="nav-item"><span class="icon">🏠</span> Hostels</a>
  <a href="admin/add_room.php"      class="nav-item"><span class="icon">🚪</span> Rooms</a>
  <a href="admin/allocate_room.php" class="nav-item"><span class="icon">📋</span> Allocate Room</a>
  <a href="admin/checkout.php"      class="nav-item"><span class="icon">🚶</span> Checkout</a>
  <a href="admin/reports.php"       class="nav-item"><span class="icon">📈</span> Reports</a>
  <?php endif; ?>

  <div class="nav-section">Students</div>
  <?php if ($role === 'ADMIN'): ?>
  <a href="student/register.php"      class="nav-item"><span class="icon">👤</span> Register Student</a>
  <?php endif; ?>
  <a href="student/view_allocation.php" class="nav-item"><span class="icon">🔍</span> Allocations</a>
  <a href="student/payment.php"         class="nav-item"><span class="icon">💳</span> Payments</a>

  <div class="nav-section">Maintenance</div>
  <a href="maintenance/report_issue.php"   class="nav-item"><span class="icon">🔧</span> Report Issue</a>
  <a href="maintenance/view_requests.php"  class="nav-item"><span class="icon">📝</span> View Requests</a>

  <div class="sidebar-footer">
    <div class="name"><?= htmlspecialchars($user) ?></div>
    <span class="role-badge"><?= $role ?></span>
    <a href="auth/logout.php" class="logout-btn">Sign Out</a>
  </div>
</nav>

<!-- MAIN CONTENT -->
<main class="main">
  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user) ?> — <?= date('l, d F Y') ?></p>
  </div>

  <!-- STAT CARDS -->
  <div class="stats-grid">
    <div class="stat-card" style="--card-color:var(--accent)">
      <div class="stat-label">Hostels</div>
      <div class="stat-value"><?= $stats['hostels'] ?></div>
      <div class="stat-sub">Total registered</div>
    </div>
    <div class="stat-card" style="--card-color:var(--purple)">
      <div class="stat-label">Rooms</div>
      <div class="stat-value"><?= $stats['rooms'] ?></div>
      <div class="stat-sub">Total rooms</div>
    </div>
    <div class="stat-card" style="--card-color:var(--green)">
      <div class="stat-label">Students</div>
      <div class="stat-value"><?= $stats['students'] ?></div>
      <div class="stat-sub">Registered</div>
    </div>
    <div class="stat-card" style="--card-color:var(--yellow)">
      <div class="stat-label">Active Alloc.</div>
      <div class="stat-value"><?= $stats['active'] ?></div>
      <div class="stat-sub">Currently staying</div>
    </div>
    <div class="stat-card" style="--card-color:var(--green)">
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value" style="font-size:1.4rem">RM <?= number_format($stats['revenue'],2) ?></div>
      <div class="stat-sub">Paid payments</div>
    </div>
    <div class="stat-card" style="--card-color:var(--red)">
      <div class="stat-label">Pending Pay</div>
      <div class="stat-value"><?= $stats['pending_pay'] ?></div>
      <div class="stat-sub">Awaiting payment</div>
    </div>
    <div class="stat-card" style="--card-color:var(--yellow)">
      <div class="stat-label">Maintenance</div>
      <div class="stat-value"><?= $stats['maintenance'] ?></div>
      <div class="stat-sub">Pending requests</div>
    </div>
  </div>

  <!-- CONTENT PANELS -->
  <div class="content-grid">

    <!-- Available Rooms Panel -->
    <div class="panel">
      <div class="panel-header">
        <h2>Available Rooms</h2>
        <a href="admin/add_room.php">View All →</a>
      </div>
      <?php
      $db = getDB();
      $rooms = $db->query("SELECT * FROM view_available_rooms LIMIT 6");
      if ($rooms->num_rows > 0):
      ?>
      <table>
        <thead><tr><th>Room</th><th>Hostel</th><th>Type</th><th>Beds Free</th><th>Fee/Mo</th></tr></thead>
        <tbody>
        <?php while($r = $rooms->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['room_number']) ?></td>
            <td><?= htmlspecialchars($r['hostel_name']) ?></td>
            <td><?= htmlspecialchars($r['type_name']) ?></td>
            <td><span class="badge badge-green"><?= $r['beds_available'] ?> free</span></td>
            <td>RM <?= number_format($r['monthly_fee'],2) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty">No available rooms found.</div>
      <?php endif; ?>
    </div>

    <!-- Audit Log Panel -->
    <div class="panel">
      <div class="panel-header">
        <h2>Audit Log</h2>
        <a href="admin/reports.php">Full Log →</a>
      </div>
      <?php if ($audit->num_rows > 0): ?>
      <table>
        <thead><tr><th>Action</th><th>Table</th><th>Description</th><th>Time</th></tr></thead>
        <tbody>
        <?php while($a = $audit->fetch_assoc()): ?>
          <tr>
            <td>
              <?php
              $badgeClass = match($a['action_type']) {
                'INSERT'   => 'badge-green',
                'CHECKOUT' => 'badge-yellow',
                'DELETE'   => 'badge-red',
                default    => 'badge-blue'
              };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= $a['action_type'] ?></span>
            </td>
            <td style="color:var(--muted)"><?= $a['table_name'] ?></td>
            <td style="font-size:.78rem"><?= htmlspecialchars(substr($a['description'],0,40)) ?>…</td>
            <td style="color:var(--muted);font-size:.75rem;font-family:'IBM Plex Mono',monospace"><?= date('d/m H:i', strtotime($a['action_time'])) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty">No audit logs yet.</div>
      <?php endif;
      $db->close(); ?>
    </div>

  </div>
</main>
</body>
</html>
