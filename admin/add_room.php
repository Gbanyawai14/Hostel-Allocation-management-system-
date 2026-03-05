<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['ADMIN','WARDEN'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hid    = (int)$_POST['hostel_id'];
    $rtid   = (int)$_POST['room_type_id'];
    $rnum   = trim($_POST['room_number']);
    $stmt   = $db->prepare("INSERT INTO Room(hostel_id, room_type_id, room_number) VALUES(?,?,?)");
    $stmt->bind_param('iis', $hid, $rtid, $rnum);
    if ($stmt->execute()) {
        $msg = '<div class="alert success">✅ Room "' . htmlspecialchars($rnum) . '" added. Hostel room count updated by trigger.</div>';
    } else {
        $msg = '<div class="alert error">❌ ' . $db->error . '</div>';
    }
}

$hostels   = $db->query("SELECT * FROM Hostel ORDER BY hostel_name");
$roomtypes = $db->query("SELECT * FROM RoomType ORDER BY type_name");
$rooms     = $db->query(
    "SELECT R.room_id, R.room_number, H.hostel_name, RT.type_name, RT.capacity,
            R.occupied_count, RT.monthly_fee
     FROM Room R
     JOIN Hostel H ON R.hostel_id=H.hostel_id
     JOIN RoomType RT ON R.room_type_id=RT.room_type_id
     ORDER BY H.hostel_name, R.room_number"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Rooms</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>🚪 Manage Rooms</h1><p>Add and view all rooms</p></div>
  <?= $msg ?>
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Add New Room</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Hostel</label>
            <select name="hostel_id" required>
              <option value="">-- Select Hostel --</option>
              <?php while($h=$hostels->fetch_assoc()): ?>
              <option value="<?= $h['hostel_id'] ?>"><?= htmlspecialchars($h['hostel_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Room Type</label>
            <select name="room_type_id" required>
              <option value="">-- Select Type --</option>
              <?php while($rt=$roomtypes->fetch_assoc()): ?>
              <option value="<?= $rt['room_type_id'] ?>"><?= htmlspecialchars($rt['type_name']) ?> — Capacity: <?= $rt['capacity'] ?> — RM <?= number_format($rt['monthly_fee'],2) ?>/mo</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Room Number</label>
            <input type="text" name="room_number" placeholder="e.g. A201" required>
          </div>
          <button type="submit" class="btn">Add Room</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>Available Rooms (View)</h2></div>
      <?php
      $avail = $db->query("SELECT * FROM view_available_rooms");
      if ($avail->num_rows > 0):
      ?>
      <table>
        <thead><tr><th>Room</th><th>Hostel</th><th>Type</th><th>Free</th><th>Fee</th></tr></thead>
        <tbody>
        <?php while($r=$avail->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['room_number']) ?></td>
            <td><?= htmlspecialchars($r['hostel_name']) ?></td>
            <td><?= htmlspecialchars($r['type_name']) ?></td>
            <td><span class="badge badge-green"><?= $r['beds_available'] ?></span></td>
            <td>RM <?= number_format($r['monthly_fee'],2) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: echo '<div class="empty">No available rooms.</div>'; endif; ?>
    </div>
  </div>

  <div class="panel panel-full" style="margin-top:1.5rem">
    <div class="panel-header"><h2>All Rooms</h2></div>
    <table>
      <thead><tr><th>ID</th><th>Room No</th><th>Hostel</th><th>Type</th><th>Capacity</th><th>Occupied</th><th>Fee/Mo</th><th>Status</th></tr></thead>
      <tbody>
      <?php while($r=$rooms->fetch_assoc()): ?>
        <tr>
          <td style="font-family:'IBM Plex Mono',monospace;color:var(--muted)">#<?= $r['room_id'] ?></td>
          <td><strong><?= htmlspecialchars($r['room_number']) ?></strong></td>
          <td><?= htmlspecialchars($r['hostel_name']) ?></td>
          <td><?= htmlspecialchars($r['type_name']) ?></td>
          <td><?= $r['capacity'] ?></td>
          <td><?= $r['occupied_count'] ?>/<?= $r['capacity'] ?></td>
          <td>RM <?= number_format($r['monthly_fee'],2) ?></td>
          <td>
            <?php if ($r['occupied_count'] >= $r['capacity']): ?>
              <span class="badge badge-red">Full</span>
            <?php elseif ($r['occupied_count'] > 0): ?>
              <span class="badge badge-yellow">Partial</span>
            <?php else: ?>
              <span class="badge badge-green">Available</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
