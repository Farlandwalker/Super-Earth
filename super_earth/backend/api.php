<?php
// 设置数据库连接信息
$host = 'localhost';          // 数据库主机
$dbname = 'super_earth';      // 数据库名称
$username = 'root';           // 数据库用户名（默认是 root）
$password = '';               // 数据库密码（默认是空）

// 创建 PDO 实例
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // 设置字符集为 utf8mb4
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    echo json_encode(['message' => '数据库连接失败: ' . $e->getMessage()]);
    exit;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取 JSON 格式的请求体
    $data = json_decode(file_get_contents("php://input"));

    // 注册功能
    if (isset($data->username) && isset($data->email) && isset($data->password)) {
        $username = $data->username;
        $email = $data->email;
        $password = $data->password;

        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            // 检查 email 是否已存在
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => '该邮箱已被注册']);
            } else {
                // 插入新用户到数据库
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->execute();

                echo json_encode(['message' => '注册成功']);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => '注册失败: ' . $e->getMessage()]);
        }
    }

    // 登录功能
    if (isset($_GET['action']) && $_GET['action'] == 'login') {
        $data = json_decode(file_get_contents("php://input"));

        if (isset($data->email) && isset($data->password)) {
            $email = $data->email;
            $password = $data->password;

            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user['password'])) {
                        session_start();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];

                        echo json_encode([
                            'message' => '登录成功',
                            'user_id' => $user['id'],
                            'username' => $user['username']
                        ]);
                    } else {
                        echo json_encode(['message' => '密码错误']);
                    }
                } else {
                    echo json_encode(['message' => '该邮箱尚未注册']);
                }
            } catch (PDOException $e) {
                echo json_encode(['message' => '登录失败: ' . $e->getMessage()]);
            }
        }
    }
}

// 获取用户信息
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_user_info') {
    session_start();

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]);
            } else {
                echo json_encode(['message' => '用户信息未找到']);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => '获取用户信息失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '请先登录']);
    }
}

// 更新用户信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update_user_info') {
    session_start();

    if (isset($_SESSION['user_id'])) {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';

        if (empty($username) || empty($email)) {
            echo json_encode(['message' => '用户名和电子邮件不能为空']);
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => '电子邮件已被其他用户使用']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            echo json_encode(['message' => '用户信息更新成功']);
        } catch (PDOException $e) {
            echo json_encode(['message' => '更新用户信息失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '请先登录']);
    }
}

// 修改密码
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update_password') {
    session_start();

    if (isset($_SESSION['user_id'])) {
        $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

        if (empty($old_password) || empty($new_password)) {
            echo json_encode(['message' => '旧密码和新密码不能为空']);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($old_password, $user['password'])) {
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                    $stmt->bindParam(':password', $hashed_new_password);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->execute();

                    echo json_encode(['message' => '密码修改成功']);
                } else {
                    echo json_encode(['message' => '旧密码不正确']);
                }
            } else {
                echo json_encode(['message' => '用户信息未找到']);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => '修改密码失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '请先登录']);
    }
}

// 注销功能
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'logout') {
    // 验证用户是否已登录（通过会话）
    session_start();

    // 销毁会话
    session_destroy();

    echo json_encode(['message' => '注销成功']);
}

