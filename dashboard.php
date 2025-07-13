<?php
session_start();
require 'db_connection.php';
require 'log_action.php';

// Role-based access
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

$adminId = $_SESSION["admin_id"];
$adminName = $_SESSION["admin_name"];
log_action($conn, "Viewed Dashboard", "Admin Panel", null, $adminId);

// Fetch Stats

// Fetch Stats and log each action
$sqlUsers = "SELECT COUNT(*) AS total_users FROM users";
$usersResult = sqlsrv_query($conn, $sqlUsers);
$users = sqlsrv_fetch_array($usersResult, SQLSRV_FETCH_ASSOC);
log_action($conn, "Fetched Total Users: " . $users["total_users"], "Dashboard", null, $adminId, date('Y-m-d H:i:s'));

$sqlBookings = "SELECT COUNT(*) AS total_bookings FROM bookings";
$bookingsResult = sqlsrv_query($conn, $sqlBookings);
$bookings = sqlsrv_fetch_array($bookingsResult, SQLSRV_FETCH_ASSOC);
log_action($conn, "Fetched Total Bookings: " . $bookings["total_bookings"], "Dashboard", null, $adminId, date('Y-m-d H:i:s'));

$sqlRevenue = "SELECT SUM(total_price) AS total_revenue FROM bookings WHERE status = 'Completed'";
$revenueResult = sqlsrv_query($conn, $sqlRevenue);
$revenue = sqlsrv_fetch_array($revenueResult, SQLSRV_FETCH_ASSOC);
log_action($conn, "Fetched Total Revenue: " . $revenue["total_revenue"], "Dashboard", null, $adminId, date('Y-m-d H:i:s'));

