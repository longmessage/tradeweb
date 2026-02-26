<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'stats') {
        // 获取统计数据
        $stats = [];
        
        // 总用户数
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch()['total'];
        
        // 本月新增
        $stmt = $pdo->query("SELECT COUNT(*) as new FROM users WHERE registered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['new_users'] = $stmt->fetch()['new'];
        
        // 总收入
        $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as revenue FROM orders WHERE status = 'completed'");
        $stats['revenue'] = $stmt->fetch()['revenue'];
        
        // 订单数
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $stmt->fetch()['total'];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
    
    if ($action === 'users') {
        // 获取用户列表
        $stmt = $pdo->query("SELECT id, name, company, email, plan, balance, role, registered_at FROM users ORDER BY registered_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);
    }
    
    if ($action === 'orders') {
        // 获取订单列表
        $stmt = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'orders' => $orders]);
    }
}

if ($method === 'POST') {
    $action = $input['action'] ?? '';
    
    if ($action === 'update_user') {
        $userId = $input['user_id'] ?? 0;
        $name = $input['name'] ?? '';
        $company = $input['company'] ?? '';
        $plan = $input['plan'] ?? 'free';
        $balance = $input['balance'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE users SET name = ?, company = ?, plan = ?, balance = ? WHERE id = ?");
        $stmt->execute([$name, $company, $plan, $balance, $userId]);
        
        echo json_encode(['success' => true]);
    }
    
    if ($action === 'delete_user') {
        $userId = $input['user_id'] ?? 0;
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true]);
    }
    
    if ($action === 'recharge') {
        $userId = $input['user_id'] ?? 0;
        $amount = $input['amount'] ?? 0;
        
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        echo json_encode(['success' => true]);
    }
}
