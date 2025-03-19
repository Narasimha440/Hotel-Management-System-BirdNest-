<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $stored_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Directly compare passwords without hashing
                        if ($password === $stored_password) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            header("location: roombookingpanel.php");
                            exit;
                        } else {
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $login_err = "Please fill in both fields.";
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BirdNest - Admin Panel</title>
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
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">
    <header class="bg-dark-green w-full py-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="images/BirdNest.png" alt="BirdNest Logo" class="h-8 w-8 mr-2">
                <h1 class="text-2xl font-bold text-white">BirdNest - Admin Panel</h1>
            </div>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center w-full">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-dark-green">Sign-In</h2>
            <?php if (!empty($login_err)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?php echo $login_err; ?></div>
            <?php endif; ?>
            <form action="login.php" method="post" class="space-y-6">
                <div class="relative">
                    <label for="username" class="block text-sm font-medium text-dark-green"><i class="fas fa-user"></i> Username: </label>
                    <input type="text" name="username" id="username" class="block w-full mt-1 p-3 border rounded-md focus:ring-dark-green focus:border-dark-green" autocomplete="off" required>
                </div>
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-dark-green"><i class="fas fa-lock"></i> Password: </label>
                    <input type="password" name="password" id="password" class="block w-full mt-1 p-3 border rounded-md focus:ring-dark-green focus:border-dark-green" required>
                </div>
                <button type="submit" class="w-full bg-dark-green text-white py-3 rounded-md text-lg font-semibold hover:bg-dark-green-light">Login</button>
            </form>
        </div>
    </main>
</body>
</html>
