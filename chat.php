<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// 获取当前用户和好友ID
$user_id = $_SESSION['id'];
$friend_id = $_GET['friend_id'];

$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取当前用户信息
$sql_user = "SELECT * FROM users WHERE id='$user_id'";
$result_user = $conn->query($sql_user);
$user_info = $result_user->fetch_assoc();

// 获取好友信息
$sql_friend = "SELECT * FROM users WHERE id='$friend_id'";
$result_friend = $conn->query($sql_friend);
$friend_info = $result_friend->fetch_assoc();

// 获取聊天记录
$sql_messages = "
    SELECT * FROM messages 
    WHERE (sender_id='$user_id' AND receiver_id='$friend_id') 
    OR (sender_id='$friend_id' AND receiver_id='$user_id')
    ORDER BY message_time ASC
";
$result_messages = $conn->query($sql_messages);

// 删除好友
if (isset($_POST['delete_friend'])) {
    $sql_delete_friend = "DELETE FROM friends WHERE 
                            (sender_id='$user_id' AND receiver_id='$friend_id') 
                         OR (sender_id='$friend_id' AND receiver_id='$user_id')";
    $conn->query($sql_delete_friend);
    header("Location: home.php");
    exit;
}

// 获取用户IP地址
$ip_address = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// 获取地理位置信息
function get_location_by_ip($ip) {
    $url = "http://ipinfo.io/{$ip}/json";  // 使用 ipinfo.io API
    $json = file_get_contents($url);
    $details = json_decode($json, true);
    return isset($details['city']) && isset($details['region']) && isset($details['country']) 
        ? $details['city'] . ', ' . $details['region'] . ', ' . $details['country'] 
        : 'Unknown location';
}

// 处理发送新消息
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $content = $_POST['message'];
    $device_info = $_SERVER['HTTP_USER_AGENT'];
    $location = get_location_by_ip($ip_address);

    // 插入消息
    $sql_insert = "INSERT INTO messages (sender_id, receiver_id, content, ip_address, device_info, location) 
                   VALUES ('$user_id', '$friend_id', '$content', '$ip_address', '$device_info', '$location')";
    $conn->query($sql_insert);

    header("Location: chat.php?friend_id=$friend_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo $friend_info['nickname']; ?></title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #f2f2f2;
            scroll-behavior: smooth;
            overscroll-behavior-y: contain; /* 防止弹性回弹效果 */
        }

        header {
            padding: 15px;
            background-color: #1f8ef1;
            color: white;
            text-align: center;
            font-size: 20px;
            position: relative;
        }

        header .back-btn {
            position: absolute;
            left: 10px;
            top: 15px;
            background-color: white;
            color: #1f8ef1;
            border: none;
            padding: 5px 10px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
        }

        .chat-container {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #e8eaf6;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .message .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
        }

        .message .content {
            max-width: 75%;
            padding: 10px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .message .content p {
            margin: 0;
            color: #333;
        }

        .message .info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sender {
            flex-direction: row-reverse;
        }

        .sender .content {
            background-color: #1f8ef1;
            color: white;
        }

        form {
            display: flex;
            padding: 15px;
            background-color: white;
            border-top: 1px solid #ddd;
        }

        input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 30px;
            margin-right: 10px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #1f8ef1;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Overlay for friend info */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(10px); /* 毛玻璃效果 */
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay .content {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            width: 320px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .overlay h3 {
            margin-top: 0;
            font-size: 22px;
            color: #333;
        }

        .close-btn {
            background-color: #1f8ef1;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 15px;
        }

        .delete-btn {
            background-color: #ff4b4b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
        }

        .delete-btn:hover {
            background-color: #ff2e2e;
        }
    </style>
    <script>
        // Function to open the overlay
        function openOverlay() {
            document.getElementById('overlay').style.display = 'flex';
        }

        // Function to close the overlay
        function closeOverlay() {
            document.getElementById('overlay').style.display = 'none';
        }

        // Function to fetch latest messages
        function fetchMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_messages.php?friend_id=<?php echo $friend_id; ?>', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.querySelector('.chat-container').innerHTML = this.responseText;
                }
            };
            xhr.send();
        }

        // 每隔5秒刷新一次聊天记录
        setInterval(fetchMessages, 500);
    </script>
</head>
<body>

<header>
    <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
    Chat with <?php echo $friend_info['nickname']; ?>
</header>

<div class="chat-container">
    <?php while ($message = $result_messages->fetch_assoc()): ?>
    <div class="message <?php echo $message['sender_id'] == $user_id ? 'sender' : ''; ?>">
        <img 
            src="<?php echo $message['sender_id'] == $user_id ? $user_info['avatar'] : $friend_info['avatar']; ?>" 
            class="avatar" 
            onclick="openOverlay()">
        <div class="content">
            <p><?php echo $message['content']; ?></p>
            <div class="info">
                <span>Time: <?php echo $message['message_time']; ?></span>
                <span>IP: <?php echo $message['ip_address']; ?></span>
                <span>Location: <?php echo $message['location']; ?></span>
                <span>Device: <?php echo $message['device_info']; ?></span>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<form method="POST" action="chat.php?friend_id=<?php echo $friend_id; ?>">
    <input type="text" name="message" placeholder="Type your message...">
    <input type="submit" value="Send">
</form>

<!-- Friend info overlay -->
<div class="overlay" id="overlay">
    <div class="content">
        <h3><?php echo $friend_info['nickname']; ?></h3>
        <p>ID: <?php echo $friend_info['id']; ?></p>
        <p>Gender: <?php echo $friend_info['gender'] === 'male' ? 'Male' : ($friend_info['gender'] === 'female' ? 'Female' : 'Other'); ?></p>
        <p>Bio: <?php echo $friend_info['bio']; ?></p>
        <button class="close-btn" onclick="closeOverlay()">Close</button>
        <form method="POST">
            <button type="submit" class="delete-btn" name="delete_friend">Delete Friend</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
