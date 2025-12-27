<?php
// action_create_list.php
session_start();
require_once __DIR__ . '/../includes/dbtools.inc.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['list_name'])) {
    header("Location: ../my_watchlists.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$list_name = trim($_POST['list_name']);

if (!empty($list_name)) {
    // Insert new list
    // 'IGNORE' prevents error if user creates duplicate name
    $sql = "INSERT IGNORE INTO watchlists (user_id, name) VALUES (?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $list_name);
    mysqli_stmt_execute($stmt);
}

header("Location: ../my_watchlists.php");
exit();
?>
