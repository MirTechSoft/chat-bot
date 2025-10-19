<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
  $_SESSION['username'] = "User_" . rand(1000, 9999);
}

$currentUser = $_SESSION['username'];

// Fetch all messages
$result = $conn->query("SELECT * FROM messages ORDER BY id ASC");

// Output each message
while ($row = $result->fetch_assoc()) {
  $isUser = ($row['username'] === $currentUser);
  $class = $isUser ? "me" : "other";
  $nameLabel = $isUser ? "You" : htmlspecialchars($row['username']);

  echo "
    <div class='chat-row $class'>
      <div class='chat-bubble'>
        <div class='sender'>$nameLabel</div>
        <div class='text'>" . nl2br(htmlspecialchars($row['message'])) . "</div>
      </div>
    </div>
  ";
}
?>

<style>
/* ---------- CHAT ROW LAYOUT ---------- */
.chat-row {
  display: flex;
  margin: 8px 0;
  font-family: 'Poppins', sans-serif;
}

/* User message (right side) */
.chat-row.me {
  justify-content: flex-end;
}

/* Other (bot) message (left side) */
.chat-row.other {
  justify-content: flex-start;
}

/* ---------- CHAT BUBBLE ---------- */
.chat-bubble {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 16px;
  position: relative;
  line-height: 1.4;
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
  word-wrap: break-word;
}

/* ---------- USER BUBBLE ---------- */
.me .chat-bubble {
  background: #dcf8c6; /* WhatsApp light green */
  color: #000;
  border-bottom-right-radius: 4px;
}

/* ---------- OTHER BUBBLE ---------- */
.other .chat-bubble {
  background: #ffffff;
  color: #000;
  border-bottom-left-radius: 4px;
}

/* ---------- SENDER NAME ---------- */
.sender {
  font-size: 12px;
  opacity: 0.6;
  margin-bottom: 4px;
}

/* ---------- TEXT ---------- */
.text {
  font-size: 15px;
  white-space: pre-wrap;
}
</style>

<!-- ✅ Removed All Auto Scroll Scripts -->
