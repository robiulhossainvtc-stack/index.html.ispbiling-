<?php require 'config.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>গ্রাহক — ISP Billing</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<aside class="sidebar">
  <h2><span>⚡</span> ISP BILLING</h2>
  <a href="index.php">🏠 Dashboard</a>
  <a href="customers.php" class="active">👥 Customer Management</a>
  <a href="packages.php">📦 Internet Packages</a>
  <a href="billing.php">🧾 Billing Management</a>
  <a href="payments.php">💵 Payment & Collection</a>
  <a href="logout.php" style="margin-top:20px;color:#ff7675;">🚪 Logout</a>
</aside>
<div class="main">
  <div class="topbar">
    <h1>গ্রাহক ব্যবস্থাপনা</h1>
    <div class="user">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></div>
  </div>
  <div class="content">
    <div class="panel">
      <div class="toolbar">
        <input type="text" id="search" placeholder="🔍 নাম / আইডি / মোবাইল..." onkeyup="loadCustomers()">
        <button class="btn" onclick="openAdd()">+ নতুন গ্রাহক</button>
      </div>
      <table>
        <thead><tr><th>ID</th><th>নাম</th><th>মোবাইল</th><th>প্যাকেজ</th><th>এরিয়া</th><th>অবস্থা</th><th>বকেয়া</th><th>Action</th></tr></thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-bg" id="addModal">
  <div class="modal">
    <h3>নতুন গ্রাহক</h3>
    <div class="form-group"><label>নাম *</label><input id="cName"></div>
    <div class="form-group"><label>মোবাইল *</label><input id="cPhone"></div>
    <div class="form-group"><label>এরিয়া</label><input id="cArea" placeholder="ঢাকা - মিরপুর"></div>
    <div class="form-group"><label>প্যাকেজ</label><select id="cPkg"></select></div>
    <div class="modal-actions">
      <button class="btn ghost" onclick="closeModal()">বাতিল</button>
      <button class="btn" onclick="saveCustomer()">সেভ</button>
    </div>
  </div>
</div>

<script>
async function loadCustomers() {
  const q = document.getElementById('search').value;
  const res = await fetch('api.php?action=get_customers&q=' + encodeURIComponent(q));
  const data = await res.json();
  document.getElementById('rows').innerHTML = data.map(c => `
    <tr>
      <td>${c.customer_code}</td><td>${c.name}</td><td>${c.phone}</td>
      <td>${c.package_name || '—'}</td><td>${c.area || '—'}</td>
      <td><span class="badge ${c.status==='Active'?'active':'inactive'}">${c.status}</span></td>
      <td>৳${parseFloat(c.due_amount).toFixed(0)}</td>
      <td><button class="btn danger" style="padding:5px 10px;font-size:12px;"
          onclick="del(${c.id},'${c.name}')">Delete</button></td>
    </tr>
  `).join('') || '<tr><td colspan="8" style="text-align:center;color:#999;">কোনো গ্রাহক নেই</td></tr>';
}
async function openAdd() {
  const res = await fetch('api.php?action=get_packages');
  const pkgs = await res.json();
  document.getElementById('cPkg').innerHTML = pkgs.map(p =>
    `<option value="${p.id}">${p.name} (${p.speed_mbps}Mbps - ৳${p.price})</option>`).join('');
  document.getElementById('addModal').classList.add('show');
}
function closeModal() { document.getElementById('addModal').classList.remove('show'); }
async function saveCustomer() {
  const name = document.getElementById('cName').value.trim();
  const phone = document.getElementById('cPhone').value.trim();
  const area = document.getElementById('cArea').value.trim();
  const pkg = document.getElementById('cPkg').value;
  if (!name || !phone) { alert('নাম ও মোবাইল দিন'); return; }
  const fd = new FormData();
  fd.append('action','add_customer');
  fd.append('name', name); fd.append('phone', phone);
  fd.append('area', area); fd.append('package_id', pkg);
  const res = await fetch('api.php', { method:'POST', body:fd });
  const data = await res.json();
  if (data.error) { alert(data.error); return; }
  closeModal();
  document.getElementById('cName').value = '';
  document.getElementById('cPhone').value = '';
  document.getElementById('cArea').value = '';
  loadCustomers();
  alert('✅ যোগ হয়েছে: ' + data.code);
}
async function del(id, name) {
  if (!confirm(`"${name}" মুছবেন?`)) return;
  const fd = new FormData();
  fd.append('action','delete_customer'); fd.append('id', id);
  await fetch('api.php', { method:'POST', body:fd });
  loadCustomers();
}
loadCustomers();
</script>
</body>
</html>