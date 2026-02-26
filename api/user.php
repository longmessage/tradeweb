<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        $action = $input['action'] ?? '';
        
        if ($action === 'register') {
            // 注册
            $name = $input['name'] ?? '';
            $company = $input['company'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            $plan = $input['plan'] ?? 'free';
            
            if (!$email || !$password || !$name) {
                echo json_encode(['error' => '缺少必要信息']);
                exit;
            }
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => '邮箱已注册']);
                exit;
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, company, email, password, plan) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $company, $email, $hash, $plan]);
            
            echo json_encode(['success' => true, 'message' => '注册成功']);
        }
        
        elseif ($action === 'login') {
            // 登录
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['error' => '邮箱或密码错误']);
                exit;
            }
            
            // 更新最后登录时间
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        }
        
        break;
        
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'profile') {
            // 获取用户信息
            $userId = $_GET['user_id'] ?? 0;
            $stmt = $pdo->prepare("SELECT id, name, company, email, plan, balance, role, registered_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['error' => '用户不存在']);
            }
        }
        
        break;
        
    case 'PUT':
        $action = $input['action'] ?? '';
        
        if ($action === 'update_profile') {
            $userId = $input['user_id'] ?? 0;
            $name = $input['name'] ?? '';
            $company = $input['company'] ?? '';
            
            $stmt = $pdo->prepare("UPDATE users SET name = ?, company = ? WHERE id = ?");
            $stmt->execute([$name, $company, $userId]);
            
            echo json_encode(['success' => true]);
        }
        
        elseif ($action === 'upgrade_plan') {
            $userId = $input['user_id'] ?? 0;
            $plan = $input['plan'] ?? 'free';
            $amount = $input['amount'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE users SET plan = ?, balance = balance - ? WHERE id = ? AND balance >= ?");
            $stmt->execute([$plan, $amount, $userId, $amount]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => '余额不足']);
            }
        }
        
        elseif ($action === 'recharge') {
            $userId = $input['user_id'] ?? 0;
            $amount = $input['amount'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);
            
            echo json_encode(['success' => true]);
        }
        
        break;
}
