<?php
/**
 * Zenith Shop - User Logout
 * This script securely clears the user session and redirects to the sign-in page.
 */

// 1. Initialize the session
session_start();

// 2. Clear all session variables
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// This is an extra security step to ensure the session ID is cleared from the browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server side
session_destroy();

// 5. Redirect back to the login page
header("Location: signin.php");
exit();
?>