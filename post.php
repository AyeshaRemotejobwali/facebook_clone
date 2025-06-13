<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $image]);
    header("Location: index.php");
    exit();
}
?>
