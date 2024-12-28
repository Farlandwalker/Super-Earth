const users = JSON.parse(localStorage.getItem('users')) || []; // 获取存储的用户信息，若没有则初始化为空数组

function registerUser() {
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (username && email && password) {
        const encryptedPassword = btoa(password);

        // 检查是否已有相同用户名
        if (users.find(u => u.username === username)) {
            alert("该用户名已被注册，请选择其他用户名。");
            return;
        }

        // 将新用户加入 users 数组
        users.push({ username, email, password: encryptedPassword });

        // 将用户信息存储到 localStorage
        localStorage.setItem('users', JSON.stringify(users));

        alert("注册成功！请登录。");
        window.location.href = "login.html";
    } else {
        alert("请填写所有字段。");
    }
}

function loginUser() {
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();
    const encryptedPassword = btoa(password);

    const user = users.find(u => u.username === username && u.password === encryptedPassword);

    if (user) {
        alert("登录成功！");
        localStorage.setItem('loggedInUser', JSON.stringify(user)); // 登录后保存当前用户信息
        window.location.href = "dashboard.html"; // 重定向到仪表盘
    } else {
        alert("用户名或密码错误。");
    }
}

// 检查用户是否已经登录，如果已登录，跳转到仪表盘
function checkLoginStatus() {
    const loggedInUser = JSON.parse(localStorage.getItem('loggedInUser'));
    if (loggedInUser) {
        window.location.href = "dashboard.html"; // 如果已经登录，跳转到仪表盘
    }
}

// 退出登录
function logout() {
    localStorage.removeItem('loggedInUser'); // 清除登录信息
    window.location.href = "index.html"; // 重定向到首页
}

// 密码加密：简单加密和解密示例
function encryptPassword(password) {
    return btoa(password);
}

function decryptPassword(encryptedPassword) {
    return atob(encryptedPassword);
}

// 验证用户名（防止包含特殊字符）
function validateUsername(username) {
    const regex = /^[a-zA-Z0-9_]+$/; // 只允许字母、数字和下划线
    return regex.test(username);
}

// 验证邮箱格式
function validateEmail(email) {
    const regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
}

// 防止 XSS 攻击：转义用户输入中的HTML标签
function sanitizeInput(input) {
    const element = document.createElement('div');
    if (input) {
        element.innerText = input;
        return element.innerHTML;  // 返回HTML转义后的文本
    }
    return "";
}

// 用户注册函数
function registerUser() {
    const username = sanitizeInput(document.getElementById("username").value.trim());
    const email = sanitizeInput(document.getElementById("email").value.trim());
    const password = sanitizeInput(document.getElementById("password").value.trim());

    if (!validateUsername(username)) {
        alert("用户名只能包含字母、数字和下划线！");
        return;
    }

    if (!validateEmail(email)) {
        alert("请输入有效的邮箱地址！");
        return;
    }

    if (username && email && password) {
        // 加密密码
        const encryptedPassword = encryptPassword(password);

        // 检查用户是否已存在
        const existingUser = users.find(u => u.username === username);
        if (existingUser) {
            alert("该用户名已被注册，请选择其他用户名。");
            return;
        }

        users.push({ username, email, password: encryptedPassword });
        alert("注册成功！请登录。");
        window.location.href = "login.html";
    } else {
        alert("请填写所有字段。");
    }
}

// 用户登录函数
function loginUser() {
    const username = sanitizeInput(document.getElementById("username").value.trim());
    const password = sanitizeInput(document.getElementById("password").value.trim());
    const encryptedPassword = encryptPassword(password); // 加密输入的密码

    const user = users.find(u => u.username === username && u.password === encryptedPassword);

    if (user) {
        alert("登录成功！");
        window.location.href = "index.html"; // 跳转到首页
    } else {
        alert("用户名或密码错误。");
    }
}

// 预设的占位账号信息
const placeholderUsers = {
    "user": "123456",  // 普通用户
    "admin": "admin1223456"  // 管理员
};

// 登录功能
function loginUser() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    // 验证用户名和密码是否匹配
    if (placeholderUsers[username] && placeholderUsers[username] === password) {
        // 创建用户信息并加密密码
        const user = {
            username: username,
            password: encryptPassword(password), // 保存加密后的密码
            donations: 100, // 默认捐赠金额
            completedActivities: ['Democracy Training Camp'], // 默认完成的活动
            upcomingActivities: ['Sprints Training Camp'] // 默认即将参加的活动
        };
        
        localStorage.setItem('user', JSON.stringify(user));  // 存储用户信息
        window.location.href = "profile.html";  // 登录成功后跳转到用户个人页面
    } else {
        alert("用户名或密码错误！");
    }
}

// 加密密码函数
function encryptPassword(password) {
    return btoa(password); // 使用 Base64 加密
}
