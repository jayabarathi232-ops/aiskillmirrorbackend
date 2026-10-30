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
    $uStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $uStmt->bind_param("i", $userId);
    $uStmt->execute();
    $name = $uStmt->get_result()->fetch_assoc()["name"] ?? "Learner";
    $uStmt->close();

    $stmt = $conn->prepare("SELECT id, sender, message, DATE_FORMAT(created_at, '%l:%i %p') AS msg_time FROM coach_messages WHERE user_id = ? ORDER BY id ASC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $messages = [];
    while ($m = $res->fetch_assoc()) {
        $messages[] = [
            "id" => (string)$m["id"],
            "text" => $m["message"],
            "isUser" => ($m["sender"] === "user"),
            "timestamp" => $m["msg_time"] ?: "9:41 AM"
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Coach chat fetched successfully",
        "data" => [
            "coachName" => "Mira, your AI coach",
            "statusText" => "ONLINE · PERSONALISED FOR " . strtoupper($name),
            "bannerNoticeText" => "Guided by your skills, goals, and learning plan",
            "timestampHeader" => "Today · " . date("g:i A"),
            "messages" => $messages,
            "suggestionChips" => ["Let's practice", "Show me an example", "How can I improve?"]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch coach chat"]);
}

$conn->close();
?>

