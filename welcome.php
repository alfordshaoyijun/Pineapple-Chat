<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LiuChat</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #007aff;
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .welcome-message {
            text-align: center;
            animation: fadeOut 3s forwards;
        }

        h1 {
            font-size: 48px;
            margin: 0;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                visibility: hidden;
            }
        }
    </style>
</head>
<body>

<div class="welcome-message">
    <h1>Welcome, <?php echo $_SESSION['nickname']; ?>!</h1>
</div>

<script>
    setTimeout(function() {
        window.location.href = "home.php"; // 跳转到主页
    }, 2000); // 3秒后跳转
</script>

</body>
</html>
