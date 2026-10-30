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
    echo json_encode([
        "status" => "error",
        "message" => "POST method required"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    $data = $_POST;
}

$user_id = filter_var(
    $data["user_id"] ?? null,
    FILTER_VALIDATE_INT
);

$fields = [
    "phone", "education", "skills",
    "career_goal", "bio"
];

if (!$user_id || $user_id < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid user_id required"
    ]);
    exit;
}

foreach ($fields as $field) {
    if (isset($data[$field]) && !is_string($data[$field])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid " . $field
        ]);
        exit;
    }
}

$phone = trim((string)($data["phone"] ?? ""));
$education = trim((string)($data["education"] ?? ""));
$skills = trim((string)($data["skills"] ?? ""));
$career_goal = trim((string)($data["career_goal"] ?? ""));
$bio = trim((string)($data["bio"] ?? ""));

try {
    $stmt = $conn->prepare(
        "INSERT INTO profiles
        (user_id, phone, education, skills, career_goal, bio)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            phone = VALUES(phone),
            education = VALUES(education),
            skills = VALUES(skills),
            career_goal = VALUES(career_goal),
            bio = VALUES(bio)"
    );

    $stmt->bind_param(
        "isssss",
        $user_id,
        $phone,
        $education,
        $skills,
        $career_goal,
        $bio
    );

    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Profile saved successfully"
    ]);

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    $code = $e->getCode();

    if ($code == 1452) {
        http_response_code(400);
        $message = "User does not exist";
    } else {
        http_response_code(500);
        $message = "Failed to save profile";
    }

    echo json_encode([
        "status" => "error",
        "message" => $message
    ]);
}

$conn->close();
?>
