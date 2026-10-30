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
    $mStmt = $conn->prepare("SELECT streak_days, minutes_today, goal_minutes_today FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $m = $mStmt->get_result()->fetch_assoc();
    $mStmt->close();

    $streak = (int)($m["streak_days"] ?? 7);
    $curMin = (int)($m["minutes_today"] ?? 14);
    $goalMin = (int)($m["goal_minutes_today"] ?? 20);

    $lStmt = $conn->prepare("SELECT id, title, category, duration_text, xp_text, is_completed FROM user_lessons WHERE user_id = ? ORDER BY id ASC");
    $lStmt->bind_param("i", $userId);
    $lStmt->execute();
    $lRes = $lStmt->get_result();

    $completedItems = [];
    $upNext = null;

    while ($row = $lRes->fetch_assoc()) {
        $item = [
            "id" => (string)$row["id"],
            "title" => $row["title"],
            "category" => $row["category"],
            "durationText" => $row["duration_text"],
            "xpText" => $row["xp_text"],
            "isCompleted" => (bool)$row["is_completed"]
        ];

        if ($row["is_completed"]) {
            $completedItems[] = $item;
        } else if (!$upNext) {
            $upNext = $item;
        }
    }
    $lStmt->close();

    $doneCount = count($completedItems);
    $totalCount = $doneCount + ($upNext ? 1 : 0);

    echo json_encode([
        "status" => "success",
        "message" => "Training fetched successfully",
        "data" => [
            "headerDateText" => strtoupper(date("l, F j")),
            "streakBadgeText" => "$streak-day streak",
            "currentMinutes" => $curMin,
            "targetMinutes" => $goalMin,
            "practiceProgressText" => "$doneCount of " . ($totalCount > 0 ? $totalCount : 3) . " done",
            "completedPracticeItems" => $completedItems,
            "upNextTitle" => $upNext["title"] ?? "Tell your project story",
            "upNextCategory" => $upNext["category"] ?? "Speech training · Interview practice",
            "upNextMetaText" => ($upNext["durationText"] ?? "6 min") . " · " . ($upNext["xpText"] ?? "+30 XP"),
            "upNextDescription" => "Use the STAR method to turn your experience into a compelling answer.",
            "startButtonText" => "Start " . ($upNext["durationText"] ?? "6-minute") . " practice"
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch daily training"]);
}

$conn->close();
?>

