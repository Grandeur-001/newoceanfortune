<?php
require 'connection.php'; // Include your database connection

// Include PHPMailer classes using Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';

session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Failed to resend OTP.'];

// Log session to ensure email is set
error_log("Session Email: " . $_SESSION['email']);

// Ensure POST request and session email exist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']); // Use session email
    $currentTime = time();

    // Check if the user exists
    $query = "SELECT firstname FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        $response['message'] = 'Email not found in the system.';
        logResponse($response); // Log the response
        echo json_encode($response);
        exit();
    }

    $user = mysqli_fetch_assoc($result);
    $firstname = $user['firstname'];

    // Invalidate the old OTPs (Option 1: Set expired)
    $expireOldOtpQuery = "UPDATE otps SET expires_at = NOW() WHERE user_email = '$email' AND expires_at > NOW()";
    mysqli_query($conn, $expireOldOtpQuery);

    // Option 2: Alternatively, you could delete the old OTPs
    // $deleteOldOtpQuery = "DELETE FROM otps WHERE user_email = '$email' AND expires_at > NOW()";
    // mysqli_query($conn, $deleteOldOtpQuery);

    // Generate a new OTP
    $otp = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', $currentTime + 120); // OTP valid for 2 minutes

    // Insert the new OTP into the database
    $insertOtpQuery = "
        INSERT INTO otps (user_email, otp_code, expires_at, created_at) 
        VALUES ('$email', '$otp', '$expiresAt', NOW())";
    
    if (mysqli_query($conn, $insertOtpQuery)) {
        try {
            // Send the OTP email
            sendOtpEmail($email, $firstname, $otp);
            $response['success'] = true;
            $response['message'] = 'A new OTP has been sent to your email.';
        } catch (Exception $e) {
            $response['message'] = 'Failed to send the OTP email. Please try again later.';
        }
    } else {
        $response['message'] = 'Failed to update OTP. Please try again.';
    }
}

// Log the final response to log.txt
logResponse($response);

// Output the response as JSON
echo json_encode($response);

// Function to log the response
function logResponse($response) {
    error_log("Response: " . json_encode($response), 3, 'log.txt');
}

// Function to send OTP email using PHPMailer
function sendOtpEmail($email, $firstname, $otp)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'server187.web-hosting.com'; // SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'support@oceanfortune.bond'; // SMTP username
        $mail->Password = 'oceanfortune.bond'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('support@oceanfortune.bond', 'Ocean Fortune Admin');
        $mail->addAddress($email, $firstname);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "
            <p>Dear $firstname,</p>
            <p>Your OTP code is:</p>
            <h3>$otp</h3>
            <p>This OTP will expire in 2 minutes.</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Failed to send OTP email.");
    }
}
?>



