<?php
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $user_name = $_POST["user_name"];
    $password = $_POST["password"];
    $address_line = $_POST["address_line"];
    $city = $_POST["city"];

    // Connect to SQL Server
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

    // Insert data
    $sql = "INSERT INTO users (full_name, email, phone, user_name, password, address_line, city, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE())";
    $params = [$full_name, $email, $phone, $user_name, $password, $address_line, $city];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        $success = "Registration successful!";
    } else {
        $error = "Something went wrong: " . print_r(sqlsrv_errors(), true);
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - Gaming Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset and Font */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: radial-gradient(circle at top left, #1a1a1a, #0d0d0d);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        /* Register Box */
        .register-box {
            background: #1f1f2e;
            border: 2px solid #8A2BE2;
            border-radius: 16px;
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 0 30px rgba(138, 43, 226, 0.4);
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }

        /* Flicker + Neon Glow Animation for h2 */
        .register-box h2 {
            font-size: 30px;
            color: #8A2BE2;
            text-shadow: 0 0 5px #8A2BE2, 0 0 10px #8A2BE2, 0 0 20px #8A2BE2, 0 0 40px #8A2BE2;
            animation: flicker 2.5s infinite alternate ease-in-out;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        /* Input Fields */
        .register-box input[type="text"],
        .register-box input[type="email"],
        .register-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 16px;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #2a2a3d;
            color: #ffffff;
            transition: all 0.3s ease-in-out;
        }

        .register-box input:focus {
            border-color: #8A2BE2;
            outline: none;
            box-shadow: 0 0 10px #8A2BE2aa;
        }

        /* Button */
        .register-box .btn {
            background: #8A2BE2;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            width: 100%;
            margin-top: 10px;
        }

        .register-box .btn:hover {
            background: #7a1cd6;
            box-shadow: 0 0 12px #8A2BE2;
        }

        /* Feedback Messages */
        p {
            font-weight: bold;
            margin-bottom: 15px;
        }

        p[style*="color: green"] {
            color: #00ff99 !important;
        }

        p[style*="color: red"] {
            color: #ff6666 !important;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Neon flicker animation */
        @keyframes flicker {
            0% {
                opacity: 1;
                text-shadow: 0 0 5px #8A2BE2, 0 0 10px #8A2BE2, 0 0 20px #8A2BE2;
            }

            20% {
                opacity: 0.9;
                text-shadow: 0 0 2px #8A2BE2, 0 0 8px #8A2BE2;
            }

            40% {
                opacity: 1;
                text-shadow: 0 0 10px #8A2BE2, 0 0 15px #8A2BE2;
            }

            60% {
                opacity: 0.85;
                text-shadow: 0 0 3px #8A2BE2, 0 0 6px #8A2BE2;
            }

            80% {
                opacity: 1;
                text-shadow: 0 0 5px #8A2BE2, 0 0 12px #8A2BE2;
            }

            100% {
                opacity: 0.95;
                text-shadow: 0 0 8px #8A2BE2, 0 0 20px #8A2BE2;
            }
        }
    </style>

</head>

<body>

    <div class="register-box">
        <h2>Register</h2>

        <?php if ($success): ?>
            <p style="color: green"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p style="color: red"><?= $error ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="full_name" placeholder="Full Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="phone" placeholder="Phone" required><br>
            <input type="text" name="user_name" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="text" name="address_line" placeholder="Address" required><br>
            <input type="text" name="city" placeholder="City" required><br>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>

</body>

</html>