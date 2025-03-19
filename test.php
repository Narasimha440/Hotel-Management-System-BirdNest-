<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emailtest";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_name = $_POST['staff_name'];
    $staff_email = $_POST['staff_email'];

    // Insert staff member into the database
    $sql = "INSERT INTO staff (name, email) VALUES ('$staff_name', '$staff_email')";
    if ($conn->query($sql) === TRUE) {
        echo "New staff member added successfully";

        // Send Welcome Email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth   = true;
            $mail->Username   = 'nimeshdavada88@gmail.com'; // SMTP username
            $mail->Password   = 'ourhefyfyzoubmjk'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 465; // TCP port to connect to

            // Recipients
            $mail->setFrom('nimeshdavada88@gmail.com', 'Bird Nest Community');
            $mail->addAddress($staff_email, $staff_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to the Bird Nest Community';
            $mail->Body    = '<h1>Welcome to the Bird Nest Community</h1><p>Dear ' . $staff_name . ',</p><p>We are thrilled to have you as part of our team. Welcome aboard!</p>';

            $mail->send();
            echo 'Welcome email has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<body>

<h2>Add New Staff Member</h2>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
  Name: <input type="text" name="staff_name"><br><br>
  Email: <input type="text" name="staff_email"><br><br>
  <input type="submit" value="Add Staff">
</form>

</body>
</html>