// 创建慈善活动
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'create_event') {
    // 获取请求体中的数据
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->name) && isset($data->description) && isset($data->start_date) && isset($data->end_date) && isset($data->location) && isset($data->status)) {
        $name = $data->name;
        $description = $data->description;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $location = $data->location;
        $status = $data->status;
        $created_at = date('Y-m-d H:i:s'); // 获取当前时间

        try {
            // 插入新活动到数据库
            $stmt = $pdo->prepare("INSERT INTO charity_events (name, description, start_date, end_date, location, status, created_at) 
                                    VALUES (:name, :description, :start_date, :end_date, :location, :status, :created_at)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->execute();

            echo json_encode(['message' => '活动创建成功']);
        } catch (PDOException $e) {
            echo json_encode(['message' => '创建活动失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '缺少必要的字段']);
    }
}

// 获取所有活动
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_all_events') {
    try {
        // 查询所有活动
        $stmt = $pdo->prepare("SELECT * FROM charity_events");
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($events) > 0) {
            echo json_encode($events);
        } else {
            echo json_encode(['message' => '没有找到活动']);
        }
    } catch (PDOException $e) {
        echo json_encode(['message' => '获取活动失败: ' . $e->getMessage()]);
    }
}

// 获取单个活动
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_event') {
    // 获取活动ID
    if (isset($_GET['id'])) {
        $event_id = $_GET['id'];

        try {
            // 查询活动详情
            $stmt = $pdo->prepare("SELECT * FROM charity_events WHERE id = :id");
            $stmt->bindParam(':id', $event_id);
            $stmt->execute();

            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($event) {
                echo json_encode($event);
            } else {
                echo json_encode(['message' => '未找到该活动']);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => '获取活动失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '缺少活动ID']);
    }
}

// 更新活动
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update_event') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->id) && isset($data->name) && isset($data->description) && isset($data->start_date) && isset($data->end_date) && isset($data->location) && isset($data->status)) {
        $event_id = $data->id;
        $name = $data->name;
        $description = $data->description;
        $start_date = $data->start_date;
        $end_date = $data->end_date;
        $location = $data->location;
        $status = $data->status;

        try {
            // 更新活动
            $stmt = $pdo->prepare("UPDATE charity_events SET name = :name, description = :description, start_date = :start_date, end_date = :end_date, location = :location, status = :status WHERE id = :id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $event_id);
            $stmt->execute();

            echo json_encode(['message' => '活动更新成功']);
        } catch (PDOException $e) {
            echo json_encode(['message' => '更新活动失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '缺少必要的字段']);
    }
}

// 删除活动
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'delete_event') {
    // 获取活动ID
    if (isset($_POST['id'])) {
        $event_id = $_POST['id'];

        try {
            // 删除活动
            $stmt = $pdo->prepare("DELETE FROM charity_events WHERE id = :id");
            $stmt->bindParam(':id', $event_id);
            $stmt->execute();

            echo json_encode(['message' => '活动删除成功']);
        } catch (PDOException $e) {
            echo json_encode(['message' => '删除活动失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '缺少活动ID']);
    }
}

// 查看用户所有参与的慈善活动
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_user_events') {
    // 获取用户ID（假设用户登录后，用户ID存在于session或jwt中）
    $user_id = $_SESSION['user_id'];  // 假设用户ID保存在session中

    if (isset($user_id)) {
        try {
            // 查询用户参与的所有活动及其状态
            $stmt = $pdo->prepare("SELECT e.id, e.name, e.description, e.start_date, e.end_date, e.location, p.status 
                                    FROM charity_events e 
                                    JOIN user_event_participation p ON e.id = p.event_id 
                                    WHERE p.user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($events) > 0) {
                echo json_encode($events);
            } else {
                echo json_encode(['message' => '没有参与的活动']);
            }
        } catch (PDOException $e) {
            echo json_encode(['message' => '获取活动失败: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => '用户未登录']);
    }
}

// 修改用户参与活动状态
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update_user_event_status') {
    // 获取请求体中的数据
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->event_id) && isset($data->status)) {
        $event_id = $data->event_id;
        $status = $data->status;
        $user_id = $_SESSION['user_id'];  // 假设用户ID保存在session中

        if (isset($user_id)) {
            try {
                // 更新用户参与活动的状态
                $stmt = $pdo->prepare("UPDATE user_event_participation SET status = :status WHERE user_id = :user_id AND event_id = :event_id");
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':event_id', $event_id);
                $stmt->execute();

                echo json_encode(['message' => '活动状态更新成功']);
            } catch (PDOException $e) {
                echo json_encode(['message' => '更新状态失败: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['message' => '用户未登录']);
        }
    } else {
        echo json_encode(['message' => '缺少必要的字段']);
    }
}

?>
