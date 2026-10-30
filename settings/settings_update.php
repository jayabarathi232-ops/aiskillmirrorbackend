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

if (!$userId || $userId < 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id required"]);
    exit;
}

$reminders = isset($data["isLearningRemindersEnabled"]) ? ($data["isLearningRemindersEnabled"] ? 1 : 0) : null;
$ai = isset($data["isPersonalizedAiEnabled"]) ? ($data["isPersonalizedAiEnabled"] ? 1 : 0) : null;
$voice = isset($data["isSaveVoiceRecordingsEnabled"]) ? ($data["isSaveVoiceRecordingsEnabled"] ? 1 : 0) : null;
$appearance = isset($data["currentAppearanceText"]) ? trim($data["currentAppearanceText"]) : null;
$language = isset($data["currentLanguageText"]) ? trim($data["currentLanguageText"]) : null;

try {
    $stmt = $conn->prepare("
        INSERT INTO user_settings (user_id, is_learning_reminders_enabled, is_personalized_ai_enabled, is_save_voice_recordings_enabled, current_appearance, current_language)
        VALUES (?, COALESCE(?, 1), COALESCE(?, 1), COALESCE(?, 0), COALESCE(?, 'Dark'), COALESCE(?, 'English'))
        ON DUPLICATE KEY UPDATE
            is_learning_reminders_enabled = COALESCE(VALUES(is_learning_reminders_enabled), is_learning_reminders_enabled),
            is_personalized_ai_enabled = COALESCE(VALUES(is_personalized_ai_enabled), is_personalized_ai_enabled),
            is_save_voice_recordings_enabled = COALESCE(VALUES(is_save_voice_recordings_enabled), is_save_voice_recordings_enabled),
            current_appearance = COALESCE(VALUES(current_appearance), current_appearance),
            current_language = COALESCE(VALUES(current_language), current_language)
    ");
    $stmt->bind_param("iiiiss", $userId, $reminders, $ai, $voice, $appearance, $language);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Settings updated successfully"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update settings"]);
}

$conn->close();
?>

