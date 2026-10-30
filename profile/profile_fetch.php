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

$method = $_SERVER["REQUEST_METHOD"];
if ($method !== "GET" && $method !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "GET or POST method required"
    ]);
    exit;
}

$user_id = null;
if ($method === "GET") {
    $user_id = filter_var($_GET["user_id"] ?? null, FILTER_VALIDATE_INT);
} else {
    $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    $user_id = filter_var($data["user_id"] ?? null, FILTER_VALIDATE_INT);
}

if (!$user_id || $user_id < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid user_id required"
    ]);
    exit;
}

try {
    $stmt = $conn->prepare(
        "SELECT
            u.id AS user_id,
            u.name,
            u.email,
            p.phone,
            p.education,
            p.skills,
            p.career_goal,
            p.bio,
            p.profile_picture
         FROM users u
         LEFT JOIN profiles p
            ON u.id = p.user_id
         WHERE u.id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $profile = $stmt->get_result()->fetch_assoc();

    if ($profile) {
        $profile["user_id"] = (int)$profile["user_id"];
        $profile["phone"] = $profile["phone"] ?? "";
        $profile["education"] = $profile["education"] ?? "";
        $profile["skills"] = $profile["skills"] ?? "";
        $profile["career_goal"] = $profile["career_goal"] ?? "";
        $profile["bio"] = $profile["bio"] ?? "";
        $profile["profile_picture"] = $profile["profile_picture"] ?? "";

        echo json_encode([
            "status" => "success",
            "message" => "Profile fetched successfully",
            "profile" => $profile
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "User not found"
        ]);
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch profile"
    ]);
}

$conn->close();
?>
