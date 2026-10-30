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
    $uStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $uStmt->bind_param("i", $userId);
    $uStmt->execute();
    $name = $uStmt->get_result()->fetch_assoc()["name"] ?? "Learner";
    $uStmt->close();

    $mStmt = $conn->prepare("SELECT monthly_pts_gain FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $ptsGain = (int)($mStmt->get_result()->fetch_assoc()["monthly_pts_gain"] ?? 14);
    $mStmt->close();

    $sStmt = $conn->prepare("
        SELECT
            s.name,
            COALESCE(us.baseline_score, 60) AS baseline_score,
            COALESCE(us.current_score, 75) AS current_score,
            COALESCE(us.points_gained, 15) AS points_gained
        FROM skills s
        LEFT JOIN user_skills us
            ON s.id = us.skill_id AND us.user_id = ?
        ORDER BY points_gained DESC
    ");
    $sStmt->bind_param("i", $userId);
    $sStmt->execute();
    $sRes = $sStmt->get_result();

    $comparisons = [];
    $biggestGain = 0;
    $breakthroughSkill = "";

    while ($row = $sRes->fetch_assoc()) {
        $gained = (int)$row["points_gained"];
        if ($gained > $biggestGain) {
            $biggestGain = $gained;
            $breakthroughSkill = $row["name"];
        }
        $comparisons[] = [
            "name" => $row["name"],
            "baselineScore" => (int)$row["baseline_score"],
            "currentScore" => (int)$row["current_score"],
            "pointsGained" => $gained,
            "maxScore" => 100
        ];
    }
    $sStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Growth report fetched successfully",
        "data" => [
            "headerDateRange" => "SEP 29 – " . strtoupper(date("M j, Y")),
            "headline" => "$ptsGain points stronger.",
            "snapshotSubtitle" => "$name, your consistency is translating into measurable skill growth.",
            "comparisonItems" => $comparisons,
            "breakthroughTitle" => "Your biggest breakthrough",
            "breakthroughDescription" => "$breakthroughSkill improved by $biggestGain points. Keep practicing project stories to reach your target score of 85."
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch growth report"]);
}

$conn->close();
?>

