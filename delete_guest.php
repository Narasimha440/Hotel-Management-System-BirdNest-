<?php
session_start();
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the ID parameter is set
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Store the ID in a variable
    $guest_id = trim($_GET["id"]);
} else {
    // Redirect to error page if ID parameter is missing or invalid
    header("location: error.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare a delete statement
    $sql = "DELETE FROM guests WHERE id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
        $param_id = $_POST["id"];
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Records deleted successfully, redirect to guests page
            header("location: guests.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    } else {
        echo "Error preparing the SQL statement.";
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
}

// Close connection
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Guest Confirmation</title>
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
        .modal-bg {
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 400px;
            margin: 100px auto;
            position: relative;
        }
        .modal i {
            font-size: 50px;
            color: green;
            display: block;
            text-align: center;
        }
        .modal-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="modal-bg flex items-center justify-center fixed inset-0">
        <div class="modal shadow-lg">
            <i class="fas fa-check-circle"></i>
            <p class="text-center text-xl mt-4">Are you sure you want to delete this guest?</p>
            <div class="modal-buttons">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo $guest_id; ?>">
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
                </form>
                <a href="guests.php" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
            </div>
        </div>
    </div>
</body>
</html>
