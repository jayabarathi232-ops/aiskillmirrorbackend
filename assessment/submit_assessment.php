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
$skillId = trim($data["skill_id"] ?? "comm");
$answers = $data["answers"] ?? []; // Array of { question_id, selected_key }

if (!$userId || $userId < 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id required"]);
    exit;
}

try {
    $totalScore = 0;
    $maxScore = 0;

    foreach ($answers as $ans) {
        $qId = (int)($ans["question_id"] ?? 0);
        $optKey = trim($ans["selected_key"] ?? "");

        $stmt = $conn->prepare("SELECT points FROM assessment_options WHERE question_id = ? AND option_key = ?");
        $stmt->bind_param("is", $qId, $optKey);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $totalScore += (int)$row["points"];
        }
        $maxScore += 25;
    }

    $finalScore = $maxScore > 0 ? (int)round(($totalScore / $maxScore) * 100) : 80;
    if ($finalScore < 50) $finalScore = 65; // realistic minimum

    // 1. Record attempt
    $jsonAnswers = json_encode($answers);
    $att = $conn->prepare("INSERT INTO assessment_attempts (user_id, skill_id, score, answers_json) VALUES (?, ?, ?, ?)");
    $att->bind_param("isis", $userId, $skillId, $finalScore, $jsonAnswers);
    $att->execute();
    $att->close();

    // 2. Update user_skills
    $upsert = $conn->prepare("
        INSERT INTO user_skills (user_id, skill_id, baseline_score, current_score, points_gained, is_top_tier)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            current_score = VALUES(current_score),
            points_gained = current_score - baseline_score,
            is_top_tier = (VALUES(current_score) >= 80),
            last_assessed_at = CURRENT_TIMESTAMP
    ");
    $baseline = 60;
    $gained = $finalScore - $baseline;
    $topTier = ($finalScore >= 80) ? 1 : 0;
    $upsert->bind_param("isiisi", $userId, $skillId, $baseline, $finalScore, $gained, $topTier);
    $upsert->execute();
    $upsert->close();

    // 3. Recalculate average skill score for user_metrics
    $avgStmt = $conn->prepare("SELECT AVG(current_score) AS avg_score FROM user_skills WHERE user_id = ?");
    $avgStmt->bind_param("i", $userId);
    $avgStmt->execute();
    $avgRes = $avgStmt->get_result()->fetch_assoc();
    $avgScore = (int)round($avgRes["avg_score"] ?? $finalScore);
    $avgStmt->close();

    $metStmt = $conn->prepare("
        INSERT INTO user_metrics (user_id, skill_score, total_sessions)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE
            skill_score = VALUES(skill_score),
            total_sessions = total_sessions + 1
    ");
    $metStmt->bind_param("ii", $userId, $avgScore);
    $metStmt->execute();
    $metStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Assessment submitted successfully",
        "data" => [
            "skillId" => $skillId,
            "score" => $finalScore,
            "overallScore" => $avgScore,
            "pointsGained" => $gained
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to submit assessment"]);
}

$conn->close();
?>

