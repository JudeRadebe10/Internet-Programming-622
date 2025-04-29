<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle posting a message (like posts)
if (isset($_POST['post'])) {
    $message = trim($_POST['message']);
    $image_path = '';

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = basename($_FILES['image']['name']);
        $target_file = $target_dir . uniqid() . "_" . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        $image_path = $target_file;
    }

    if (!empty($message) || !empty($image_path)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, message, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $message, $image_path);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle liking a post
if (isset($_GET['like'])) {
    $post_id = intval($_GET['like']);
    $conn->query("UPDATE posts SET likes = likes + 1 WHERE id = $post_id");
    header("Location: dashboard.php");
}

// Handle searching users
$search_results = [];
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string($_GET['search']);
    $result = $conn->query("SELECT id, name, profile_picture FROM users WHERE name LIKE '%$search%'");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}

// Get all posts
$posts = $conn->query("SELECT posts.*, users.name, users.profile_picture FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");

// Handle message sending
if (isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message_text'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Get messages where the user is either sender or receiver
$messages = $conn->query("SELECT messages.*, users.name AS sender_name, users.profile_picture AS sender_profile_picture FROM messages JOIN users ON messages.sender_id = users.id WHERE messages.receiver_id = $user_id OR messages.sender_id = $user_id ORDER BY messages.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-body">

<header class="header">
    <h1>Judify Chat</h1>
    <a href="profile.php">Profile</a> | 
    <a href="logout.php">Logout</a> | 
    <a href="inbox.php">Inbox</a>
</header>

<div class="container">
    <!-- Search Users -->
    <form method="GET" action="dashboard.php" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search users..." 
            style="padding: 10px; border-radius: 8px; border: 1px solid #333; background: #1e1e1e; color: #fff; width: 70%;">
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($search_results)): ?>
        <div class="posts">
            <h2>Search Results:</h2>
            <?php foreach ($search_results as $user): ?>
                <div class="post-card">
                    <div class="post-header">
                        <img src="<?= htmlspecialchars($user['profile_picture'] ?: 'uploads/default.png') ?>" alt="Profile" class="profile-pic">
                        <span><?= htmlspecialchars($user['name']) ?></span>
                        <a href="profile.php?id=<?= $user['id'] ?>" style="margin-left:10px; color: #64b5f6;">View Profile</a>
                        <a href="dashboard.php?message_to=<?= $user['id'] ?>" style="margin-left:10px; color: #64b5f6;">Message</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Message Sending -->
    <?php if (isset($_GET['message_to'])): ?>
        <?php $receiver_id = $_GET['message_to']; ?>
        <form method="POST" class="message-form">
            <h3>Send Message to User</h3>
            <textarea name="message_text" placeholder="Type your message..." required></textarea>
            <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
            <button type="submit" name="send_message">Send Message</button>
        </form>
    <?php endif; ?>

    <!-- Posts -->
    <div class="posts">
        <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="post-card">
                <div class="post-header">
                    <img src="<?= htmlspecialchars($post['profile_picture'] ?: 'uploads/default.png') ?>" alt="Profile" class="profile-pic">
                    <span><?= htmlspecialchars($post['name']) ?></span>
                </div>
                <div class="post-body">
                    <p><?= nl2br(htmlspecialchars($post['message'])) ?></p>
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image" class="post-img">
                    <?php endif; ?>
                </div>
                <div class="post-actions">
                    <a href="?like=<?= $post['id'] ?>">❤️ Like (<?= $post['likes'] ?>)</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
