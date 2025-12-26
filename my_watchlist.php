<?php
// my_watchlist.php
session_start();
require_once 'dbtools.inc.php';

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User's Watchlist
// We JOIN the 'watchlist' table with the 'movies' table to get the title and images
$sql = "SELECT m.* FROM watchlist w 
        JOIN movies m ON w.movie_tmdb_id = m.tmdb_id 
        WHERE w.user_id = ? 
        ORDER BY w.added_at DESC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$movies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $movies[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Watchlist - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .remove-btn {
            display: block;
            width: 100%;
            padding: 8px 0;
            background-color: #333;
            color: #fff;
            text-align: center;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .remove-btn:hover {
            background-color: #e50914; /* Red on hover */
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="my_watchlist.php" style="color: #e50914;">My Watchlist</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </nav>
    </header>

    <div class="container">
        <h2>My Watchlist</h2>

        <?php if (empty($movies)): ?>
            <div style="text-align: center; padding: 50px; color: #aaa;">
                <p>You haven't added any movies yet.</p>
                <a href="index.php" style="color: #e50914;">Go find some movies!</a>
            </div>
        <?php else: ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php 
                        // Image Fallback Logic
                        if (!empty($movie['poster_path'])) {
                            $poster_url = "https://image.tmdb.org/t/p/w500" . $movie['poster_path'];
                        } else {
                            $poster_url = "images/no_poster.jpg";
                        }
                    ?>
                    
                    <div class="movie-card">
                        <a href="details.php?id=<?= $movie['tmdb_id'] ?>">
                            <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        </a>
                        
                        <div class="movie-info">
                            <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                            
                            <a href="action_remove_watchlist.php?id=<?= $movie['tmdb_id'] ?>" 
                               class="remove-btn"
                               onclick="return confirm('Remove this movie from your watchlist?');">
                                Remove
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>