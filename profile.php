<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];

// 获取用户信息
$sql_user = "SELECT * FROM users WHERE id='$user_id'";
$result_user = $conn->query($sql_user);
$user_info = $result_user->fetch_assoc();

// 处理编辑个人信息
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $updates = [];
    
    if (!empty($_POST['nickname'])) {
        $nickname = $_POST['nickname'];
        $updates[] = "nickname='$nickname'";
    }
    
    if (!empty($_POST['phone_number'])) {
        $phone_number = $_POST['phone_number'];
        $updates[] = "phone_number='$phone_number'";
    }
    
    if (!empty($_POST['area_code'])) {
        $area_code = $_POST['area_code'];
        $updates[] = "area_code='$area_code'";
    }
    
    if (!empty($_POST['bio'])) {
        $bio = $_POST['bio'];
        $updates[] = "bio='$bio'";
    }
    
    if (!empty($_POST['gender'])) {
        $gender = $_POST['gender'];
        $updates[] = "gender='$gender'";
    }
    
    // 处理头像上传
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $avatar_path = $target_dir . basename($_FILES['avatar']['name']);
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
            $updates[] = "avatar='$avatar_path'";
        } else {
            echo "头像上传失败";
        }
    }
    
    if (!empty($updates)) {
        $sql_update = "UPDATE users SET " . implode(', ', $updates) . " WHERE id='$user_id'";
        if ($conn->query($sql_update) === TRUE) {
            echo "个人信息已更新！";
        } else {
            echo "更新失败：" . $conn->error;
        }
    }

    // 刷新页面
    header("Location: profile.php");
    exit;
}

// 处理账户注销
if (isset($_POST['delete'])) {
    // 删除用户
    $sql_delete = "DELETE FROM users WHERE id='$user_id'";
    $conn->query($sql_delete);

    // 注销后跳转回登录页面
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>编辑个人信息</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
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
            margin-top: 20px;
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

        input[type="text"], input[type="file"], select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: #007aff;
            box-shadow: 0 0 8px rgba(0, 122, 255, 0.2);
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 2px solid #ddd;
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

        .btn-delete {
            background-color: red;
            margin-top: 10px;
        }

        /* 手机优化 */
        @media (max-width: 768px) {
            .form-group {
                flex: 1 1 100%;
            }

            .container {
                max-width: 90%;
                padding: 20px 30px;
            }

            input[type="text"], input[type="file"], select, textarea {
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
                padding: 20px 40px;
                margin-top: 60px;
            }

            h1 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            input[type="text"], input[type="file"], select, textarea {
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
    <h1>编辑个人信息</h1>

    <form method="POST" action="profile.php" enctype="multipart/form-data">
        <div class="form-group full-width">
            <label for="avatar">当前头像：</label>
            <img src="<?php echo $user_info['avatar']; ?>" alt="头像" class="avatar-preview">
        </div>

        <div class="form-group full-width">
            <label for="avatar">上传新头像：</label>
            <input type="file" name="avatar" accept="image/*">
        </div>

        <div class="form-group">
            <label for="nickname">昵称：</label>
            <input type="text" name="nickname" value="<?php echo $user_info['nickname']; ?>">
        </div>

        <div class="form-group">
            <label for="phone_number">手机号：</label>
            <input type="text" name="phone_number" value="<?php echo $user_info['phone_number']; ?>">
        </div>

        <div class="form-group">
            <label for="area_code">区号：</label>
            <input type="text" name="area_code" value="<?php echo $user_info['area_code']; ?>">
        </div>

        <div class="form-group full-width">
            <label for="bio">个性签名：</label>
            <textarea name="bio" rows="4"><?php echo $user_info['bio']; ?></textarea>
        </div>

        <div class="form-group">
            <label for="gender">性别：</label>
            <select name="gender">
                <option value="male" <?php if ($user_info['gender'] == 'male') echo 'selected'; ?>>男</option>
                <option value="female" <?php if ($user_info['gender'] == 'female') echo 'selected'; ?>>女</option>
                <option value="other" <?php if ($user_info['gender'] == 'other') echo 'selected'; ?>>其他</option>
            </select>
        </div>

        <input type="submit" name="update" value="更新信息">

        <input type="submit" name="delete" value="注销账户" class="btn-delete">

        <button class="btn-back" onclick="window.location.href='home.php'">返回主页</button>
    </form>
</div>

</body>
</html>
