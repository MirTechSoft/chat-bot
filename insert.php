<?php
session_start();
include 'db.php';

if (isset($_POST['message'])) {
    $user = $_SESSION['username'];
    $session_id = session_id();
    $message = strtolower(trim($_POST['message']));
    $message = $conn->real_escape_string($message);

    // ✅ Insert user message
    $conn->query("INSERT INTO messages (username, message, session_id) 
                  VALUES ('$user', '$message', '$session_id')");

    // ✅ Bot reply logic
    $reply = "";

if ($message == "hi") {
    $reply = "Hello! 👋 How can I help you today?";
}

elseif ($message == "you're welcome") {
    $reply = "You're welcome! 😊";
}
elseif ($message == "hello") {
    $reply = "Hi there! 👋 What can I do for you?";
}
elseif ($message == "what is your name") {
    $reply = "I am your friendly chat bot! 🤖";
}
elseif ($message == "who created you") {
    $reply = "Ibrahim made me using PHP 🧑‍💻";
}

elseif ($message == "bye") {
    $reply = "Goodbye! Have a great day! 👋";
}

elseif ($message == "thank you") {
    $reply = "You're welcome! 🙏";
}

elseif ($message == "what can you do") {
    $reply = "I can reply to simple questions and keep you company!";
}

elseif ($message == "can you help me") {
    $reply = "Of course! What do you need help with?";
}

elseif ($message == "what is php") {
    $reply = "PHP is a server-side scripting language used to make dynamic websites.";
}

elseif ($message == "what is html") {
    $reply = "HTML is the standard markup language for creating web pages.";
}

elseif ($message == "what is css") {
    $reply = "CSS is used for styling HTML content.";
}

elseif ($message == "what is javascript") {
    $reply = "JavaScript makes web pages interactive!";
}

elseif ($message == "tell me a joke") {
    $reply = "Why did the developer go broke? Because he used up all his cache!";
}

elseif ($message == "how to learn coding") {
    $reply = "Start with HTML/CSS, then learn JavaScript and PHP!";
}

elseif ($message == "how to make a website") {
    $reply = "Use HTML, CSS, and PHP to build dynamic sites.";
}


    $botName = "AI BOt";
    $conn->query("INSERT INTO messages (username, message, session_id) 
                  VALUES ('$botName', '$reply', '$session_id')");
}
?>
