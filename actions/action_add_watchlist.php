<?php
// action_add_watchlist.php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// check login
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in.");
}

// check form data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tmdb_id']) && isset($_POST['watchlist_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $tmdb_id = intval($_POST['tmdb_id']);
    $watchlist_id = intval($_POST['watchlist_id']);

    // security: verify that this watchlist actually belongs to the user
    // prevents users from hacking the form to add movies to someone else's list
    $check_sql = "SELECT watchlist_id FROM watchlists WHERE watchlist_id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($link, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $watchlist_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) == 0) {
        die("Error: Access Denied. You do not own this list.");
    }

    // lazy load: ensure movie is in local db
    if (ensureMovieInLocalDB($tmdb_id)) {
        
        // add to watchlist items
        $sql = "INSERT IGNORE INTO watchlist_items (watchlist_id, movie_tmdb_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $watchlist_id, $tmdb_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // success
            header("Location: ../details.php?id=" . $tmdb_id . "&added=1");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($link);
        }
        
    } else {
        echo "Error: Could not fetch movie data from API.";
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>