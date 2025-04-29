<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the conversation ID (receiver's ID)
$receiver_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle sending a new message
if (isset($_POST['send_message'])) {
    $message_text = trim($_POST['message_text']);

    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message_text);
        $stmt->execute();
        $stmt->close();
        
        // Reload the page to show the new message
        header("Location: inbox.php?id=" . $receiver_id);
        exit();
    }
}

// Fetch conversation messages if a receiver is selected
$message_result = null;
if ($receiver_id != 0) {
    $messages = $conn->prepare("SELECT messages.*, users.name AS sender_name, users.profile_picture AS sender_profile_picture 
                                FROM messages 
                                JOIN users ON messages.sender_id = users.id 
                                WHERE (messages.sender_id = ? AND messages.receiver_id = ?) 
                                   OR (messages.sender_id = ? AND messages.receiver_id = ?)
                                ORDER BY messages.created_at ASC");
    $messages->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $messages->execute();
    $message_result = $messages->get_result();
}

// Fetch all conversations for sidebar
$conversations = $conn->prepare("SELECT DISTINCT
                                    CASE
                                        WHEN sender_id = ? THEN receiver_id
                                        ELSE sender_id
                                    END AS other_user_id
                                FROM messages
                                WHERE sender_id = ? OR receiver_id = ?");
$conversations->bind_param("iii", $user_id, $user_id, $user_id);
$conversations->execute();
$conversation_result = $conversations->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox - Judify Chat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-body">

<header class="header">
    <h1>Judify Chat</h1>
    <a href="profile.php">Profile</a> | 
    <a href="dashboard.php">Dashboard</a> | 
    <a href="logout.php">Logout</a>
</header>

<div class="container inbox-container">
    <h2>Conversations</h2>

    <!-- List of conversations -->
    <?php while ($conversation = $conversation_result->fetch_assoc()): ?>
        <?php
        $other_user_id = $conversation['other_user_id'];
        $user_query = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
        $user_query->bind_param("i", $other_user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        $user = $user_result->fetch_assoc();
        ?>
        <div class="conversation-card">
            <img src="<?= htmlspecialchars($user['profile_picture'] ?: 'uploads/default.png') ?>" alt="Profile" class="profile-pic">
            <span class="sender-name"><?= htmlspecialchars($user['name']) ?></span>
            <a href="inbox.php?id=<?= $other_user_id ?>" class="view-conversation">View Conversation</a>
        </div>
    <?php endwhile; ?>

    <?php if ($message_result): ?>
        <h2>Conversation with <?= htmlspecialchars($user['name']) ?></h2>

        <!-- Chat messages -->
        <?php while ($message = $message_result->fetch_assoc()): ?>
            <div class="inbox-message">
                <div class="message-header">
                    <img src="<?= htmlspecialchars($message['sender_profile_picture'] ?: 'uploads/default.png') ?>" alt="Profile" class="profile-pic">
                    <span class="sender-name"><?= htmlspecialchars($message['sender_name']) ?></span>
                </div>
                <div class="message-body">
                    <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- New message form -->
        <form method="POST" class="inbox-form">
            <h3>Send a New Message</h3>
            <textarea name="message_text" placeholder="Type your message..." required></textarea>
            <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
            <button type="submit" name="send_message">Send Message</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
