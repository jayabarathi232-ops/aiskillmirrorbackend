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

$userId = filter_var($_GET["user_id"] ?? $_POST["user_id"] ?? null, FILTER_VALIDATE_INT);
if (!$userId) {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = filter_var($data["user_id"] ?? null, FILTER_VALIDATE_INT);
}

if (!$userId || $userId < 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id required"]);
    exit;
}

try {
    // 1. Fetch user info
    $uStmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $uStmt->bind_param("i", $userId);
    $uStmt->execute();
    $user = $uStmt->get_result()->fetch_assoc();
    $uStmt->close();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    // 2. Fetch or create user metrics
    $mStmt = $conn->prepare("SELECT * FROM user_metrics WHERE user_id = ?");
    $mStmt->bind_param("i", $userId);
    $mStmt->execute();
    $metrics = $mStmt->get_result()->fetch_assoc();
    $mStmt->close();

    if (!$metrics) {
        $ins = $conn->prepare("INSERT INTO user_metrics (user_id) VALUES (?)");
        $ins->bind_param("i", $userId);
        $ins->execute();
        $ins->close();

        $metrics = [
            "skill_score" => 78,
            "monthly_pts_gain" => 14,
            "streak_days" => 7,
            "minutes_today" => 14,
            "goal_minutes_today" => 20,
            "total_sessions" => 24,
            "time_invested_hours" => 6.2,
            "xp_points" => 1240,
            "target_score" => 85,
            "target_date" => "November 15"
        ];
    }

    // 3. Count assessed skills
    $sStmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_skills WHERE user_id = ?");
    $sStmt->bind_param("i", $userId);
    $sStmt->execute();
    $assessedCount = (int)$sStmt->get_result()->fetch_assoc()["total"];
    $sStmt->close();
    if ($assessedCount === 0) $assessedCount = 4;

    // 4. Current training lesson
    $lStmt = $conn->prepare("SELECT title, category, duration_text FROM user_lessons WHERE user_id = ? AND is_completed = 0 ORDER BY id ASC LIMIT 1");
    $lStmt->bind_param("i", $userId);
    $lStmt->execute();
    $activeLesson = $lStmt->get_result()->fetch_assoc();
    $lStmt->close();

    $lessonTitle = $activeLesson["title"] ?? "Speak with confidence";
    $lessonCat = $activeLesson["category"] ?? "Communication · Lesson 3 of 5";
    $lessonDur = $activeLesson["duration_text"] ?? "6 min";

    // 5. Lessons counts and progress
    $cntStmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total, 
            SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) AS completed 
        FROM user_lessons 
        WHERE user_id = ?
    ");
    $cntStmt->bind_param("i", $userId);
    $cntStmt->execute();
    $cRow = $cntStmt->get_result()->fetch_assoc();
    $cntStmt->close();

    $totalLessons = (int)($cRow["total"] ?? 5);
    $completedLessons = (int)($cRow["completed"] ?? 2);
    $remainingLessons = max(0, $totalLessons - $completedLessons);
    $progressFloat = $totalLessons > 0 ? (float)round($completedLessons / $totalLessons, 2) : 0.6;

    // 6. Profile picture
    $pStmt = $conn->prepare("SELECT profile_picture FROM profiles WHERE user_id = ?");
    $pStmt->bind_param("i", $userId);
    $pStmt->execute();
    $prof = $pStmt->get_result()->fetch_assoc();
    $pStmt->close();
    $profilePicture = $prof["profile_picture"] ?? "";

    // 7. Greeting & Date
    $hour = (int)date('G');
    if ($hour < 12) {
        $greeting = "Good morning";
    } elseif ($hour < 17) {
        $greeting = "Good afternoon";
    } else {
        $greeting = "Good evening";
    }
    $currentDateText = date('l, F j');

    // 8. Learning plan title
    $currentPlanTitle = "Career-ready roadmap · Week 2 of 4";

    echo json_encode([
        "status" => "success",
        "message" => "Dashboard fetched successfully",
        "data" => [
            "userName" => $user["name"],
            "greeting" => $greeting,
            "currentDateText" => $currentDateText,
            "profilePicture" => $profilePicture,
            "skillScore" => (int)$metrics["skill_score"],
            "monthlyPtsGain" => (int)$metrics["monthly_pts_gain"],
            "currentStreakDays" => (int)$metrics["streak_days"],
            "minutesToday" => (int)$metrics["minutes_today"],
            "goalMinutesToday" => (int)$metrics["goal_minutes_today"],
            "assessedSkillsCount" => $assessedCount,
            "learnerStatus" => "Advanced learner · Top 24%",
            "currentLessonTitle" => $lessonTitle,
            "currentLessonCategory" => $lessonCat,
            "currentLessonDuration" => $lessonDur,
            "currentLessonProgress" => $progressFloat,
            "completedLessonsCount" => $completedLessons,
            "totalLessonsCount" => $totalLessons,
            "remainingLessonsCount" => $remainingLessons,
            "currentPlanTitle" => $currentPlanTitle,
            "aiCoachTitle" => "Your AI coach is ready",
            "aiCoachSubtitle" => "\"Let's build on your interview skills.\""
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch dashboard data"]);
}

$conn->close();
?>
