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
        SELECT speech_score, baseline_gain_text, pronunciation_score, fluency_score, clarity_score, duration_seconds, transcript, ai_tip
        FROM speech_sessions
        WHERE user_id = ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $score = (int)($s["speech_score"] ?? 72);
    $gain = $s["baseline_gain_text"] ?? "+8 pts from baseline";
    $dur = (int)($s["duration_seconds"] ?? 58);
    $pScore = (int)($s["pronunciation_score"] ?? 76);
    $fScore = (int)($s["fluency_score"] ?? 70);
    $cScore = (int)($s["clarity_score"] ?? 70);
    $tip = $s["ai_tip"] ?? "Try a brief pause instead of \"um.\" Break \"collaborative\" into five syllables: co-lab-o-ra-tive.";

    echo json_encode([
        "status" => "success",
        "message" => "Speech analysis fetched successfully",
        "data" => [
            "speechScore" => $score,
            "baselineGainText" => $gain,
            "headlineLine1" => "Clear ideas.",
            "headlineLine2" => "Growing confidence.",
            "sessionDurationText" => "Session 03 · $dur seconds",
            "metrics" => [
                ["name" => "Pronunciation", "score" => $pScore, "maxScore" => 100],
                ["name" => "Fluency", "score" => $fScore, "maxScore" => 100],
                ["name" => "Clarity", "score" => $cScore, "maxScore" => 100]
            ],
            "transcriptPart1" => "\"I built a ",
            "transcriptHighlightPronunciation" => "collaborative",
            "transcriptPart2" => " study app. My role was to, ",
            "transcriptHighlightFiller" => "um",
            "transcriptPart3" => ", design the API and improve the user experience.\"",
            "aiTipText" => $tip
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch speech analysis"]);
}

$conn->close();
?>

