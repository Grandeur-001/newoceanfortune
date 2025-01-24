<?php
session_start();
require 'connection.php';

// Ensure the email session is set before verifying OTP
if (!isset($_SESSION["email"])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $email = $_SESSION["email"]; // User's email from session

    // Fetch OTP from the database, strictly checking expiration
    $select_otp = "
        SELECT * 
        FROM otps 
        WHERE user_email = '$email' 
          AND otp_code = '$otp'
    ";
    $qry = mysqli_query($conn, $select_otp);

    if (mysqli_num_rows($qry) > 0) {
        $otp_data = mysqli_fetch_assoc($qry);
        $expires_at_time = strtotime($otp_data['expires_at']);
        $current_time = time();

        if ($current_time > $expires_at_time) {
            // OTP is expired
            echo json_encode(['success' => false, 'message' => 'The OTP has expired. Please request a new one.']);
        } else {
            // OTP is valid and not expired, update user as verified
            $update = "UPDATE users SET email_verified = 1 WHERE email = '$email'";
            if (mysqli_query($conn, $update)) {
                // OTP verified, user is now verified
                echo json_encode(['success' => true, 'message' => 'OTP verified successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error verifying OTP.']);
            }
        }
    } else {
        // OTP does not exist or is invalid
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    }
}
?>

