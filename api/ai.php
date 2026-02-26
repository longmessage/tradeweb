<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST') {
    $action = $input['action'] ?? '';
    
    if ($action === 'save') {
        // 保存分析记录
        $userId = $input['user_id'] ?? 0;
        $url = $input['url'] ?? '';
        $result = $input['result'] ?? [];
        
        if (!$userId || !$url) {
            echo json_encode(['error' => '缺少必要信息']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO ai_analysis (user_id, url, result) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $url, json_encode($result)]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    }
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'list') {
        // 获取分析记录列表
        $userId = $_GET['user_id'] ?? 0;
        
        if ($userId) {
            // 普通用户只能看到自己的记录
            $stmt = $pdo->prepare("SELECT * FROM ai_analysis WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
        } else {
            // 管理员可以看到所有记录
            $stmt = $pdo->prepare("SELECT a.*, u.name as user_name, u.email as user_email FROM ai_analysis a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 100");
            $stmt->execute();
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$r) {
            $r['result'] = json_decode($r['result'], true);
        }
        
        echo json_encode(['success' => true, 'data' => $results]);
    }
    
    if ($action === 'detail') {
        $id = $_GET['id'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT a.*, u.name as user_name, u.email as user_email FROM ai_analysis a LEFT JOIN users u ON a.user_id = u.id WHERE a.id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['result'] = json_decode($result['result'], true);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['error' => '记录不存在']);
        }
    }
}
