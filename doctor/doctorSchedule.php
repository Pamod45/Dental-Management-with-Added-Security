<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require('../config/logger.php');

$doctorSchedule = false;
$logger = createLogger('doctor.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    if(!isset($_SESSION['doctorid'])) {
        throw new Exception('Doctor session not found.', 403);
    }
    $docid = $_SESSION['doctorid'];
    $hireddate = $_SESSION['registereddate'];

    $hireYear = date('Y', strtotime($hireddate));
    $currentYear = date('Y');

    $doctorSchedule = true;
} catch (Exception $e) {
    if ($logger)
        $logger->error($e->getMessage());
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    <a href="/user/login.php">Go to Login Page</a>
    ';
    exit;
}


if ($doctorSchedule): ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Doctor Schedule</title>
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="doctorSchedule.css">
        <link rel="icon" href="/images_new/favicon.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
        <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
        <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
    </head>

    <body>
        <nav>
            <ul>
                <li>
                    <a href="#">
                        <i class="fa-solid fa-bars sideBarIcon"></i>
                        <span class="nav-item">PSW Dental</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php" class="nav-list-item">
                        <i class="fas fa-home sideBarIcon"></i>
                        <span class="nav-item">Home</span>
                    </a>
                </li>
                <li>
                    <a href="patientMedicalRecords.php" class="nav-list-item">
                        <i class="fa-solid fa-notes-medical sideBarIcon"></i>
                        <span class="nav-item">Patient Medical Record</span>
                    </a>
                </li>
                <li>
                    <a href="addMedicalRecord.php" class="nav-list-item">
                        <i class="fa-solid fa-folder-plus sideBarIcon"></i>
                        <span class="nav-item">Add New Record</span>
                    </a>
                </li>
                <li>
                    <a href="Pappointments.php" class="nav-list-item">
                        <i class="fa-regular fa-calendar-check sideBarIcon"></i>
                        <span class="nav-item">MY Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="doctorSchedule.php" class="nav-list-item">
                        <i class="fa-solid fa-calendar-days sideBarIcon"></i>
                        <span class="nav-item">My Schedule</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="logout nav-list-item">
                        <i class="fas fa-sign-out-alt sideBarIcon"></i>
                        <span class="nav-item">Log out</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="top">
            <p>Doctor Schedule</p>
        </div>
        <div class="bottom">
            <select id="year" class="form-select">
                <?php
                for ($year = $hireYear; $year <= $currentYear; $year++) {
                    $selected = ($year == $currentYear) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($year, ENT_QUOTES) . "' $selected>" . htmlspecialchars($year, ENT_QUOTES) . "</option>";
                }
                ?>
            </select>

            <select id="weekNo" class="form-select">
            </select>

            <div class="mycontainer">
                <div class="item header">
                    <div class="innerItem">Time Slot</div>
                    <div class="innerItem day1 head">Day 1</div>
                    <div class="innerItem day2 head">Day 2</div>
                    <div class="innerItem day3 head">Day 3</div>
                    <div class="innerItem day4 head">Day 4</div>
                    <div class="innerItem day5 head">Day 5</div>
                    <div class="innerItem day6 head">Day 6</div>
                    <div class="innerItem day7 head">Day 7</div>
                </div>
                <div class="item slot1 slot">
                    <div class="innerItem">08:30-11:00 AM</div>
                    <div class="innerItem day day1" data-starttime="08:30AM"></div>
                    <div class="innerItem day day2" data-starttime="08:30AM"></div>
                    <div class="innerItem day day3" data-starttime="08:30AM"></div>
                    <div class="innerItem day day4" data-starttime="08:30AM"></div>
                    <div class="innerItem day day5" data-starttime="08:30AM"></div>
                    <div class="innerItem day day6" data-starttime="08:30AM"></div>
                    <div class="innerItem day day7" data-starttime="08:30AM"></div>
                </div>
                <div class="item slot2 slot">
                    <div class="innerItem">11:30-01:30 PM</div>
                    <div class="innerItem day day1" data-starttime="11:30AM"></div>
                    <div class="innerItem day day2" data-starttime="11:30AM"></div>
                    <div class="innerItem day day3" data-starttime="11:30AM"></div>
                    <div class="innerItem day day4" data-starttime="11:30AM"></div>
                    <div class="innerItem day day5" data-starttime="11:30AM"></div>
                    <div class="innerItem day day6" data-starttime="11:30AM"></div>
                    <div class="innerItem day day7" data-starttime="11:30AM"></div>
                </div>
                <div class="item slot3 slot">
                    <div class="innerItem">02:30-05:00 PM</div>
                    <div class="innerItem day day1" data-starttime="02:30PM"></div>
                    <div class="innerItem day day2" data-starttime="02:30PM"></div>
                    <div class="innerItem day day3" data-starttime="02:30PM"></div>
                    <div class="innerItem day day4" data-starttime="02:30PM"></div>
                    <div class="innerItem day day5" data-starttime="02:30PM"></div>
                    <div class="innerItem day day6" data-starttime="02:30PM"></div>
                    <div class="innerItem day day7" data-starttime="02:30PM"></div>
                </div>
                <div class="item slot4 slot">
                    <div class="innerItem">05:30-08:00 PM</div>
                    <div class="innerItem day day1" data-starttime="05:30PM"></div>
                    <div class="innerItem day day2" data-starttime="05:30PM"></div>
                    <div class="innerItem day day3" data-starttime="05:30PM"></div>
                    <div class="innerItem day day4" data-starttime="05:30PM"></div>
                    <div class="innerItem day day5" data-starttime="05:30PM"></div>
                    <div class="innerItem day day6" data-starttime="05:30PM"></div>
                    <div class="innerItem day day7" data-starttime="05:30PM"></div>
                </div>
            </div>

        </div>


    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        $('.logout').click(function() {
                $.ajax({
                    type: 'POST',
                    url: '../user/logout.php',
                    data: {
                        csrf_token: '<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32));echo $_SESSION['csrf_token']; ?>'
                    },
                    success: function(response) {
                        window.location.href = '../user/login.php';
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to logout. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                        });
                    }
                });
            });

        function getDateWeek(date) {
            const currentDate =
                (typeof date === 'object') ? date : new Date();
            const januaryFirst =
                new Date(currentDate.getFullYear(), 0, 1);
            const daysToNextMonday =
                (januaryFirst.getDay() === 1) ? 0 :
                (7 - januaryFirst.getDay()) % 7;
            const nextMonday =
                new Date(currentDate.getFullYear(), 0,
                    januaryFirst.getDate() + daysToNextMonday);

            return (currentDate < nextMonday) ? 52 :
                (currentDate > nextMonday ? Math.ceil(
                    (currentDate - nextMonday) / (24 * 3600 * 1000) / 7) : 1);
        }

        function getWeeksInYear(year) {
            const firstDayOfYear = new Date(year, 0, 1);
            const lastDayOfYear = new Date(year, 11, 31);
            const daysInYear = (lastDayOfYear - firstDayOfYear) / (1000 * 60 * 60 * 24);
            return Math.ceil((daysInYear + firstDayOfYear.getDay()) / 7);
        }


        document.addEventListener("DOMContentLoaded", function() {
            const yearSelect = document.getElementById("year");
            const weekNoSelect = document.getElementById("weekNo");
            // Event listener for year select box
            yearSelect.addEventListener("change", function() {
                const selectedYear = parseInt(this.value);
                if (!isNaN(selectedYear)) {
                    const weeksInYear = getWeeksInYear(selectedYear);
                    weekNoSelect.innerHTML = "";
                    for (let week = 1; week <= weeksInYear; week++) {
                        const option = document.createElement("option");
                        const date = new Date(selectedYear, 0, week * 7 - 6);
                        const monthName = date.toLocaleString("default", {
                            month: "long"
                        });
                        option.value = week;
                        option.textContent = `Week ${week} (${monthName})`;
                        weekNoSelect.appendChild(option);
                    }
                }
                if (selectedYear == new Date().getFullYear()) {
                    weekNoSelect.value = getDateWeek(new Date()) - 1;
                } else {
                    weekNoSelect.value = 1;
                }

                weekNoSelect.dispatchEvent(new Event("change"));
            });

            weekNoSelect.addEventListener("change", function() {
                const selectedYear = parseInt(yearSelect.value);
                const selectedWeek = parseInt(this.value);

                if (!isNaN(selectedYear) && !isNaN(selectedWeek)) {
                    const startDate = getStartDateOfWeek(selectedYear, selectedWeek);
                    updateHeaderDates(startDate);
                }
                updateAvailability();
            });

            // making slots clickable 
            const daySlots = document.querySelectorAll('.day');
            daySlots.forEach(daySlot => {
                daySlot.addEventListener('click', function() {
                    const dayIndex = this.classList[2].slice(-1);
                    const headerDay = document.querySelector(`.day${dayIndex}`); // Matching with the corresponding header day
                    const slotDate = parseDate(headerDay.textContent);
                    if (slotDate > new Date()) {

                    }

                });
            });
            // manually selecting a option in doctorname combo
            yearSelect.dispatchEvent(new Event("change"));
        });

        // Function to calculate the start date of a week
        function getStartDateOfWeek(year, week) {
            const januaryFirst = new Date(year, 0, 1);
            const daysOffset = 8 - januaryFirst.getDay();
            const firstWeekStartDate = new Date(year, 0, daysOffset);
            const startDate = new Date(firstWeekStartDate.getTime() + (week - 1) * 7 * 24 * 60 * 60 * 1000);
            return startDate;
        }

        // Function to update the header dates
        function updateHeaderDates(startDate) {
            const daysOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
            for (let i = 0; i < 7; i++) {
                const day = startDate.getDate();
                const month = startDate.getMonth() + 1;
                const year = startDate.getFullYear();
                const formattedDate = `${year}/${month}/${day}`;
                const dayElement = document.querySelector(`.innerItem.day${i + 1}`);
                dayElement.textContent = `${daysOfWeek[startDate.getDay()]} ${formattedDate}`;
                startDate.setDate(startDate.getDate() + 1);
            }
        }

        function updateAvailability() {
            var doctorid = "<?php echo $docid; ?>";
            resetSlots();

            for (let i = 1; i <= 7; i++) {

                var date = document.querySelector(`.head.day${i}`).textContent;
                date = date.substring(4).replace(/\//g, "-");
                var availableslots;
                $.ajax({
                    type: 'POST',
                    url: 'get_available_slots.php',
                    data: {
                        date: date,
                        doctorid: doctorid
                    },
                    headers: {
                        'X-CSRF-Token': "<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); echo $_SESSION['csrf_token']; ?>", // CSRF token
                        'Accept': 'application/json', 
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response[0] != null) {
                            response.forEach(function(availableslot) {
                                var currentslotnumber = getSlot(availableslot);
                                var currentslot = document.querySelector(`.${currentslotnumber}.slot`);
                                var currentdayslot = currentslot.querySelector(`.day${i}`);
                                currentdayslot.style.backgroundColor = "#0B5460";
                            });
                        }
                    },error: function(xhr, status, error) {
                        console.error('Error occurred while fetching available slots:', error);
                        
                    }
                });
                var dailyslots = document.querySelectorAll(`.day.day${i}`);

            }
        }

        // Function to parse date string into Date object
        function parseDate(dateString) {
            const [dayOfWeek, date] = dateString.split(' ');
            const [day, month, year] = date.split('/');
            return new Date(`${month}/${day}/${year}`);
        }

        function resetSlots() {
            var slots = document.querySelectorAll(".slot");
            slots.forEach(slot => {
                const days = slot.querySelectorAll(".day");
                var color = true;
                days.forEach(day => {
                    if (color == true)
                        day.style.backgroundColor = "#BEBEBE";
                    else
                        day.style.backgroundColor = "";
                    color = !color;
                });
            });
        }

        function getSlot(time) {
            if (time == "08:30AM")
                return "slot1";
            if (time == "11:30AM")
                return "slot2";
            if (time == "02:30PM")
                return "slot3";
            if (time == "05:30PM")
                return "slot4";
        }
    </script>

    </html>

<?php endif; ?>