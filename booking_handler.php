// booking_handler.php
<?php
$station = $_POST['station'];

// Connect to DB
$conn = new PDO("sqlsrv:Server=AHAMED-ILYAAS;Database=gaming_arena", "pll", "myposadminauthentication");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Insert booking (simplified)
$stmt = $conn->prepare("INSERT INTO bookings (station_id, status) VALUES (?, 'booked')");
$stmt->execute([$station]);

echo "success";
?>