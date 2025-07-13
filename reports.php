<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

$report_type = isset($_GET['type']) ? $_GET['type'] : 'bookings';
$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : '';

$data = [];
$columns = [];
$title = '';

if ($report_type === 'bookings') {
    $title = 'Booking Report';
    // Pagination setup
    $per_page = 20;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $per_page;
    // Filter Booking_View by created_at date
    $sql_base = "FROM Booking_View WHERE 1=1";
    $params = [];
    if (isset($from) && isset($to)) {
        $sql_base .= " AND CAST(created_at AS DATE) >= ? AND CAST(created_at AS DATE) <= ?";
        $params[] = $from;
        $params[] = $to;
    }
    // Get total bookings for filtered period
    $sql_count = "SELECT COUNT(*) AS total_count, ISNULL(SUM(CAST(total_hours AS FLOAT)),0) AS total_hours, ISNULL(SUM(CAST(total_price AS FLOAT)),0) AS total_price " . $sql_base;
    $stmt_count = sqlsrv_query($conn, $sql_count, $params);
    $total_bookings = 0;
    $total_hours = 0;
    $total_price = 0;
    if ($stmt_count && ($row = sqlsrv_fetch_array($stmt_count, SQLSRV_FETCH_ASSOC))) {
        $total_bookings = intval($row['total_count']);
        $total_hours = floatval($row['total_hours']);
        $total_price = floatval($row['total_price']);
    }
    // Get paginated data
    $sql = "SELECT * " . $sql_base . " ORDER BY created_at DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
    $params_page = array_merge($params, [$offset, $per_page]);
    $stmt = sqlsrv_query($conn, $sql, $params_page);
    $data = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    $columns = [
        'Booking ID' => 'booking_id',
        'User ID' => 'user_id',
        'Station ID' => 'station_id',
        'Total Hours' => 'total_hours',
        'Total Price' => 'total_price',
    ];
} elseif ($report_type === 'users') {
    $title = 'User Report';
    $sql = "SELECT user_id, name, email, phone, created_at FROM users WHERE CAST(created_at AS DATE) >= ? AND CAST(created_at AS DATE) <= ? ORDER BY created_at DESC";
    $params = [$from, $to];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    $columns = [
        'User ID' => 'user_id',
        'Name' => 'name',
        'Email' => 'email',
        'Phone' => 'phone',
        'Created At' => 'created_at',
    ];
} elseif ($report_type === 'stations') {
    $title = 'Station Report';
    $sql = "SELECT station_id, name, type, price, status, created_by, created_at FROM stations WHERE CAST(created_at AS DATE) >= ? AND CAST(created_at AS DATE) <= ? ORDER BY created_at DESC";
    $params = [$from, $to];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    $columns = [
        'Station ID' => 'station_id',
        'Name' => 'name',
        'Type' => 'type',
        'Price/hr' => 'price',
        'Status' => 'status',
        'Created By' => 'created_by',
        'Created At' => 'created_at',
    ];
}

