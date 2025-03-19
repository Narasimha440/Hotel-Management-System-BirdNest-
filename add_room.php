<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Handle add room request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $floor_number = $_POST["floor_number"];
    $room_number = $_POST["room_number"];
    $room_type = $_POST["room_type"];
    
    // Set the rate based on the room type
    $rate = 0;
    switch ($room_type) {
        case 'single':
            $rate = 500;
            break;
        case 'double':
            $rate = 2500;
            break;
        case 'triple':
            $rate = 3500;
            break;
        case 'delux':
            $rate = 50000;
            break;
        case 'mansion':
            $rate = 150000;
            break;
    }
    
    // Set room status to available by default
    $status = 'available';

    mysqli_query($link, "INSERT INTO rooms (floor_number, room_number, type, status, rate) VALUES ('$floor_number', '$room_number', '$room_type', '$status', '$rate')");
    header("location: rooms.php");
    exit;
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueBird Staff Panel - Add Room</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .bg-dark-green {
            background-color: #1c1f1c;
        }
        .header-logo {
            height: 32px;
            width: auto;
            margin-right: 8px;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
        }
        .content {
            margin-left: 250px;
        }
        .shake {
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
        }
    </style>
</head>
<body class="bg-gray-100 flex">
    <aside class="sidebar bg-dark-green flex flex-col items-center py-4">
        <div class="text-white text-2xl font-bold mb-6">BlueBird</div>
        <nav class="flex flex-col space-y-4 w-full">
            <!-- Sidebar links -->
            <a href="roombookingpanel.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
            <a href="reservations.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-calendar-check mr-2"></i>Reservations
            </a>
            <a href="guests.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-users mr-2"></i>Guests
            </a>
            <a href="rooms.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-bed mr-2 fa-beat-fade"></i>Rooms
            </a>
            <a href="staff.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-user-tie mr-2"></i>Staff
            </a>
            <a href="reports.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-chart-line mr-2 fa-beat-fade"></i>Reports
            </a>
            <a href="settings.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-cog mr-2"></i>Settings
            </a>
            <a href="logout.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </nav>
    </aside>
    
    <div class="content flex flex-col w-full">
        <header class="bg-dark-green py-4 shadow-md fixed w-full">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center">
                    <img src="images/BirdNest.png" alt="Hotel Logo" class="header-logo">
                    <h1 class="text-2xl font-bold text-white">Add Room</h1>
                </div>
                <div class="text-white">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </div>
            </div>
        </header>
        
        <main class="p-8 flex-grow pt-16">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Add Room</h2>
                <form action="add_room.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="floor_number" class="block text-gray-700 font-bold mb-2">Floor Number</label>
                            <input type="number" id="floor_number" name="floor_number" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label for="room_number" class="block text-gray-700 font-bold mb-2">Room Number</label>
                            <input type="text" id="room_number" name="room_number" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label for="room_type" class="block text-gray-700 font-bold mb-2">Room Type</label>
                            <select id="room_type" name="room_type" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" onchange="updateRate()" required>
                                <option value="">Select Room Type</option>
                                <option value="single">Single Room</option>
                                <option value="double">Double Room</option>
                                <option value="triple">Triple Room</option>
                                <option value="delux">Delux House</option>
                                <option value="mansion">Mansion</option>
                            </select>
                        </div>
                        <div>
                            <label for="rate" class="block text-gray-700 font-bold mb-2">Rate (INR)</label>
                            <input type="text" id="rate" name="rate" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" readonly>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="submit" class="text-white bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/50 dark:shadow-lg dark:shadow-blue-800/80 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Room</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function updateRate() {
            const roomType = document.getElementById('room_type').value;
            const rateInput = document.getElementById('rate');
            let rate = 0;

            switch (roomType) {
                case 'single':
                    rate = 500;
                    break;
                case 'double':
                    rate = 2500;
                    break;
                case 'triple':
                    rate = 3500;
                    break;
                case 'delux':
                    rate = 50000;
                    break;
                case 'mansion':
                    rate = 150000;
                    break;
            }

            rateInput.value = rate;
        }
    </script>
</body>
</html>
