<?php
require_once __DIR__ . "/config/database.php";

$token = null;
$error = "";

$officeNames = [
    1 => "Municipal Corporation",
    2 => "Collector Office",
    3 => "Mamlatdar Office"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token_number = trim($_POST["token_number"] ?? "");

    if ($token_number !== "") {

        $stmt = $conn->prepare(
            "SELECT t.token_number, t.token_status,
                    t.office_id, c.full_name, s.service_name
             FROM tokens t
             JOIN citizens c ON t.citizen_id = c.citizen_id
             JOIN services s ON t.service_id = s.service_id
             WHERE t.token_number = ?"
        );

        $stmt->bind_param("s", $token_number);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = $result->fetch_assoc();
        } else {
            $error = "Token not found. Please check your token number.";
        }

        $stmt->close();

    } else {
        $error = "Please enter your token number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Track Your Token | QueueLess</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow mx-auto" style="max-width: 550px;">

        <div class="card-body p-4">

            <h2 class="text-center text-primary fw-bold">
                Track Your Token
            </h2>

            <p class="text-center text-muted">
                Enter your token number to check status.
            </p>

            <form method="POST">

                <label class="form-label">Token Number</label>

                <input type="text"
                       name="token_number"
                       class="form-control mb-3"
                       placeholder="Example: MAM-0013"
                       required>

                <button type="submit" class="btn btn-primary w-100">
                    Track Token
                </button>

            </form>

            <?php if ($error !== "") { ?>

                <div class="alert alert-danger mt-3">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php } ?>

            <?php if ($token) { ?>

                <div class="alert alert-success mt-4">

                    <h5>Token Details</h5>

                    <p><b>Token Number:</b>
                        <?php echo htmlspecialchars($token["token_number"]); ?>
                    </p>

                    <p><b>Citizen Name:</b>
                        <?php echo htmlspecialchars($token["full_name"]); ?>
                    </p>

                    <p><b>Service:</b>
                        <?php echo htmlspecialchars($token["service_name"]); ?>
                    </p>

                    <p><b>Office:</b>
                        <?php echo htmlspecialchars(
                            $officeNames[$token["office_id"]] ?? "Unknown"
                        ); ?>
                    </p>

                    <p><b>Status:</b>
                        <?php echo htmlspecialchars($token["token_status"]); ?>
                    </p>

                </div>

            <?php } ?>

            <a href="index.html" class="btn btn-outline-secondary w-100 mt-2">
                Back to Home
            </a>

        </div>
    </div>
</div>

</body>
</html>