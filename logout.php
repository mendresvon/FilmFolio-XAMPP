<?php
// logout.php
session_start();
$_SESSION = array(); // Clear variables
session_destroy();   // Destroy session
header("Location: login.php");
exit();
?>