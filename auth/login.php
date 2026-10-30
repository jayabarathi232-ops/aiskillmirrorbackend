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

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (!is_string($email) ||
    !is_string($password) ||
    !filter_var(trim($email), FILTER_VALIDATE_EMAIL) ||
    trim($password) === "") {

    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid email and password required"
    ]);
    exit;
}

$email = strtolower(trim($email));

try {
    $stmt = $conn->prepare(
        "SELECT id, name, email, password
         FROM users
         WHERE LOWER(email) = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "id" => (int)$user["id"],
                "user_id" => (int)$user["id"],
                "name" => $user["name"],
                "email" => $user["email"]
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email or password"
        ]);
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Login failed"
    ]);
}

$conn->close();
?>
