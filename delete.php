<?php
session_start();
include 'db.php';

$session_id = session_id();
$conn->query("DELETE FROM messages WHERE session_id = '$session_id'");

header("Location: chat.php");
exit;
?>
