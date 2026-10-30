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
    $mStmt = $conn->prepare("SELECT skill_score, monthly_pts_gain, total_sessions, time_invested_hours FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $mRes = $mStmt->get_result()->fetch_assoc();
    $mStmt->close();

    $overallScore = (int)($mRes["skill_score"] ?? 78);
    $ptsGain = (int)($mRes["monthly_pts_gain"] ?? 14);
    $sessions = (int)($mRes["total_sessions"] ?? 24);
    $hours = (float)($mRes["time_invested_hours"] ?? 6.2);

    $sStmt = $conn->prepare("
        SELECT
            s.name,
            COALESCE(us.current_score, 72) AS score,
            COALESCE(us.is_top_tier, 0) AS is_top_tier
        FROM skills s
        LEFT JOIN user_skills us
            ON s.id = us.skill_id AND us.user_id = ?
        ORDER BY score DESC
    ");
    $sStmt->bind_param("i", $userId);
    $sStmt->execute();
    $sRes = $sStmt->get_result();

    $skillItems = [];
    while ($row = $sRes->fetch_assoc()) {
        $skillItems[] = [
            "name" => $row["name"],
            "score" => (int)$row["score"],
            "maxScore" => 100,
            "isTopTier" => (bool)$row["is_top_tier"]
        ];
    }
    $sStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Analytics fetched successfully",
        "data" => [
            "overallScore" => $overallScore,
            "pointsGainText" => "↑ $ptsGain points",
            "scoreRangeText" => "From " . ($overallScore - $ptsGain) . " to $overallScore · Sep 29 – " . date("M j"),
            "totalSessions" => $sessions,
            "sessionsWeeklyGainText" => "↑ 8 this week",
            "timeInvestedText" => $hours . " h",
            "timeWeeklyGainText" => "↑ 1.2h this week",
            "skillItems" => $skillItems
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch analytics"]);
}

$conn->close();
?>

