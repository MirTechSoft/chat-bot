<?php
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "User_" . rand(1000, 9999);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Chat</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <h2>
  <span>Welcome Chat Clone</span>
  <div style="display: flex; gap: 10px;">
    <button id="faqToggle" class="faq-button">❓</button>
    <button onclick="deleteChat()" class="delete-button">🗑️</button>
  </div>
</h2>

<div id="faqBox" style="display: none; position: absolute; right: 0; top: 40px; background: #ffffffff; border: 1px solid black; padding: 10px 15px; border-radius: 10px; max-height: 200px; overflow-y: auto; width: 250px; z-index: 100;">
  <strong style="color: black;">Common Questions:</strong>
  <ul style="list-style: none; padding-left: 0; color: black;">
 <li><button class="faq-btn" style="color: black;">hi</button></li>
  <li><button class="faq-btn" style="color: black;">you're welcome</button></li>
  <li><button class="faq-btn" style="color: black;">how are you</button></li>
  <li><button class="faq-btn" style="color: black;">who created you</button></li>
  <li><button class="faq-btn" style="color: black;">bye</button></li>
  <li><button class="faq-btn" style="color: black;">thank you</button></li>
  <li><button class="faq-btn" style="color: black;">what can you do</button></li>
  <li><button class="faq-btn" style="color: black;">can you help me</button></li>
  <li><button class="faq-btn" style="color: black;">what is php</button></li>
  <li><button class="faq-btn" style="color: black;">what is html</button></li>
  <li><button class="faq-btn" style="color: black;">what is css</button></li>
  <li><button class="faq-btn" style="color: black;">what is javascript</button></li>
  <li><button class="faq-btn" style="color: black;">tell me a joke</button></li>
  <li><button class="faq-btn" style="color: black;">how to learn coding</button></li>
  <li><button class="faq-btn" style="color: black;">how to make a website</button></li>
</ul>
</div>

  <div id="chat-box"></div>

  <form id="messageForm">
    <input type="text" id="message" placeholder="Type a message..." required />
    <button type="submit">Send</button>
  </form>
<!-- chat -->
  <script>
    function fetchMessages() {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "fetch.php", true);
      xhr.onload = function () {
        document.getElementById("chat-box").innerHTML = this.responseText;
        const chatBox = document.getElementById("chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
      };
      xhr.send();
    }

    setInterval(fetchMessages, 2000); // every 2 sec

    document.getElementById("messageForm").onsubmit = function (e) {
      e.preventDefault();
      const msg = document.getElementById("message").value;
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "insert.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        document.getElementById("message").value = "";
        fetchMessages();
      };
      xhr.send("message=" + encodeURIComponent(msg));
    };

    fetchMessages(); // initial load
  </script>
  <!-- faq's -->
  <script>
  document.getElementById("faqToggle").addEventListener("click", function () {
    const box = document.getElementById("faqBox");
    box.style.display = box.style.display === "none" ? "block" : "none";
  });

  document.querySelectorAll(".faq-btn").forEach(function (btn) {
    btn.style.margin = "5px";
    btn.style.padding = "5px 10px";
    btn.style.borderRadius = "6px";
    btn.style.border = "1px solid black";
    btn.style.backgroundColor = "#dcf8c6";
    btn.style.cursor = "pointer";
    btn.addEventListener("click", function () {
      document.getElementById("message").value = this.textContent;
      document.getElementById("message").focus();
    });
  });
</script>
<!-- delete -->
<script>
function deleteChat() {
  if (confirm("Are you sure you want to delete the entire chat?")) {
    fetch("delete.php", { method: "POST" })
      .then(() => {
        alert("Chat deleted successfully!");
        location.reload(); // refresh page to see empty chat
      });
  }
}
</script>

</body>
</html>
