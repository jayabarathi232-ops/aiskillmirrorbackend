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
        SELECT is_learning_reminders_enabled, is_personalized_ai_enabled, is_save_voice_recordings_enabled, current_appearance, current_language
        FROM user_settings
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$s) {
        $ins = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
        $ins->bind_param("i", $userId);
        $ins->execute();
        $ins->close();

        $s = [
            "is_learning_reminders_enabled" => 1,
            "is_personalized_ai_enabled" => 1,
            "is_save_voice_recordings_enabled" => 0,
            "current_appearance" => "Dark",
            "current_language" => "English"
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Settings fetched successfully",
        "data" => [
            "isLearningRemindersEnabled" => (bool)$s["is_learning_reminders_enabled"],
            "isPersonalizedAiEnabled" => (bool)$s["is_personalized_ai_enabled"],
            "isSaveVoiceRecordingsEnabled" => (bool)$s["is_save_voice_recordings_enabled"],
            "currentAppearanceText" => $s["current_appearance"],
            "currentLanguageText" => $s["current_language"],
            "appVersionText" => "AI Skills Mirror · Version 1.0.0"
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch settings"]);
}

$conn->close();
?>

