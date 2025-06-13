<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id IN (SELECT friend_id FROM friends WHERE user_id = ? AND status = 'accepted') OR user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id, $user_id]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Clone - News Feed</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 10px; text-align: center; }
        .post-form { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .post-form textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
        .post-form button { background: #1877f2; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .post { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .post img { max-width: 100%; border-radius: 5px; }
        .interaction { display: flex; gap: 10px; }
        .interaction button { background: #f0f2f5; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .nav { background: #fff; padding: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #1877f2; }
        @media (max-width: 600px) { .container { padding: 10px; } .post-form textarea { font-size: 14px; } }
    </style>
</head>
<body>
    <div class="nav">
        <a href="#" onclick="redirect('profile.php')">Profile</a>
        <a href="#" onclick="redirect('friends.php')">Friends</a>
        <a href="#" onclick="redirect('messages.php')">Messages</a>
        <a href="#" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="container">
        <h2 class="header">News Feed</h2>
        <div class="post-form">
            <form action="post.php" method="POST" enctype="multipart/form-data">
                <textarea name="content" placeholder="What's on your mind?" required></textarea>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Post</button>
            </form>
        </div>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($post['user_id']); ?></strong> - <?php echo $post['created_at']; ?></p>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <?php if ($post['image']): ?>
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                <?php endif; ?>
                <div class="interaction">
                    <form action="like.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit">Like</button>
                    </form>
                    <form action="comment.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="text" name="comment" placeholder="Add a comment" required>
                        <button type="submit">Comment</button>
                    </form>
                </div>
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
