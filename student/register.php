<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
     header('Location: ../auth/login.php'); exit;
}
require_once '../config/db.php';
$db  = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uname  = trim($_POST['username']);
    $pass   = md5(trim($_POST['password']));
    $fname  = trim($_POST['full_name']);
    $email  = trim($_POST['email']);
    $phone  = trim($_POST['phone']);
    $dept   = trim($_POST['department']);

    // Insert user account first
    $stmt = $db->prepare("INSERT INTO UserAccount(username,password,role) VALUES(?,?,'STUDENT')");
    $stmt->bind_param('ss', $uname, $pass);
    if ($stmt->execute()) {
        $uid  = $db->insert_id;
        $stmt2 = $db->prepare(
            "INSERT INTO Student(user_id,full_name,email,phone,department,registration_date)
             VALUES(?,?,?,?,?,CURDATE())"
        );
        $stmt2->bind_param('issss', $uid, $fname, $email, $phone, $dept);
        if ($stmt2->execute()) {
            $msg = '<div class="alert success">✅ Student "' . htmlspecialchars($fname) . '" registered. Username: ' . htmlspecialchars($uname) . '</div>';
        } else {
            $msg = '<div class="alert error">❌ Student insert failed: ' . $db->error . '</div>';
        }
    } else {
        $msg = '<div class="alert error">❌ Account creation failed: ' . $db->error . '</div>';
    }
}

$students = $db->query(
    "SELECT S.*, U.username,
            CASE WHEN A.allocation_id IS NOT NULL THEN 'Allocated' ELSE 'Unallocated' END AS alloc_status
     FROM Student S
     JOIN UserAccount U ON S.user_id = U.user_id
     LEFT JOIN Allocation A ON S.student_id = A.student_id AND A.status='ACTIVE'
     ORDER BY S.registration_date DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HAMS — Register Student</title>
<?php include '../assets/css/style_inline.php'; ?>
</head>
<body>
<?php include '../assets/nav_inline.php'; ?>
<main class="main">
  <div class="page-header"><h1>👤 Student Registration</h1><p>Register a new student and create login account</p></div>
  <?= $msg ?>
  <div class="content-grid">
    <div class="panel">
      <div class="panel-header"><h2>Register New Student</h2></div>
      <div style="padding:1.5rem">
        <form method="POST">
          <div class="form-group"><label>Full Name</label><input type="text" name="full_name" placeholder="Muhammad Ali bin Ahmad" required></div>
          <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="student@university.edu.my" required></div>
          <div class="form-group"><label>Phone</label><input type="text" name="phone" placeholder="012-3456789"></div>
          <div class="form-group"><label>Department / Faculty</label><input type="text" name="department" placeholder="Computer Science"></div>
          <div class="form-group"><label>Login Username</label><input type="text" name="username" placeholder="student006" required></div>
          <div class="form-group"><label>Password</label><input type="password" name="password" value="student123" required></div>
          <button type="submit" class="btn">Register Student</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>All Students</h2></div>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Dept</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($s=$students->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($s['full_name']) ?></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars($s['email']) ?></td>
            <td style="font-size:.8rem"><?= htmlspecialchars($s['department']) ?></td>
            <td>
              <?php if($s['alloc_status']==='Allocated'): ?>
                <span class="badge badge-green">Allocated</span>
              <?php else: ?>
                <span class="badge badge-yellow">Unallocated</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</body></html>
<?php $db->close(); ?>
