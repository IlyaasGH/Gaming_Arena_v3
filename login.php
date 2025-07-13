<?php
session_start();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // SQL Server connection
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

    // Query to check credentials (user_name is the correct column)
    $sql = "SELECT * FROM users WHERE user_name = ? AND password = ?";
    $params = [$username, $password];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($user) {
        // Login success
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_id'] = $user['user_id'];

        // Insert login record
        $loginInsertSQL = "INSERT INTO user_logins (user_id, login_time) VALUES (?, GETDATE())";
        $loginParams = [$user['user_id']];
        sqlsrv_query($conn, $loginInsertSQL, $loginParams);

        header("Location: index.php");
        exit();
    } else {
        $errors[] = "Invalid username or password.";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Gaming Arena</title>
    <style>
        body {
            background-color: #1c1c1c;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #f1f1f1;
        }

        .login-box {
            width: 360px;
            margin: 100px auto;
            padding: 40px;
            background-color: #2e2e2e;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #ffffff;
            font-size: 24px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #1a1a1a;
            color: #fff;
        }

        .login-box input:focus {
            border-color: #888;
            outline: none;
        }

        .login-box .btn {
            width: 90%;
            padding: 12px;
            background-color: #5a5a5a;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-box .btn:hover {
            background-color: #3a3a3a;
        }

        .login-box p {
            color: #ff4d4d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
            color: #ccc;
            text-align: left;
            padding-left: 5%;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>🔐 Login</h2>

        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>

</html>