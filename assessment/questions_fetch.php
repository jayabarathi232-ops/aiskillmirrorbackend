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

$skillId = trim($_GET["skill_id"] ?? $_POST["skill_id"] ?? "comm");

try {
    // 1. Fetch questions
    $qStmt = $conn->prepare("
        SELECT id, skill_id, topic_badge, question_headline, question_scenario, question_order
        FROM assessment_questions
        WHERE skill_id = ?
        ORDER BY question_order ASC
    ");
    $qStmt->bind_param("s", $skillId);
    $qStmt->execute();
    $qRes = $qStmt->get_result();

    $questions = [];
    while ($q = $qRes->fetch_assoc()) {
        $qId = (int)$q["id"];

        // 2. Fetch options for each question
        $oStmt = $conn->prepare("
            SELECT option_key, option_text, points
            FROM assessment_options
            WHERE question_id = ?
            ORDER BY option_key ASC
        ");
        $oStmt->bind_param("i", $qId);
        $oStmt->execute();
        $oRes = $oStmt->get_result();

        $options = [];
        while ($o = $oRes->fetch_assoc()) {
            $options[] = [
                "key" => $o["option_key"],
                "text" => $o["option_text"],
                "points" => (int)$o["points"]
            ];
        }
        $oStmt->close();

        $questions[] = [
            "id" => $qId,
            "topicBadge" => $q["topic_badge"],
            "headline" => $q["question_headline"],
            "scenario" => $q["question_scenario"],
            "options" => $options
        ];
    }
    $qStmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Questions fetched successfully",
        "data" => [
            "skillId" => $skillId,
            "totalQuestions" => count($questions),
            "questions" => $questions
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch questions"]);
}

$conn->close();
?>

