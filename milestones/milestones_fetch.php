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
    $mStmt = $conn->prepare("SELECT skill_score, target_score, streak_days, xp_points, target_date FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $m = $mStmt->get_result()->fetch_assoc();
    $mStmt->close();

    $curScore = (int)($m["skill_score"] ?? 78);
    $tgtScore = (int)($m["target_score"] ?? 85);
    $streak = (int)($m["streak_days"] ?? 7);
    $xp = (int)($m["xp_points"] ?? 1240);
    $tgtDate = $m["target_date"] ?? "November 15";
    $ptsToGo = max(0, $tgtScore - $curScore);

    // Achievements earned by user
    $aStmt = $conn->prepare("
        SELECT a.id, a.title, ua.earned_date_text, a.icon_type
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.id
        WHERE ua.user_id = ?
        ORDER BY ua.id ASC
    ");
    $aStmt->bind_param("i", $userId);
    $aStmt->execute();
    $aRes = $aStmt->get_result();

    $achievements = [];
    while ($row = $aRes->fetch_assoc()) {
        $achievements[] = [
            "id" => (string)$row["id"],
            "title" => $row["title"],
            "earnedDateText" => $row["earned_date_text"],
            "iconType" => $row["icon_type"]
        ];
    }
    $aStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Milestones fetched successfully",
        "data" => [
            "mainGoalTitle" => "Reach a skill score of $tgtScore",
            "currentScore" => $curScore,
            "targetScore" => $tgtScore,
            "statusText" => "On track",
            "targetDateText" => "Target date: $tgtDate · $ptsToGo points to go",
            "streakTitle" => "$streak days of showing up",
            "streakSubtitle" => "Next milestone: a 14-day streak",
            "streakProgressText" => "$streak / 14",
            "earnedCountText" => count($achievements) . " earned",
            "achievements" => $achievements,
            "nextRewardTitle" => "Rising expert",
            "nextRewardSubtitle" => "Reach $tgtScore to unlock your next badge",
            "nextRewardTarget" => $tgtScore,
            "totalXpText" => "You've earned " . number_format($xp) . " XP. Keep your momentum."
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch milestones"]);
}

$conn->close();
?>

