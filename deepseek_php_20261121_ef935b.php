<?php require 'config.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="bn">
<head><meta charset="UTF-8"><title>বিলিং</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body>
<aside class="sidebar">
  <h2><span>⚡</span> ISP BILLING</h2>
  <a href="index.php">🏠 Dashboard</a>
  <a href="customers.php">👥 Customer Management</a>
  <a href="packages.php">📦 Internet Packages</a>
  <a href="billing.php" class="active">🧾 Billing Management</a>
  <a href="payments.php">💵 Payment & Collection</a>
  <a href="logout.php" style="margin-top:20px;color:#ff7675;">🚪 Logout</a>
</aside>
<div class="main">
  <div class="topbar"><h1>বিলিং</h1><div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></div></div>
  <div class="content">
    <div class="panel">
      <div class="toolbar">
        <h3 style="border:none;margin:0;padding:0;">বিল তালিকা</h3>
        <button class="btn" onclick="genBills()">⚙️ অটো বিল জেনারেট</button>
      </div>
      <table>
        <thead><tr><th>বিল নং</th><th>গ্রাহক</th><th>মাস</th><th>মোট</th><th>জমা</th><th>বাকি</th><th>স্ট্যাটাস</th></tr></thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
  </div>
</div>
<script>
async function load() {
  const res = await fetch('api.php?action=get_bills');
  const data = await res.json();
  document.getElementById('rows').innerHTML = data.map(b => {
    const rem = b.total_amount - b.paid_amount;
    const cls = b.status==='Paid'?'active':(b.status==='Partial'?'due':'inactive');
    return `<tr><td>${b.bill_no}</td><td>${b.customer_name}</td><td>${b.bill_month}</td>
      <td>৳${parseFloat(b.total_amount).toFixed(0)}</td>
      <td>৳${parseFloat(b.paid_amount).toFixed(0)}</td>
      <td>৳${parseFloat(rem).toFixed(0)}</td>
      <td><span class="badge ${cls}">${b.status}</span></td></tr>`;
  }).join('') || '<tr><td colspan="7" style="text-align:center;color:#999;">কোনো বিল নেই</td></tr>';
}
async function genBills() {
  if (!confirm('চলতি মাসের জন্য সব Active গ্রাহকের বিল তৈরি করবেন?')) return;
  const fd = new FormData(); fd.append('action','generate_bills');
  const res = await fetch('api.php', { method:'POST', body:fd });
  const data = await res.json();
  alert('✅ ' + data.generated + ' টি বিল তৈরি হয়েছে');
  load();
}
load();
</script>
</body>
</html>