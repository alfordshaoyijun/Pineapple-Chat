<?php
// 连接数据库
$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 定义消息变量
$message = "";

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $phone_number = $_POST['phone_number'];
    $area_code = $_POST['area_code'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 加密密码

    // 检查可选字段
    $nickname = !empty($_POST['nickname']) ? $_POST['nickname'] : 'New User';
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : 'other';
    $bio = !empty($_POST['bio']) ? $_POST['bio'] : 'This user is lazy and left nothing.';

    // 处理头像上传
    $avatar = 'default_avatar.png';  // 默认头像
    if ($_FILES['avatar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $avatar = $target_dir . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar)) {
            // 上传成功
        } else {
            $message = "Failed to upload avatar.";
        }
    }

    // 检查是否有相同ID的用户
    $sql_check = "SELECT * FROM users WHERE id='$id'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $message = "User ID already exists, please use another ID.";
    } else {
        // 插入用户数据
        $sql = "INSERT INTO users (id, phone_number, area_code, password, nickname, gender, avatar, bio) 
                VALUES ('$id', '$phone_number', '$area_code', '$password', '$nickname', '$gender', '$avatar', '$bio')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful! <a href='index.php'>Login now</a>";
        } else {
            $message = "Registration failed: " . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> <!-- 预防缩放问题 -->
    <title>LiuChat Registration</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #74ebd5, #ACB6E5); /* 背景渐变 */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 20px; /* 增加顶部外边距，防止遮挡 */
        }

        h1 {
            font-size: 26px;
            margin-bottom: 30px;
            color: #007aff;
            text-align: center;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-group {
            flex: 1 1 calc(50% - 20px);
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
        }

        input[type="text"], input[type="password"], select, textarea, input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus, select:focus, textarea:focus {
            border-color: #007aff;
            box-shadow: 0 0 8px rgba(0, 122, 255, 0.2);
        }

        .full-width {
            flex: 1 1 100%;
        }

        input[type="submit"] {
            background-color: #007aff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #005bb5;
        }

        .message-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            display: <?php echo !empty($message) ? 'block' : 'none'; ?>;
            z-index: 1000;
        }

        .message-box p {
            font-size: 16px;
            color: #333;
        }

        .message-box a {
            color: #007aff;
            text-decoration: none;
        }

        .message-box a:hover {
            text-decoration: underline;
        }

        /* 针对手机用户的优化 */
        @media (max-width: 768px) {
            .form-group {
                flex: 1 1 100%; /* 变为单列 */
            }

            .container {
                max-width: 90%;
                padding: 20px 30px; /* 增加左右空白区域 */
                margin-top: 40px; /* 针对小屏增加顶部空间，防止遮挡 */
            }

            input[type="text"], input[type="password"], select, textarea {
                font-size: 14px;
                padding: 10px;
            }

            h1 {
                font-size: 22px;
            }

            input[type="submit"] {
                font-size: 15px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px 40px; /* 增加左右空白区域 */
                margin-top: 60px; /* 针对更小屏幕的顶部间距 */
            }

            h1 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            input[type="text"], input[type="password"], select, textarea {
                padding: 8px;
                font-size: 13px;
            }

            input[type="submit"] {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Register to LiuChat</h1>

    <form method="POST" action="register.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">User ID:</label>
            <input type="text" name="id" required>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" required>
        </div>

        <div class="form-group">
            <label for="area_code">Area Code:</label>
            <input type="text" name="area_code" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="nickname">Nickname (Optional):</label>
            <input type="text" name="nickname">
        </div>

        <div class="form-group">
            <label for="gender">Gender (Optional):</label>
            <select name="gender">
                <option value="other">Other</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <div class="form-group full-width">
            <label for="avatar">Upload Avatar (Optional):</label>
            <input type="file" name="avatar" accept="image/*">
        </div>

        <div class="form-group full-width">
            <label for="bio">Bio (Optional):</label>
            <textarea name="bio" rows="4"></textarea>
        </div>

        <input type="submit" value="Register">
    </form>
</div>

<div class="message-box">
    <p><?php echo $message; ?></p>
</div>

</body>
</html>
