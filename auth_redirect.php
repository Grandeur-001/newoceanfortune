<?php
function redirectIfLoggedIn() {

    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        // Redirect based on the user's role
        if ($_SESSION['user_role'] === 'user') {
            header("Location: dashboard.php"); // Redirect users to their dashboard
        } elseif ($_SESSION['user_role'] === 'admin') {
            header("Location: admin_dashboard.php"); // Redirect admins to their dashboard
        }
        exit();
    }
}
?>
