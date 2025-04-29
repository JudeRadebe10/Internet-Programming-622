<!-- message.php -->
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    
    // Insert message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Get users list for messaging
$users = $conn->query("SELECT id, name FROM users WHERE id != $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Message</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <h1>Send Message</h1>
        <a href="dashboard.php">Back to Dashboard</a>
    </header>

    <div class="container">
        <form method="POST">
            <label for="receiver">Select a user to message:</label>
            <select name="receiver_id" required>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <textarea name="message" placeholder="Type your message here..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
