<?php
// action_add_watchlist.php
session_start();
require_once 'functions.php'; // Includes dbtools and API logic

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to add to watchlist.");
}

// 2. Check if Form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tmdb_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $tmdb_id = intval($_POST['tmdb_id']);

    // 3. LAZY LOAD: Ensure movie exists in local DB first!
    // This is the critical "Hybrid" step.
    if (ensureMovieInLocalDB($tmdb_id)) {
        
        // 4. Add to Watchlist Table
        // We use INSERT IGNORE to prevent crashing if it's already added (due to our UNIQUE constraint)
        $sql = "INSERT IGNORE INTO watchlist (user_id, movie_tmdb_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $tmdb_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Success: Redirect back to the movie page
            header("Location: details.php?id=" . $tmdb_id . "&added=1");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($link);
        }
        
    } else {
        echo "Error: Could not fetch movie data from API.";
    }

} else {
    // If accessed directly without POST
    header("Location: index.php");
    exit();
}
?>