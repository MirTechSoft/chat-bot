<?php
session_start();
include 'db.php';

$currentUser = $_SESSION['username'];

$result = $conn->query("SELECT * FROM messages ORDER BY id ASC");

while ($row = $result->fetch_assoc()) {
  echo "<div class='message'><strong>" . htmlspecialchars($row['username']) . ":</strong> " . htmlspecialchars($row['message']) . "</div>";
}
?>

<style>
  .message {
  display: block;
  margin: 10px 0;
  padding: 10px 15px;
  background-color: #dcf8c6; /* WhatsApp green */
  border-radius: 8px;
  max-width: 80%;
  font-size: 15px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

  
  p.me {
  background-color: #dcf8c6;
  margin-left: auto;
  text-align: right;
}

p.other {
  background-color: #fff;
  margin-right: auto;
  text-align: left;
}

</style>