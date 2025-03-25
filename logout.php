<?php
session_start();
session_destroy(); // 清除所有会话数据
header("Location: index.php"); // 重定向回登录页面
exit;
?>
