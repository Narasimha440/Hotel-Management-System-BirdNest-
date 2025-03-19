<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch data from the database
$rooms = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM rooms"), MYSQLI_ASSOC);
$reservations = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM reservations"), MYSQLI_ASSOC);
$guests = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM guests"), MYSQLI_ASSOC);
$staff = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM staff"), MYSQLI_ASSOC);
$tasks = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM tasks"), MYSQLI_ASSOC);
$notifications = mysqli_fetch_all(mysqli_query($link, "SELECT * FROM notifications"), MYSQLI_ASSOC);

// Calculate current occupancy rate
$total_rooms = count($rooms);
$occupied_rooms = count(array_filter($rooms, function($room) {
    return $room['status'] == 'Occupied';
}));
$occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100) : 0;

// Calculate revenue (example logic, should be adjusted based on actual data)
$revenue = array_reduce($reservations, function($carry, $reservation) use ($rooms) {
    $room = array_filter($rooms, function($room) use ($reservation) {
        return $room['id'] == $reservation['room_id'];
    });
    $room = array_values($room)[0];
    $carry += (strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / 86400 * $room['rate'];
    return $carry;
}, 0);

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueBird Staff Panel</title>
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
    </style>
</head>
<body class="bg-gray-100 flex">
    <aside class="sidebar bg-dark-green flex flex-col items-center py-4">
        <div class="text-white text-2xl font-bold mb-6">BlueBird</div>
        <nav class="flex flex-col space-y-4 w-full">
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
                    <img src="images/BirdNest.png" alt="Hotel Logo" class="header-logo" draggable = "false">
                    <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                </div>
                <div class="text-white">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </div>
            </div>
        </header>
        
        <main class="p-8 flex-grow pt-16">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-xl font-bold mb-4"><i class="fas fa-bed text-3xl mr-4"></i>Occupancy Rate</h2>
                        <p class="text-3xl"><?php echo $occupancy_rate; ?>%</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-xl font-bold mb-4"><i class="fas fa-door-open text-3xl mr-4"></i>Available Rooms</h2>
                        <p class="text-3xl"><?php echo $total_rooms - $occupied_rooms; ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-xl font-bold mb-4"><i class="fa-solid fa-money-bill fa-xl"></i> Revenue Overview</h2>
                        <p class="text-3xl">₹<?php echo number_format($revenue, 2); ?></p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold mt-8">Upcoming Check-ins and Check-outs</h2>
                <div class="bg-white p-6 rounded-lg shadow-lg mt-4">
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-4">Room Number</th>
                                <th class="p-4">Guest Name</th>
                                <th class="p-4">Check-in Date</th>
                                <th class="p-4">Check-out Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <?php
                            $room = array_values(array_filter($rooms, function($room) use ($reservation) {
                                return $room['id'] == $reservation['room_id'];
                            }))[0];
                            $guest = array_values(array_filter($guests, function($guest) use ($reservation) {
                                return $guest['id'] == $reservation['guest_id'];
                            }))[0];
                            ?>
                            <tr>
                                <td class="p-4"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($reservation['check_in']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($reservation['check_out']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h2 class="text-2xl font-bold mt-8"><i class="fa fa-calendar-alt"></i> Recent Bookings</h2>
                <div class="bg-white p-6 rounded-lg shadow-lg mt-4">
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-4">Room Number</th>
                                <th class="p-4">Guest Name</th>
                                <th class="p-4">Check-in Date</th>
                                <th class="p-4">Check-out Date</th>
                                <th class="p-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <?php
                            $room = array_values(array_filter($rooms, function($room) use ($reservation) {
                                return $room['id'] == $reservation['room_id'];
                            }))[0];
                            $guest = array_values(array_filter($guests, function($guest) use ($reservation) {
                                return $guest['id'] == $reservation['guest_id'];
                            }))[0];
                            ?>
                            <tr>
                                <td class="p-4"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($reservation['check_in']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($reservation['check_out']); ?></td>
                                <td class="p-4">
                                    <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" class="text-blue-500">Edit</a>
                                    <a href="delete_reservation.php?id=<?php echo $reservation['id']; ?>" class="text-red-500 ml-2">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h2 class="text-2xl font-bold mt-8"><i class="fas fa-tasks"></i> Pending Tasks</h2>
                <div class="bg-white p-6 rounded-lg shadow-lg mt-4">
                    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-4">Task Description</th>
                                <th class="p-4">Assigned Staff</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                            <?php
                            $staff_member = array_values(array_filter($staff, function($staff_member) use ($task) {
                                return $staff_member['id'] == $task['staff_id'];
                            }))[0];
                            ?>
                            <tr>
                                <td class="p-4"><?php echo htmlspecialchars($task['description']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($staff_member['first_name'] . ' ' . $staff_member['last_name']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($task['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
