<?php
session_start(); // Start the session to access session variables
require 'auth_redirect.php';
redirectIfLoggedIn(); // Prevent logged-in users from accessing this page

require 'connection.php'; // Include your database connection

// Include PHPMailer classes using Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';

if (isset($_POST['signup_btn'])) {
    signup();
}

function signup()
{
    global $conn;

    $firstname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $conf_pass = mysqli_real_escape_string($conn, $_POST['conf_pass']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Check for password match
    if ($conf_pass == $password) {
        // Check if email is taken
        $select = "SELECT * FROM users WHERE email = '$email'";
        $qry = mysqli_query($conn, $select);

        if (mysqli_num_rows($qry) < 1) {
            // Hash password
            $options = [
                'cost' => 12
            ];
            $hashedPwd = password_hash($password, PASSWORD_BCRYPT, $options);

            // Generate a 6-digit random user ID
            do {
                $user_id = random_int(100000, 999999); // Generate random 6-digit number
                // Check if this user ID already exists
                $check_id_query = "SELECT * FROM users WHERE user_id = '$user_id'";
                $check_id_result = mysqli_query($conn, $check_id_query);
            } while (mysqli_num_rows($check_id_result) > 0); // Repeat if the ID already exists

            // Insert details including the random unique user_id
            $insert = "INSERT INTO users (user_id, firstname, lastname, email, nationality, state, dob, gender, password, phone, role, email_verified) 
                       VALUES ('$user_id', '$firstname', '$lastname', '$email', '$nationality', '$state', '$dob', '$gender', '$hashedPwd', '$phone','user', 0)"; // email_verified = 0 for unverified users

            $sql = mysqli_query($conn, $insert);

            if ($sql) {
                // Set the email session after successful insertion
                $_SESSION['email'] = $email; // Save the email in session

                // Generate OTP and set expiration time to strictly 2 minutes
                $otp = rand(100000, 999999); // Generate a random OTP
                $expires_at = date("Y-m-d H:i:s", strtotime('+2 minutes')); // OTP expires in 2 minutes

                // Store OTP in the database
                $insert_otp = "INSERT INTO otps (user_email, otp_code, expires_at) VALUES ('$email', '$otp', '$expires_at')";
                mysqli_query($conn, $insert_otp);

                // Send OTP to the user's email
                sendOtpEmail($email, $firstname, $otp);


                // Redirect user to OTP verification page
                header('Location: otp_verification.php?msg=Signup success, please verify your email');
                exit();
            } else {
                $GLOBALS['ERROR'] = "Unexpected error, please try again"; 
            }
        } else {
            // Display error
            $GLOBALS['ERROR'] = "Email already in use!";
        }
    } else {
        // Display error
        $GLOBALS['ERROR'] = "Passwords mismatch";
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
        $mail->setFrom('support@oceanfortune.bond', 'Ocean Fortune Admin');
        $mail->addAddress($email, $firstname); // Send to the user's email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "
            <p>Dear $firstname,</p>
            <p>Thank you for signing up! Your OTP code is:</p>
            <h3>$otp</h3>
            <p>This OTP will expire in 2 minutes.</p>
            <p>Please use it to verify your email address.</p>
            <p>Thank you for choosing Ocean Fortune!</p>
        ";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Log error if email could not be sent
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}


?>
