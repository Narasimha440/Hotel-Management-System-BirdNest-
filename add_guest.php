<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Define variables and initialize with empty values
$first_name = $last_name = $email = $phone = $vip_status = "";
$first_name_err = $last_name_err = $email_err = $phone_err = $vip_status_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter a first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter a last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate VIP status
    if (!isset($_POST["vip_status"])) {
        $vip_status_err = "Please select a VIP status.";
    } else {
        $vip_status = $_POST["vip_status"] ? 1 : 0;
    }

    // Check input errors before inserting in database
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($phone_err) && empty($vip_status_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO guests (first_name, last_name, email, phone, vip_status) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssi", $param_first_name, $param_last_name, $param_email, $param_phone, $param_vip_status);

            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_vip_status = $vip_status;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to guests page
                header("location: guests.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueBird Staff Panel - Add Guest</title>
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
                <i class="fas fa-calendar-check mr-2 fa-beat-fade"></i>Reservations
            </a>
            <a href="guests.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-users mr-2"></i>Guests
            </a>
            <a href="rooms.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-bed mr-2"></i>Rooms
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
                    <h1 class="text-2xl font-bold text-white">Add Guest</h1>
                </div>
                <div class="text-white">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </div>
            </div>
        </header>
        
        <main class="p-8 flex-grow pt-16">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4"><i class="fas fa-user-plus"></i> Add New Guest</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">First Name</label>
                        <input type="text" name="first_name" class="shadow appearance-none border <?php echo (!empty($first_name_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $first_name; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $first_name_err; ?></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">Last Name</label>
                        <input type="text" name="last_name" class="shadow appearance-none border <?php echo (!empty($last_name_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $last_name; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $last_name_err; ?></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                        <input type="email" name="email" class="shadow appearance-none border <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $email; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $email_err; ?></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                        <input type="text" name="phone" class="shadow appearance-none border <?php echo (!empty($phone_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo $phone; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="vip_status">VIP Status</label>
                        <select name="vip_status" class="shadow appearance-none border <?php echo (!empty($vip_status_err)) ? 'border-red-500' : ''; ?> rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <span class="text-red-500 text-xs italic"><?php echo $vip_status_err; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Guest</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
