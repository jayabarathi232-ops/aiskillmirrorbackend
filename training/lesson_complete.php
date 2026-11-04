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
$lessonId = filter_var($data["lesson_id"] ?? null, FILTER_VALIDATE_INT);

if (!$userId || $userId < 1 || !$lessonId || $lessonId < 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id and lesson_id required"]);
    exit;
}

try {
    // 1. Fetch lesson details
    $stmt = $conn->prepare("SELECT id, title, duration_minutes, is_completed FROM user_lessons WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $lessonId, $userId);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$lesson) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Lesson not found"]);
        exit;
    }

    $minutesToAdd = (int)($lesson["duration_minutes"] ?? 6);

    // 2. Mark lesson as completed
    $upStmt = $conn->prepare("UPDATE user_lessons SET is_completed = 1, completed_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    $upStmt->bind_param("ii", $lessonId, $userId);
    $upStmt->execute();
    $upStmt->close();

    // 3. Update user metrics: minutes_today, xp_points, streak
    $metStmt = $conn->prepare("
        UPDATE user_metrics 
        SET minutes_today = minutes_today + ?, 
            xp_points = xp_points + 30 
        WHERE user_id = ?
    ");
    $metStmt->bind_param("ii", $minutesToAdd, $userId);
    $metStmt->execute();
    $metStmt->close();

    // 4. Fetch updated metrics
    $getMet = $conn->prepare("SELECT minutes_today, goal_minutes_today, streak_days, xp_points FROM user_metrics WHERE user_id = ?");
    $getMet->bind_param("i", $userId);
    $getMet->execute();
    $updatedMetrics = $getMet->get_result()->fetch_assoc();
    $getMet->close();

    echo json_encode([
        "status" => "success",
        "message" => "Lesson completed successfully! +30 XP earned.",
        "data" => [
            "lesson_id" => $lessonId,
            "minutesToday" => (int)($updatedMetrics["minutes_today"] ?? 0),
            "goalMinutesToday" => (int)($updatedMetrics["goal_minutes_today"] ?? 20),
            "streakDays" => (int)($updatedMetrics["streak_days"] ?? 7),
            "xpPoints" => (int)($updatedMetrics["xp_points"] ?? 1240)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to complete lesson"]);
}

$conn->close();
?>

