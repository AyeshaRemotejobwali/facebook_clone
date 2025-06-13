<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $content = $_POST['content'];
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $content]);
}

$friends = $pdo->prepare("SELECT u.* FROM users u JOIN friends f ON u.id = f.friend_id WHERE f.user_id = ? AND f.status = 'accepted'");
$friends->execute([$user_id]);
$friend_list = $friends->fetchAll();

$messages = $pdo->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? OR m.receiver_id = ?) ORDER BY m.created_at");
$messages->execute([$user_id, $user_id]);
$chat_history = $messages->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .message-form { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .message-form select, .message-form textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .message-form button { background: #1877f2; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .message { background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 10px; }
        .nav { background: #fff; padding: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #1877f2; }
        @media (max-width: 600px) { .container { padding: 10px; } .message-form textarea { font-size: 14px; } }
    </style>
</head>
<body>
    <div class="nav">
        <a href="#" onclick="redirect('index.php')">Home</a>
        <a href="#" onclick="redirect('profile.php')">Profile</a>
        <a href="#" onclick="redirect('friends.php')">Friends</a>
        <a href="#" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2>Messages</h2>
        <div class="message-form">
            <form method="POST">
                <select name="receiver_id" required>
                    <option value="">Select Friend</option>
                    <?php foreach ($friend_list as $friend): ?>
                        <option value="<?php echo $friend['id']; ?>"><?php echo htmlspecialchars($friend['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="content" placeholder="Type your message" required></textarea>
                <button type="submit">Send</button>
            </form>
        </div>
        <?php foreach ($chat_history as $message): ?>
            <div class="message">
                <p><strong><?php echo htmlspecialchars($message['username']); ?>:</strong> <?php echo htmlspecialchars($message['content']); ?></p>
                <p><?php echo $message['created_at']; ?></p>
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
