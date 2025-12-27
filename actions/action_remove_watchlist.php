<?php
// action_remove_watchlist.php
session_start();
require_once __DIR__ . '/../includes/dbtools.inc.php';

// check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// check if id provided
if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $tmdb_id = intval($_GET['id']);

    // delete from watchlist
    $sql = "DELETE FROM watchlist WHERE user_id = ? AND movie_tmdb_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $tmdb_id);

    if (mysqli_stmt_execute($stmt)) {
        // success: redirect back to watchlist
        header("Location: ../my_watchlists.php?removed=1");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($link);
    }
} else {
    // no id provided
    header("Location: ../my_watchlists.php");
    exit();
}
?>