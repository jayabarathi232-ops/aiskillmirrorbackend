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
$email = strtolower(trim($data["email"] ?? ""));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid email required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", time() + 900); // 15 mins

        $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $email, $token, $expires);
        $ins->execute();
        $ins->close();
    }
    $stmt->close();

    // Always return success so emails are not enumerated
    echo json_encode([
        "status" => "success",
        "message" => "If your email is registered, a password reset link has been sent."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to process recovery request"]);
}

$conn->close();
?>

