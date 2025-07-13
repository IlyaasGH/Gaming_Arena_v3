<?php
// process_booking.php - Full Booking Logic + Invoice Generation

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_name'])) {
    die("User not logged in");
}

$serverName = "AHAMED-ILYAAS";
$connectionOptions = [
    "Database" => "gaming_arena",
    "Uid" => "pll",
    "PWD" => "myposadminauthentication",
    "TrustServerCertificate" => true
];
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

$station_ids = array_filter(array_map('trim', explode(',', $_POST['station_id'])), fn($s) => $s !== '');
$start_time_raw = $_POST['start_time_hidden'];
$end_time_raw = $_POST['end_time_hidden'];

$start_time = date('Y-m-d H:i:s', strtotime($start_time_raw));
$end_time = date('Y-m-d H:i:s', strtotime($end_time_raw));

$start = new DateTime($start_time);
$end = new DateTime($end_time);
$interval = $start->diff($end);
$total_hours = $interval->h + ($interval->i / 60);

$user_name = $_SESSION['user_name'];
$userQuery = "SELECT user_id FROM users WHERE user_name = ?";
$userStmt = sqlsrv_query($conn, $userQuery, [$user_name]);
$userRow = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC);
$user_id = $userRow['user_id'] ?? null;

if (!$user_id) {
    die("User not found");
}

$bookedStations = [];
$skippedStations = [];

foreach ($station_ids as $station_id) {
    $station_id = intval($station_id);

    $overlapSQL = "SELECT 1 FROM bookings 
                   WHERE station_id = ? 
                   AND status != 'cancelled'
                   AND (start_time < ? AND end_time > ?)";
    $checkParams = [$station_id, $end_time, $start_time];
    $checkStmt = sqlsrv_query($conn, $overlapSQL, $checkParams);

    if ($checkStmt === false) {
        die("Overlap check failed: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($checkStmt)) {
        $skippedStations[] = $station_id;
        continue;
    }

    $pricePerStation = round($total_hours * 500);
    $sql = "INSERT INTO bookings (user_id, station_id, start_time, end_time, total_hours, total_price, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'confirmed', GETDATE(), GETDATE())";
    $params = [$user_id, $station_id, $start_time, $end_time, $total_hours, $pricePerStation];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Insert failed: " . print_r(sqlsrv_errors(), true));
    }

    $bookedStations[] = $station_id;
    sqlsrv_free_stmt($stmt);
}

$invoiceId = null;
if (!empty($bookedStations)) {
    $stationCount = count($bookedStations);
    $pricePerStation = 500;
    $totalPrice = $stationCount * $pricePerStation * $total_hours;

    $issuedDate = date('Y-m-d H:i:s');
    $companyName = 'Gaming Arena Pvt Ltd';
    $companyAddress = '123, Main Street, Colombo, Sri Lanka';

    $lastInvoiceSql = "SELECT TOP 1 invoice_id FROM invoice ORDER BY invoice_id DESC";
    $lastInvoiceStmt = sqlsrv_query($conn, $lastInvoiceSql);
    $lastInvoiceId = 0;
    if ($lastInvoiceStmt && $row = sqlsrv_fetch_array($lastInvoiceStmt, SQLSRV_FETCH_ASSOC)) {
        $lastInvoiceId = $row['invoice_id'];
    }
    $newInvoiceId = $lastInvoiceId + 1;
    $invoiceNumber = 'INV' . str_pad($newInvoiceId, 4, '0', STR_PAD_LEFT);
    $stationsStr = implode(',', $bookedStations);

    $insertInvoiceSql = "INSERT INTO invoice (invoice_number, user_name, company_name, company_address, issued_date, stations, start_time, end_time, total_price)
                         OUTPUT INSERTED.invoice_id
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [
        $invoiceNumber,
        $user_name,
        $companyName,
        $companyAddress,
        $issuedDate,
        $stationsStr,
        $start_time,
        $end_time,
        $totalPrice
    ];
    $stmt = sqlsrv_query($conn, $insertInvoiceSql, $params);
    if ($stmt === false) {
        die("Invoice insert failed: " . print_r(sqlsrv_errors(), true));
    }
    $invoiceRow = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $invoiceId = $invoiceRow['invoice_id'] ?? null;

    include 'send_email.php';
    $userEmail = $_SESSION['user_email'] ?? 'mrrilyaas@gmail.com';
    sendInvoiceEmail($invoiceNumber, $userEmail);
}

$invoiceEncoded = urlencode($invoiceId);
sqlsrv_close($conn);
header("Location: receipt.php?invoice_id=$invoiceEncoded");
exit;
