<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_SESSION['role'],['ADMIN','WARDEN'])) {
    $sid    = (int)$_POST['student_id'];
    $amt    = (float)$_POST['amount'];
    $mid    = (int)$_POST['method_id'];
    $status = $_POST['status'];

    $db->query("CALL proc_add_payment($sid, $amt, $mid, '$status')");
    if ($db->error) {
        $msg = '<div class="alert error">❌ ' . $db->error . '</div>';
    } else {
        $msg = '<div class="alert success">✅ Payment of RM ' . number_format($amt,2) . ' recorded successfully.</div>';
    }
}

$students = $db->query("SELECT * FROM Student ORDER BY full_name");
$methods  = $db->query("SELECT * FROM PaymentMethod");
$payments = $db->query("SELECT * FROM view_payment_overview LIMIT 30");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Payments</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>💳 Payment Management</h1><p>Record and track student hostel payments</p></div>
  <?= $msg ?>
  <div class="content-grid">
    <?php if (in_array($_SESSION['role'],['ADMIN','WARDEN'])): ?>
    <div class="panel">
      <div class="panel-header"><h2>Record Payment (proc_add_payment)</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Student</label>
            <select name="student_id" required>
              <option value="">-- Select Student --</option>
              <?php while($s=$students->fetch_assoc()): ?>
              <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Amount (RM)</label>
            <input type="number" name="amount" step="0.01" min="0" placeholder="250.00" required>
          </div>
          <div class="form-group">
            <label>Payment Method</label>
            <select name="method_id" required>
              <option value="">-- Select Method --</option>
              <?php while($m=$methods->fetch_assoc()): ?>
              <option value="<?= $m['method_id'] ?>"><?= htmlspecialchars($m['method_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <option value="PAID">PAID</option>
              <option value="PENDING">PENDING</option>
            </select>
          </div>
          <button type="submit" class="btn btn-green">Record Payment</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel-header"><h2>Payment Overview (View)</h2></div>
      <table>
        <thead><tr><th>Student</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($payments->num_rows > 0):
          while($p=$payments->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($p['full_name']) ?></td>
            <td style="font-family:'IBM Plex Mono',monospace;font-weight:700">RM <?= number_format($p['amount'],2) ?></td>
            <td style="color:var(--muted);font-size:.8rem"><?= $p['payment_date'] ?></td>
            <td style="font-size:.8rem"><?= htmlspecialchars($p['method_name']) ?></td>
            <td>
              <?php if($p['status']==='PAID'): ?>
                <span class="badge badge-green">PAID</span>
              <?php else: ?>
                <span class="badge badge-red">PENDING</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile;
        else: echo '<tr><td colspan="5" class="empty">No payments recorded.</td></tr>'; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
