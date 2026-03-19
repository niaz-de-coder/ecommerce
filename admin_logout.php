<?php
/**
 * Niaz De Coder Shop Admin Portal - Secure Sign Out
 * This script terminates the administrative session and redirects to the login page.
 */

// Initialize the session
session_start();

// Unset all session variables specific to the admin portal
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This completely destroys the session, not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Final step: Destroy the session on the server
session_destroy();

// Redirect back to the admin login gateway
header("Location: admin.php");
exit();
?>