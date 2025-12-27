<?php
// action_remove_watchlist.php
session_start();
require_once __DIR__ . '/../includes/dbtools.inc.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Check if ID provided
if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $tmdb_id = intval($_GET['id']);

    // 3. Delete from Watchlist
    $sql = "DELETE FROM watchlist WHERE user_id = ? AND movie_tmdb_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $tmdb_id);

    if (mysqli_stmt_execute($stmt)) {
        // Success: Redirect back to watchlist
        header("Location: ../my_watchlists.php?removed=1");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($link);
    }
} else {
    // No ID provided
    header("Location: ../my_watchlists.php");
    exit();
}
?>