<?php
session_start();

// 检查是否有表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $password = $_POST['password'];

    // 连接数据库
    $conn = new mysqli('localhost', 'root', '', 'liuchat');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 查询用户
    $sql = "SELECT * FROM users WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // 获取用户数据
        $row = $result->fetch_assoc();
        
        // 验证密码
        if (password_verify($password, $row['password'])) {
            // 设置会话数据
            $_SESSION['id'] = $row['id'];
            $_SESSION['nickname'] = $row['nickname'];
            $_SESSION['avatar'] = $row['avatar'];

            // 跳转到欢迎页面
            header("Location: welcome.php");
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "User ID does not exist";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiuChat Login</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f7;
            color: #1d1d1f;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.85); 
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s ease-in-out;
        }
        
        h1 {
            font-size: 28px;
            color: #007aff;
            margin-bottom: 30px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        label {
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
            color: #333;
            text-align: left;
            width: 100%;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #007aff;
            box-shadow: 0 0 8px rgba(0, 122, 255, 0.2);
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007aff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005bb5;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #007aff;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 20px;
                border-radius: 10px;
                max-width: 90%;
            }

            h1 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            input[type="text"], input[type="password"] {
                font-size: 13px;
                padding: 10px;
            }

            input[type="submit"] {
                font-size: 14px;
                padding: 10px;
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                padding: 15px;
                border-radius: 8px;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 15px;
            }

            input[type="text"], input[type="password"] {
                font-size: 12px;
                padding: 8px;
            }

            input[type="submit"] {
                font-size: 13px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<video autoplay muted loop>
    <source src="background.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<div class="container">
    <h1>PineappleChat Login</h1>

    <form method="POST" action="index.php">
        <label for="id">User ID:</label>
        <input type="text" name="id" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>

    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</div>

</body>
</html>
