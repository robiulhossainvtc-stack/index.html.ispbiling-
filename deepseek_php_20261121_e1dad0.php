<?php require 'config.php'; requireLogin();
$pkgs = $pdo->query("SELECT * FROM packages ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn"><head><meta charset="UTF-8"><title>প্যাকেজ</title>
<link rel="stylesheet" href="assets/style.css"></head><body>
<aside class="sidebar">
  <h2><span>⚡</span> ISP BILLING</h2>
  <a href="index.php">🏠 Dashboard</a>
  <a href="customers.php">👥 Customer Management</a>
  <a href="packages.php" class="active">📦 Internet Packages</a>
  <a href="billing.php">🧾 Billing Management</a>
  <a href="payments.php">💵 Payment & Collection</a>
  <a href="logout.php" style="margin-top:20px;color:#ff7675;">🚪 Logout</a>
</aside>
<div class="main">
  <div class="topbar"><h1>ইন্টারনেট প্যাকেজ</h1><div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></div></div>
  <div class="content"><div class="panel">
    <table>
      <thead><tr><th>প্যাকেজ</th><th>স্পিড</th><th>মূল্য</th><th>মেয়াদ</th><th>অবস্থা</th></tr></thead>
      <tbody>
      <?php foreach ($pkgs as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= $p['speed_mbps'] ?> Mbps</td>
          <td><?= money($p['price']) ?></td>
          <td><?= $p['duration_days'] ?> দিন</td>
          <td><span class="badge <?= $p['status']==='Active'?'active':'inactive' ?>"><?= $p['status'] ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div>
</body></html>