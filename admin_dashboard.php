<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

// Dashboard counts
$citizens = $conn->query(
    "SELECT COUNT(*) AS total FROM citizens"
)->fetch_assoc()['total'];

$tokens = $conn->query(
    "SELECT COUNT(*) AS total FROM tokens"
)->fetch_assoc()['total'];

$pending = $conn->query(
    "SELECT COUNT(*) AS total FROM tokens WHERE token_status='Pending'"
)->fetch_assoc()['total'];

$serving = $conn->query(
    "SELECT COUNT(*) AS total FROM tokens WHERE token_status='Serving'"
)->fetch_assoc()['total'];

$completed = $conn->query(
    "SELECT COUNT(*) AS total FROM tokens WHERE token_status='Completed'"
)->fetch_assoc()['total'];

// Filters
$search = trim($_GET['search'] ?? '');
$officeFilter = $_GET['office'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$conditions = [];

if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $conditions[] = "(t.token_number LIKE '%$safeSearch%'
                     OR c.full_name LIKE '%$safeSearch%')";
}

if (in_array($officeFilter, ['1', '2', '3'], true)) {
    $conditions[] = "t.office_id = " . (int)$officeFilter;
}

if (in_array($statusFilter, ['Pending', 'Processing', 'Completed'], true)) {
    $safeStatus = $conn->real_escape_string($statusFilter);
    $conditions[] = "t.token_status = '$safeStatus'";
}

$sql = "SELECT t.token_number, c.full_name,
               s.service_name, t.office_id, t.token_status
        FROM tokens t
        JOIN citizens c ON t.citizen_id = c.citizen_id
        JOIN services s ON t.service_id = s.service_id";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY t.citizen_id DESC LIMIT 50";

$recent = $conn->query($sql);

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

    <title>QueueLess Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <style>
        body {
            background: #f4f7fc;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            background: #123b70;
            min-height: 100vh;
            color: white;
            padding: 25px 15px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
        }

        .sidebar a:hover {
            background: #28558c;
        }

        .main-content {
            padding: 30px;
        }

        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.07);
        }

        .dashboard-card h2 {
            font-weight: bold;
        }

        .table-card {
            border: none;
            border-radius: 12px;
        }

        @media(max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
<div class="row">

    <!-- Sidebar -->
    <div class="col-md-2 sidebar">

        <h3 class="fw-bold mb-4">QueueLess</h3>

        <a href="admin_dashboard.php">📊 Dashboard</a>

        <a href="index.html">🏠 Home</a>

        <a href="live_queue.php">🎫 Live Queue</a>

        <a href="admin_logout.php" class="bg-danger mt-4">
            Logout
        </a>

    </div>

    <!-- Main Content -->
    <div class="col-md-10 main-content">

        <h2 class="fw-bold">Admin Dashboard</h2>

        <p class="text-muted">
            Government Office Management System
        </p>

        <!-- Dashboard Cards -->
        <div class="row g-4 mt-2">

            <div class="col-md-4 col-lg">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Total Citizens</h6>
                        <h2 class="text-primary">
                            <?php echo $citizens; ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Total Tokens</h6>
                        <h2 class="text-info">
                            <?php echo $tokens; ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Pending</h6>
                        <h2 class="text-warning">
                            <?php echo $pending; ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Serving</h6>
<h2 class="text-primary">
    <?php echo $serving; ?>
</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Completed</h6>
                        <h2 class="text-success">
                            <?php echo $completed; ?>
                        </h2>
                    </div>
                </div>
            </div>

        </div>
        <div class="card p-3 mb-4">
    <h5>Call Next Token</h5>

    <form action="call_next_token.php" method="POST">
        <select name="office_id" class="form-select mb-3" required>
            <option value="">Select Office</option>
            <option value="1">Municipal Corporation</option>
            <option value="2">Collector Office</option>
            <option value="3">Mamlatdar Office</option>
        </select>

        <button type="submit" class="btn btn-primary">
            Call Next Token
        </button>
    </form>
</div>

        <!-- Token Table -->
        <div class="card table-card shadow-sm mt-5">

            <div class="card-body">

                <h5 class="fw-bold mb-4">
                    Recent Token Registrations
                </h5>

                <!-- Search and Filters -->
                <form method="GET" class="row g-3 mb-4">

                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search token or citizen"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-md-3">
                        <select name="office" class="form-select">
                            <option value="">All Offices</option>

                            <option value="1" <?php if($officeFilter === '1') echo 'selected'; ?>>
                                Municipal Corporation
                            </option>

                            <option value="2" <?php if($officeFilter === '2') echo 'selected'; ?>>
                                Collector Office
                            </option>

                            <option value="3" <?php if($officeFilter === '3') echo 'selected'; ?>>
                                Mamlatdar Office
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">

                            <option value="">All Status</option>

                            <?php foreach (['Pending', 'Serving', 'Completed'] as $s) { ?>

                                <option value="<?php echo $s; ?>"
                                    <?php if($statusFilter === $s) echo 'selected'; ?>>
                                    <?php echo $s; ?>
                                </option>

                            <?php } ?>

                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Search
                        </button>
                    </div>

                </form>

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle">

                        <thead class="table-primary">
                            <tr>
                                <th>Token Number</th>
                                <th>Citizen Name</th>
                                <th>Service</th>
                                <th>Office</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if ($recent && $recent->num_rows > 0) { ?>

                            <?php while ($row = $recent->fetch_assoc()) { ?>

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
                                        <?php
                                        $badge = 'warning text-dark';

                                        if ($row['token_status'] === 'Serving') {
                                            $badge = 'success';
                                        } elseif ($row['token_status'] === 'Completed') {
                                            $badge = 'success';
                                        }
                                        ?>

                                        <span class="badge bg-<?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($row['token_status']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <form action="update_token_status.php" method="POST">

                                            <input type="hidden"
                                                   name="token_number"
                                                   value="<?php echo htmlspecialchars($row['token_number']); ?>">

                                            <select name="token_status"
                                                    class="form-select form-select-sm mb-2">

                                                <?php foreach (['Pending', 'Serving', 'Completed'] as $s) { ?>

                                                    <option value="<?php echo $s; ?>"
                                                        <?php if ($row['token_status'] === $s) echo 'selected'; ?>>
                                                        <?php echo $s; ?>
                                                    </option>

                                                <?php } ?>

                                            </select>

                                            <button type="submit"
                                                    class="btn btn-primary btn-sm w-100">
                                                Update
                                            </button>

                                        </form>
                                    </td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="6" class="text-center">
                                    No tokens found.
                                </td>
                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>
</div>
</div>

</body>
</html>