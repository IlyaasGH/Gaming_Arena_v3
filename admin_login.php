<?php
session_start();
require 'db_connection.php';
require 'log_action.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // BYPASS: Allow ADMIN/ADMIN only, comment DB logic
    // $sql = "SELECT username, password FROM admins WHERE username = 'ADMIN'";
    // $params = [$username];
    // $stmt = sqlsrv_query($conn, $sql, $params);
    // if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    //     $db_pass = trim($row['password']);
    //     if (trim($password) === $db_pass) {
    //         ...
    //     }
    // }
    if (strtoupper(trim($username)) === 'ADMIN' && trim($password) === 'ADMIN') {
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_id"] = 1;
        $_SESSION["admin_name"] = "ADMIN";

        // Log action to audit_log
        $action = "Admin Login";
        $source = "Admin Panel";
        $user_id = null;
        $admin_id = 1;
        $created_at = date('Y-m-d H:i:s');
        log_action($conn, $action, $source, $user_id, $admin_id, $created_at);

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>


<head>
    <title>ADMIN LOGIN</title>
    <style>
        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            color: #273c75;
            margin-bottom: 24px;
        }

        form {
            background: #fff;
            padding: 32px 28px 24px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
            display: flex;
            flex-direction: column;
            min-width: 320px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #dcdde1;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.2s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border: 1.5px solid #4078c0;
            outline: none;
        }

        button[type="submit"] {
            background: #4078c0;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 0;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        button[type="submit"]:hover {
            background: #273c75;
        }

        p[style*="color:red"] {
            margin-bottom: 18px;
            color: #e84118 !important;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <h2>ADMIN LOGIN</h2>
    <?php if ($error): ?>
        <p style="color:red"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="ADMIN" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>

</html>