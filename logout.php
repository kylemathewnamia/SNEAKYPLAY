<?php
// =============================================
// FILE: logout.php
// PURPOSE: Simple logout - destroy session and go to login page
// =============================================

// 1. Start the session
session_start();

// 2. Destroy ALL session data
$_SESSION = array(); // Empty the session array

// 3. Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // Session cookie name
        '',             // Empty value
        time() - 42000, // Expire in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destroy the session
session_destroy();

// 5. Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, "/");
}

// 6. Redirect to login page immediately
header("Location:login.php");
exit();
