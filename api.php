<?php
session_start();
ini_set('display_errors', '0'); // warnings would break the JSON response
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';

function body(): array { return json_decode(file_get_contents('php://input'), true) ?: []; }
function out($data, int $status = 200): void { http_response_code($status); echo json_encode($data); exit; }
function signIn(array $u): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$u['id'];
    out(['success' => true, 'user' => ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email']]]);
}

if (!$pdo) out(['success' => false, 'message' => 'Cannot connect to MySQL. Start MySQL in XAMPP and check config.php.'], 500);

$action = $_GET['action'] ?? '';

try {
    if ($action === 'products') {
        out(['success' => true, 'products' => $pdo->query('SELECT id,name,category,price,deposit,image_url AS img FROM products ORDER BY id')->fetchAll()]);
    }

    if ($action === 'register') {
        $d = body(); $name = trim($d['name'] ?? ''); $email = strtolower(trim($d['email'] ?? '')); $password = $d['password'] ?? '';
        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 4) out(['success' => false, 'message' => 'Please enter valid registration details.'], 422);

        $check = $pdo->prepare('SELECT id FROM users WHERE email=?'); $check->execute([$email]);
        if ($check->fetch()) out(['success' => false, 'message' => 'Email is already registered.'], 409);

        $pdo->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)')->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        signIn(['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email]);
    }

    if ($action === 'login') {
        $d = body(); $email = strtolower(trim($d['email'] ?? '')); $password = $d['password'] ?? '';
        $st = $pdo->prepare('SELECT id,name,email,password FROM users WHERE email=?'); $st->execute([$email]); $u = $st->fetch();
        if (!$u || !password_verify($password, $u['password'])) out(['success' => false, 'message' => 'Invalid email or password.'], 401);
        signIn($u);
    }

    if ($action === 'logout') { session_destroy(); out(['success' => true]); }

    if ($action === 'checkout') {
        $d = body(); $items = $d['items'] ?? [];
        $name = trim($d['name'] ?? ''); $phone = trim($d['phone'] ?? ''); $email = trim($d['email'] ?? ''); $address = trim($d['address'] ?? '');
        if (!$items || empty($d['startDate']) || empty($d['endDate'])) out(['success' => false, 'message' => 'Rental period and cart are required.'], 422);
        if (!$name || !$phone || !$address || !filter_var($email, FILTER_VALIDATE_EMAIL)) out(['success' => false, 'message' => 'Please fill in all customer details.'], 422);

        $start = new DateTime($d['startDate']); $end = new DateTime($d['endDate']);
        if ($end < $start) out(['success' => false, 'message' => 'Invalid rental dates.'], 422);
        $days = (int)$start->diff($end)->days + 1;

        $subtotal = 0; $deposit = 0; $cleanItems = [];
        $find = $pdo->prepare('SELECT id,price,deposit FROM products WHERE id=?');
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? 1));
            $find->execute([(int)($item['id'] ?? 0)]); $p = $find->fetch();
            if (!$p) continue;
            $subtotal += $p['price'] * $days * $qty; $deposit += $p['deposit'] * $qty;
            $cleanItems[] = ['id' => $p['id'], 'qty' => $qty, 'price' => $p['price'], 'deposit' => $p['deposit']];
        }
        if (!$cleanItems) out(['success' => false, 'message' => 'No valid products in cart.'], 422);

        $total = $subtotal + $deposit;
        $code = 'RR-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO rentals(user_id,customer_name,customer_phone,customer_email,customer_address,start_date,end_date,days,subtotal,deposit,total,order_code) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$_SESSION['user_id'] ?? null, $name, $phone, $email, $address, $start->format('Y-m-d'), $end->format('Y-m-d'), $days, $subtotal, $deposit, $total, $code]);
        $rid = $pdo->lastInsertId();
        $it = $pdo->prepare('INSERT INTO rental_items(rental_id,product_id,quantity,price_per_day,deposit_per_item) VALUES(?,?,?,?,?)');
        foreach ($cleanItems as $i) $it->execute([$rid, $i['id'], $i['qty'], $i['price'], $i['deposit']]);
        $pdo->commit();
        out(['success' => true, 'orderId' => $code, 'total' => $total]);
    }

    out(['success' => false, 'message' => 'Unknown API action.'], 404);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    out(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
