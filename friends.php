<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $friend_id = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
    
    // Validate friend_id
    if ($friend_id == $user_id) {
        $error = "You cannot add yourself as a friend.";
    } else {
        // Check if friend_id exists in users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$friend_id]);
        if (!$stmt->fetch()) {
            $error = "User does not exist.";
        } else {
            try {
                // Check if friend request already exists
                $stmt = $pdo->prepare("SELECT id FROM friends WHERE user_id = ? AND friend_id = ?");
                $stmt->execute([$user_id, $friend_id]);
                if ($stmt->fetch()) {
                    $error = "Friend request already sent.";
                } else {
                    // Insert friend request
                    $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
                    $stmt->execute([$user_id, $friend_id]);
                    $success = "Friend request sent successfully!";
                }
            } catch (PDOException $e) {
                $error = "Error sending friend request: " . $e->getMessage();
            }
        }
    }
}

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? AND id != ?");
$stmt->execute(["%$search%", $user_id]);
$users = $stmt->fetchAll();

$friends = $pdo->prepare("SELECT u.* FROM users u JOIN friends f ON u.id = f.friend_id WHERE f.user_id = ? AND f.status = 'accepted'");
$friends->execute([$user_id]);
$friend_list = $friends->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .search-form { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .search-form input { width: 70%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .search-form button { background: #1877f2; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .user { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .nav { background: #fff; padding: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #1877f2; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
        .success { color: green; text-align: center; margin-bottom: 10px; }
        @media (max-width: 600px) { .container { padding: 10px; } .search-form input { width: 60%; } }
    </style>
</head>
<body>
    <div class="nav">
        <a href="#" onclick="redirect('index.php')">Home</a>
        <a href="#" onclick="redirect('profile.php')">Profile</a>
        <a href="#" onclick="redirect('messages.php')">Messages</a>
        <a href="#" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2>Friends</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search users" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
        <?php foreach ($users as $user): ?>
            <div class="user">
                <span><?php echo htmlspecialchars($user['username']); ?></span>
                <form method="POST">
                    <input type="hidden" name="friend_id" value="<?php echo $user['id']; ?>">
                    <button type="submit">Add Friend</button>
                </form>
            </div>
        <?php endforeach; ?>
        <h3>Your Friends</h3>
        <?php foreach ($friend_list as $friend): ?>
            <div class="user">
                <span><?php echo htmlspecialchars($friend['username']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
