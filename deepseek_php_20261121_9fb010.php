<?php
require 'config.php';
requireLogin();

$total     = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$active    = $pdo->query("SELECT COUNT(*) FROM customers WHERE status='Active'")->fetchColumn();
$inactive  = $pdo->query("SELECT COUNT(*) FROM customers WHERE status='Inactive'")->fetchColumn();
$totalDue  = $pdo->query("SELECT COALESCE(SUM(due_amount),0) FROM customers")->fetchColumn();
$todayColl = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date=CURDATE()")->fetchColumn();
$monthColl = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())")->fetchColumn();
$currentBills = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bills WHERE bill_month=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();

$recent = $pdo->query("
  SELECT c.*, p.name AS package_name FROM customers c
  LEFT JOIN packages p ON c.package_id = p.id
  ORDER BY c.id DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Dashboard — ISP Billing</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<aside class="sidebar">
  <h2><span>⚡</span> ISP BILLING</h2>
  <a href="index.php" class="active">🏠 Dashboard</a>
  <a href="customers.php">👥 Customer Management</a>
  <a href="packages.php">📦 Internet Packages</a>
  <a href="billing.php">🧾 Billing Management</a>
  <a href="payments.php">💵 Payment & Collection</a>
  <a href="logout.php" style="margin-top:20px;color:#ff7675;">🚪 Logout</a>
</aside>

<div class="main">
  <div class="topbar">
    <h1>Dashboard</h1>
    <div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= $_SESSION['role'] ?>)</div>
  </div>

  <div class="content">
    <div class="cards">
      <div class="card"><div class="label">মোট গ্রাহক</div><div class="value"><?= $total ?></div></div>
      <div class="card green"><div class="label">Active গ্রাহক</div><div class="value"><?= $active ?></div></div>
      <div class="card red"><div class="label">Inactive গ্রাহক</div><div class="value"><?= $inactive ?></div></div>
      <div class="card orange"><div class="label">মোট বকেয়া বিল</div><div class="value"><?= money($totalDue) ?></div></div>
      <div class="card green"><div class="label">আজকের কালেকশন</div><div class="value"><?= money($todayColl) ?></div></div>
      <div class="card"><div class="label">মাসিক কালেকশন</div><div class="value"><?= money($monthColl) ?></div></div>
      <div class="card"><div class="label">চলতি মাসের মোট বিল</div><div class="value"><?= money($currentBills) ?></div></div>
    </div>

    <div class="panel">
      <h3>সাম্প্রতিক গ্রাহক</h3>
      <table>
        <thead><tr><th>ID</th><th>নাম</th><th>মোবাইল</th><th>প্যাকেজ</th><th>এরিয়া</th><th>অবস্থা</th><th>বকেয়া</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['customer_code']) ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['phone']) ?></td>
            <td><?= htmlspecialchars($c['package_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($c['area']) ?></td>
            <td><span class="badge <?= $c['status']==='Active'?'active':'inactive' ?>"><?= $c['status'] ?></span></td>
            <td><?= money($c['due_amount']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>