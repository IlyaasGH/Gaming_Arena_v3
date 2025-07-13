<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Accept invoice_id from GET (corrected)
$invoiceId = $_GET['invoice_id'] ?? '';
if (!$invoiceId) {
    die("Invoice ID is required.");
}

// Get invoice by ID
$sql = "SELECT * FROM invoice WHERE invoice_id = ?";
$stmt = sqlsrv_query($conn, $sql, [$invoiceId]);
$invoice = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$invoice) {
    die("Invoice not found.");
}

$userStmt = sqlsrv_query($conn, "SELECT full_name, email FROM users WHERE user_name = ?", [$invoice['user_name']]);
$user = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC);

$userName = $user['full_name'] ?? 'Unknown';
$userEmail = $user['email'] ?? 'Unknown';

$stations = array_filter(array_map('trim', explode(',', $invoice['stations'] ?? '')));
$stationCount = count($stations);
$pricePerStation = 500;
$totalPrice = $stationCount * $pricePerStation;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Booking Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #fff;
            color: #333;
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #8A2BE2;
        }

        .section {
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .btn {
            padding: 10px 15px;
            background-color: #8A2BE2;
            color: white;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #a14ef0;
        }

        .station-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .station-box {
            background: #eee;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        .glitch-section {
            margin-top: 50px;
            text-align: center;
            padding: 30px;
            background: #000;
            border-top: 3px solid #8A2BE2;
            border-bottom: 3px solid #8A2BE2;
            box-shadow: 0 0 20px #8A2BE2;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .glitch-text {
            font-size: 2em;
            font-weight: bold;
            color: #8A2BE2;
            text-shadow: 0 0 5px #8A2BE2, 0 0 10px #fff;
            animation: glitch 1.5s infinite;
        }

        @keyframes glitch {
            0% {
                text-shadow: 2px 2px #ff00c1, -2px -2px #00fff9;
                transform: translate(0);
            }

            20% {
                text-shadow: -2px -2px #ff00c1, 2px 2px #00fff9;
                transform: translate(-1px, 1px);
            }

            40% {
                text-shadow: 2px -2px #ff00c1, -2px 2px #00fff9;
                transform: translate(1px, -1px);
            }

            60% {
                text-shadow: 0 0 10px #fff;
                transform: translate(0);
            }

            80% {
                text-shadow: 2px 2px #00fff9, -2px -2px #ff00c1;
                transform: translate(-1px, 1px);
            }

            100% {
                text-shadow: 0 0 5px #8A2BE2;
                transform: translate(0);
            }
        }

        .footer-logo {
            text-align: center;
            padding: 20px 0;
            margin-top: 30px;
            background-color: white;
            color: #888;
            font-size: 14px;
        }

        .footer-logo img {
            width: 80px;
            height: auto;
            opacity: 0.8;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <h1>🎟 Booking Receipt</h1>

    <div class="section">
        <span class="label">Customer Name:</span> <?= htmlspecialchars($userName) ?><br>
        <span class="label">Customer Email:</span> <?= htmlspecialchars($userEmail) ?>
    </div>

    <div class="section">
        <span class="label">Invoice Number:</span> <?= htmlspecialchars($invoice['invoice_number']) ?><br>
        <span class="label">Issued Date:</span> <?= $invoice['issued_date']->format('Y-m-d H:i:s') ?><br>
        <span class="label">Company:</span> <?= htmlspecialchars($invoice['company_name']) ?><br>
        <span class="label">Address:</span> <?= htmlspecialchars($invoice['company_address']) ?>
    </div>

    <div class="section">
        <span class="label">Stations Booked:</span>
        <div class="station-list">
            <?php foreach ($stations as $s): ?>
                <div class="station-box">Station <?= htmlspecialchars($s) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section">
        <span class="label">Start Time:</span> <?= (new DateTime($invoice['start_time']))->format('Y-m-d H:i:s') ?><br>
        <span class="label">End Time:</span> <?= (new DateTime($invoice['end_time']))->format('Y-m-d H:i:s') ?>

    </div>

    <div class="section">
        <span class="label">Price per Station:</span> Rs. <?= number_format($pricePerStation) ?><br>
        <span class="label">Total Stations:</span> <?= $stationCount ?><br>
        <span class="label">Total Price:</span> Rs. <?= number_format($totalPrice) ?>
    </div>

    <div class="section">
        <button class="btn" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="btn" onclick="downloadReceipt()">📄 Download Receipt</button>
    </div>

    <script>
        function downloadReceipt() {
            const html = document.documentElement.outerHTML;
            const blob = new Blob([html], {
                type: 'text/html'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'booking_receipt.html';
            link.click();
        }
    </script>

    <div class="glitch-section">
        <h2 class="glitch-text">⚡ Enjoying Your Gaming ⚡</h2>
        <dotlottie-player
            src="https://lottie.host/f508c7d4-722d-4819-8fa2-7a083f2420be/FLWmFeiWE6.lottie"
            background="transparent"
            speed="1"
            style="width: 300px; height: 300px;"
            loop autoplay>
        </dotlottie-player>
    </div>

    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

    <div class="footer-logo">
        <img src="images/logo.png" alt="Gaming Arena Logo">
        <p>&copy; <?= date("Y") ?> Gaming Arena. All rights reserved.</p>
    </div>
</body>

</html>

<?php

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only send if not already sent to avoid double emails
if (!isset($_SESSION['email_sent_' . $invoiceId])) {

    require 'vendor/autoload.php'; // Only if using Composer

    $email = 'not_provided@example.com';
    $userStmt = sqlsrv_query($conn, "SELECT email FROM users WHERE user_name = ?", [$invoice['user_name']]);
    if ($userStmt && ($userRow = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC))) {
        $email = $userRow['email'] ?? 'not_provided@example.com';
    }

    // Prepare HTML for PDF (copy from above or reuse)
    ob_start();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                color: #333;
                font-size: 14px;
                padding: 30px;
            }

            h1 {
                color: #8A2BE2;
                text-align: center;
            }

            .logo {
                text-align: center;
                margin-bottom: 20px;
            }

            .section {
                margin-bottom: 20px;
            }

            .label {
                font-weight: bold;
                color: #555;
                display: inline-block;
                min-width: 150px;
            }

            .station-box {
                background: #eee;
                display: inline-block;
                padding: 5px 10px;
                border-radius: 5px;
                margin: 5px;
                font-weight: bold;
            }

            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 40px;
                color: #999;
            }

            .divider {
                border-bottom: 1px solid #ccc;
                margin: 20px 0;
            }
        </style>
    </head>

    <body>

        <div class="logo">
            <img src="images/logo.png" width="100" alt="D-Gaming Arena Logo">
        </div>

        <h1>Booking Receipt</h1>

        <div class="section">
            <div><span class="label">Customer Name:</span> <?= htmlspecialchars($invoice['user_name']) ?></div>
            <div><span class="label">Email:</span> <?= htmlspecialchars($email ?? 'N/A') ?></div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div><span class="label">Invoice Number:</span> <?= htmlspecialchars($invoice['invoice_number']) ?></div>
            <div><span class="label">Issued Date:</span> <?= $invoice['issued_date']->format('Y-m-d H:i:s') ?></div>
            <div><span class="label">Company:</span> <?= htmlspecialchars($invoice['company_name']) ?></div>
            <div><span class="label">Address:</span> <?= htmlspecialchars($invoice['company_address']) ?></div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div><span class="label">Stations Booked:</span></div>
            <div>
                <?php foreach ($stations as $s): ?>
                    <div class="station-box">Station <?= htmlspecialchars($s) ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <div><span class="label">Start Time:</span> <?= (new DateTime($invoice['start_time']))->format('Y-m-d H:i:s') ?></div>
            <div><span class="label">End Time:</span> <?= (new DateTime($invoice['end_time']))->format('Y-m-d H:i:s') ?></div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div><span class="label">Price per Station:</span> Rs. <?= number_format($pricePerStation) ?></div>
            <div><span class="label">Total Stations:</span> <?= $stationCount ?></div>
            <div><span class="label">Total Price:</span> Rs. <?= number_format($totalPrice) ?></div>
        </div>

        <div class="footer">
            &copy; <?= date('Y') ?> D-Gaming Arena. All rights reserved.
        </div>

    </body>

    </html>
<?php
    $pdfHTML = ob_get_clean();



    // Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($pdfHTML);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf = $dompdf->output();

    $pdfPath = sys_get_temp_dir() . "/receipt_" . $invoiceId . ".pdf";
    file_put_contents($pdfPath, $pdf);

    // Fetch email from users table using user_name from invoice
    $email = 'unknown@example.com'; // fallback

    $userStmt = sqlsrv_query($conn, "SELECT email FROM users WHERE user_name = ?", [$invoice['user_name']]);
    if ($userStmt && ($userRow = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC))) {
        $email = $userRow['email'];
    }


    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mrrilyaas@gmail.com'; // your email
        $mail->Password = 'nxoz ylyd cljn nciw';   // your Gmail App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('mrrilyaas@gmail.com', 'D-Gaming Arena');
        $mail->addAddress($email, $invoice['user_name']); // to user

        $mail->isHTML(true);
        $mail->Subject = 'Your Booking Receipt';
        $mail->Body    = "Hi {$invoice['user_name']},<br>Thanks for your booking. Your receipt is attached.<br><br>Regards,<br>D-Gaming Arena";

        $mail->addAttachment($pdfPath, 'Booking_Receipt.pdf');

        // Get user_id using user_name from invoice
        $userId = null;
        $userStmt = sqlsrv_query($conn, "SELECT user_id FROM users WHERE user_name = ?", [$invoice['user_name']]);
        if ($userStmt && ($userRow = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC))) {
            $userId = $userRow['user_id'];
        }

        // Prepare email content
        $subject = "Your Booking Receipt";
        $body = "Hi {$invoice['user_name']},\nThank you for your booking. Please find your receipt attached.";

        // Insert with status 'pending'
        $insertEmailStmt = sqlsrv_query($conn, "
    INSERT INTO email_notifications (user_id, subject, body, status, sent_at, created_at)
    VALUES (?, ?, ?, 'pending', NULL, GETDATE())
", [$userId, $subject, $body]);

        // Get the inserted email_id for update later
        $emailId = null;
        if ($insertEmailStmt) {
            $getLastId = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS email_id");
            $idRow = sqlsrv_fetch_array($getLastId, SQLSRV_FETCH_ASSOC);
            $emailId = $idRow['email_id'];
        }

        $mail->send();

        if ($emailId) {
            $updateSent = sqlsrv_prepare($conn, "
        UPDATE email_notifications
        SET status = 'sent', sent_at = GETDATE()
        WHERE email_id = ?
    ", [$emailId]);

            sqlsrv_execute($updateSent);
        }


        $_SESSION['email_sent_' . $invoiceId] = true; // avoid resending
        unlink($pdfPath); // clean up

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);

        if ($emailId) {
            $updateFail = sqlsrv_prepare($conn, "
        UPDATE email_notifications
        SET status = 'failed', sent_at = GETDATE()
        WHERE email_id = ?
    ", [$emailId]);

            sqlsrv_execute($updateFail);
        }
    }
}
?>