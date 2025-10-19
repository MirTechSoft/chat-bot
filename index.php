<?php
session_start();

// Agar username nahi set, to random assign karo
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "User_" . rand(1000, 9999);
}

// Direct redirect to chat page
header("Location: chat.php");
exit();
?>
