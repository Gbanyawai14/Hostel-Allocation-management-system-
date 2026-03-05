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
    $rid = (int)$_POST['room_id'];

    // Call stored procedure with OUT parameter
    $db->query("SET @result = ''");
    $db->query("CALL proc_allocate_room($sid, $rid, @result)");
    $res = $db->query("SELECT @result AS r")->fetch_assoc()['r'];

    if (str_starts_with($res, 'SUCCESS')) {
        $msg = '<div class="alert success">✅ ' . htmlspecialchars($res) . '</div>';
    } else {
        $msg = '<div class="alert error">❌ ' . htmlspecialchars($res) . '</div>';
    }
}

$students = $db->query(
    "SELECT S.student_id, S.full_name, S.email
     FROM Student S
     WHERE S.student_id NOT IN (
         SELECT student_id FROM Allocation WHERE status='ACTIVE'
     ) ORDER BY S.full_name"
);
$rooms = $db->query("SELECT * FROM view_available_rooms ORDER BY hostel_name, room_number");
$active = $db->query("SELECT * FROM view_student_details ORDER BY hostel_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Allocate Room</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>📋 Room Allocation</h1><p>Assign a room to a student using stored procedure with transaction</p></div>
  <?= $msg ?>

  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Allocate Room (proc_allocate_room)</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Select Student (unallocated only)</label>
            <select name="student_id" required>
              <option value="">-- Select Student --</option>
              <?php while($s=$students->fetch_assoc()): ?>
              <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?> — <?= htmlspecialchars($s['email']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Select Available Room</label>
            <select name="room_id" required>
              <option value="">-- Select Room --</option>
              <?php while($r=$rooms->fetch_assoc()): ?>
              <option value="<?= $r['room_id'] ?>"><?= htmlspecialchars($r['room_number']) ?> — <?= htmlspecialchars($r['hostel_name']) ?> — <?= htmlspecialchars($r['type_name']) ?> — <?= $r['beds_available'] ?> beds free</option>
              <?php endwhile; ?>
            </select>
          </div>
          <button type="submit" class="btn">Allocate Room</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>Currently Allocated Students</h2></div>
      <?php if ($active->num_rows > 0): ?>
      <table>
        <thead><tr><th>Student</th><th>Room</th><th>Hostel</th><th>Since</th></tr></thead>
        <tbody>
        <?php while($a=$active->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($a['room_number']) ?></span></td>
            <td><?= htmlspecialchars($a['hostel_name']) ?></td>
            <td style="color:var(--muted);font-size:.8rem"><?= $a['check_in_date'] ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: echo '<div class="empty">No active allocations.</div>'; endif; ?>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
