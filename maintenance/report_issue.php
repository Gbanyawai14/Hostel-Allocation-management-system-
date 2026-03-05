<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid  = (int)$_POST['room_id'];
    $sid  = (int)$_POST['student_id'];
    $cat  = $db->real_escape_string(trim($_POST['category']));
    $desc = $db->real_escape_string(trim($_POST['description']));
    $pri  = $db->real_escape_string(trim($_POST['priority']));

    $db->query("CALL proc_report_maintenance($rid, $sid, '$cat', '$desc', '$pri')");
    if ($db->error) {
        $msg = '<div class="alert error">❌ ' . $db->error . '</div>';
    } else {
        $msg = '<div class="alert success">✅ Maintenance request submitted. Staff will be notified.</div>';
    }
}

$rooms    = $db->query("SELECT R.room_id, R.room_number, H.hostel_name FROM Room R JOIN Hostel H ON R.hostel_id=H.hostel_id ORDER BY H.hostel_name, R.room_number");
$students = $db->query("SELECT student_id, full_name FROM Student ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Report Issue</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>🔧 Report Maintenance Issue</h1><p>Submit an issue using proc_report_maintenance — stores JSON data</p></div>
  <?= $msg ?>
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Submit Issue (proc_report_maintenance + JSON)</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group">
            <label>Student</label>
            <select name="student_id" required>
              <option value="">-- Select Student --</option>
              <?php while($s=$students->fetch_assoc()): ?>
              <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Room</label>
            <select name="room_id" required>
              <option value="">-- Select Room --</option>
              <?php while($r=$rooms->fetch_assoc()): ?>
              <option value="<?= $r['room_id'] ?>"><?= htmlspecialchars($r['room_number']) ?> — <?= htmlspecialchars($r['hostel_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Category</label>
            <select name="category" required>
              <option value="Plumbing">Plumbing</option>
              <option value="Electrical">Electrical</option>
              <option value="Furniture">Furniture</option>
              <option value="Internet">Internet / WiFi</option>
              <option value="Cleaning">Cleaning</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Priority</label>
            <select name="priority">
              <option value="High">High</option>
              <option value="Medium" selected>Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Describe the issue in detail..." style="resize:vertical"></textarea>
          </div>
          <button type="submit" class="btn">Submit Request</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>JSON Storage Preview</h2></div>
      <div style="padding:1.5rem">
        <p style="color:var(--muted);font-size:.85rem;margin-bottom:1rem">
          Issue details are stored as a JSON object in MySQL, demonstrating the advanced JSON column feature:
        </p>
        <pre style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:1rem;font-family:'IBM Plex Mono',monospace;font-size:.8rem;color:var(--green);overflow-x:auto">{
  "category":    "Plumbing",
  "description": "Leaking tap in bathroom",
  "priority":    "High"
}</pre>
        <p style="color:var(--muted);font-size:.78rem;margin-top:1rem">
          Queried using:<br>
          <code style="color:var(--accent)">JSON_EXTRACT(issue_details, '$.category')</code>
        </p>
      </div>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
