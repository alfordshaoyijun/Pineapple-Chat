<?php
session_start();

// 确保用户已登录
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// 连接数据库
$conn = new mysqli('localhost', 'root', '', 'liuchat');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 获取当前登录用户的信息
$user_id = $_SESSION['id'];
$sql_user = "SELECT * FROM users WHERE id='$user_id'";
$result_user = $conn->query($sql_user);
$user_info = $result_user->fetch_assoc();

// 获取好友列表及最近消息
$sql_friends = "
    SELECT u.id, u.nickname, u.avatar, m.content AS last_message, m.message_time 
    FROM friends f 
    JOIN users u ON (f.receiver_id = u.id OR f.sender_id = u.id)
    LEFT JOIN messages m ON m.id = (
        SELECT MAX(id) FROM messages 
        WHERE (sender_id = u.id AND receiver_id = '$user_id') 
        OR (sender_id = '$user_id' AND receiver_id = u.id)
    )
    WHERE f.status = 'accepted' 
    AND ((f.sender_id = '$user_id' AND u.id = f.receiver_id) 
         OR (f.receiver_id = '$user_id' AND u.id = f.sender_id))
    GROUP BY u.id";
$result_friends = $conn->query($sql_friends);

// 获取好友申请
$sql_requests = "
    SELECT u.id, u.nickname, u.avatar 
    FROM friends f 
    JOIN users u ON f.sender_id = u.id 
    WHERE f.receiver_id = '$user_id' AND f.status = 'pending'
";
$result_requests = $conn->query($sql_requests);

// 获取发送的好友申请
$sql_sent_requests = "
    SELECT u.id, u.nickname, u.avatar 
    FROM friends f 
    JOIN users u ON f.receiver_id = u.id 
    WHERE f.sender_id = '$user_id' AND f.status = 'pending'
";
$result_sent_requests = $conn->query($sql_sent_requests);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiuChat Home</title>
    <style>
        /* Background with multi-color vibrant gradient */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2, #f54ea2, #ff7676);
            background-size: 300% 300%;
            animation: gradientAnimation 15s ease infinite;
            margin: 0;
            padding: 0;
            color: #333;
            overflow-x: hidden; /* 禁止左右滚动 */
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        /* Header (profile section) with unique background */
        header {
            background-color: #ffffffdd;
            padding: 30px 20px; /* 增加顶部 padding 适配 iPhone 灵动岛 */
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: white;
        }

        .profile-info {
            display: flex;
            align-items: center;
        }

        .profile-info img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-right: 20px;
        }

        .profile-details {
            flex-grow: 1;
        }

        .profile-details h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .profile-details p {
            margin: 5px 0;
            font-size: 16px;
        }

        .profile-details small {
            display: block;
            font-size: 14px;
            color: #e0e0e0;
        }

        .logout-btn {
            background-color: white;
            border: none;
            color: #007aff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #005bb5;
            color: white;
        }

        /* Section (module design) */
        section {
            background-color: #ffffffdd;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .card {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .card p {
            margin: 0;
            font-size: 16px;
            flex-grow: 1;
        }

        .card small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #888;
        }

        .action-btn {
            background-color: #007aff;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s ease;
        }

        .action-btn:hover {
            background-color: #005bb5;
        }

        /* Search bar design */
        .search {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 15px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: none;
            border-radius: 30px;
            background-color: #f0f0f5;
            font-size: 16px; /* 增加字体大小 */
            margin-right: 10px;
            transition: 0.3s ease;
        }

        .search input[type="text"]:focus {
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 122, 255, 0.2);
        }

        .search input[type="submit"] {
            background-color: #007aff;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            border: none;
            transition: 0.3s ease;
        }

        .search input[type="submit"]:hover {
            background-color: #005bb5;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .profile-info img {
                width: 80px;
                height: 80px;
            }

            .profile-details h1 {
                font-size: 24px;
            }

            .card {
                padding: 15px;
            }

            .card img {
                width: 50px;
                height: 50px;
            }

            .search {
                flex-direction: column;
            }

            .search input[type="text"] {
                width: 100%; /* 确保输入框在手机上占满宽度 */
                margin-right: 0;
                margin-bottom: 10px;
            }

            .container {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .profile-info img {
                width: 60px;
                height: 60px;
            }

            .profile-details h1 {
                font-size: 20px;
            }

            .card img {
                width: 40px;
                height: 40px;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div class="profile-info">
            <img src="<?php echo $user_info['avatar']; ?>" alt="Profile Avatar">
            <div class="profile-details">
                <h1><?php echo $user_info['nickname']; ?></h1>
                <p>ID: <?php echo $user_info['id']; ?></p>
                <p>Gender: <?php echo $user_info['gender'] === 'male' ? 'Male' : ($user_info['gender'] === 'female' ? 'Female' : 'Other'); ?></p>
                <p>Bio: <?php echo $user_info['bio']; ?></p>
            </div>
        </div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
    </header>

    <section>
        <h2>Friends List</h2>
        <?php if ($result_friends->num_rows > 0): ?>
            <?php while ($friend = $result_friends->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $friend['avatar']; ?>" alt="Friend Avatar">
                <p><?php echo $friend['nickname']; ?><small>Last message: <?php echo $friend['last_message'] ?: "No messages"; ?><br>Time: <?php echo $friend['message_time'] ?: "No record"; ?></small></p>
                <button class="action-btn" onclick="window.location.href='chat.php?friend_id=<?php echo $friend['id']; ?>'">Chat</button>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No friends yet. Start adding some!</p>
        <?php endif; ?>
    </section>

    <!-- "Search and Add Friends" now comes after Friends List -->
    <section>
        <h2>Search and Add Friends</h2>
        <div class="search">
            <form method="POST" action="send_request.php">
                <input type="text" name="search_id" placeholder="Enter user ID">
                <input type="submit" value="Send Request">
            </form>
        </div>
    </section>

    <section>
        <h2>Friend Requests</h2>
        <?php if ($result_requests->num_rows > 0): ?>
            <?php while ($request = $result_requests->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $request['avatar']; ?>" alt="Request Avatar">
                <p><?php echo $request['nickname']; ?></p>
                <form method="POST" action="handle_request.php">
                    <input type="hidden" name="friend_id" value="<?php echo $request['id']; ?>">
                    <button type="submit" class="action-btn" name="action" value="accept">Accept</button>
                    <button type="submit" class="action-btn" name="action" value="reject">Reject</button>
                </form>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No friend requests at the moment.</p>
        <?php endif; ?>
    </section>

    <section>
        <h2>Pending Friend Requests</h2>
        <?php if ($result_sent_requests->num_rows > 0): ?>
            <?php while ($sent_request = $result_sent_requests->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $sent_request['avatar']; ?>" alt="Pending Request Avatar">
                <p>Waiting for <?php echo $sent_request['nickname']; ?> to respond.</p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No pending requests.</p>
        <?php endif; ?>
    </section>

    <section>
    <h2>Edit Profile</h2>
    <div class="card">
        <p>Edit your personal information.</p>
        <button class="action-btn" onclick="window.location.href='profile.php'">Go to Edit Profile</button>
    </div>
    </section>

</div>

<?php
$conn->close();
?>

</body>
</html>