function formatCell($value)
{
    if (is_object($value) && method_exists($value, 'format')) {
        return htmlspecialchars($value->format('Y-m-d H:i'));
    }
    return htmlspecialchars($value);
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $title ?> | Gaming Arena</title>
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
            max-width: 1200px;
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

        form {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        input,
        select {
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid #444;
            background: #22223a;
            color: #fff;
        }

        button,
        .btn {
            background: #8A2BE2;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            cursor: pointer;
            margin-right: 0.5rem;
        }

        .btn-print,
        .btn-download {
            background: #28a745;
            color: #fff;
        }

        .btn-download {
            background: #007bff;
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

        .actions {
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= $title ?></h1>
        <form method="get" action="reports.php">
            <label>Report Type:
                <select name="type" onchange="this.form.submit()">
                    <option value="bookings" <?= $report_type === 'bookings' ? 'selected' : '' ?>>Bookings</option>
                    <option value="users" <?= $report_type === 'users' ? 'selected' : '' ?>>Users</option>
                    <option value="stations" <?= $report_type === 'stations' ? 'selected' : '' ?>>Stations</option>
                    <option value="profit" <?= $report_type === 'profit' ? 'selected' : '' ?>>Profit</option>
                    <option value="hours_played" <?= $report_type === 'hours_played' ? 'selected' : '' ?>>Hours Played</option>
                    <option value="food_orders" <?= $report_type === 'food_orders' ? 'selected' : '' ?>>Food Orders</option>
                </select>
            </label>
            <label>From: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" required></label>
            <label>To: <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" required></label>
            <?php if ($report_type === 'bookings'): ?>
                <label>Status:
                    <select name="status">
                        <option value="">All</option>
                        <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </label>
            <?php endif; ?>
            <button type="submit">Filter</button>
            <button type="button" class="btn btn-print" onclick="window.print()">Print</button>
            <button type="button" class="btn btn-download" onclick="showDownloadModal()">Download</button>
            <!-- Download Modal -->
            <div id="downloadModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:999; align-items:center; justify-content:center;">
                <div style="background:#23234a; color:#fff; padding:2rem 2.5rem; border-radius:12px; min-width:260px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.4); position:relative; text-align:center;">
                    <h2 style="margin-top:0; color:#8A2BE2;">Download Report</h2>
                    <p style="margin-bottom:1.5rem;">Choose file type to download:</p>
                    <button class="btn btn-download" style="background:#007bff; margin-right:1rem;" onclick="downloadCSV(); closeDownloadModal();">CSV</button>
                    <button class="btn btn-download" style="background:#28a745;" onclick="downloadPDF(); closeDownloadModal();">PDF</button>
                    <br><br>
                    <button class="btn" style="background:#e84118;" onclick="closeDownloadModal()">Cancel</button>
                </div>
            </div>
            <script>
                function showDownloadModal() {
                    document.getElementById('downloadModal').style.display = 'flex';
                }

                function closeDownloadModal() {
                    document.getElementById('downloadModal').style.display = 'none';
                }
            </script>
        </form>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
        <script>
            function downloadPDF() {
                var doc = new jspdf.jsPDF('l', 'pt', 'a4');
                doc.text("<?= $title ?>", 40, 40);
                var elem = document.getElementById('reportTable');
                // Get column headers
                var headers = [];
                var ths = elem.querySelectorAll('thead th');
                for (var i = 0; i < ths.length; i++) {
                    headers.push(ths[i].innerText);
                }
                // Get data rows (excluding summary row)
                var body = [];
                var trs = elem.querySelectorAll('tbody tr');
                for (var i = 0; i < trs.length; i++) {
                    // skip summary row (has bold font or 'Total Bookings' in first cell)
                    var tds = trs[i].querySelectorAll('td');
                    if (tds.length && tds[0].innerText.trim().startsWith('Total Bookings')) continue;
                    var row = [];
                    for (var j = 0; j < tds.length; j++) {
                        row.push(tds[j].innerText);
                    }
                    if (row.length) body.push(row);
                }
                // Add summary row for PDF only
                var summaryRow = [
                    'Total Bookings: <?= $total_bookings ?>',
                    '',
                    '',
                    '<?= number_format($total_hours, 2) ?>',
                    '<?= number_format($total_price, 2) ?>'
                ];
                body.push(summaryRow);
                doc.autoTable({
                    head: [headers],
                    body: body,
                    startY: 60,
                    styles: {
                        fontSize: 9
                    },
                    didParseCell: function(data) {
                        if (data.row.index === body.length - 1) {
                            data.cell.styles.fontStyle = 'bold';
                            data.cell.styles.textColor = '#fff';
                            data.cell.styles.fillColor = [24, 24, 42];
                        }
                    }
                });
                doc.save('<?= strtolower($title) ?>.pdf');
            }
        </script>
        <table id="reportTable">
            <thead>
                <tr>
                    <?php foreach ($columns as $col => $key): ?>
                        <th><?= $col ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($columns) ?>" style="text-align:center; color:#aaa;">No records found for this period.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($columns as $key): ?>
                                <td><?= formatCell($row[$key] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Summary row -->
                    <tr style="background:#18182a; font-weight:700; color:#fff;">
                        <td>Total Bookings: <?= $total_bookings ?></td>
                        <td></td>
                        <td></td>
                        <td><?= number_format($total_hours, 2) ?></td>
                        <td><?= number_format($total_price, 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination controls -->
        <?php if ($report_type === 'bookings' && $total_bookings > $per_page): ?>
            <div style="display:flex; justify-content:center; align-items:center; gap:0.5rem; margin-bottom:2rem;">
                <?php $total_pages = ceil($total_bookings / $per_page); ?>
                <?php if ($page > 1): ?>
                    <a href="?type=bookings&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&page=<?= $page - 1 ?>" style="color:#fff; background:#8A2BE2; padding:0.4em 1em; border-radius:5px; text-decoration:none; font-weight:600;">&laquo; Prev</a>
                <?php endif; ?>
                <span style="color:#fff; font-weight:600;">Page <?= $page ?> of <?= $total_pages ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?type=bookings&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&page=<?= $page + 1 ?>" style="color:#fff; background:#8A2BE2; padding:0.4em 1em; border-radius:5px; text-decoration:none; font-weight:600;">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function downloadCSV() {
            var table = document.getElementById('reportTable');
            var rows = table.querySelectorAll('tr');
            var csv = [];
            for (var i = 0; i < rows.length; i++) {
                var cols = rows[i].querySelectorAll('th,td');
                var row = [];
                for (var j = 0; j < cols.length; j++) {
                    var text = cols[j].innerText.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
                    // Escape quotes
                    text = '"' + text.replace(/"/g, '""') + '"';
                    row.push(text);
                }
                csv.push(row.join(','));
            }
            var csvContent = csv.join('\n');
            var blob = new Blob([csvContent], {
                type: 'text/csv'
            });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = '<?= strtolower($title) ?>.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
</body>