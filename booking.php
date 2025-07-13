<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    <title>Station Booking by Floor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #18122B 0%, #393053 100%);
            color: #fff;
            text-align: center;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        h1 {
            margin-top: 32px;
            font-size: 3em;
            color: #fff;
            letter-spacing: 2px;
            text-shadow: 0 0 16px #8A2BE2, 0 0 32px #8A2BE2;
            animation: flicker 2.5s infinite alternate;
        }

        @keyframes flicker {

            0%,
            100% {
                opacity: 1;
                text-shadow:
                    0 0 8px #8A2BE2,
                    0 0 16px #8A2BE2,
                    0 0 32px #8A2BE2,
                    0 0 64px #8A2BE2;
            }

            50% {
                opacity: 0.7;
                text-shadow:
                    0 0 16px #8A2BE2,
                    0 0 32px #8A2BE2,
                    0 0 48px #8A2BE2,
                    0 0 80px #8A2BE2;
            }
        }

        .floor {
            margin: 40px auto 32px auto;
            max-width: 850px;
            background: rgba(34, 34, 51, 0.7);
            box-shadow: 0 8px 32px 0 rgba(138, 43, 226, 0.15), 0 1.5px 8px 0 rgba(0, 0, 0, 0.12);
            border: 1.5px solid rgba(138, 43, 226, 0.25);
            padding: 28px 20px 20px 20px;
            border-radius: 18px;
            backdrop-filter: blur(4px);
            transition: box-shadow 0.3s;
        }

        .floor:hover {
            box-shadow: 0 12px 40px 0 rgba(138, 43, 226, 0.25), 0 2px 12px 0 rgba(0, 0, 0, 0.18);
        }

        .legend {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 28px;
            align-items: center;
            margin-bottom: 24px;
            font-size: 17px;
            background: rgba(34, 34, 51, 0.5);
            border-radius: 8px;
            padding: 8px 18px;
            box-shadow: 0 2px 8px 0 rgba(138, 43, 226, 0.08);
        }

        .legend-item {
            width: 22px;
            height: 22px;
            border-radius: 5px;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.10);
        }

        .legend-item.booked {
            background: linear-gradient(135deg, #444 60%, #8A2BE2 100%);
            border: 2px solid #ff3c3c;
        }

        .legend-item.selected {
            background: linear-gradient(135deg, #8A2BE2 60%, #fff 100%);
            border: 2px solid #fff;
        }

        .legend-item.available {
            background: linear-gradient(135deg, #bbb 60%, #fff 100%);
            border: 2px solid #bbb;
        }

        .floor h2 {
            color: #fff;
            font-size: 2em;
            margin-bottom: 18px;
            letter-spacing: 1px;
            text-shadow: 0 0 8px #8A2BE2, 0 0 16px #8A2BE2;
        }

        .station-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            justify-content: center;
            margin-top: 18px;
        }

        .station {
            padding: 18px 0 10px 0;
            border-radius: 10px;
            background: linear-gradient(135deg, #bbb 60%, #fff 100%);
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            color: #222;
            box-shadow: 0 2px 8px 0 rgba(138, 43, 226, 0.08);
            border: 2px solid transparent;
            transform-origin: center center;
            transform: scale(1);
            transition: background 0.3s, color 0.3s, box-shadow 0.3s, border 0.3s;
        }

        .station:hover:not(.booked):not(.selected) {
            background: linear-gradient(135deg, #d1b3ff 60%, #fff 100%);
            color: #8A2BE2;
            box-shadow: 0 4px 16px 0 rgba(138, 43, 226, 0.18);
            border: 2px solid #8A2BE2;
        }

        .station.booked {
            background: linear-gradient(135deg, #444 60%, #8A2BE2 100%);
            color: #bbb;
            border: 2px solid #ff3c3c;
            pointer-events: none;
            opacity: 0.7;
            cursor: not-allowed;
            box-shadow: 0 2px 8px 0 rgba(255, 60, 60, 0.10);
        }

        .station.selected {
            background: linear-gradient(135deg, #8A2BE2 60%, #fff 100%);
            color: #fff;
            border: 2px solid #fff;
            box-shadow: 0 4px 16px 0 rgba(138, 43, 226, 0.18);
        }

        .calendar {
            margin: 24px auto 0 auto;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: rgba(34, 34, 51, 0.5);
            border-radius: 10px;
            padding: 18px 24px 12px 24px;
            box-shadow: 0 2px 8px 0 rgba(138, 43, 226, 0.08);
            position: relative;
            z-index: 10;
        }

        .calendar label {
            margin-bottom: 2px;
            font-size: 1.08em;
            color: #d1b3ff;
            text-align: left;
        }

        select,
        input[type="date"] {
            padding: 10px 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1.5px solid #8A2BE2;
            outline: none;
            background: #22223b;
            color: #fff;
            margin-bottom: 6px;
            transition: border 0.2s, box-shadow 0.2s;
            position: relative;
            z-index: 20;
        }

        select:focus,
        input[type="date"]:focus {
            border: 2px solid #d1b3ff;
            box-shadow: 0 0 8px #8A2BE2;
        }

        .btn {
            margin-top: 24px;
            padding: 14px 32px;
            background: linear-gradient(90deg, #8A2BE2 60%, #a14ef0 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1.15em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px 0 rgba(138, 43, 226, 0.10);
            letter-spacing: 1px;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            background: linear-gradient(90deg, #a14ef0 60%, #8A2BE2 100%);
            box-shadow: 0 4px 16px 0 rgba(138, 43, 226, 0.18);
        }

        @media (max-width: 900px) {
            .floor {
                max-width: 98vw;
                padding: 16px 4vw 12px 4vw;
            }

            .station-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 2em;
            }

            .floor h2 {
                font-size: 1.2em;
            }

            .calendar {
                max-width: 98vw;
                padding: 10px 2vw 8px 2vw;
            }

            .station-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .btn {
                padding: 10px 10vw;
                font-size: 1em;
            }
        }
    </style>
</head>

<body>



    <h1>Select Station</h1>

    <div class="legend">
        <span class="legend-item booked"></span> Booked
        <span class="legend-item selected"></span> Selected
        <span class="legend-item available"></span> Available
    </div>

    <div style="display:flex;justify-content:center;align-items:center;gap:18px;margin-bottom:10px;">
        <label for="stationTypeFilter" style="color:#d1b3ff;font-size:1em;">Filter by Type:</label>
        <select id="stationTypeFilter" style="padding:7px 14px;border-radius:6px;font-size:1em;">
            <option value="all">All</option>
            <option value="PC">PC</option>
            <option value="Premium PC">Premium PC</option>
            <option value="PS5">PS5</option>
            <option value="PS4">PS4</option>
            <option value="Xbox">Xbox</option>
            <option value="Pool">Pool</option>
            <option value="Snooker">Snooker</option>
        </select>
        <button id="clearSelectionBtn" class="btn" type="button" style="background:#393053;box-shadow:none;">Clear Selection</button>
    </div>

    <div class="calendar">
        <label>Select Booking Date</label>
        <input type="date" id="booking_date" name="booking_date" required>

        <label>Select Booking Start Time</label>
        <select id="start_time" name="start_time" required></select>

        <label>Select Booking End Time</label>
        <select id="end_time" name="end_time" required></select>
    </div>

    <div id="bookingSummary" style="margin:18px auto 0 auto;max-width:420px;background:rgba(34,34,51,0.7);border-radius:10px;padding:14px 20px;color:#fff;box-shadow:0 2px 8px 0 rgba(138,43,226,0.08);font-size:1.1em;display:none;"></div>

    <div id="countdownTimer" style="margin:10px auto 0 auto;max-width:420px;background:rgba(255,0,0,0.08);border-radius:10px;padding:8px 0;color:#ffb3b3;font-size:1.1em;display:none;"></div>

    <div id="toast" style="position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#22223b;color:#fff;padding:16px 32px;border-radius:8px;box-shadow:0 2px 8px 0 rgba(138,43,226,0.18);font-size:1.1em;z-index:999;display:none;">Booking Successful!</div>

    <!-- Floor 1 -->
    <div class="floor">
        <h2>🎮 Floor 1</h2>
        <div class="station-grid" id="floor1"></div>
    </div>

    <!-- Floor 2 -->
    <div class="floor">
        <h2>🔥 Floor 2</h2>
        <div class="station-grid" id="floor2"></div>
    </div>

    <!-- Floor 3: PS5 + PS4 -->
    <div class="floor">
        <h2>🕹️ Floor 3</h2>
        <div class="station-grid" id="floor3"></div>
    </div>

    <!-- Floor 4: Xbox + Pool + Snooker -->
    <div class="floor">
        <h2>🎱 Floor 4</h2>
        <div class="station-grid" id="floor4"></div>
    </div>

    <form id="bookingForm" action="process_booking.php" method="POST">
        <input type="hidden" name="station_id" id="stationInput">
        <input type="hidden" name="start_time_hidden" id="startTimeInput">
        <input type="hidden" name="end_time_hidden" id="endTimeInput">
        <button type="submit" class="btn">Confirm Booking</button>
    </form>

    <script>
        // --- Animation for h1 ---
        anime({
            targets: 'h1',
            opacity: [{
                    value: 0.5,
                    duration: 100
                },
                {
                    value: 1,
                    duration: 100
                },
                {
                    value: 0.7,
                    duration: 80
                },
                {
                    value: 1,
                    duration: 100
                }
            ],
            easing: 'linear',
            loop: true
        });

        function animateStationsFadeIn(containerId) {
            const stations = document.querySelectorAll(`#${containerId} .station`);
            const floor = document.getElementById(containerId).closest('.floor');
            anime({
                targets: stations,
                opacity: [0, 1],
                translateY: [20, 0],
                delay: anime.stagger(100),
                easing: 'easeOutQuad',
                duration: 800
            });
            anime({
                targets: floor,
                scale: [0.9, 1],
                opacity: [0, 1],
                easing: 'easeOutQuad',
                duration: 600
            });
        }

        function animateFloorWithStations(containerId) {
            const container = document.getElementById(containerId);
            const stations = container.querySelectorAll('.station');
            anime({
                targets: container,
                scale: [0.9, 1],
                opacity: [0, 1],
                easing: 'easeOutQuad',
                duration: 600
            });
            anime({
                targets: stations,
                opacity: [0, 1],
                translateY: [20, 0],
                delay: anime.stagger(100),
                easing: 'easeOutQuad',
                duration: 800
            });
        }

        function pad(n) {
            return n < 10 ? '0' + n : n;
        }

        function generateTimeOptions(selectId, selectedValue) {
            const select = document.getElementById(selectId);
            select.innerHTML = '';
            const startHour = 8;
            const endHour = 23;
            for (let hour = startHour; hour <= endHour; hour++) {
                for (let min = 0; min < 60; min += 15) {
                    const label = `${pad(hour)}:${pad(min)}`;
                    const option = document.createElement('option');
                    option.value = label;
                    option.textContent = label;
                    if (selectedValue && selectedValue === label) option.selected = true;
                    select.appendChild(option);
                }
            }
        }
        async function fetchBookedStations(date, startTime, endTime) {
            try {
                const res = await fetch(`get_booked_stations.php?date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`);
                const data = await res.json();
                return data.booked || [];
            } catch (e) {
                console.error('Error fetching booked stations:', e);
                return [];
            }
        }
        // Station type mapping for filtering and pricing
        const stationTypeMap = {};
        for (let i = 1; i <= 10; i++) stationTypeMap[i] = 'PC';
        for (let i = 11; i <= 20; i++) stationTypeMap[i] = 'Premium PC';
        for (let i = 21; i <= 24; i++) stationTypeMap[i] = 'PS5';
        for (let i = 25; i <= 26; i++) stationTypeMap[i] = 'PS4';
        for (let i = 27; i <= 28; i++) stationTypeMap[i] = 'Xbox';
        for (let i = 29; i <= 32; i++) stationTypeMap[i] = 'Pool';
        for (let i = 33; i <= 34; i++) stationTypeMap[i] = 'Snooker';
        const stationPrices = {
            'PC': 50,
            'Premium PC': 80,
            'PS5': 120,
            'PS4': 100,
            'Xbox': 100,
            'Pool': 60,
            'Snooker': 70
        };

        function renderStations(start, end, containerId, bookedStations) {
            const grid = document.getElementById(containerId);
            grid.innerHTML = '';
            const filterType = document.getElementById('stationTypeFilter')?.value || 'all';
            const bookedSet = new Set((bookedStations || []).map(String));
            for (let i = start; i <= end; i++) {
                const type = stationTypeMap[i] || '';
                if (filterType !== 'all' && type !== filterType) continue;
                const div = document.createElement('div');
                div.className = 'station';
                div.dataset.id = i;
                div.dataset.type = type;
                div.innerHTML = `<div>Station ${i}</div><small style="font-size: 12px; opacity: 0.7;">${type}</small>`;
                if (bookedSet.has(String(i))) {
                    div.classList.add('booked');
                    div.style.pointerEvents = 'none';
                } else {
                    div.addEventListener('click', () => {
                        if (div.classList.contains('selected')) {
                            anime({
                                targets: div,
                                scale: 1,
                                duration: 300,
                                easing: 'easeOutQuad',
                                complete: () => div.classList.remove('selected')
                            });
                        } else {
                            anime({
                                targets: div,
                                scale: 1.2,
                                duration: 300,
                                easing: 'easeOutQuad',
                                begin: () => div.classList.add('selected')
                            });
                        }
                        updateBookingSummary();
                        const selectedIds = Array.from(document.querySelectorAll('.station.selected')).map(el => el.dataset.id);
                        document.getElementById('stationInput').value = selectedIds.join(',');
                    });
                }
                grid.appendChild(div);
            }
            updateBookingSummary();
        }
        let autoRefreshInterval = null;
        let countdownInterval = null;
        let countdownSeconds = 300; // 5 minutes
        function startCountdown() {
            clearInterval(countdownInterval);
            countdownSeconds = 300;
            document.getElementById('countdownTimer').style.display = 'block';
            updateCountdownDisplay();
            countdownInterval = setInterval(() => {
                countdownSeconds--;
                updateCountdownDisplay();
                if (countdownSeconds <= 0) {
                    clearInterval(countdownInterval);
                    clearSelection();
                    document.getElementById('countdownTimer').textContent = 'Session expired. Please start again.';
                }
            }, 1000);
        }

        function updateCountdownDisplay() {
            const min = Math.floor(countdownSeconds / 60);
            const sec = countdownSeconds % 60;
            document.getElementById('countdownTimer').textContent = `Session expires in ${min}:${sec.toString().padStart(2, '0')}`;
        }

        function clearSelection() {
            document.querySelectorAll('.station.selected').forEach(s => {
                s.classList.remove('selected');
                s.style.transform = 'scale(1)';
            });
            document.getElementById('stationInput').value = '';
            updateBookingSummary();
        }
        async function updateLayout() {
            const dateInput = document.getElementById('booking_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            if (dateInput && startTime && endTime) {
                const bookedStations = await fetchBookedStations(dateInput, startTime, endTime);
                renderStations(1, 10, 'floor1', bookedStations);
                animateStationsFadeIn('floor1');
                setTimeout(() => {
                    renderStations(11, 20, 'floor2', bookedStations);
                    animateStationsFadeIn('floor2');
                }, 400);
                setTimeout(() => {
                    renderStations(21, 26, 'floor3', bookedStations);
                    animateFloorWithStations('floor3');
                }, 800);
                setTimeout(() => {
                    renderStations(27, 34, 'floor4', bookedStations);
                    animateFloorWithStations('floor4');
                }, 1200);
            }
        }

        function updateBookingSummary() {
            const selected = Array.from(document.querySelectorAll('.station.selected'));
            if (selected.length === 0) {
                document.getElementById('bookingSummary').style.display = 'none';
                return;
            }
            const start = document.getElementById('start_time').value;
            const end = document.getElementById('end_time').value;
            let duration = 0;
            if (start && end) {
                const [sh, sm] = start.split(':').map(Number);
                const [eh, em] = end.split(':').map(Number);
                duration = (eh * 60 + em - sh * 60 - sm) / 60;
                if (duration < 0) duration += 24;
            }
            let total = 0;
            let details = '';
            selected.forEach(s => {
                const type = s.dataset.type;
                const price = stationPrices[type] || 0;
                total += price * (duration || 1);
                details += `<li>Station ${s.dataset.id} (${type}) - ₹${price}/hr</li>`;
            });
            document.getElementById('bookingSummary').innerHTML =
                `<b>Selected Stations:</b><ul style='margin:6px 0 0 18px;text-align:left;'>${details}</ul>` +
                (duration ? `<div style='margin-top:8px;'>Duration: <b>${duration}</b> hour(s)</div>` : '') +
                `<div style='margin-top:8px;'>Estimated Total: <b>Rs.${total}</b></div>`;
            document.getElementById('bookingSummary').style.display = 'block';
        }
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const date = document.getElementById('booking_date').value;
            const start = document.getElementById('start_time').value;
            const end = document.getElementById('end_time').value;
            const station = document.getElementById('stationInput').value;
            if (!date || !start || !end || !station) {
                alert('Please select a date, start time, end time, and station before booking.');
                e.preventDefault();
                return;
            }
            document.getElementById('startTimeInput').value = `${date}T${start}`;
            document.getElementById('endTimeInput').value = `${date}T${end}`;
            showToast('Booking Successful!');
            clearInterval(countdownInterval);
        });
        window.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('booking_date').value = today;
            generateTimeOptions('start_time');
            // Set start time to next available 15-min slot
            const now = new Date();
            let startHour = now.getHours();
            let startMin = now.getMinutes();
            startMin = Math.ceil(startMin / 15) * 15;
            if (startMin === 60) {
                startHour++;
                startMin = 0;
            }
            let startLabel = `${pad(startHour)}:${pad(startMin)}`;
            document.getElementById('start_time').value = startLabel;
            // Set end time to +1 hour
            let endHour = startHour + 1;
            let endLabel = `${pad(endHour)}:${pad(startMin)}`;
            generateTimeOptions('end_time', endLabel);
            document.getElementById('end_time').value = endLabel;
            updateLayout();
            startCountdown();
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(updateLayout, 300000); // 5 minutes (same as session)
        });
        document.getElementById('booking_date').addEventListener('change', () => {
            updateLayout();
            startCountdown();
        });
        document.getElementById('start_time').addEventListener('change', () => {
            // Set end time to +1 hour by default
            const start = document.getElementById('start_time').value;
            if (start) {
                const [sh, sm] = start.split(':').map(Number);
                let eh = sh + 1;
                let em = sm;
                if (eh > 23) eh = 8; // wrap to opening hour
                let endLabel = `${pad(eh)}:${pad(em)}`;
                generateTimeOptions('end_time', endLabel);
                document.getElementById('end_time').value = endLabel;
            }
            updateLayout();
            startCountdown();
        });
        document.getElementById('end_time').addEventListener('change', () => {
            updateLayout();
            startCountdown();
        });
        document.getElementById('stationTypeFilter').addEventListener('change', updateLayout);
        document.getElementById('clearSelectionBtn').addEventListener('click', clearSelection);

        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2500);
        }
    </script>

</body>

</html>