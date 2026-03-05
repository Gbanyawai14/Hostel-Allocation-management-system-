<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';
$db = getDB();
$msg = '';

// Update status if admin/warden
if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($_SESSION['role'],['ADMIN','WARDEN'])) {
    $rid    = (int)$_POST['request_id'];
    $status = $_POST['new_status'];
    $db->query("UPDATE MaintenanceRequest SET status='$status' WHERE request_id=$rid");
    $msg = '<div class="alert success">✅ Request #' . $rid . ' updated to ' . htmlspecialchars($status) . '</div>';
}

$requests = $db->query("SELECT * FROM view_pending_maintenance");
$all      = $db->query(
    "SELECT MR.request_id, S.full_name, R.room_number, H.hostel_name,
            MR.issue_details, MR.status, MR.request_date
     FROM MaintenanceRequest MR
     JOIN Student S ON MR.student_id=S.student_id
     JOIN Room R ON MR.room_id=R.room_id
     JOIN Hostel H ON R.hostel_id=H.hostel_id
     ORDER BY MR.request_date DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Maintenance Requests</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>📝 Maintenance Requests</h1><p>View and manage all maintenance requests — uses view_pending_maintenance</p></div>
  <?= $msg ?>

  <div class="panel">
    <div class="panel-header"><h2>All Requests (with JSON_EXTRACT)</h2></div>
    <table>
      <thead><tr><th>ID</th><th>Student</th><th>Room</th><th>Hostel</th><th>Category</th><th>Priority</th><th>Description</th><th>Status</th><th>Date</th>
        <?php if(in_array($_SESSION['role'],['ADMIN','WARDEN'])): ?><th>Update</th><?php endif; ?>
      </tr></thead>
      <tbody>
      <?php if ($all->num_rows > 0):
        while($r=$all->fetch_assoc()):
          $details  = json_decode($r['issue_details'], true);
          $cat      = $details['category'] ?? 'N/A';
          $desc     = $details['description'] ?? '';
          $pri      = $details['priority'] ?? '';
          $priClass = match(strtolower($pri)) { 'high'=>'badge-red','medium'=>'badge-yellow', default=>'badge-green' };
          $stClass  = match($r['status']) { 'COMPLETED'=>'badge-green','IN_PROGRESS'=>'badge-yellow', default=>'badge-red' };
      ?>
        <tr>
          <td style="font-family:'IBM Plex Mono',monospace;color:var(--muted)">#<?= $r['request_id'] ?></td>
          <td><?= htmlspecialchars($r['full_name']) ?></td>
          <td><?= htmlspecialchars($r['room_number']) ?></td>
          <td><?= htmlspecialchars($r['hostel_name']) ?></td>
          <td><?= htmlspecialchars($cat) ?></td>
          <td><span class="badge <?= $priClass ?>"><?= htmlspecialchars($pri) ?></span></td>
          <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars(substr($desc,0,50)) ?><?= strlen($desc)>50?'…':'' ?></td>
          <td><span class="badge <?= $stClass ?>"><?= $r['status'] ?></span></td>
          <td style="font-size:.78rem;color:var(--muted)"><?= $r['request_date'] ?></td>
          <?php if(in_array($_SESSION['role'],['ADMIN','WARDEN'])): ?>
          <td>
            <form method="POST" style="display:flex;gap:.4rem;align-items:center">
              <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
              <select name="new_status" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:.25rem .4rem;border-radius:4px;font-size:.75rem">
                <option value="PENDING"     <?= $r['status']==='PENDING'?'selected':'' ?>>PENDING</option>
                <option value="IN_PROGRESS" <?= $r['status']==='IN_PROGRESS'?'selected':'' ?>>IN_PROGRESS</option>
                <option value="COMPLETED"   <?= $r['status']==='COMPLETED'?'selected':'' ?>>COMPLETED</option>
              </select>
              <button type="submit" style="background:var(--accent);color:#000;border:none;border-radius:4px;padding:.25rem .6rem;font-size:.75rem;cursor:pointer">✓</button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endwhile;
      else: echo '<tr><td colspan="10" class="empty">No maintenance requests.</td></tr>'; endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