$sqlActive = "SELECT COUNT(*) AS active_stations FROM bookings WHERE status = 'Active'";
$activeResult = sqlsrv_query($conn, $sqlActive);
$active = sqlsrv_fetch_array($activeResult, SQLSRV_FETCH_ASSOC);
log_action($conn, "Fetched Active Stations: " . $active["active_stations"], "Dashboard", null, $adminId, date('Y-m-d H:i:s'));
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gaming Arena | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #111;
            color: #eee;
            margin: 0;
            min-height: 100vh;
            overflow-y: auto;
        }

        .dashboard {
            max-width: 1200px;
            margin: 30px auto;
            padding: 2rem;
        }

        .greeting {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .card {
            background: #1e1e2f;
            border-radius: 12px;
            flex: 1 1 220px;
            padding: 1.5rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
            transition: 0.3s;
        }

        .card:hover {
            transform: scale(1.03);
            background: #292942;
        }

        .card h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #aaa;
        }

        .card p {
            font-size: 2rem;
            margin-top: 0.3rem;
            color: #8A2BE2;
        }

        .side-panel {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 240px;
            background: #18182a;
            box-shadow: 2px 0 16px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem 1rem 1rem;
            z-index: 100;
            overflow-y: auto;
        }

        .side-panel h2 {
            color: #8A2BE2;
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .side-panel .nav-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .side-panel a {
            background: #8A2BE2;
            padding: 0.8rem 1.2rem;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.2s;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            border: 2px solid transparent;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(138, 43, 226, 0.08);
        }

        .side-panel a[href="stations.php"] {
            background: #fff;
            color: #8A2BE2;
            border: 2px solid #8A2BE2;
            font-weight: 700;
            box-shadow: 0 2px 12px rgba(138, 43, 226, 0.12);
        }

        .side-panel a[href="stations.php"]:hover {
            background: #8A2BE2;
            color: #fff;
        }

        .side-panel a:hover {
            background: #6b21c4;
        }

        .side-panel .section-title {
            color: #aaa;
            font-size: 0.95rem;
            margin: 1.5rem 0 0.5rem 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dashboard {
            margin-left: 260px;
        }
    </style>
</head>

<body>
    <div class="side-panel">
        <div style="display:flex; flex-direction:column; align-items:center; gap:0.7rem; margin-bottom:1.2rem;">
            <img src="logo/logo.png" alt="Gaming Arena Logo" style="height:90px; width:auto; display:block; margin:0 auto 0.2rem auto;">
            <span id="arenaName" style="font-size:1.5rem; font-weight:700; color:#8A2BE2; letter-spacing:1px; transition:color 0.2s;">Gaming Arena</span>
            <span style="font-size:1.05rem; color:#aaa; font-weight:400;">Admin Panel</span>
        </div>
        <!-- User info removed from side panel -->
        <div class="nav-links">
            <span class="section-title">Management</span>
            <a href="view_users.php" class="nav-btn"><span class="icon">👤</span> <span class="label">Users</span></a>
            <a href="reports.php" class="nav-btn"><span class="icon">🕹️</span> <span class="label">Reports</span></a>
            <a href="stations.php" class="nav-btn"><span class="icon">💻</span> <span class="label">Stations</span></a>
            <?php if ($_SESSION["admin_name"] === 'Main Admin'): ?>
                <a href="view_logs.php" class="nav-btn"><span class="icon">🛡️</span> <span class="label">Audit Logs</span></a>
                <a href="add_admin.php" class="nav-btn"><span class="icon">➕</span> <span class="label">Add Admin/Staff</span></a>
            <?php endif; ?>
            <span class="section-title">Quick Actions</span>
            <a href="#" class="nav-btn" onclick="refreshStats(event)"><span class="icon">🔄</span> <span class="label">Refresh Stats</span></a>
            <a href="#" class="nav-btn disabled"><span class="icon">📊</span> <span class="label">Export Data</span></a>
            <a href="#" class="nav-btn disabled"><span class="icon">📅</span> <span class="label">Calendar</span></a>
            <a href="#" class="nav-btn" onclick="openSettings()"><span class="icon">⚙️</span> <span class="label">Settings</span></a>
            <a href="logout.php" class="nav-btn logout"><span class="icon">🚪</span> <span class="label">Logout</span></a>
        </div>
        <script>
            function refreshStats(e) {
                e.preventDefault();
                // Show loading indicator
                const btn = event.target.closest('a');
                const original = btn.innerHTML;
                btn.innerHTML = '<span class="icon">⏳</span> <span class="label">Refreshing...</span>';
                btn.classList.add('disabled');
                btn.style.pointerEvents = 'none';
                fetch(window.location.href, {
                        method: 'GET',
                        cache: 'reload'
                    })
                    .then(() => window.location.reload())
                    .catch(() => {
                        btn.innerHTML = original;
                        btn.classList.remove('disabled');
                        btn.style.pointerEvents = '';
                        alert('Failed to refresh stats.');
                    });
            }
        </script>
    </div>

    <div class="dashboard">
        <div class="greeting" style="display:flex; align-items:center; gap:1.5rem;">
            <span>👋 Welcome, <?= $adminName ?>!</span>
            <span style="font-size:1rem; color:#8A2BE2; background:#23234a; border-radius:6px; padding:0.3rem 0.8rem;">
                ID: <?= htmlspecialchars($adminId) ?>
            </span>
            <?php if (isset($_SESSION['admin_email'])): ?>
                <span style="font-size:1rem; color:#8A2BE2; background:#23234a; border-radius:6px; padding:0.3rem 0.8rem;">
                    Email: <?= htmlspecialchars($_SESSION['admin_email']) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="cards">
            <div class="card card-users" onclick="toggleChart('users')" style="cursor:pointer;">
                <div class="card-icon"><span>👤</span></div>
                <div class="card-content">
                    <h3>Total Users</h3>
                    <p><?= $users["total_users"] ?></p>
                </div>
                <div id="chart-users" class="chart-container" style="display:none;"></div>
            </div>
            <div class="card card-bookings" onclick="window.location.href='select_report.php?highlight=bookings'" style="cursor:pointer;">
                <div class="card-icon"><span>🕹️</span></div>
                <div class="card-content">
                    <h3>Total Bookings</h3>
                    <p><?= $bookings["total_bookings"] ?></p>
                </div>
                <div id="chart-bookings" class="chart-container" style="display:none;"></div>
            </div>
            <div class="card card-revenue" onclick="toggleChart('revenue')" style="cursor:pointer;">
                <div class="card-icon"><span>💰</span></div>
                <div class="card-content">
                    <h3>Total Revenue</h3>
                    <p>Rs. <?= number_format($revenue["total_revenue"], 2) ?></p>
                </div>
                <div id="chart-revenue" class="chart-container" style="display:none;"></div>
            </div>
            <div class="card card-active" onclick="toggleChart('active')" style="cursor:pointer;">
                <div class="card-icon"><span>💻</span></div>
                <div class="card-content">
                    <h3>Active Stations</h3>
                    <p><?= $active["active_stations"] ?></p>
                </div>
                <div id="chart-active" class="chart-container" style="display:none;"></div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            .nav-links .nav-btn {
                display: flex;
                align-items: center;
                gap: 0.7rem;
                background: #8A2BE2;
                color: #fff;
                border-radius: 8px;
                padding: 0.8rem 1.2rem;
                font-size: 1rem;
                font-weight: 600;
                border: 2px solid transparent;
                box-shadow: 0 2px 8px rgba(138, 43, 226, 0.08);
                margin-bottom: 0.2rem;
                transition: 0.2s;
                text-decoration: none;
            }

            .nav-links .nav-btn.logout {
                background: #e84118;
                border-color: #e84118;
            }

            .nav-links .nav-btn.logout:hover {
                background: #c23616;
            }

            .nav-links .nav-btn.disabled {
                opacity: 0.5;
                pointer-events: none;
            }

            .nav-links .nav-btn:hover {
                background: #6b21c4;
            }

            .nav-links .icon {
                font-size: 1.3rem;
                width: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .nav-links .label {
                flex: 1;
                text-align: left;
            }

            .cards {
                display: flex;
                flex-wrap: wrap;
                gap: 1.5rem;
            }

            .card {
                background: #1e1e2f;
                border-radius: 16px;
                flex: 1 1 220px;
                padding: 1.5rem 1.2rem 1.2rem 1.2rem;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
                transition: 0.3s;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                min-width: 220px;
                position: relative;
                overflow: hidden;
            }

            .card:hover {
                transform: scale(1.04);
                background: #292942;
                box-shadow: 0 12px 24px rgba(138, 43, 226, 0.10);
            }

            .card-icon {
                font-size: 2.2rem;
                margin-bottom: 0.7rem;
                color: #8A2BE2;
                background: #23234a;
                border-radius: 50%;
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(138, 43, 226, 0.10);
            }

            .card-content h3 {
                margin: 0;
                font-size: 1.1rem;
                color: #aaa;
                font-weight: 600;
            }

            .card-content p {
                font-size: 2.1rem;
                margin-top: 0.2rem;
                color: #8A2BE2;
                font-weight: 700;
            }

            .card-bookings {
                border: 2px solid #8A2BE2;
                background: #18182a;
            }

            .card-bookings:hover {
                background: #2d2d4d;
                border-color: #fff;
            }

            .card-revenue .card-icon {
                color: #28a745;
                background: #23234a;
            }

            .card-active .card-icon {
                color: #007bff;
                background: #23234a;
            }

            .chart-container {
                width: 100% !important;
                max-width: 500px;
                margin: 1.2rem auto 0 auto;
                background: #23234a;
                border-radius: 10px;
                padding: 1.2rem 1rem 1.5rem 1rem;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            }
        </style>
        <script>
            // Chart data (demo, replace with real data as needed)
            const chartData = {
                users: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    data: [2, 3, 4, 5, 5, 5, <?= $users["total_users"] ?>]
                },
                bookings: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    data: [30, 60, 120, 180, 210, 250, <?= $bookings["total_bookings"] ?>]
                },
                revenue: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    data: [0, 0, 0, 0, 0, 0, <?= $revenue["total_revenue"] ?: 0 ?>]
                },
                active: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    data: [1, 2, 0, 1, 0, 0, <?= $active["active_stations"] ?>]
                }
            };

            let chartInstances = {};

            function toggleChart(type) {
                // Hide all
                document.querySelectorAll('.chart-container').forEach(el => el.style.display = 'none');
                // Remove all chart canvases
                Object.values(chartInstances).forEach(inst => inst && inst.destroy());
                chartInstances = {};
                // Show selected
                const container = document.getElementById('chart-' + type);
                if (!container) return;
                if (container.style.display === 'block') {
                    container.style.display = 'none';
                    return;
                }
                container.style.display = 'block';
                // Create canvas
                container.innerHTML = '<canvas id="canvas-' + type + '" height="180"></canvas>';
                const ctx = document.getElementById('canvas-' + type).getContext('2d');
                let label = '';
                let bg = '#8A2BE2';
                switch (type) {
                    case 'users':
                        label = 'Users Growth';
                        break;
                    case 'bookings':
                        label = 'Bookings Over Time';
                        break;
                    case 'revenue':
                        label = 'Revenue Over Time';
                        bg = '#28a745';
                        break;
                    case 'active':
                        label = 'Active Stations';
                        bg = '#007bff';
                        break;
                }
                chartInstances[type] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData[type].labels,
                        datasets: [{
                            label: label,
                            data: chartData[type].data,
                            backgroundColor: bg + '33',
                            borderColor: bg,
                            borderWidth: 2,
                            pointBackgroundColor: bg,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#fff'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#fff'
                                },
                                grid: {
                                    color: '#444'
                                }
                            },
                            y: {
                                ticks: {
                                    color: '#fff'
                                },
                                grid: {
                                    color: '#444'
                                }
                            }
                        }
                    }
                });
            }
        </script>
    </div>
    <!-- Settings Modal -->
    <div id="settingsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:200; align-items:center; justify-content:center;">
        <div style="background:#222; color:#fff; padding:2rem 2.5rem; border-radius:12px; min-width:320px; max-width:90vw; box-shadow:0 8px 32px rgba(0,0,0,0.4); position:relative;">
            <h2 style="margin-top:0; color:#8A2BE2;">Settings</h2>
            <label for="colorTheme">Choose Theme Color:</label>
            <select id="colorTheme" style="margin-left:1rem; padding:0.3rem 0.7rem; border-radius:5px;">
                <option value="#8A2BE2">Purple (Default)</option>
                <option value="#007bff">Blue</option>
                <option value="#28a745">Green</option>
                <option value="#e84118">Red</option>
                <option value="#f39c12">Orange</option>
                <option value="#111">Dark</option>
            </select>
            <button onclick="closeSettings()" style="position:absolute; top:1rem; right:1.5rem; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
    </div>

    <script>
        function openSettings() {
            document.getElementById('settingsModal').style.display = 'flex';
        }

        function closeSettings() {
            document.getElementById('settingsModal').style.display = 'none';
        }
        // Color theme change
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('colorTheme');
            var root = document.documentElement;
            // Load saved color
            var saved = localStorage.getItem('adminThemeColor');
            if (saved) setThemeColor(saved);
            select.value = saved || '#8A2BE2';
            select.addEventListener('change', function() {
                setThemeColor(this.value);
                localStorage.setItem('adminThemeColor', this.value);
            });
        });

        function setThemeColor(color) {
            var style = document.createElement('style');
            style.id = 'themeColorStyle';
            style.innerHTML = `
                .card p {
                    background: none !important;
                    color: ${color} !important;
                }
                #arenaName {
                    color: ${color} !important;
                }
                .side-panel a {
                    background: ${color} !important;
                    border-color: ${color} !important;
                    color: #fff !important;
                }
                .side-panel a:hover {
                    background: ${shadeColor(color, -20)} !important;
                }
            `;
            // Remove old
            var old = document.getElementById('themeColorStyle');
            if (old) old.remove();
            document.head.appendChild(style);
        }
        // Helper to darken color
        function shadeColor(color, percent) {
            let R = parseInt(color.substring(1, 3), 16);
            let G = parseInt(color.substring(3, 5), 16);
            let B = parseInt(color.substring(5, 7), 16);
            R = parseInt(R * (100 + percent) / 100);
            G = parseInt(G * (100 + percent) / 100);
            B = parseInt(B * (100 + percent) / 100);
            R = (R < 255) ? R : 255;
            G = (G < 255) ? G : 255;
            B = (B < 255) ? B : 255;
            let RR = ((R.toString(16).length == 1) ? "0" : "") + R.toString(16);
            let GG = ((G.toString(16).length == 1) ? "0" : "") + G.toString(16);
            let BB = ((B.toString(16).length == 1) ? "0" : "") + B.toString(16);
            return "#" + RR + GG + BB;
        }
    </script>
</body>

</html>