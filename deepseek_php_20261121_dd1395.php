<?php
require 'config.php';
$newPass = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$newPass]);
echo "✅ অ্যাডমিন পাসওয়ার্ড সেট হয়েছে: <b>admin / admin123</b><br>
       ⚠️ এখন এই ফাইলটি (<code>setup_admin.php</code>) ডিলিট করুন!";