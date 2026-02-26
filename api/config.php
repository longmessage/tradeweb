<?php
// 数据库配置 - 请修改为你的数据库信息
$db_config = [
    'host' => 'localhost',
    'dbname' => 'tradeweb',
    'username' => 'root',
    'password' => 'your_password'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => '数据库连接失败: ' . $e->getMessage()]));
}

// 初始化数据库表
function initDatabase($pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        company VARCHAR(200),
        email VARCHAR(200) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        plan ENUM('free', 'basic', 'pro') DEFAULT 'free',
        balance INT DEFAULT 0,
        role ENUM('member', 'admin') DEFAULT 'member',
        registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME
    );
    
    CREATE TABLE IF NOT EXISTS ai_analysis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        url VARCHAR(500) NOT NULL,
        result JSON,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    ";
    $pdo->exec($sql);
}

initDatabase($pdo);
