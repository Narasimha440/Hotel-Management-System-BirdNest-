<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'nimeshdavada88@gmail.com');
define('SMTP_PASSWORD', '');
define('SMTP_PORT', '465');

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Create a new database connection
$host = 'localhost'; // Database host
$dbUsername = 'root'; // Database username
$dbPassword = ''; // Database password
$dbName = 'birdnest'; // Database name

// Connect to the database
$link = mysqli_connect($host, $dbUsername, $dbPassword, $dbName);

// Check the connection
if (!$link) {
    die('Could not connect: ' . mysqli_connect_error());
}

// Function to load the HTML email template and replace placeholders
function loadEmailTemplate($filePath, $placeholders) {
    if (!file_exists($filePath)) {
        die("Email template file not found.");
    }
    
    $template = file_get_contents($filePath);
    
    foreach ($placeholders as $key => $value) {
        $template = str_replace("{{{$key}}}", $value, $template);
    }
    return $template;
}

// Function to send the welcome email using PHPMailer
function sendWelcomeEmail($email, $first_name, $last_name, $role, $phone) {
    $mail = new PHPMailer(true);

    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST; // Your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME; // Your SMTP username
        $mail->Password = SMTP_PASSWORD; // Your SMTP password
        $mail->SMTPSecure = 'ssl'; // Enable TLS encryption
        $mail->Port = SMTP_PORT; // TCP port to connect to

        // Email headers
        $mail->setFrom('no-reply@bluebird.com', 'BlueBird');
        $mail->addAddress($email, "$first_name $last_name");
        $mail->isHTML(true);
        $mail->Subject = 'Your Profile Has Been Updated';

        // Email template
        $htmlTemplatePath = 'edit_staff_email_template.html'; // Path to your email template file

        // Load email template with dynamic content
        $placeholders = [
            'first_name' => htmlspecialchars($first_name),
            'last_name' => htmlspecialchars($last_name),
            'role' => htmlspecialchars($role),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
        ];
        $mail->Body = loadEmailTemplate($htmlTemplatePath, $placeholders);

        $mail->send();
        echo 'Email has been sent';
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Get the staff ID from the URL parameter
$staff_id = $_GET['id'] ?? null;

if (!$staff_id) {
    echo "Staff ID is missing.";
    exit;
}

// Fetch staff details from the database
$result = mysqli_query($link, "SELECT * FROM staff WHERE id = '$staff_id'");
$staff_member = mysqli_fetch_assoc($result);

if (!$staff_member) {
    echo "Staff member not found.";
    exit;
}

// Handle form submission to update staff details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_staff"])) {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $role = $_POST["role"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    $query = "UPDATE staff SET first_name = '$first_name', last_name = '$last_name', role = '$role', email = '$email', phone = '$phone' WHERE id = '$staff_id'";

    if (mysqli_query($link, $query)) {
        // Send email notification
        sendWelcomeEmail($email, $first_name, $last_name, $role, $phone);

        header("location: staff.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($link);
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff - BlueBird Staff Management</title>
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
                <i class="fas fa-user-tie mr-2 fa-beat-fade"></i>Staff
            </a>
            <a href="reports.php" class="text-white px-4 py-2 hover:bg-gray-700 w-full text-center">
                <i class="fas fa-chart-line mr-2"></i>Reports
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
                    <h1 class="text-2xl font-bold text-white">Edit Staff</h1>
                </div>
                <div class="text-white">
                    Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </div>
            </div>
        </header>

        <main class="p-8 flex-grow pt-16">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Edit Staff Member</h2>
                <form action="edit_staff.php?id=<?php echo $staff_id; ?>" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" value="<?php echo htmlspecialchars($staff_member['first_name']); ?>" required>
                        </div>
                        <div>
                            <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" value="<?php echo htmlspecialchars($staff_member['last_name']); ?>" required>
                        </div>
                        <div>
                            <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
                            <select id="role" name="role" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" required>
                                <option value="">Select Role</option>
                                <option value="Ticket Counter" <?php if ($staff_member['role'] === 'Ticket Counter') echo 'selected'; ?>>Ticket Counter</option>
                                <option value="Cleaner" <?php if ($staff_member['role'] === 'Cleaner') echo 'selected'; ?>>Cleaner</option>
                                <option value="Chef" <?php if ($staff_member['role'] === 'Chef') echo 'selected'; ?>>Chef</option>
                                <option value="Manager" <?php if ($staff_member['role'] === 'Manager') echo 'selected'; ?>>Manager</option>
                            </select>
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                            <input type="email" id="email" name="email" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" value="<?php echo htmlspecialchars($staff_member['email']); ?>" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-gray-700 font-bold mb-2">Phone</label>
                            <input type="text" id="phone" name="phone" class="block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600" value="<?php echo htmlspecialchars($staff_member['phone']); ?>" required>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" name="update_staff" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50">Update Staff</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
