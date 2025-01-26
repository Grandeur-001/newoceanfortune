<?php
session_start(); // Start the session
require 'auth_redirect.php';
redirectIfLoggedIn(); // Prevent logged-in users from accessing this page

require 'connection.php'; // Include your database connection

// Include PHPMailer classes using Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';
$GLOBALS['ERROR'] = ''; // Initialize error variable

if (isset($_POST['signin_btn'])) {
    login(); // Call the login function when the form is submitted
}

function login()
{
    global $conn;

    // Get user input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Retrieve user details from the database
    $select = "SELECT * FROM users WHERE email = '$email'";
    $sql = mysqli_query($conn, $select);

    if (mysqli_num_rows($sql) > 0) {
        $fetch = mysqli_fetch_assoc($sql);

        // Check if user status is 'Disabled'
        if ($fetch['status'] === 'Disabled') {
            // If the user is disabled, show error message and prevent login
            $GLOBALS['ERROR'] = 'Your account is disabled. Please contact support.';
            return;
        }

        // Check password (hashed or plain)
        $storedPassword = $fetch['password'];
        $isPasswordCorrect = false;

        if (password_verify($password, $storedPassword)) {
            // If the password matches the hashed value
            $isPasswordCorrect = true;
        } elseif ($password === $storedPassword) {
            // If the stored password is plain text
            $isPasswordCorrect = true;

            // Hash the plain text password and update the database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";
            mysqli_query($conn, $updateQuery);
        }

        if ($isPasswordCorrect) {
            // Correct password
            $id = $fetch['user_id'];
            $name = $fetch['lastname'];
            $name2 = $fetch['firstname']; // Adjust field as per your database
            $email = $fetch['email'];
            $role = $fetch['role']; // Fetch role from database
            $dob = $fetch['dob'];
            $gender = $fetch['gender'];
            $nationality = $fetch['nationality'];
            $state = $fetch['state'];
            $phone = $fetch['phone']; // Fetch phone number from database

            // Generate OTP and set expiration time to strictly 2 minutes
            $otp = rand(100000, 999999); // Generate random 6-digit OTP
            $expires_at = date("Y-m-d H:i:s", strtotime('+2 minutes')); // OTP expires in 2 minutes

            // Store OTP in the database (you can create an 'otps' table as needed)
            $insertOtp = "INSERT INTO otps (user_email, otp_code, expires_at) VALUES ('$email', '$otp', '$expires_at')";
            mysqli_query($conn, $insertOtp);

            // Send OTP to the user's email
            sendOtpEmail($email, $name2, $otp);

            // Store user details in session
            $_SESSION['user_id'] = $id;
            $_SESSION['user_firstname'] = $name;
            $_SESSION['user_lastname'] = $name2;
            $_SESSION['email'] = $email;
            $_SESSION['user_role'] = $role; // Store role in session
            $_SESSION['user_dob'] = $dob;
            $_SESSION['user_gender'] = $gender;
            $_SESSION['user_nationality'] = $nationality;
            $_SESSION['user_state'] = $state;
            $_SESSION['user_phone'] = $phone; // Store phone in session

            // Redirect based on role
            if ($role === 'admin') {
                // Redirect to admin dashboard if the user is an admin
                header('Location: admin_dashboard.php');
                exit();
            } else {
                // Redirect to user dashboard if the user is not an admin
                header('Location: otp_verification_login.php');
                exit();
            }
        } else {
            // Incorrect password
            $GLOBALS['ERROR'] = 'Invalid login details.';
        }
    } else {
        // User not found
        $GLOBALS['ERROR'] = 'Invalid login details.';
    }
}

// Function to send OTP email using PHPMailer
function sendOtpEmail($email, $firstname, $otp)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'server187.web-hosting.com'; // Set SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'support@oceanfortune.bond'; // SMTP username
        $mail->Password = 'oceanfortune.bond'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('support@oceanfortune.bond', 'Simart Pro Admin');
        $mail->addAddress($email, $firstname); // Send to the user's email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "
            <p>Dear $firstname,</p> <p>Thank you for logging in! To complete your login process, 
            please use the One-Time Password (OTP) below:</p> <h3>$otp</h3> 
            <p>This OTP will expire in 2 minutes. Please use it to verify your login.</p> 
            <p>If you did not request this login, please disregard this message.
            </p> <p>Thank you for choosing Simart Pro!</p>
        ";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Log error if email could not be sent
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
