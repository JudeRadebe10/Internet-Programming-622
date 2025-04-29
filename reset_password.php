<!-- reset_password.php -->
<?php
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $new_password, $token);
            $stmt->execute();
            echo "Password updated successfully!";
        }
    } else {
        echo "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <h1>Reset Password</h1>
    </header>

    <div class="auth-container">
        <form method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
