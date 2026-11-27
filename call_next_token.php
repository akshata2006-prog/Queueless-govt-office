<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_dashboard.php");
    exit;
}

$office_id = (int)($_POST["office_id"] ?? 0);

if ($office_id <= 0) {
    die("Invalid office selected.");
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "SELECT token_id, token_number
         FROM tokens
         WHERE office_id = ? AND token_status = 'Pending'
         ORDER BY token_id ASC
         LIMIT 1
         FOR UPDATE"
    );

    $stmt->bind_param("i", $office_id);
    $stmt->execute();

    $token = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$token) {
        $conn->rollback();
        die("No pending tokens available.");
    }

    $update = $conn->prepare(
        "UPDATE tokens SET token_status = 'Serving'
         WHERE token_id = ?"
    );

    $update->bind_param("i", $token["token_id"]);
    $update->execute();
    $update->close();

    $conn->commit();

    echo "Now Serving: " . htmlspecialchars($token["token_number"]);

} catch (Exception $e) {
    $conn->rollback();
    echo "Something went wrong.";
}

$conn->close();
?>