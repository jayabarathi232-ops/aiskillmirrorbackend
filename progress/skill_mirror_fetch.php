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
    // 1. Fetch user metrics
    $mStmt = $conn->prepare("SELECT skill_score, monthly_pts_gain FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $mRes = $mStmt->get_result()->fetch_assoc();
    $mStmt->close();

    $overallScore = (int)($mRes["skill_score"] ?? 78);
    $ptsGain = (int)($mRes["monthly_pts_gain"] ?? 14);

    // 2. Fetch all skills and scores
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

    $skillScores = [];
    while ($row = $sRes->fetch_assoc()) {
        $skillScores[] = [
            "name" => $row["name"],
            "score" => (int)$row["score"],
            "maxScore" => 100,
            "isTopTier" => (bool)$row["is_top_tier"]
        ];
    }
    $sStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Skill mirror results fetched successfully",
        "data" => [
            "overallScore" => $overallScore,
            "statusBadge" => $overallScore >= 80 ? "EXPERT" : "ADVANCED",
            "pointsGainSubtitle" => "+$ptsGain points since your first assessment",
            "skillCountText" => count($skillScores) . " skills",
            "skillScores" => $skillScores,
            "nextMoveTitle" => "Your next best move",
            "nextMoveDescription" => "Practice concise, structured responses to bring your communication up to match your technical strengths.",
            "lastUpdatedText" => "Updated " . date("M j") . " · Based on your latest assessments"
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch skill mirror"]);
}

$conn->close();
?>

