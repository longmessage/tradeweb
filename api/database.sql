-- 外贸拓客网站 MySQL 数据库

-- 创建数据库
CREATE DATABASE IF NOT EXISTS tradeweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tradeweb;

-- 用户表
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI分析记录表
CREATE TABLE IF NOT EXISTS ai_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    result JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单表
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入管理员账号 (密码: admin123)
INSERT INTO users (name, email, password, plan, role) 
VALUES ('Administrator', 'admin@tradeweb.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pro', 'admin')
ON DUPLICATE KEY UPDATE name = 'Administrator';

-- 插入测试用户
INSERT INTO users (name, company, email, password, plan, balance) 
VALUES ('测试用户', '测试公司', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'free', 0)
ON DUPLICATE KEY UPDATE name = '测试用户';
