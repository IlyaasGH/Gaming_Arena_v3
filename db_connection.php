<?php
$serverName = "AHAMED-ILYAAS";
$connectionOptions = [
    "Database" => "gaming_arena",
    "Uid" => "pll",
    "PWD" => "myposadminauthentication",
    "TrustServerCertificate" => true
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
