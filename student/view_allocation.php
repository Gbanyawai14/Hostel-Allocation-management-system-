<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';
$db = getDB();

$active   = $db->query("SELECT * FROM view_student_details ORDER BY hostel_name, room_number");
$checkout = $db->query(
     "SELECT S.full_name, S.email, R.room_number, H.hostel_name,
            A.check_in_date, A.check_out_date
     FROM Allocation A
     JOIN Student S ON A.student_id=S.student_id
     JOIN Room R ON A.room_id=R.room_id
     JOIN Hostel H ON R.hostel_id=H.hostel_id
     WHERE A.status='CHECKED_OUT' ORDER BY A.check_out_date DESC LIMIT 20"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — View Allocations</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>🔍 Allocations</h1><p>Active and past student room allocations (using view_student_details)</p></div>

  <div class="panel" style="margin-bottom:1.5rem">
    <div class="panel-header"><h2>Active Allocations — view_student_details</h2></div>
    <table>
      <thead><tr><th>Student</th><th>Email</th><th>Dept</th><th>Hostel</th><th>Room</th><th>Type</th><th>Fee</th><th>Total Paid</th><th>Since</th></tr></thead>
      <tbody>
      <?php if ($active->num_rows > 0):
        while($a=$active->fetch_assoc()): ?>
        <tr>
          <td><strong><?= htmlspecialchars($a['full_name']) ?></strong></td>
          <td style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars($a['email']) ?></td>
          <td style="font-size:.8rem"><?= htmlspecialchars($a['department']) ?></td>
          <td><?= htmlspecialchars($a['hostel_name']) ?></td>
          <td><span class="badge badge-blue"><?= htmlspecialchars($a['room_number']) ?></span></td>
          <td><?= htmlspecialchars($a['type_name']) ?></td>
          <td>RM <?= number_format($a['monthly_fee'],2) ?></td>
          <td style="color:var(--green);font-weight:700">RM <?= number_format($a['total_paid'],2) ?></td>
          <td style="color:var(--muted);font-size:.8rem"><?= $a['check_in_date'] ?></td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="9" class="empty">No active allocations.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="panel">
    <div class="panel-header"><h2>Checkout History</h2></div>
    <table>
      <thead><tr><th>Student</th><th>Email</th><th>Room</th><th>Hostel</th><th>Check In</th><th>Check Out</th></tr></thead>
      <tbody>
      <?php if ($checkout->num_rows > 0):
        while($c=$checkout->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($c['full_name']) ?></td>
          <td style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['room_number']) ?></td>
          <td><?= htmlspecialchars($c['hostel_name']) ?></td>
          <td style="color:var(--muted)"><?= $c['check_in_date'] ?></td>
          <td><span class="badge badge-yellow"><?= $c['check_out_date'] ?></span></td>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="6" class="empty">No checkout history.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
