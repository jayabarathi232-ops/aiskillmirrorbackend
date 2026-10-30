<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "POST method required"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
$userId = filter_var($data["user_id"] ?? 1, FILTER_VALIDATE_INT);
$message = trim($data["message"] ?? "");

if ($message === "") {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
    exit;
}

try {
    // 1. Insert user message
    $uStmt = $conn->prepare("INSERT INTO coach_messages (user_id, sender, message) VALUES (?, 'user', ?)");
    $uStmt->bind_param("is", $userId, $message);
    $uStmt->execute();
    $uId = $conn->insert_id;
    $uStmt->close();

    // 2. Generate contextual response from AI coach Mira
    $lower = strtolower($message);
    if (strpos($lower, "star") !== false || strpos($lower, "interview") !== false) {
        $reply = "The STAR technique gives your answer structure:\n• Situation: What was happening?\n• Task: What was your objective?\n• Action: Exactly what steps did you take?\n• Result: What was the measurable outcome?\n\nWould you like to try answering a sample interview question using STAR?";
    } elseif (strpos($lower, "practice") !== false || strpos($lower, "example") !== false) {
        $reply = "Great idea! Here's an example:\n\"At my university team project, our server was crashing under concurrent requests (Situation/Task). I implemented Redis caching and query batching in PHP (Action), reducing load times by 45% (Result).\"\n\nNow, how would you describe your strongest technical achievement?";
    } elseif (strpos($lower, "confidence") !== false || strpos($lower, "nervous") !== false) {
        $reply = "It's completely normal to feel nervous. One great exercise is to pause for 2 seconds before answering. That gives you time to frame your thoughts and demonstrates calm composure.";
    } else {
        $reply = "That's a thoughtful question! Based on your current skill profile, focusing on structured storytelling and concise answers will bring the highest impact. Want to do a quick 60-second speech practice?";
    }

    $cStmt = $conn->prepare("INSERT INTO coach_messages (user_id, sender, message) VALUES (?, 'coach', ?)");
    $cStmt->bind_param("is", $userId, $reply);
    $cStmt->execute();
    $cId = $conn->insert_id;
    $cStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Message sent successfully",
        "data" => [
            "userMessage" => [
                "id" => (string)$uId,
                "text" => $message,
                "isUser" => true,
                "timestamp" => date("g:i A")
            ],
            "coachReply" => [
                "id" => (string)$cId,
                "text" => $reply,
                "isUser" => false,
                "timestamp" => date("g:i A")
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to process chat message"]);
}

$conn->close();
?>

