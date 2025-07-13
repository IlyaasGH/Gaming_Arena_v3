<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$serverName = "AHAMED-ILYAAS";
$connectionOptions = [
    "Database" => "gaming_arena",
    "Uid" => "pll",
    "PWD" => "myposadminauthentication",
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$date = $_GET['date'] ?? null;
$startTime = $_GET['start_time'] ?? null;
$endTime = $_GET['end_time'] ?? null;

if (!$date || !$startTime || !$endTime) {
    echo json_encode(["booked" => []]);
    exit;
}

$startDatetime = "$date $startTime";
$endDatetime = "$date $endTime";

$sql = "
    SELECT station_id 
    FROM bookings 
    WHERE status != 'cancelled'
      AND (start_time < ? AND end_time > ?)
";

$params = [$endDatetime, $startDatetime];
$options = ["Scrollable" => SQLSRV_CURSOR_FORWARD];

$stmt = sqlsrv_query($conn, $sql, $params, $options);

$bookedStations = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $bookedStations[] = (int)$row['station_id'];
    }
}

echo json_encode(["booked" => $bookedStations]);
