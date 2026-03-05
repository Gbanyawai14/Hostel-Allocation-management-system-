<?php
// ============================================================
// admin/add_hostel.php
// ============================================================
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ../auth/login.php'); exit;
}
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['hostel_name']);
    $loc  = trim($_POST['location']);
    $stmt = $db->prepare("INSERT INTO Hostel(hostel_name, location) VALUES(?,?)");
    $stmt->bind_param('ss', $name, $loc);
    if ($stmt->execute()) {
        $msg = '<div class="alert success">✅ Hostel "' . htmlspecialchars($name) . '" added successfully.</div>';
    } else {
        $msg = '<div class="alert error">❌ Error: ' . $db->error . '</div>';
    }
}

$hostels = $db->query(
    "SELECT H.*, COUNT(R.room_id) AS room_count
     FROM Hostel H
     LEFT JOIN Room R ON H.hostel_id = R.hostel_id
     GROUP BY H.hostel_id
     ORDER BY H.hostel_id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Manage Hostels</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header">
    <h1>🏠 Manage Hostels</h1>
    <p>Add and view all registered hostels</p>
  </div>
  <?= $msg ?>
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Add New Hostel</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Hostel Name</label>
            <input type="text" name="hostel_name" placeholder="e.g. Al-Hikmah Block C" required>
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="e.g. West Campus">
          </div>
          <button type="submit" class="btn">Add Hostel</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>All Hostels</h2></div>
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Location</th><th>Rooms</th></tr></thead>
        <tbody>
        <?php while($h = $hostels->fetch_assoc()): ?>
          <tr>
            <td style="font-family:'IBM Plex Mono',monospace;color:var(--muted)">#<?= $h['hostel_id'] ?></td>
            <td><strong><?= htmlspecialchars($h['hostel_name']) ?></strong></td>
            <td style="color:var(--muted)"><?= htmlspecialchars($h['location']) ?></td>
            <td><span class="badge badge-blue"><?= $h['room_count'] ?> rooms</span></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
