
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["admin_id"])) {
    die("Admin login required.");
}

require_once __DIR__ . "/config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

$token_number = $_POST["token_number"] ?? "";
$status = $_POST["token_status"] ?? "";

if ($token_number === "" || $status === "") {
    die("Token number or status is missing.");
}

$allowed_status = ["Pending", "Serving", "Completed"];

if (!in_array($status, $allowed_status, true)) {
    die("Invalid status.");
}

$stmt = $conn->prepare(
    "UPDATE tokens SET token_status = ? WHERE token_number = ?"
);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("ss", $status, $token_number);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: admin_dashboard.php");
    exit;
} else {
    echo "Update failed: " . $stmt->error;
}


$stmt->close();
$conn->close();
?>