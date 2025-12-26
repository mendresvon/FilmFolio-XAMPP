<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// index.php
session_start();

// If the user is NOT logged in, redirect them to the login page immediately.
if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit();
}

require_once 'functions.php';

// 1. Determine what to show (Search Results or Popular)
$search_query = "";
$movies = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $movies = searchMovies($search_query);
    $page_title = "Search Results for: " . htmlspecialchars($search_query);
} else {
    $movies = getPopularMovies();
    $page_title = "Trending Now";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmFolio - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="my_watchlists.php">My Watchlists</a>
                <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <div class="search-container">
            <form action="index.php" method="GET">
                <input type="text" name="search" placeholder="Search for movies..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2><?= $page_title ?></h2>
        
        <?php if (!empty($movies)): ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php 
                        // TMDB returns a partial path (e.g., "/abc.jpg"). We need the full URL.
                        // Use a placeholder if no image exists.
                        $poster_path = $movie['poster_path'];
                        if (!empty($poster_path)) {
                            $poster_url = "https://image.tmdb.org/t/p/w500" . $poster_path;
                        } else {
                            // Fallback to local image
                            $poster_url = "images/no_poster.png"; 
                        }
                    ?>
                    
                    <a href="details.php?id=<?= $movie['id'] ?>" class="movie-card">
                        <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <div class="movie-info">
                            <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                            <p class="movie-year">
                                <?= isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A' ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #aaa;">No movies found. Try a different search.</p>
        <?php endif; ?>
    </div>

</body>
</html>