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
        SELECT id, title, description, timestamp_text, badge_text, is_green_badge, icon_type, category, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY id DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $todayNotifications = [];
    $yesterdayNotifications = [];

    while ($n = $res->fetch_assoc()) {
        $item = [
            "id" => (string)$n["id"],
            "title" => $n["title"],
            "description" => $n["description"],
            "timestampText" => $n["timestamp_text"],
            "badgeText" => $n["badge_text"],
            "isGreenBadge" => (bool)$n["is_green_badge"],
            "iconType" => $n["icon_type"],
            "category" => $n["category"]
        ];

        if ($n["timestamp_text"] === "Yesterday") {
            $yesterdayNotifications[] = $item;
        } else {
            $todayNotifications[] = $item;
        }
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Notifications fetched successfully",
        "data" => [
            "isDailyReminderEnabled" => true,
            "reminderTimeText" => "Every day at 7:00 PM",
            "reminderNoticeText" => "A gentle nudge to invest in yourself.",
            "todayNotifications" => $todayNotifications,
            "yesterdayNotifications" => $yesterdayNotifications
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch notifications"]);
}

$conn->close();
?>

