<?php require 'config.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="bn">
<head><meta charset="UTF-8"><title>পেমেন্ট</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body>
<aside class="sidebar">
  <h2><span>⚡</span> ISP BILLING</h2>
  <a href="index.php">🏠 Dashboard</a>
  <a href="customers.php">👥 Customer Management</a>
  <a href="packages.php">📦 Internet Packages</a>
  <a href="billing.php">🧾 Billing Management</a>
  <a href="payments.php" class="active">💵 Payment & Collection</a>
  <a href="logout.php" style="margin-top:20px;color:#ff7675;">🚪 Logout</a>
</aside>
<div class="main">
  <div class="topbar"><h1>পেমেন্ট ও কালেকশন</h1><div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></div></div>
  <div class="content">
    <div class="panel">
      <div class="toolbar">
        <h3 style="border:none;margin:0;padding:0;">পেমেন্ট তালিকা</h3>
        <button class="btn" onclick="openPay()">+ নতুন পেমেন্ট</button>
      </div>
      <table>
        <thead><tr><th>রসিদ</th><th>গ্রাহক</th><th>পরিমাণ</th><th>মাধ্যম</th><th>কালেক্টর</th><th>তারিখ</th></tr></thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-bg" id="payModal">
  <div class="modal">
    <h3>পেমেন্ট গ্রহণ</h3>
    <div class="form-group"><label>গ্রাহক</label><select id="pCust"></select></div>
    <div class="form-group"><label>পরিমাণ (৳)</label><input type="number" id="pAmount"></div>
    <div class="form-group"><label>মাধ্যম</label>
      <select id="pMethod"><option>Cash</option><option>bKash</option><option>Nagad</option>
      <option>Rocket</option><option>Bank</option><option>Online</option></select>
    </div>
    <div class="modal-actions">
      <button class="btn ghost" onclick="document.getElementById('payModal').classList.remove('show')">বাতিল</button>
      <button class="btn" onclick="savePay()">জমা করুন</button>
    </div>
  </div>
</div>

<script>
async function load() {
  const res = await fetch('api.php?action=get_payments');
  const data = await res.json();
  document.getElementById('rows').innerHTML = data.map(p => `
    <tr><td>${p.receipt_no}</td><td>${p.customer_name}</td>
    <td>৳${parseFloat(p.amount).toFixed(0)}</td><td>${p.method}</td>
    <td>${p.collector}</td><td>${p.payment_date}</td></tr>
  `).join('') || '<tr><td colspan="6" style="text-align:center;color:#999;">কোনো পেমেন্ট নেই</td></tr>';
}
async function openPay() {
  const res = await fetch('api.php?action=get_customers');
  const custs = await res.json();
  document.getElementById('pCust').innerHTML = custs.map(c =>
    `<option value="${c.id}">${c.customer_code} - ${c.name} (বকেয়া ৳${c.due_amount})</option>`).join('');
  document.getElementById('payModal').classList.add('show');
}
async function savePay() {
  const fd = new FormData();
  fd.append('action','add_payment');
  fd.append('customer_id', document.getElementById('pCust').value);
  fd.append('amount', document.getElementById('pAmount').value);
  fd.append('method', document.getElementById('pMethod').value);
  const res = await fetch('api.php', { method:'POST', body:fd });
  const data = await res.json();
  if (data.error) { alert(data.error); return; }
  document.getElementById('payModal').classList.remove('show');
  document.getElementById('pAmount').value = '';
  alert('✅ জমা হয়েছে — রসিদ: ' + data.receipt);
  load();
}
load();
</script>
</body>
</html>