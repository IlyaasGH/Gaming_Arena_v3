<?php
session_start();
require 'db_connection.php';

// AJAX endpoint for report content (must be before any HTML output)
if (isset($_GET['ajax']) && $_GET['ajax'] && isset($_GET['type'])) {
    $type = $_GET['type'];
    switch ($type) {
        case 'bookings':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Bookings Report</h2><p>Show bookings table, filters, export, etc. here.</p>';
            break;
        case 'users':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Users Report</h2><p>Show users table, filters, export, etc. here.</p>';
            break;
        case 'stations':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Stations Report</h2><p>Show stations table, filters, export, etc. here.</p>';
            break;
        case 'profit':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Profit Report</h2><p>Show profit analytics, export, etc. here.</p>';
            break;
        case 'hours_played':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Hours Played Report</h2><p>Show hours played stats, export, etc. here.</p>';
            break;
        case 'food_orders':
            echo '<h2 style="color:var(--ga-theme);margin-top:0;">Food Orders Report</h2><p>Show food orders, export, etc. here.</p>';
            break;
        default:
            echo '<div style="color:#aaa;">Unknown report type.</div>';
    }
    exit;
}

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

$reports = [
    ["label" => "Booking Report", "type" => "bookings"],
    ["label" => "User Report", "type" => "users"],
    ["label" => "Station Report", "type" => "stations"],
    ["label" => "Profit Report", "type" => "profit"],
    ["label" => "Hours Played Report", "type" => "hours_played"],
    ["label" => "Food Orders Report", "type" => "food_orders"],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Select Report</title>
    <style>
        :root {
            --ga-theme: #8A2BE2;
            --ga-theme-dark: #6b21c4;
        }

        body {
            background: #18182a;
            color: #eee;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        .side-panel {
            width: 260px;
            background: #23234a;
            padding: 2.5rem 1.2rem 1.2rem 1.2rem;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            box-shadow: 2px 0 16px rgba(0, 0, 0, 0.10);
        }

        .side-panel h2 {
            color: var(--ga-theme);
            font-size: 1.3rem;
            margin-bottom: 2.2rem;
            text-align: center;
        }

        .report-list {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .report-btn {
            background: var(--ga-theme);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 1.1rem 1.2rem;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            text-align: left;
            box-shadow: 0 2px 8px rgba(138, 43, 226, 0.10);
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
            min-height: 56px;
            width: 100%;
        }

        .report-btn.active,
        .report-btn:focus,
        .report-btn:hover {
            background: var(--ga-theme-dark);
            color: #fff;
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.18);
            outline: none;
        }

        .report-btn .icon {
            font-size: 1.5rem;
            margin-right: 0.7rem;
        }

        .report-btn .label {
            flex: 1;
        }

        .content-panel {
            flex: 1;
            background: #18182a;
            padding: 2.5rem 2.5rem 2.5rem 2.5rem;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .report-content {
            background: #23234a;
            border-radius: 14px;
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.12);
            min-height: 300px;
            color: #eee;
        }

        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }

            .side-panel {
                width: 100%;
                flex-direction: row;
                gap: 0.5rem;
                padding: 1rem;
                overflow-x: auto;
            }

            .report-list {
                flex-direction: row;
                gap: 0.5rem;
                width: 100%;
            }

            .report-btn {
                min-width: 120px;
                min-height: 48px;
                font-size: 0.98rem;
            }

            .content-panel {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-layout">
        <div class="side-panel">
            <h2>Reports</h2>
            <div class="report-list" id="reportList">
                <?php foreach ($reports as $report): ?>
                    <button class="report-btn" data-type="<?= htmlspecialchars($report['type']) ?>">
                        <span class="label"><?= htmlspecialchars($report['label']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="content-panel">
            <div class="report-content" id="reportContent">
                <div style="color:#aaa;">Select a report to view.</div>
            </div>
        </div>
    </div>
    <script>
        // SPA logic for reports
        const reportList = document.getElementById('reportList');
        const reportContent = document.getElementById('reportContent');
        const reportBtns = Array.from(reportList.querySelectorAll('.report-btn'));

        function setActive(btn) {
            reportBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function loadReport(type) {
            setActive(reportBtns.find(b => b.dataset.type === type));
            reportContent.innerHTML = '<div style="text-align:center; color:#aaa; font-size:1.1rem;">Loading...</div>';
            fetch('select_report.php?ajax=1&type=' + encodeURIComponent(type))
                .then(r => r.text())
                .then(html => {
                    reportContent.innerHTML = html;
                });
        }
        reportBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                loadReport(this.dataset.type);
            });
        });
        // Optionally auto-load first report
        // loadReport('bookings');
    </script>
</body>

</html>