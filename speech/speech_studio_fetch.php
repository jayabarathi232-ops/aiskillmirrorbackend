<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/config.php";

$userId = filter_var($_GET["user_id"] ?? $_POST["user_id"] ?? 1, FILTER_VALIDATE_INT);

try {
    $stmt = $conn->prepare("SELECT prompt_title, prompt_quote, duration_seconds FROM speech_sessions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $title = $session["prompt_title"] ?? "Speak with confidence";
    $quote = $session["prompt_quote"] ?? "\"Tell me about a project you're proud of.\"";

    echo json_encode([
        "status" => "success",
        "message" => "Speech studio data fetched successfully",
        "data" => [
            "title" => $title,
            "subtitle" => "AI SPEECH STUDIO",
            "categoryBadgeText" => "INTERVIEW PRACTICE",
            "sessionText" => "Session 03",
            "promptHeader" => "YOUR SPEAKING PROMPT",
            "promptQuote" => $quote,
            "promptInstruction" => "Use the STAR method. Aim for a clear, focused response in 60 seconds.",
            "statusText" => "Listening to you",
            "timerText" => "00:24",
            "feedbackHelpText" => "Tap stop when you're ready for feedback",
            "privacyNoticeText" => "A safe space to practice. Only you can access your recordings."
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch speech studio data"]);
}

$conn->close();
?>

