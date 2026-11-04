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
$userId = filter_var($data["user_id"] ?? null, FILTER_VALIDATE_INT);
$reminderId = filter_var($data["reminder_id"] ?? null, FILTER_VALIDATE_INT);
$isEnabled = isset($data["is_enabled"]) ? ($data["is_enabled"] ? 1 : 0) : null;

if (!$userId || !$reminderId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "user_id and reminder_id required"]);
    exit;
}

try {
    if ($isEnabled !== null) {
        $stmt = $conn->prepare("UPDATE reminders SET is_enabled = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $isEnabled, $reminderId, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE reminders SET is_enabled = IF(is_enabled = 1, 0, 1) WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $reminderId, $userId);
    }
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Reminder updated successfully"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to toggle reminder"]);
}

$conn->close();
?>

