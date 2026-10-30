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

$name = $data["name"] ?? $data["fullName"] ?? "";
$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (!is_string($name) ||
    !is_string($email) ||
    !is_string($password)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit;
}

$name = trim($name);
$email = strtolower(trim($email));

if ($name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 8) {

    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid name, email and password of at least 8 characters required"
    ]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param("sss", $name, $email, $hash);
    $stmt->execute();
    $userId = (int)$conn->insert_id;
    $stmt->close();

    // Auto-create initial blank profile row for the user
    try {
        $pStmt = $conn->prepare("INSERT INTO profiles (user_id) VALUES (?)");
        $pStmt->bind_param("i", $userId);
        $pStmt->execute();
        $pStmt->close();
    } catch (Exception $pe) {
        // Ignore if profile already exists
    }

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Signup successful",
        "user_id" => $userId,
        "user" => [
            "id" => $userId,
            "user_id" => $userId,
            "name" => $name,
            "email" => $email
        ]
    ]);

} catch (mysqli_sql_exception $e) {
    $duplicate = ($e->getCode() == 1062);

    http_response_code($duplicate ? 409 : 500);
    echo json_encode([
        "status" => "error",
        "message" => $duplicate
            ? "Email already registered"
            : "Signup failed"
    ]);
}

$conn->close();
?>
