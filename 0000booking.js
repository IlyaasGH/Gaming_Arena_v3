<script>
function formatLocalDateTime(date) {
  const pad = num => String(num).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function generateTimeOptions(selectId) {
  const select = document.getElementById(selectId);
  select.innerHTML = '';
  const startHour = 8;
  const endHour = 23;

  const today = new Date();
  for (let hour = startHour; hour <= endHour; hour++) {
    for (let min = 0; min < 60; min += 15) {
      const time = new Date(today);
      time.setHours(hour, min, 0, 0);

      const label = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      const value = `${pad(time.getHours())}:${pad(time.getMinutes())}`;

      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      select.appendChild(option);
    }
  }

  function pad(n) {
    return n < 10 ? '0' + n : n;
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

function renderStations(start, end, containerId, bookedStations) {
    const grid = document.getElementById(containerId);
    grid.innerHTML = '';

    for (let i = start; i <= end; i++) {
        const div = document.createElement('div');
        div.className = 'station';
        div.dataset.id = i;
        div.innerText = `Station ${i}`;

        if (bookedStations.includes(i)) {
            div.classList.add('booked');
            console.log("Booked station:", i);
        } else {
            div.addEventListener('click', () => {
                document.querySelectorAll('.station.selected').forEach(s => s.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('stationInput').value = i;
            });
        }

        grid.appendChild(div);
    }
}


async function updateLayout() {
  const dateInput = document.getElementById('booking_date').value;
  const startTime = document.getElementById('start_time').value;
  const endTime = document.getElementById('end_time').value;

  if (dateInput && startTime && endTime) {
    const fullStart = `${dateInput}T${startTime}`;
    const fullEnd = `${dateInput}T${endTime}`;

    const bookedStations = await fetchBookedStations(dateInput, startTime, endTime);
    renderStations(1, 10, 'floor1', bookedStations);
    renderStations(11, 20, 'floor2', bookedStations);
  }
}

document.getElementById('bookingForm').addEventListener('submit', function (e) {
  const date = document.getElementById('booking_date').value;
  const start = document.getElementById('start_time').value;
  const end = document.getElementById('end_time').value;
  const station = document.getElementById('stationInput').value;

  if (!date || !start || !end || !station) {
    alert('Please select a date, start time, end time, and station before booking.');
    e.preventDefault();
    return;
  }

  // Store full datetime in hidden inputs
  document.getElementById('startTimeInput').value = `${date}T${start}`;
  document.getElementById('endTimeInput').value = `${date}T${end}`;
});

window.addEventListener('DOMContentLoaded', () => {
  generateTimeOptions('start_time');
  generateTimeOptions('end_time');

  const today = new Date().toISOString().split('T')[0];
  document.getElementById('booking_date').value = today;
  updateLayout();
});

['booking_date', 'start_time', 'end_time'].forEach(id => {
  document.getElementById(id).addEventListener('change', updateLayout);
});
</script>
