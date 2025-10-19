<?php
session_start();
include 'db.php';

if (isset($_POST['message'])) {
    $user = $_SESSION['username'] ?? 'Guest';
    $session_id = session_id();
    $message = trim($_POST['message']);

    if (!$conn) {
        die("Error: Database connection not established.");
    }

    // ✅ Save user's message
    $message_safe = $conn->real_escape_string($message);
    $conn->query("INSERT INTO messages (username, message, session_id)
                  VALUES ('$user', '$message_safe', '$session_id')");

    // Default reply
    $reply = "Sorry, I couldn't get a valid reply right now.";

    // ✅ Custom reply for "who created you" type questions
    $lower_msg = strtolower($message);
    $creator_keywords = [
        "kisne banaya", "kisna banaya", "kisne develop", "who made you",
        "who created you", "tumhe kisne", "kis ne banaya", "kis ne develop"
    ];

    foreach ($creator_keywords as $key) {
        if (strpos($lower_msg, $key) !== false) {
            $reply = "Mujhe MirTechSoft ke engineers ne develop kiya hai 💻. Main unka Chat Bot hoon jo users se baat karne ke liye bana gaya hoon.";
            break;
        }
    }

    // ✅ If a custom reply found, skip Gemini API
    if ($reply !== "Sorry, I couldn't get a valid reply right now.") {
        $botName = "Chat Bot";
        $reply_safe = $conn->real_escape_string($reply);
        $conn->query("INSERT INTO messages (username, message, session_id)
                      VALUES ('$botName', '$reply_safe', '$session_id')");
        exit;
    }

    // ✅ Gemini API setup
    $apiKey = "AIzaSyCKaErofkHr3jlQq3l57GVOXJztrfF3NXM";
    $modelName = "gemini-2.0-flash";
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/"
              . $modelName . ":generateContent?key=" . $apiKey;

    // ✅ Retrieve previous conversation for memory
    $history = "";
    $result = $conn->query("SELECT username, message FROM messages
                            WHERE session_id='$session_id'
                            ORDER BY id DESC LIMIT 6");
    if ($result && $result->num_rows > 0) {
        $rows = array_reverse($result->fetch_all(MYSQLI_ASSOC));
        foreach ($rows as $row) {
            $history .= "{$row['username']}: {$row['message']}\n";
        }
    }

    // ✅ Create prompt
    $prompt = "You are 'Chat Bot', a friendly and realistic chatbot created by MirTechSoft. "
            . "You respond naturally in Urdu + English mix if needed. "
            . "Keep replies short (2–3 lines), emotional but with minimal emojis (max 1). "
            . "Never use too many symbols or emojis. Be simple, warm, and human-like.\n\n"
            . "Recent conversation:\n$history\n\n"
            . "User says: \"$message\"\n"
            . "Reply naturally as if continuing the same chat.";

    // ✅ Request body
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.9,
            "maxOutputTokens" => 300
        ]
    ];

    $json_data = json_encode($data);

    // ✅ Call Gemini API
    if (!function_exists('curl_init')) {
        $reply = "Error: cURL not enabled on this server.";
    } else {
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curl_error)) {
            $reply = "Network error: " . htmlspecialchars($curl_error);
        } else {
            $res = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $reply = "Invalid response from AI.";
            } elseif ($http_code !== 200) {
                $err = $res['error']['message'] ?? "Unknown error.";
                $reply = "API Error: " . htmlspecialchars($err);
            } else {
                if (!empty($res['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = trim($res['candidates'][0]['content']['parts'][0]['text']);
                } else {
                    $reply = "Main samajh nahi paya, lekin main yahan hoon baat karne ke liye.";
                }

                // ✅ Remove multiple emojis / symbols
                $reply = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $reply); // Remove emoticons
                $reply = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $reply); // Remove symbols & pictographs
                $reply = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $reply); // Remove transport & map symbols
                $reply = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $reply);   // Remove misc symbols
                $reply = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $reply);   // Remove dingbats
                $reply = preg_replace('/\s{2,}/', ' ', $reply);                // Extra spaces cleanup
                $reply = trim($reply);

                if (!empty($res['candidates'][0]['finishReason']) &&
                    $res['candidates'][0]['finishReason'] === "MAX_TOKENS") {
                    $reply .= " (Lagta hai reply cut ho gaya)";
                }
            }
        }
    }

    // ✅ Save AI reply
    $botName = "Chat Bot";
    $reply_safe = $conn->real_escape_string($reply);
    $conn->query("INSERT INTO messages (username, message, session_id)
                  VALUES ('$botName', '$reply_safe', '$session_id')");
}
?>
