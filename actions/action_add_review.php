<?php
// action_add_review.php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// check login
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to review.");
}

// check form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $tmdb_id = intval($_POST['tmdb_id']);
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);

    // validate input
    if ($rating < 1 || $rating > 5 || empty($review_text)) {
        die("Invalid input.");
    }

    // lazy load: ensure movie is in our local db
    if (ensureMovieInLocalDB($tmdb_id)) {
        
        // insert review
        // we use on duplicate key update so users can update their existing review
        $sql = "INSERT INTO reviews (user_id, movie_tmdb_id, rating, review_text) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text)";
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "iiis", $user_id, $tmdb_id, $rating, $review_text);
        
        if (mysqli_stmt_execute($stmt)) {
            // success: go back to movie page
            header("Location: ../details.php?id=" . $tmdb_id . "&review_saved=1");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($link);
        }

    } else {
        echo "Error: Could not sync movie with database.";
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>