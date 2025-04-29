<!-- forgot_password.php -->
<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(50)); 
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Send password reset link via email
        $reset_link = "http://localhost/Whatsapp_clone/reset_password.php?token=" . $token;
        mail($email, "Password Reset Request", "Click this link to reset your password: " . $reset_link);

        echo "Please check your email for the password reset link.";
    } else {
        echo "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <h1>Forgot Password</h1>
    </header>
    
    <div class="auth-container">
        <form method="POST">
            <label for="email">Enter your Email:</label>
            <input type="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
