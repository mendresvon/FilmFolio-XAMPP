<?php
// action_delete_list.php
session_start();
require_once __DIR__ . '/../includes/dbtools.inc.php';

if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $watchlist_id = intval($_GET['id']);

    // delete the list (items will be deleted automatically due to cascade in database)
    $sql = "DELETE FROM watchlists WHERE watchlist_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $watchlist_id, $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../my_watchlists.php");
exit();
?>