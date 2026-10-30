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

if (!$user_id || $user_id < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid user_id required"
    ]);
    exit;
}

$allowedProfileFields = [
    "phone", "education", "skills",
    "career_goal", "bio"
];

$profileUpdates = [];
$profileValues = [];
$profileTypes = "";

foreach ($allowedProfileFields as $field) {
    if (array_key_exists($field, $data)) {
        if (!is_string($data[$field]) && !is_null($data[$field])) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid " . $field
            ]);
            exit;
        }

        $profileUpdates[] = "$field = ?";
        $profileValues[] = trim((string)$data[$field]);
        $profileTypes .= "s";
    }
}

$hasNameUpdate = false;
$nameValue = "";
if (array_key_exists("name", $data)) {
    if (!is_string($data["name"]) || trim($data["name"]) === "") {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid name"
        ]);
        exit;
    }
    $hasNameUpdate = true;
    $nameValue = trim($data["name"]);
}

if (empty($profileUpdates) && !$hasNameUpdate) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "No fields to update"
    ]);
    exit;
}

try {
    // 1. Verify user exists in users table
    $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->bind_param("i", $user_id);
    $userCheck->execute();
    $userCheck->store_result();

    if ($userCheck->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "User not found"
        ]);
        $userCheck->close();
        exit;
    }
    $userCheck->close();

    // 2. If name is supplied, update users table
    if ($hasNameUpdate) {
        $nameStmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $nameStmt->bind_param("si", $nameValue, $user_id);
        $nameStmt->execute();
        $nameStmt->close();
    }

    // 3. Update or create profile in profiles table
    if (!empty($profileUpdates)) {
        $profileCheck = $conn->prepare("SELECT user_id FROM profiles WHERE user_id = ?");
        $profileCheck->bind_param("i", $user_id);
        $profileCheck->execute();
        $profileCheck->store_result();
        $profileExists = ($profileCheck->num_rows > 0);
        $profileCheck->close();

        if ($profileExists) {
            $sql = "UPDATE profiles SET " . implode(", ", $profileUpdates) . " WHERE user_id = ?";
            $profileValues[] = $user_id;
            $profileTypes .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($profileTypes, ...$profileValues);
            $stmt->execute();
            $stmt->close();
        } else {
            // Profile row does not exist yet: insert it
            $insertFields = ["user_id"];
            $insertPlaceholders = ["?"];
            $insertValues = [$user_id];
            $insertTypes = "i";

            foreach ($allowedProfileFields as $field) {
                if (array_key_exists($field, $data)) {
                    $insertFields[] = $field;
                    $insertPlaceholders[] = "?";
                    $insertValues[] = trim((string)$data[$field]);
                    $insertTypes .= "s";
                }
            }

            $sql = "INSERT INTO profiles (" . implode(", ", $insertFields) . ") VALUES (" . implode(", ", $insertPlaceholders) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($insertTypes, ...$insertValues);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully"
    ]);

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Profile update failed"
    ]);
}

$conn->close();
?>
