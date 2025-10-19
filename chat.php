<?php
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "User_" . rand(1000, 9999);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MirTechSoft ChatBot</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
    body {
      background: radial-gradient(circle at top left, #0f2027, #203a43, #2c5364);
      color: #fff;
      height: 100vh; display: flex; justify-content: center; align-items: center;
    }

    .chat-container {
      display: flex; flex-direction: column;
      height: 90vh; width: 95%; max-width: 600px;
      background: rgba(15,20,25,0.85); backdrop-filter: blur(12px);
      border-radius: 15px; overflow: hidden;
      box-shadow: 0 0 20px rgba(0,255,200,0.15);
      border: 1px solid rgba(0,255,180,0.15);
    }

    h2 {
      background: rgba(0,0,0,0.3); color: #00ffc3; text-align: center;
      padding: 15px; font-size: 20px; font-weight: 600;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid rgba(0,255,200,0.2); letter-spacing: 0.5px;
    }

    .delete-button {
      background: transparent; border: 1px solid #ff3b3b; color: #ff3b3b;
      font-size: 18px; padding: 6px 10px; border-radius: 6px;
      cursor: pointer; transition: 0.3s;
    }
    .delete-button:hover { background: #ff3b3b; color: #fff; transform: scale(1.05); }

    #chat-box {
      flex: 1; overflow-y: auto; padding: 20px;
      background: linear-gradient(180deg, rgba(18,25,32,0.9), rgba(15,20,25,0.9));
    }

    #chat-box::-webkit-scrollbar { width: 6px; }
    #chat-box::-webkit-scrollbar-thumb { background: #00ffc3; border-radius: 4px; }

    .message {
      max-width: 80%; margin-bottom: 14px; padding: 12px 16px;
      border-radius: 18px; line-height: 1.4; word-wrap: break-word;
      display: inline-block; animation: fadeIn 0.3s ease-in;
    }

    .user {
      background: linear-gradient(135deg, #00ffc3, #00bfa5);
      color: #000; align-self: flex-end; border-bottom-right-radius: 3px;
      margin-left: auto; box-shadow: 0 2px 6px rgba(0,255,180,0.3);
    }

    .bot {
      background: rgba(255,255,255,0.08); color: #e0e0e0;
      border-bottom-left-radius: 3px; margin-right: auto;
      box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    }

    form {
      display: flex; background: rgba(0,0,0,0.4);
      padding: 15px; border-top: 1px solid rgba(0,255,200,0.2);
      align-items: center; gap: 10px;
    }

    input[type="text"] {
      flex: 1; padding: 12px 14px; border: none;
      border-radius: 10px; outline: none;
      background: rgba(255,255,255,0.08); color: #fff;
      font-size: 15px; transition: 0.3s;
    }
    input[type="text"]:focus {
      background: rgba(255,255,255,0.15);
      box-shadow: 0 0 8px rgba(0,255,200,0.2);
    }

    button[type="submit"] {
      background: linear-gradient(135deg, #00ffc3, #00bfa5);
      border: none; color: #000; font-weight: 600;
      font-size: 15px; padding: 10px 22px;
      border-radius: 10px; cursor: pointer; transition: 0.3s;
      display: flex; align-items: center; justify-content: center;
    }

    button.loading { pointer-events: none; opacity: 0.7; }
    .loader {
      border: 3px solid rgba(0,0,0,0.2);
      border-top: 3px solid #000;
      border-radius: 50%; width: 16px; height: 16px;
      animation: spin 0.7s linear infinite;
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(5px);}
      to {opacity: 1; transform: translateY(0);}
    }

    /* Typing indicator */
    .typing-indicator {
      display: inline-block;
      background: rgba(255,255,255,0.08);
      color: #ccc;
      border-radius: 18px;
      padding: 10px 14px;
      font-size: 14px;
      margin-bottom: 10px;
      animation: fadeIn 0.3s ease-in;
    }

    @media (max-width: 600px) {
      .chat-container { height: 100vh; border-radius: 0; }
      h2 { font-size: 18px; padding: 10px; }
      input[type="text"] { font-size: 14px; }
    }
  </style>
</head>
<body>

  <div class="chat-container">
    <h2>
      <span>🤖 MirTechSoft ChatBot</span>
      <button onclick="deleteChat()" class="delete-button">🗑️</button>
    </h2>

    <div id="chat-box"></div>

    <!-- Typing Indicator -->
    <div id="typingIndicator" style="display:none; padding-left:20px; margin-bottom:5px;">
      <div class="typing-indicator">MirTechSoft is typing<span class="dots">...</span></div>
    </div>

    <form id="messageForm">
      <input type="text" id="message" placeholder="Type your message..." required autocomplete="off" />
      <button type="submit" id="sendBtn">Send</button>
    </form>
  </div>

  <script>
    let userJustSent = false;
    let typingTimer;

    function fetchMessages() {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "fetch.php", true);
      xhr.onload = function () {
        const chatBox = document.getElementById("chat-box");
        const wasAtBottom = Math.abs(chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 50;
        chatBox.innerHTML = this.responseText;
        if (userJustSent) {
          chatBox.scrollTop = chatBox.scrollHeight;
          userJustSent = false;
        } else if (wasAtBottom) {
          chatBox.scrollTop = chatBox.scrollHeight;
        }
      };
      xhr.send();
    }

    setInterval(fetchMessages, 1500);

    const input = document.getElementById("message");
    const typingIndicator = document.getElementById("typingIndicator");

    input.addEventListener("input", function () {
      clearTimeout(typingTimer);
      if (input.value.trim() !== "") {
        typingIndicator.style.display = "block";
        typingIndicator.querySelector(".typing-indicator").textContent = "You are typing...";
      }
      typingTimer = setTimeout(() => {
        typingIndicator.style.display = "none";
      }, 2000);
    });

    document.getElementById("messageForm").onsubmit = function (e) {
      e.preventDefault();
      const msg = input.value.trim();
      if (msg === "") return;

      const sendBtn = document.getElementById("sendBtn");
      sendBtn.classList.add("loading");
      sendBtn.innerHTML = '<div class="loader"></div>';
      typingIndicator.style.display = "block";
      typingIndicator.querySelector(".typing-indicator").textContent = "MirTechSoft is typing...";

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "insert.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        input.value = "";
        userJustSent = true;
        setTimeout(() => {
          typingIndicator.style.display = "none";
          fetchMessages();
        }, 1000);
        sendBtn.classList.remove("loading");
        sendBtn.textContent = "Send";
      };
      xhr.send("message=" + encodeURIComponent(msg));
    };

    fetchMessages();

    function deleteChat() {
      if (confirm("Are you sure you want to delete the entire chat?")) {
        fetch("delete.php", { method: "POST" })
          .then(() => {
            alert("Chat deleted successfully!");
            location.reload();
          });
      }
    }
  </script>

</body>
</html>
