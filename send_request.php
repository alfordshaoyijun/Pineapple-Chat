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
$search_id = $_POST['search_id'];

// 检查目标用户是否存在
$sql = "SELECT * FROM users WHERE id='$search_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 检查是否已经是好友或者已经有申请
    $sql_check = "
        SELECT * FROM friends 
        WHERE (sender_id='$user_id' AND receiver_id='$search_id') 
        OR (sender_id='$search_id' AND receiver_id='$user_id')
    ";
    $check_result = $conn->query($sql_check);
    
    if ($check_result->num_rows > 0) {
        // 如果已经是好友或申请存在
        $row = $check_result->fetch_assoc();
        if ($row['status'] == 'accepted') {
            echo "你们已经是好友！";
        } else {
            echo "好友申请已发送，等待对方处理。";
        }
    } else {
        // 发送好友申请
        $sql_request = "INSERT INTO friends (sender_id, receiver_id, status) VALUES ('$user_id', '$search_id', 'pending')";
        if ($conn->query($sql_request) === TRUE) {
            echo "好友申请已发送！";
        } else {
            echo "发送失败，请重试。";
        }
    }
} else {
    echo "用户不存在！";
}

$conn->close();

header("Location: home.php");
exit;
?>
