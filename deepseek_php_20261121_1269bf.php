<?php
require 'config.php';
requireLogin();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

  case 'get_customers':
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT c.*, p.name AS package_name, p.price AS package_price
            FROM customers c LEFT JOIN packages p ON c.package_id = p.id";
    if ($q !== '') {
      $sql .= " WHERE c.name LIKE ? OR c.customer_code LIKE ? OR c.phone LIKE ?";
      $stmt = $pdo->prepare($sql . " ORDER BY c.id DESC");
      $like = "%$q%";
      $stmt->execute([$like, $like, $like]);
    } else {
      $stmt = $pdo->query($sql . " ORDER BY c.id DESC");
    }
    jsonResponse($stmt->fetchAll());

  case 'add_customer':
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $area    = trim($_POST['area'] ?? '');
    $pkgId   = (int)($_POST['package_id'] ?? 0);
    if (!$name || !$phone) jsonResponse(['error'=>'নাম ও মোবাইল দিন'], 400);

    $nextId = ($pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM customers")->fetchColumn());
    $code = 'ISP-' . (1000 + $nextId);

    $pkgPrice = 0;
    if ($pkgId) {
      $stmt = $pdo->prepare("SELECT price FROM packages WHERE id=?");
      $stmt->execute([$pkgId]);
      $pkgPrice = (float)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("INSERT INTO customers
      (customer_code,name,phone,area,package_id,status,due_amount,connection_date)
      VALUES (?,?,?,?,?,'Active',?,CURDATE())");
    $stmt->execute([$code,$name,$phone,$area,$pkgId,$pkgPrice]);
    jsonResponse(['success'=>true,'code'=>$code]);

  case 'delete_customer':
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM customers WHERE id=?")->execute([$id]);
    jsonResponse(['success'=>true]);

  case 'generate_bills':
    $month = date('Y-m');
    $customers = $pdo->query("
      SELECT c.id, c.due_amount, p.price FROM customers c
      LEFT JOIN packages p ON c.package_id = p.id
      WHERE c.status='Active'
    ")->fetchAll();

    $count = 0;
    $ins = $pdo->prepare("INSERT INTO bills
      (bill_no,customer_id,bill_month,total_amount,paid_amount,status)
      VALUES (?,?,?,?,0,'Unpaid')");
    $upd = $pdo->prepare("UPDATE customers SET due_amount = due_amount + ? WHERE id=?");

    foreach ($customers as $c) {
      $chk = $pdo->prepare("SELECT id FROM bills WHERE customer_id=? AND bill_month=?");
      $chk->execute([$c['id'], $month]);
      if ($chk->fetch()) continue;
      $price = (float)$c['price'];
      if ($price <= 0) continue;
      $billNo = 'B-' . date('Ym') . '-' . str_pad($c['id'], 4, '0', STR_PAD_LEFT);
      $ins->execute([$billNo, $c['id'], $month, $price]);
      $upd->execute([$price, $c['id']]);
      $count++;
    }
    jsonResponse(['success'=>true,'generated'=>$count]);

  case 'get_bills':
    $stmt = $pdo->query("
      SELECT b.*, c.name AS customer_name FROM bills b
      JOIN customers c ON b.customer_id = c.id
      ORDER BY b.id DESC LIMIT 100
    ");
    jsonResponse($stmt->fetchAll());

  case 'add_payment':
    $custId = (int)($_POST['customer_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'Cash';
    if (!$custId || $amount <= 0) jsonResponse(['error'=>'সঠিক তথ্য দিন'], 400);

    try {
      $pdo->beginTransaction();
      $rcpNo = 'RCP-' . date('Ymd') . '-' . rand(100,999);
      $pdo->prepare("INSERT INTO payments
        (receipt_no,customer_id,amount,method,collector,payment_date)
        VALUES (?,?,?,?,?,CURDATE())")
        ->execute([$rcpNo, $custId, $amount, $method, $_SESSION['full_name']]);

      $pdo->prepare("UPDATE customers SET due_amount = GREATEST(0, due_amount - ?) WHERE id=?")
          ->execute([$amount, $custId]);

      $bills = $pdo->prepare("SELECT * FROM bills WHERE customer_id=? AND status!='Paid' ORDER BY id ASC");
      $bills->execute([$custId]);
      $remaining = $amount;
      foreach ($bills->fetchAll() as $b) {
        if ($remaining <= 0) break;
        $need = $b['total_amount'] - $b['paid_amount'];
        $apply = min($need, $remaining);
        $newPaid = $b['paid_amount'] + $apply;
        $newStatus = $newPaid >= $b['total_amount'] ? 'Paid' : 'Partial';
        $pdo->prepare("UPDATE bills SET paid_amount=?, status=? WHERE id=?")
            ->execute([$newPaid, $newStatus, $b['id']]);
        $remaining -= $apply;
      }
      $pdo->commit();
      jsonResponse(['success'=>true,'receipt'=>$rcpNo]);
    } catch (Exception $e) {
      $pdo->rollBack();
      jsonResponse(['error'=>$e->getMessage()], 500);
    }

  case 'get_payments':
    $stmt = $pdo->query("
      SELECT p.*, c.name AS customer_name FROM payments p
      JOIN customers c ON p.customer_id = c.id
      ORDER BY p.id DESC LIMIT 100
    ");
    jsonResponse($stmt->fetchAll());

  case 'get_packages':
    jsonResponse($pdo->query("SELECT * FROM packages ORDER BY id")->fetchAll());

  default:
    jsonResponse(['error'=>'Unknown action'], 400);
}