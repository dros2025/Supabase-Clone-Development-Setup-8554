<?php
session_start();

// Destroy the session
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Clear any local storage data via JavaScript
echo '<script>
localStorage.removeItem("user_logged_in");
localStorage.removeItem("user_email");
window.location.href = "login.html";
</script>';

exit();
?>