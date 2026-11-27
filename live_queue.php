<?php
require_once __DIR__ . "/config/database.php";

$queue = $conn->query(
    "SELECT t.token_number, c.full_name,
            s.service_name, t.office_id, t.token_status
     FROM tokens t
     JOIN citizens c ON t.citizen_id = c.citizen_id
     JOIN services s ON t.service_id = s.service_id
     WHERE t.token_status IN ('Pending', 'Serving')
     ORDER BY
        CASE WHEN t.token_status = 'Serving' THEN 0 ELSE 1 END,
        t.office_id ASC,
        t.token_id ASC"
);

$officeNames = [
    1 => "Municipal Corporation",
    2 => "Collector Office",
    3 => "Mamlatdar Office"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="15">

    <title>Live Queue | QueueLess</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <h2 class="text-center text-primary fw-bold">
        Live Queue Display
    </h2>

    <p class="text-center text-muted">
        Current Government Office Queue
    </p>

    <div class="card shadow border-0 mt-4">

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-primary">
                    <tr>
                        <th>Token Number</th>
                        <th>Citizen Name</th>
                        <th>Service</th>
                        <th>Office</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($queue && $queue->num_rows > 0) { ?>

                    <?php while ($row = $queue->fetch_assoc()) { ?>

                    <tr>
                        <td>
                            <?php echo htmlspecialchars($row['token_number']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['service_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars(
                                $officeNames[$row['office_id']] ?? 'Unknown'
                            ); ?>
                        </td>

                        <td>
                            <?php if ($row['token_status'] === 'Serving') { ?>

                                <span class="badge bg-success">
                                    NOW SERVING
                                </span>

                            <?php } else { ?>

                                <span class="badge bg-warning text-dark">
                                    WAITING
                                </span>

                            <?php } ?>
                        </td>
                    </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            No tokens in queue currently.
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>
    </div>

    <p class="text-center text-muted mt-3">
        Queue automatically refreshes every 15 seconds.
    </p>

    <div class="text-center">
        <a href="index.html" class="btn btn-primary">
            Back to Home
        </a>
    </div>

</div>

</body>
</html>