<?php
// logout.php
session_start();

// destroy the session and redirect to landing page
session_destroy();
header("Location: landing.php");
exit();
?>