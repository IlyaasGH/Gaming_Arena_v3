<?php

require('PHPMailer/PHPMailer.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendInvoiceEmail($invoiceNumber)
{
    // DB connection
    $serverName = "AHAMED-ILYAAS";
    $connectionOptions = [
        "Database" => "gaming_arena",
        "Uid" => "pll",
        "PWD" => "myposadminauthentication",
        "TrustServerCertificate" => true
    ];
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if (!$conn) {
        error_log(print_r(sqlsrv_errors(), true));
        return false;
    }

    // Fetch invoice by invoice number
    $sql = "SELECT * FROM invoice WHERE invoice_number = ?";
    $stmt = sqlsrv_query($conn, $sql, [$invoiceNumber]);
    $invoice = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$invoice) return false;

    // Format issued_date
    $issuedDateRaw = $invoice['issued_date'];
    if ($issuedDateRaw instanceof DateTime) {
        $issuedDateFormatted = $issuedDateRaw->format('Y-m-d H:i:s');
    } else {
        $issuedDateFormatted = date('Y-m-d H:i:s', strtotime($issuedDateRaw));
    }

    // Get user email from database
    $userEmail = getUserEmail($conn, $invoice['user_name']);
    if (!$userEmail) return false;

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mrrilyaas@gmail.com'; // your email
        $mail->Password = 'yynsrpuheytffvwd';    // your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('mrrilyaas@gmail.com', 'Gaming Arena');
        $mail->addAddress($userEmail, $invoice['user_name']);

        $mail->isHTML(true);
        $mail->Subject = "🎮 Your Booking Receipt - {$invoice['invoice_number']}";

        $mail->Body = "
            <h2>Gaming Arena - Invoice</h2>
            <p><strong>Invoice Number:</strong> {$invoice['invoice_number']}</p>
            <p><strong>Issued Date:</strong> {$issuedDateFormatted}</p>
            <p><strong>User:</strong> {$invoice['user_name']}</p>
            <p><strong>Stations:</strong> {$invoice['stations']}</p>
            <p><strong>Start:</strong> {$invoice['start_time']}</p>
            <p><strong>End:</strong> {$invoice['end_time']}</p>
            <p><strong>Total:</strong> Rs. {$invoice['total_price']}</p>
            <p>Thank you for booking with Gaming Arena!</p>
        ";

        $mail->send();

        // Update email_notification status if you’re using this table
        $update = "UPDATE email_notification SET status='sent', sent_at=GETDATE() WHERE invoice_number = ?";
        sqlsrv_query($conn, $update, [$invoiceNumber]);

        return true;
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
}

// Helper to fetch user email by username
function getUserEmail($conn, $username)
{
    $sql = "SELECT user_email FROM users WHERE user_name = ?";
    $stmt = sqlsrv_query($conn, $sql, [$username]);
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row['user_email'];
    }
    return null;
}
