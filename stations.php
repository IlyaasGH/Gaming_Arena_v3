<?php
session_start();
require 'db_connection.php';

// Only allow logged-in admins
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

// Handle add, update, delete, maintenance actions (backend logic to be implemented)
// ...

// Handle add station
if (isset($_POST['add_station'])) {
    $name = $_POST['station_name'];
    $type = $_POST['station_type'];
    $price = isset($_POST['station_price']) ? floatval($_POST['station_price']) : 0;
    $created_by = $_SESSION['admin_id'];
    $created_at = date('Y-m-d H:i:s');
    $status = 'Active';
    $sqlAdd = "INSERT INTO stations (name, type, price, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?)";
    sqlsrv_query($conn, $sqlAdd, [$name, $type, $price, $status, $created_by, $created_at]);
    // Optionally log action here
    header('Location: stations.php');
    exit();
}

// Handle delete station
if (isset($_GET['delete'])) {
    $station_id = intval($_GET['delete']);
    $sqlDel = "DELETE FROM stations WHERE station_id = ?";
    sqlsrv_query($conn, $sqlDel, [$station_id]);
    // Optionally log action here
    header('Location: stations.php');
    exit();
}

// Handle set maintenance/activate
if (isset($_GET['maint']) || isset($_GET['activate'])) {
    $station_id = intval(isset($_GET['maint']) ? $_GET['maint'] : $_GET['activate']);
    $new_status = isset($_GET['maint']) ? 'Maintenance' : 'Active';
    $sqlStatus = "UPDATE stations SET status = ? WHERE station_id = ?";
    sqlsrv_query($conn, $sqlStatus, [$new_status, $station_id]);
    // Optionally log action here
    header('Location: stations.php');
    exit();
}

// Fetch all stations
$stations = [];
$sql = "SELECT station_id, name, type, price, status, created_by, created_at FROM stations ORDER BY station_id ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $stations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Stations | Gaming Arena</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #18182a;
            color: #eee;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #23234a;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.12);
        }

        h1 {
            color: #8A2BE2;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        th,
        td {
            padding: 0.8rem 0.5rem;
            text-align: left;
        }

        th {
            background: #292942;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #22223a;
        }

        tr:nth-child(odd) {
            background: #23234a;
        }

        .status-active {
            color: #28a745;
            font-weight: 600;
        }

        .status-maintenance {
            color: #f39c12;
            font-weight: 600;
        }

        .actions a {
            margin-right: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .actions .delete {
            color: #e84118;
            background: none;
            font-weight: bold;
            border: 2px solid #e84118;
        }

        .actions .maint {
            color: #f39c12;
            background: none;
            font-weight: bold;
            border: 2px solid #f39c12;
        }

        .actions .activate {
            color: #28a745;
            background: none;
            font-weight: bold;
            border: 2px solid #28a745;
        }

        .add-form {
            margin-bottom: 2rem;
        }

        .add-form input {
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #444;
            margin-right: 0.7rem;
        }

        .add-form button {
            background: #8A2BE2;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Stations</h1>
        <form class="add-form" method="POST" action="stations.php">
            <input type="text" name="station_name" placeholder="Station Name" required>
            <select name="station_type" required>
                <option value="">Type</option>
                <option value="PC">PC</option>
                <option value="PS5">PS5</option>
                <option value="PS4">PS4</option>
                <option value="XBOX">XBOX</option>
                <option value="Billiard Table">Billiard Table</option>
            </select>
            <input type="number" name="station_price" placeholder="Price per hour" min="0" step="0.01" required>
            <button type="submit" name="add_station">Add Station</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Price/hr</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $station): ?>
                    <tr>
                        <td><?= htmlspecialchars($station['station_id']) ?></td>
                        <td><?= htmlspecialchars($station['name']) ?></td>
                        <td><?= htmlspecialchars($station['type']) ?></td>
                        <td><?= htmlspecialchars($station['price']) ?></td>
                        <td>
                            <?php if ($station['status'] === 'Active'): ?>
                                <span class="status-active">Active</span>
                            <?php elseif ($station['status'] === 'Maintenance'): ?>
                                <span class="status-maintenance">Maintenance</span>
                            <?php else: ?>
                                <span><?= htmlspecialchars($station['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($station['created_by']) ?></td>
                        <td><?= is_object($station['created_at']) && method_exists($station['created_at'], 'format') ? $station['created_at']->format('Y-m-d H:i:s') : htmlspecialchars($station['created_at']) ?></td>
                        <td class="actions">
                            <!-- Edit can be implemented as a modal or separate page -->
                            <a href="?delete=<?= $station['station_id'] ?>" class="delete" onclick="return confirm('Delete this station?')">Delete</a>
                            <?php if ($station['status'] === 'Active'): ?>
                                <a href="?maint=<?= $station['station_id'] ?>" class="maint">Set Maintenance</a>
                            <?php else: ?>
                                <a href="?activate=<?= $station['station_id'] ?>" class="activate">Activate</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>