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
            s.id,
            s.name,
            s.description,
            s.icon_type,
            COALESCE(us.current_score, 70) AS last_score,
            100 AS max_score
        FROM skills s
        LEFT JOIN user_skills us
            ON s.id = us.skill_id AND us.user_id = ?
        ORDER BY s.order_index ASC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $skills = [];
    while ($row = $res->fetch_assoc()) {
        $skills[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "description" => $row["description"],
            "lastScore" => (int)$row["last_score"],
            "maxScore" => (int)$row["max_score"],
            "iconType" => $row["icon_type"]
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Skills fetched successfully",
        "data" => [
            "skills" => $skills
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch skills"]);
}

$conn->close();
?>

