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

if (!$userId || !$reminderId) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "user_id and reminder_id required"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reminderId, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Reminder deleted successfully"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to delete reminder"]);
}

$conn->close();
?>

