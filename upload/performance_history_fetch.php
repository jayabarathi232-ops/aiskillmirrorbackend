<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../config/config.php";

$user_id = filter_var($_GET["user_id"] ?? 1, FILTER_VALIDATE_INT);
if (!$user_id || $user_id < 1) {
    $user_id = 1;
}

try {
    $stmt = $conn->prepare(
        "SELECT id, user_id, media_type, skill_category, title, file_path, file_name, 
                file_size_bytes, duration_seconds, score, analysis_summary, ai_feedback, 
                metrics_json, created_at 
         FROM performance_uploads 
         WHERE user_id = ? 
         ORDER BY id DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $metrics = json_decode($row["metrics_json"] ?? "{}", true);
        $fullUrl = "http://" . $host . "/skillmirrorbackend/" . $row["file_path"];

        $items[] = [
            "id" => (int) $row["id"],
            "user_id" => (int) $row["user_id"],
            "media_type" => $row["media_type"],
            "skill_category" => $row["skill_category"],
            "title" => $row["title"],
            "file_url" => $fullUrl,
            "relative_path" => $row["file_path"],
            "file_name" => $row["file_name"],
            "file_size_bytes" => (int) $row["file_size_bytes"],
            "duration_seconds" => $row["duration_seconds"] !== null ? (int) $row["duration_seconds"] : null,
            "score" => (int) $row["score"],
            "analysis_summary" => $row["analysis_summary"],
            "ai_feedback" => $row["ai_feedback"],
            "metrics" => $metrics,
            "created_at" => $row["created_at"]
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Performance upload history fetched successfully",
        "data" => [
            "total_uploads" => count($items),
            "uploads" => $items
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
