<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['ADMIN','WARDEN'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../config/db.php';
$db = getDB();

// QUERY 1: Monthly Revenue
$monthly = $db->query(
    "SELECT MONTHNAME(payment_date) AS month, YEAR(payment_date) AS yr,
            COUNT(*) AS txns, SUM(amount) AS revenue
     FROM Payment WHERE status='PAID'
     GROUP BY YEAR(payment_date), MONTH(payment_date)
     ORDER BY yr DESC, MONTH(payment_date) DESC"
);

// QUERY 2: Occupancy per Hostel (using function)
$occupancy = $db->query(
    "SELECT H.hostel_name, H.location,
            COUNT(R.room_id) AS total_rooms,
            SUM(R.occupied_count) AS occupied,
            fn_occupancy_rate(H.hostel_id) AS occ_pct
     FROM Hostel H JOIN Room R ON H.hostel_id=R.hostel_id
     GROUP BY H.hostel_id"
);

// QUERY 3: Maintenance by Category (JSON)
$maint = $db->query(
    "SELECT JSON_UNQUOTE(JSON_EXTRACT(issue_details,'$.category')) AS category,
            JSON_UNQUOTE(JSON_EXTRACT(issue_details,'$.priority')) AS priority,
            COUNT(*) AS total,
            SUM(CASE WHEN status='COMPLETED' THEN 1 ELSE 0 END) AS done,
            SUM(CASE WHEN status='PENDING'   THEN 1 ELSE 0 END) AS pending
     FROM MaintenanceRequest
     GROUP BY category, priority ORDER BY total DESC"
);

// QUERY 4: Students with highest payment (subquery)
$topPay = $db->query(
    "SELECT S.full_name, S.email, fn_total_paid(S.student_id) AS total_paid
     FROM Student S
     ORDER BY total_paid DESC LIMIT 5"
);

// QUERY 5: Hostel-wise student distribution
$dist = $db->query(
    "SELECT H.hostel_name,
            COUNT(DISTINCT A.student_id) AS total_students,
            COUNT(DISTINCT R.room_id) AS occupied_rooms
     FROM Hostel H
     JOIN Room R ON H.hostel_id=R.hostel_id
     JOIN Allocation A ON R.room_id=A.room_id AND A.status='ACTIVE'
     GROUP BY H.hostel_name"
);

// QUERY 6: Full Audit Log
$audit = $db->query("SELECT * FROM AuditLog ORDER BY action_time DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Analytics Reports</title>
<?php include '../assets/css/style_inline.php'; ?>
<style>
.section-title { font-size:1rem; font-weight:700; margin:1.75rem 0 .75rem; color:var(--text); display:flex; align-items:center; gap:.5rem; }
.section-title::after { content:''; flex:1; height:1px; background:var(--border); }
.progress-bar { height:6px; background:var(--border); border-radius:3px; overflow:hidden; margin-top:.35rem; }
.progress-fill { height:100%; background:var(--accent); border-radius:3px; transition:width .8s ease; }
</style>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>📈 Analytics & Reports</h1><p>Complex SQL analytical queries — Week 13 demonstration</p></div>

  <!-- Monthly Revenue -->
  <div class="section-title">📅 Monthly Revenue (GROUP BY + AGGREGATE)</div>
  <div class="panel">
    <table>
      <thead><tr><th>Month</th><th>Year</th><th>Transactions</th><th>Total Revenue</th></tr></thead>
      <tbody>
      <?php if ($monthly->num_rows > 0):
        while($r=$monthly->fetch_assoc()): ?>
        <tr>
          <td><?= $r['month'] ?></td>
          <td style="color:var(--muted)"><?= $r['yr'] ?></td>
          <td><span class="badge badge-blue"><?= $r['txns'] ?></span></td>
          <td style="color:var(--green);font-family:'IBM Plex Mono',monospace;font-weight:700">RM <?= number_format($r['revenue'],2) ?></td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="4" class="empty">No payment data yet.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Occupancy Rate -->
  <div class="section-title">🏠 Hostel Occupancy Rate (fn_occupancy_rate function)</div>
  <div class="panel">
    <table>
      <thead><tr><th>Hostel</th><th>Location</th><th>Total Rooms</th><th>Occupied</th><th>Occupancy %</th></tr></thead>
      <tbody>
      <?php if ($occupancy->num_rows > 0):
        while($r=$occupancy->fetch_assoc()): ?>
        <tr>
          <td><strong><?= htmlspecialchars($r['hostel_name']) ?></strong></td>
          <td style="color:var(--muted)"><?= htmlspecialchars($r['location']) ?></td>
          <td><?= $r['total_rooms'] ?></td>
          <td><?= $r['occupied'] ?></td>
          <td>
            <span style="font-family:'IBM Plex Mono',monospace"><?= number_format($r['occ_pct'],1) ?>%</span>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= min(100,$r['occ_pct']) ?>%"></div></div>
          </td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="5" class="empty">No data.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="content-grid">
    <!-- Top Paying Students -->
    <div class="panel">
      <div class="panel-header"><h2>Top Paying Students (fn_total_paid + Subquery)</h2></div>
      <table>
        <thead><tr><th>#</th><th>Student</th><th>Total Paid</th></tr></thead>
        <tbody>
        <?php $rank=1;
        if ($topPay->num_rows > 0):
          while($r=$topPay->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--muted);font-family:'IBM Plex Mono',monospace"><?= $rank++ ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td style="color:var(--green);font-weight:700">RM <?= number_format($r['total_paid'],2) ?></td>
          </tr>
        <?php endwhile;
        else: echo '<tr><td colspan="3" class="empty">No data.</td></tr>'; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Hostel Distribution -->
    <div class="panel">
      <div class="panel-header"><h2>Student Distribution Per Hostel</h2></div>
      <table>
        <thead><tr><th>Hostel</th><th>Students</th><th>Rooms Occupied</th></tr></thead>
        <tbody>
        <?php if ($dist->num_rows > 0):
          while($r=$dist->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['hostel_name']) ?></td>
            <td><span class="badge badge-green"><?= $r['total_students'] ?></span></td>
            <td><span class="badge badge-blue"><?= $r['occupied_rooms'] ?></span></td>
          </tr>
        <?php endwhile;
        else: echo '<tr><td colspan="3" class="empty">No active allocations.</td></tr>'; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Maintenance Categories (JSON) -->
  <div class="section-title">🔧 Maintenance by Category (JSON_EXTRACT)</div>
  <div class="panel">
    <table>
      <thead><tr><th>Category</th><th>Priority</th><th>Total</th><th>Completed</th><th>Pending</th></tr></thead>
      <tbody>
      <?php if ($maint->num_rows > 0):
        while($r=$maint->fetch_assoc()):
          $priClass = match(strtolower($r['priority']??'')) { 'high'=>'badge-red','medium'=>'badge-yellow', default=>'badge-green' };
      ?>
        <tr>
          <td><?= htmlspecialchars($r['category']) ?></td>
          <td><span class="badge <?= $priClass ?>"><?= htmlspecialchars($r['priority']) ?></span></td>
          <td><?= $r['total'] ?></td>
          <td style="color:var(--green)"><?= $r['done'] ?></td>
          <td style="color:var(--yellow)"><?= $r['pending'] ?></td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="5" class="empty">No maintenance data.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Audit Log -->
  <div class="section-title">🔍 Full Audit Log (Trigger-generated)</div>
  <div class="panel">
    <table>
      <thead><tr><th>ID</th><th>Table</th><th>Action</th><th>Description</th><th>Time</th></tr></thead>
      <tbody>
      <?php if ($audit->num_rows > 0):
        while($a=$audit->fetch_assoc()):
          $bc = match($a['action_type']) { 'INSERT'=>'badge-green','CHECKOUT'=>'badge-yellow','DELETE'=>'badge-red', default=>'badge-blue' };
      ?>
        <tr>
          <td style="color:var(--muted);font-family:'IBM Plex Mono',monospace"><?= $a['audit_id'] ?></td>
          <td style="color:var(--muted)"><?= $a['table_name'] ?></td>
          <td><span class="badge <?= $bc ?>"><?= $a['action_type'] ?></span></td>
          <td style="font-size:.8rem"><?= htmlspecialchars($a['description']) ?></td>
          <td style="color:var(--muted);font-size:.75rem;font-family:'IBM Plex Mono',monospace"><?= date('d/m/y H:i',strtotime($a['action_time'])) ?></td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="5" class="empty">No logs yet.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
