<?php
session_start();

// 确保用户已登录
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$user_id = $_SESSION['id'];
$friend_id = $_POST['friend_id'];
$action = $_POST['action'];

if ($action == 'accept') {
    $sql = "UPDATE friends SET status='accepted' WHERE sender_id='$friend_id' AND receiver_id='$user_id'";
} else if ($action == 'reject') {
    $sql = "UPDATE friends SET status='rejected' WHERE sender_id='$friend_id' AND receiver_id='$user_id'";
}

$conn->query($sql);

$conn->close();

header("Location: home.php");
exit;
?>
