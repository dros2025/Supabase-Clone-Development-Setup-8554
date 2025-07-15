<?php
// Start session
session_start();

// Output content type
header('Content-Type: text/plain');

// Check if the session is working
if (!isset($_SESSION['test_count'])) {
    $_SESSION['test_count'] = 1;
    echo "First visit - session initialized\n";
} else {
    $_SESSION['test_count']++;
    echo "You've visited this page {$_SESSION['test_count']} times\n";
}

// Display session information
echo "\nSession Details:\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . " (1=disabled, 2=enabled but no session, 3=active)\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Cookie Parameters: \n";
print_r(session_get_cookie_params());

// Display all session data
echo "\nCurrent Session Data:\n";
print_r($_SESSION);

// Display PHP session configuration
echo "\nPHP Session Configuration:\n";
$session_config = array(
    'session.save_handler',
    'session.use_cookies',
    'session.use_only_cookies',
    'session.name',
    'session.auto_start',
    'session.cookie_lifetime',
    'session.cookie_path',
    'session.cookie_domain',
    'session.cookie_httponly',
    'session.cookie_samesite',
    'session.serialize_handler',
    'session.gc_probability',
    'session.gc_divisor',
    'session.gc_maxlifetime',
    'session.referer_check',
    'session.cache_limiter',
    'session.cache_expire',
    'session.use_trans_sid',
    'session.sid_length',
    'session.sid_bits_per_character',
    'session.trans_sid_tags',
    'session.trans_sid_hosts'
);

foreach ($session_config as $config) {
    echo "$config: " . ini_get($config) . "\n";
}

// Display cookie information
echo "\nCookies Received:\n";
print_r($_COOKIE);

// Display server information
echo "\nServer Information:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";

// Test if we can write session data
$_SESSION['test_write'] = 'This is a test at ' . date('Y-m-d H:i:s');
echo "\nTrying to write to session... ";
session_write_close();
echo "Session data written and closed.\n";

echo "\nTest complete. If the visit count increments on refresh, sessions are working correctly.";
?>