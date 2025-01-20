<?php
include 'connection.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload
require 'vendor/autoload.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID and the content of the email
    $userId = $_POST['user_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Query to get the user's email from the database
    $query = "SELECT email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userEmail = $user['email'];

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'server187.web-hosting.com'; // Set the SMTP server (e.g., Gmail, SMTP provider)
            $mail->SMTPAuth = true;
            $mail->Username = 'support@oceanfortune.bond'; // SMTP username
            $mail->Password = 'oceanfortune.bond'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('support@oceanfortune.bond', 'Admin');
            $mail->addAddress($userEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br($message);

            // Handle attachment
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
            }

            // Send email
            $mail->send();
            $toastMessage = 'Message has been sent';
        } catch (Exception $e) {
            $toastMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $toastMessage = 'User email not found.';
    }

    // JavaScript to display the alert and redirect
    echo "<script>
            alert('$toastMessage');
            window.location.href = 'users.php'; // Redirect to users.php
          </script>";
}
?>

