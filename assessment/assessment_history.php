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
        SELECT 
            aa.id, 
            aa.skill_id, 
            s.name AS skill_name, 
            aa.score, 
            DATE_FORMAT(aa.created_at, '%b %d, %Y · %h:%i %p') AS attempt_date,
            aa.created_at
        FROM assessment_attempts aa
        LEFT JOIN skills s ON aa.skill_id = s.id
        WHERE aa.user_id = ?
        ORDER BY aa.id DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $history = [];
    while ($row = $res->fetch_assoc()) {
        $history[] = [
            "id" => (int)$row["id"],
            "skillId" => $row["skill_id"],
            "skillName" => $row["skill_name"] ?: ucfirst($row["skill_id"]),
            "score" => (int)$row["score"],
            "dateText" => $row["attempt_date"]
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Assessment history fetched successfully",
        "data" => [
            "totalAttempts" => count($history),
            "history" => $history
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch assessment history"]);
}

$conn->close();
?>

