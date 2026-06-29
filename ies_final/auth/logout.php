<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Optional: Prevent back navigation to dashboard after logout
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");

// Redirect to the module selection page
header("Location: ../index.php");
exit;
?>
