// API 配置
const API_BASE = 'https://your-domain.com/api'; // 替换为你的API地址

// API 请求封装
async function apiCall(endpoint, data = {}, method = 'POST') {
    const url = `${API_BASE}/${endpoint}`;
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (method === 'POST' || method === 'PUT') {
        options.body = JSON.stringify(data);
    } else if (method === 'GET' && Object.keys(data).length > 0) {
        const params = new URLSearchParams(data).toString();
        return fetch(`${url}?${params}`, { method: 'GET' })
            .then(r => r.json());
    }
    
    const response = await fetch(url, options);
    return response.json();
}

// 用户相关 API
const UserAPI = {
    register: (data) => apiCall('user.php', { action: 'register', ...data }, 'POST'),
    login: (email, password) => apiCall('user.php', { action: 'login', email, password }, 'POST'),
    getProfile: (userId) => apiCall('user.php', { action: 'profile', user_id: userId }, 'GET'),
    updateProfile: (userId, data) => apiCall('user.php', { action: 'update_profile', user_id: userId, ...data }, 'PUT'),
    upgradePlan: (userId, plan, amount) => apiCall('user.php', { action: 'upgrade_plan', user_id: userId, plan, amount }, 'PUT'),
    recharge: (userId, amount) => apiCall('user.php', { action: 'recharge', user_id: userId, amount }, 'PUT'),
};

// AI 分析 API
const AIAPI = {
    save: (userId, url, result) => apiCall('ai.php', { action: 'save', user_id: userId, url, result }, 'POST'),
    list: (userId) => apiCall('ai.php', { action: 'list', user_id: userId }, 'GET'),
    adminList: () => apiCall('ai.php', { action: 'list' }, 'GET'),
    detail: (id) => apiCall('ai.php', { action: 'detail', id }, 'GET'),
};

// 管理后台 API
const AdminAPI = {
    stats: () => apiCall('admin.php', { action: 'stats' }, 'GET'),
    users: () => apiCall('admin.php', { action: 'users' }, 'GET'),
    orders: () => apiCall('admin.php', { action: 'orders' }, 'GET'),
    updateUser: (data) => apiCall('admin.php', { action: 'update_user', ...data }, 'POST'),
    deleteUser: (userId) => apiCall('admin.php', { action: 'delete_user', user_id: userId }, 'POST'),
    recharge: (userId, amount) => apiCall('admin.php', { action: 'recharge', user_id: userId, amount }, 'POST'),
};
