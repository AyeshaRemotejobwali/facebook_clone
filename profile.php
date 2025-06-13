<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE id = ?");
    $stmt->execute([$name, $bio, $user_id]);
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .profile-header { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .profile-header img { width: 100px; height: 100px; border-radius: 50%; }
        .profile-form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        .profile-form input, .profile-form textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .profile-form button { background: #1877f2; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .nav { background: #fff; padding: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #1877f2; }
        @media (max-width: 600px) { .container { padding: 10px; } .profile-header img { width: 80px; height: 80px; } }
    </style>
</head>
<body>
    <div class="nav">
        <a href="#" onclick="redirect('index.php')">Home</a>
        <a href="#" onclick="redirect('friends.php')">Friends</a>
        <a href="#" onclick="redirect('messages.php')">Messages</a>
        <a href="#" onclick="redirect('logout.php')">Logout</a>
    </div>
    <div class="container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p><?php echo htmlspecialchars($user['bio'] ?? 'No bio'); ?></p>
        </div>
        <div class="profile-form">
            <form method="POST">
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <textarea name="bio" placeholder="Bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
