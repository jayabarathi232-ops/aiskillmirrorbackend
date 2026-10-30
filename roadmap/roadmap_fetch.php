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

    $rStmt = $conn->query("SELECT id, week_number, title, subtitle, description, lesson_count_text, icon_type, is_active FROM roadmap_steps ORDER BY week_number ASC");
    $steps = [];
    $activeStep = null;
    while ($r = $rStmt->fetch_assoc()) {
        if ($r["is_active"] == 1) {
            $activeStep = $r;
        } else {
            $steps[] = [
                "id" => $r["id"],
                "title" => $r["title"],
                "subtitle" => $r["subtitle"],
                "lessonCountText" => $r["lesson_count_text"],
                "iconType" => $r["icon_type"]
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Roadmap fetched successfully",
        "data" => [
            "currentWeekBadgeText" => "WEEK 2 OF 4",
            "planTitle" => "Your career-ready plan.",
            "planSubtitle" => "Built for $name · 20 min/day · Target score 85",
            "planProgress" => 0.3,
            "planProgressText" => "Week 1 complete · 6 of 20 lessons done",
            "activeStepTitle" => $activeStep["title"] ?? "Communicate with impact",
            "activeStepSubtitle" => $activeStep["subtitle"] ?? "Week 2 · 3 of 5 lessons complete",
            "activeStepProgressPercentage" => "60%",
            "activeStepDescription" => $activeStep["description"] ?? "Build clarity with structured answers.",
            "activeStepTags" => ["AI recommended", "Communication 72"],
            "upcomingSteps" => $steps,
            "continueButtonText" => "Continue week 2"
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch roadmap"]);
}

$conn->close();
?>

