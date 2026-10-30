
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "root";
$password = "";
$database = "skill mirror";

try {
    $conn = new mysqli(
        $host,
        $username,
        $password,
        $database
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    header("Content-Type: application/json");
    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}
?>
