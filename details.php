<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// details.php
session_start();
require_once 'functions.php';

// 1. Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$tmdb_id = $_GET['id'];

// 2. Fetch Movie Details from API
$movie = getMovieDetails($tmdb_id);

// Handle API errors (e.g. invalid ID)
if (!$movie || isset($movie['success']) && $movie['success'] === false) {
    die("Movie not found.");
}

// 3. Fetch Local Reviews (from your database)
// We join with the users table to show WHO wrote the review
$reviews = [];
$sql = "SELECT r.*, u.username 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.movie_tmdb_id = ? 
        ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $tmdb_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($movie['title']) ?> - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Specific styles for details page */
        .hero {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .hero img {
            width: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .hero-content {
            flex: 1;
        }
        .btn-action {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #e50914;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .review-box {
            background: #1f1f1f;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">FilmFolio</a>
    <nav>
        <a href="index.php">Home</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="my_watchlist.php">My Watchlist</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <div class="hero">
        <?php 
            if (!empty($movie['poster_path'])) {
                $poster_url = "https://image.tmdb.org/t/p/w500" . $movie['poster_path'];
            } else {
                $poster_url = "images/no_poster.png";
            }
        ?>
        <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
        
        <div class="hero-content">
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            <p><strong>Release Date:</strong> <?= $movie['release_date'] ?></p>
            <p><strong>Rating:</strong> ⭐ <?= $movie['vote_average'] ?>/10</p>
            <p><strong>Overview:</strong></p>
            <p><?= nl2br(htmlspecialchars($movie['overview'])) ?></p>

            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="action_add_watchlist.php" method="POST" style="display:inline;">
                    <input type="hidden" name="tmdb_id" value="<?= $tmdb_id ?>">
                    <button type="submit" class="btn-action">Add to Watchlist</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn-action" style="background-color: #333;">Login to Add to Watchlist</a>
            <?php endif; ?>
        </div>
    </div>

    <hr style="border-color: #333;">

    <h3>User Reviews</h3>

    <?php if(isset($_SESSION['user_id'])): ?>
        <div style="background-color: #2a2a2a; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h4>Write a Review</h4>
            <form action="action_add_review.php" method="POST">
                <input type="hidden" name="tmdb_id" value="<?= $tmdb_id ?>">
                
                <label style="margin-right: 10px;">Rating:</label>
                <select name="rating" required style="padding: 5px; border-radius: 4px; border: none; margin-bottom: 10px;">
                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                    <option value="3">⭐⭐⭐ (3/5)</option>
                    <option value="2">⭐⭐ (2/5)</option>
                    <option value="1">⭐ (1/5)</option>
                </select>
                <br>
                
                <textarea name="review_text" rows="3" placeholder="What did you think of this movie?" required
                    style="width: 100%; padding: 10px; border-radius: 4px; border: none; margin-top: 5px; font-family: sans-serif;"></textarea>
                
                <button type="submit" class="btn-action" style="margin-top: 10px;">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <p><a href="login.php" style="color: #e50914;">Login</a> to write a review.</p>
    <?php endif; ?>

    <?php if(empty($reviews)): ?>
        <p style="color: #aaa;">No reviews yet. Be the first to review!</p>
    <?php else: ?>
        <?php foreach($reviews as $review): ?>
            <div class="review-box">
                <div class="review-header">
                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                    <span style="color: gold;">
                        <?= str_repeat("⭐", $review['rating']) ?> 
                        <span style="color: #aaa; font-size: 0.8rem;">(<?= $review['rating'] ?>/5)</span>
                    </span>
                </div>
                <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                <small style="color: #666;">Posted on <?= $review['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>