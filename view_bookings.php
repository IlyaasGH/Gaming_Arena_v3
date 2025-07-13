<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}


$today = date('Y-m-d');
$show_past = isset($_GET['past']);

if ($show_past) {
    $from = date('Y-m-d', strtotime('-14 days'));
    // Show bookings from $from up to and including yesterday
    $sql = "SELECT booking_id, user_id, station_id, start_time, end_time, total_hours, total_price, status, created_at, updated_at FROM bookings WHERE CAST(start_time AS DATE) >= ? AND CAST(start_time AS DATE) < ? ORDER BY start_time DESC";
    $params = [$from, $today];
    $title = "Past Bookings (Last 2 Weeks)";
} else {
    // Show bookings for today and the future
    $sql = "SELECT booking_id, user_id, station_id, start_time, end_time, total_hours, total_price, status, created_at, updated_at FROM bookings WHERE CAST(start_time AS DATE) >= ? ORDER BY start_time ASC";
    $params = [$today];
    $title = "Upcoming & Today's Bookings";
}

$bookings = [];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $bookings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Bookings | Gaming Arena</title>
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
            max-width: 1100px;
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

        .actions {
            margin-bottom: 1.5rem;
        }

        .actions a {
            background: #8A2BE2;
            color: #fff;
            border-radius: 6px;
            padding: 0.5rem 1.2rem;
            text-decoration: none;
            font-weight: 600;
            margin-right: 1rem;
        }

        .actions a.selected {
            background: #28a745;
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

        .status-completed {
            color: #28a745;
            font-weight: 600;
        }

        .status-active {
            color: #f39c12;
            font-weight: 600;
        }

        .status-cancelled {
            color: #e84118;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= $title ?></h1>
        <div class="actions">
            <a href="view_bookings.php" class="<?= !$show_past ? 'selected' : '' ?>">Upcoming & Today's Bookings</a>
            <a href="view_bookings.php?past=1" class="<?= $show_past ? 'selected' : '' ?>">View Past Bookings (2 Weeks)</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Station</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Total Hours</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:#aaa;">No bookings found for this period.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td><?= htmlspecialchars($booking['user_id']) ?></td>
                            <td><?= htmlspecialchars($booking['station_id']) ?></td>
                            <td><?= htmlspecialchars(is_object($booking['start_time']) && method_exists($booking['start_time'], 'format') ? $booking['start_time']->format('Y-m-d H:i') : $booking['start_time']) ?></td>
                            <td><?= htmlspecialchars(is_object($booking['end_time']) && method_exists($booking['end_time'], 'format') ? $booking['end_time']->format('Y-m-d H:i') : $booking['end_time']) ?></td>
                            <td><?= htmlspecialchars($booking['total_hours']) ?></td>
                            <td><?= htmlspecialchars($booking['total_price']) ?></td>
                            <td>
                                <?php if ($booking['status'] === 'Completed'): ?>
                                    <span class="status-completed">Completed</span>
                                <?php elseif ($booking['status'] === 'Active'): ?>
                                    <span class="status-active">Active</span>
                                <?php elseif ($booking['status'] === 'Cancelled'): ?>
                                    <span class="status-cancelled">Cancelled</span>
                                <?php else: ?>
                                    <span><?= htmlspecialchars($booking['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(is_object($booking['created_at']) && method_exists($booking['created_at'], 'format') ? $booking['created_at']->format('Y-m-d H:i') : $booking['created_at']) ?></td>
                            <td><?= htmlspecialchars(is_object($booking['updated_at']) && method_exists($booking['updated_at'], 'format') ? $booking['updated_at']->format('Y-m-d H:i') : $booking['updated_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>