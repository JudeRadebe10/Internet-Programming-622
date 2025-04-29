<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['update_picture'])) {
    $user_id = $_SESSION['user_id'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . uniqid() . "_" . $image_name;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: profile.php");
    exit();
}
?>
