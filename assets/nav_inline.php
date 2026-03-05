<?php
// assets/nav_inline.php — Shared sidebar navigation
$role = $_SESSION['role'] ?? 'STUDENT';
$user = $_SESSION['username'] ?? 'User';
// Detect depth for correct relative paths
$depth = substr_count(str_replace('\\','/',dirname($_SERVER['PHP_SELF'])), '/') - 1;
// Simple: always use root-relative paths via absolute from /hams/
$base = '/hams/';
?>
<nav class="sidebar">
  <div class="logo">HAMS <small>Hostel Management System</small></div>

  <div class="nav-section">Overview</div>
  <a href="<?= $base ?>index.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
    <span class="icon">📊</span> Dashboard
  </a>

  <?php if ($role === 'ADMIN' || $role === 'WARDEN'): ?>
  <div class="nav-section">Management</div>
  <a href="<?= $base ?>admin/add_hostel.php"    class="nav-item <?= basename($_SERVER['PHP_SELF'])=='add_hostel.php'?'active':'' ?>"><span class="icon">🏠</span> Hostels</a>
  <a href="<?= $base ?>admin/add_room.php"      class="nav-item <?= basename($_SERVER['PHP_SELF'])=='add_room.php'?'active':'' ?>"><span class="icon">🚪</span> Rooms</a>
  <a href="<?= $base ?>admin/allocate_room.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='allocate_room.php'?'active':'' ?>"><span class="icon">📋</span> Allocate Room</a>
  <a href="<?= $base ?>admin/checkout.php"      class="nav-item <?= basename($_SERVER['PHP_SELF'])=='checkout.php'?'active':'' ?>"><span class="icon">🚶</span> Checkout</a>
  <a href="<?= $base ?>admin/reports.php"       class="nav-item <?= basename($_SERVER['PHP_SELF'])=='reports.php'?'active':'' ?>"><span class="icon">📈</span> Reports</a>
  <?php endif; ?>

  <div class="nav-section">Students</div>
  <?php if ($role === 'ADMIN'): ?>
  <a href="<?= $base ?>student/register.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='register.php'?'active':'' ?>"><span class="icon">👤</span> Register Student</a>
  <?php endif; ?>
  <a href="<?= $base ?>student/view_allocation.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='view_allocation.php'?'active':'' ?>"><span class="icon">🔍</span> Allocations</a>
  <a href="<?= $base ?>student/payment.php"         class="nav-item <?= basename($_SERVER['PHP_SELF'])=='payment.php'?'active':'' ?>"><span class="icon">💳</span> Payments</a>

  <div class="nav-section">Maintenance</div>
  <a href="<?= $base ?>maintenance/report_issue.php"  class="nav-item <?= basename($_SERVER['PHP_SELF'])=='report_issue.php'?'active':'' ?>"><span class="icon">🔧</span> Report Issue</a>
  <a href="<?= $base ?>maintenance/view_requests.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='view_requests.php'?'active':'' ?>"><span class="icon">📝</span> View Requests</a>

  <div class="sidebar-footer">
    <div class="name"><?= htmlspecialchars($user) ?></div>
    <span class="role-badge"><?= $role ?></span>
    <a href="<?= $base ?>auth/logout.php" class="logout-btn">Sign Out</a>
  </div>
</nav>
