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
    $stmt = $conn->prepare("
        SELECT id, title, reminder_time, reminder_days, is_enabled, created_at 
        FROM reminders 
        WHERE user_id = ? 
        ORDER BY id DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $reminders = [];
    while ($r = $res->fetch_assoc()) {
        $reminders[] = [
            "id" => (int)$r["id"],
            "title" => $r["title"],
            "reminderTime" => $r["reminder_time"],
            "reminderDays" => $r["reminder_days"],
            "isEnabled" => (bool)$r["is_enabled"]
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Reminders fetched successfully",
        "data" => [
            "reminders" => $reminders
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch reminders"]);
}

$conn->close();
?>

