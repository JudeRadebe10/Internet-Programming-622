<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Determine which profile to view
if (isset($_GET['id'])) {
    $profile_id = intval($_GET['id']);
} else {
    $profile_id = $_SESSION['user_id'];
}

// Fetch user details
$stmt = $conn->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-body">
<header class="header">
    <h1>Judify Chat</h1>
    <a href="dashboard.php">Dashboard</a> | 
    <a href="logout.php">Logout</a>
</header>

<div class="container" style="text-align: center; margin-top: 30px;">
    <img src="<?= htmlspecialchars($user['profile_picture'] ?: 'uploads/default.png') ?>" alt="Profile Picture" class="profile-pic-large">
    <h2><?= htmlspecialchars($user['name']) ?></h2>
    <p><?= htmlspecialchars($user['email']) ?></p>

    <?php if ($profile_id == $_SESSION['user_id']): ?>
        <form method="POST" action="update_profile_picture.php" enctype="multipart/form-data" style="margin-top: 20px;">
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit" name="update_picture">Update Profile Picture</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
