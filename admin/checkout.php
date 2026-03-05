<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['ADMIN','WARDEN'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)$_POST['student_id'];
    $db->query("SET @result = ''");
    $db->query("CALL proc_checkout_student($sid, @result)");
    $res = $db->query("SELECT @result AS r")->fetch_assoc()['r'];

    if (str_starts_with($res, 'SUCCESS')) {
        $msg = '<div class="alert success">✅ ' . htmlspecialchars($res) . '</div>';
    } else {
        $msg = '<div class="alert error">❌ ' . htmlspecialchars($res) . '</div>';
    }
}

$active   = $db->query("SELECT * FROM view_student_details ORDER BY full_name");
$checkout = $db->query(
    "SELECT S.full_name, S.email, R.room_number, H.hostel_name,
            A.check_in_date, A.check_out_date
     FROM Allocation A
     JOIN Student S ON A.student_id = S.student_id
     JOIN Room R ON A.room_id = R.room_id
     JOIN Hostel H ON R.hostel_id = H.hostel_id
     WHERE A.status='CHECKED_OUT'
     ORDER BY A.check_out_date DESC LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Checkout</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>🚶 Student Checkout</h1><p>Process student checkout using stored procedure with transaction</p></div>
  <?= $msg ?>
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Checkout Student (proc_checkout_student)</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Select Active Student</label>
            <select name="student_id" required>
              <option value="">-- Select Student --</option>
              <?php
              // Reload active students
              $activeS = $db->query("SELECT * FROM view_student_details ORDER BY full_name");
              while($s=$activeS->fetch_assoc()):
              ?>
              <option value="<?= $s['student_id'] ?>">
                <?= htmlspecialchars($s['full_name']) ?> — Room <?= htmlspecialchars($s['room_number']) ?> (<?= htmlspecialchars($s['hostel_name']) ?>)
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-danger">Process Checkout</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>Active Allocations</h2></div>
      <?php if ($active->num_rows > 0): ?>
      <table>
        <thead><tr><th>Student</th><th>Room</th><th>Hostel</th><th>Paid</th></tr></thead>
        <tbody>
        <?php while($a=$active->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($a['room_number']) ?></span></td>
            <td><?= htmlspecialchars($a['hostel_name']) ?></td>
            <td style="color:var(--green)">RM <?= number_format($a['total_paid'],2) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: echo '<div class="empty">No active allocations.</div>'; endif; ?>
    </div>

    <div class="panel panel-full">
      <div class="panel-header"><h2>Recent Checkouts</h2></div>
      <?php if ($checkout->num_rows > 0): ?>
      <table>
        <thead><tr><th>Student</th><th>Room</th><th>Hostel</th><th>Check In</th><th>Check Out</th></tr></thead>
        <tbody>
        <?php while($c=$checkout->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($c['full_name']) ?></td>
            <td><?= htmlspecialchars($c['room_number']) ?></td>
            <td><?= htmlspecialchars($c['hostel_name']) ?></td>
            <td style="color:var(--muted)"><?= $c['check_in_date'] ?></td>
            <td><span class="badge badge-yellow"><?= $c['check_out_date'] ?></span></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: echo '<div class="empty">No checkouts yet.</div>'; endif; ?>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
