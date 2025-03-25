<?php
session_start();

if (!isset($_SESSION['id'])) {
    exit('Unauthorized');
}

$user_id = $_SESSION['id'];
$friend_id = $_GET['friend_id'];

$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取当前用户和好友的头像信息
$sql_user = "SELECT avatar FROM users WHERE id='$user_id'";
$result_user = $conn->query($sql_user);
$user_info = $result_user->fetch_assoc();

$sql_friend = "SELECT avatar FROM users WHERE id='$friend_id'";
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

while ($message = $result_messages->fetch_assoc()):
?>
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
<?php
endwhile;

$conn->close();
?>
