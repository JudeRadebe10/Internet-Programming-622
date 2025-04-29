<?php
session_start();
include 'db.php';

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $profile_picture = '';

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . uniqid() . "_" . $image_name;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $profile_picture = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $profile_picture);

    if ($stmt->execute()) {
        header("Location: login.php");
    } else {
        echo "Registration failed.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <h1>Register</h1>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="file" name="profile_picture" accept="image/*"><br>
            <button type="submit" name="register">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>
