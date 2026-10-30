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

$user_id = filter_var($_POST["user_id"] ?? null, FILTER_VALIDATE_INT);
if (!$user_id || $user_id < 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user_id required"]);
    exit;
}

if (!isset($_FILES["profile_picture"]) || $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid profile_picture file required"]);
    exit;
}

$file = $_FILES["profile_picture"];
$maxSize = 5 * 1024 * 1024; // 5 MB

if ($file["size"] > $maxSize) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Image size exceeds 5MB limit"]);
    exit;
}

$allowedExtensions = ["jpg", "jpeg", "png", "webp"];
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid image type. Allowed: jpg, jpeg, png, webp"]);
    exit;
}

$uploadDir = __DIR__ . "/../upload/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = "profile_" . $user_id . "_" . time() . "." . $ext;
$destPath = $uploadDir . $newFileName;

if (!move_uploaded_file($file["tmp_name"], $destPath)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to save uploaded image"]);
    exit;
}

$relativePath = "upload/" . $newFileName;

try {
    // Upsert into profiles
    $stmt = $conn->prepare(
        "INSERT INTO profiles (user_id, profile_picture)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE profile_picture = VALUES(profile_picture)"
    );
    $stmt->bind_param("is", $user_id, $relativePath);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "message" => "Profile picture uploaded successfully",
        "data" => [
            "profile_picture" => $relativePath,
            "url" => "http://" . $_SERVER["HTTP_HOST"] . "/skillmirrorbackend/" . $relativePath
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database update failed"]);
}

$conn->close();
?>

