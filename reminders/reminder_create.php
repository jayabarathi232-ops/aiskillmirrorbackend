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
$title = trim($data["title"] ?? "");
$time = trim($data["reminder_time"] ?? "07:00 PM");
$days = trim($data["reminder_days"] ?? "Every day");

if (!$userId || $userId < 1 || $title === "") {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id and title required"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, title, reminder_time, reminder_days, is_enabled) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("isss", $userId, $title, $time, $days);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Reminder created successfully",
        "data" => [
            "id" => $id,
            "title" => $title,
            "reminderTime" => $time,
            "reminderDays" => $days,
            "isEnabled" => true
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to create reminder"]);
}

$conn->close();
?>

