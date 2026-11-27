<?php
session_start();
require_once __DIR__ . "/config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT admin_id, username, password
         FROM admins
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {

        if (password_verify($password, $admin["password"])) {

            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: admin_dashboard.php");
            exit;

        }
    }

    $error = "Invalid username or password.";

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>QueueLess Admin Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow mx-auto p-4"
         style="max-width: 420px;">

        <h2 class="text-center text-primary mb-4">
            QueueLess Admin
        </h2>

        <h5 class="text-center mb-4">Admin Login</h5>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label class="form-label">Username</label>

            <input type="text"
                   name="username"
                   class="form-control mb-3"
                   required>

            <label class="form-label">Password</label>

            <input type="password"
                   name="password"
                   class="form-control mb-4"
                   required>

            <button type="submit"
                    class="btn btn-primary w-100">
                Login
            </button>

        </form>

        <div class="text-center mt-3">
            <a href="index.html">Back to Home</a>
        </div>

    </div>

</div>

</body>
</html>