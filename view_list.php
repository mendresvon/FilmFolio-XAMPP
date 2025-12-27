<?php
// view_list.php
session_start();
require_once 'includes/dbtools.inc.php';

// security check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_watchlists.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$watchlist_id = intval($_GET['id']);

// verify list ownership and get name
$sql_check = "SELECT name FROM watchlists WHERE watchlist_id = ? AND user_id = ?";
$stmt_check = mysqli_prepare($link, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $watchlist_id, $user_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if ($row_check = mysqli_fetch_assoc($result_check)) {
    $list_name = $row_check['name'];
} else {
    die("Error: List not found or access denied.");
}

// fetch movies
$sql_movies = "SELECT m.*, wi.item_id 
               FROM watchlist_items wi
               JOIN movies m ON wi.movie_tmdb_id = m.tmdb_id 
               WHERE wi.watchlist_id = ? 
               ORDER BY wi.added_at DESC";

$stmt_movies = mysqli_prepare($link, $sql_movies);
mysqli_stmt_bind_param($stmt_movies, "i", $watchlist_id);
mysqli_stmt_execute($stmt_movies);
$result_movies = mysqli_stmt_get_result($stmt_movies);

$movies = [];
while ($row = mysqli_fetch_assoc($result_movies)) {
    $movies[] = $row;
}

// determine background image (use first movie poster if available)
$bg_image = "images/dashboard_bg.jpg"; // default fallback
if (!empty($movies) && !empty($movies[0]['poster_path'])) {
    $bg_image = "https://image.tmdb.org/t/p/original" . $movies[0]['poster_path'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($list_name) ?> - FilmFolio</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="library-hero-bg" style="background-image: url('<?= $bg_image ?>');"></div>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="my_watchlists.php" style="color: #e50914;">My Libraries</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        
        <div class="library-header">
            <div>
                <a href="my_watchlists.php" class="btn-back-ghost">
                    <i class="fas fa-arrow-left"></i> Back to Libraries
                </a>
                <span class="library-subtitle">Collection</span>
                <h1 class="library-title"><?= htmlspecialchars($list_name) ?></h1>
            </div>
            
            <a href="actions/action_delete_list.php?id=<?= $watchlist_id ?>" 
               class="btn-icon-text btn-delete-ghost"
               onclick="return confirm('Are you sure you want to delete this entire list? This cannot be undone.');">
               <i class="fas fa-trash"></i> Delete List
            </a>
        </div>

        <?php if (empty($movies)): ?>
            <div style="text-align: center; padding: 100px; color: #aaa; background: rgba(0,0,0,0.3); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.1);">
                <i class="fas fa-film" style="font-size: 3rem; margin-bottom: 20px; color: #444;"></i>
                <p style="font-size: 1.2rem;">This list is empty.</p>
                <a href="index.php" style="color: #e50914; font-weight: 600; text-decoration: none;">Browse movies to add</a>
            </div>
        <?php else: ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php 
                        $poster_url = !empty($movie['poster_path']) 
                            ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] 
                            : "images/no_poster.png";
                    ?>
                    
                    <div class="movie-card-modern">
                        <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        
                        <div class="card-overlay">
                            <div class="card-title-overlay"><?= htmlspecialchars($movie['title']) ?></div>
                            
                            <div style="display: flex; gap: 10px;">
                                <a href="details.php?id=<?= $movie['tmdb_id'] ?>" style="flex:1; background: rgba(255,255,255,0.2); color: white; text-align: center; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; backdrop-filter: blur(4px);">
                                    View
                                </a>
                                <a href="actions/action_remove_item.php?item_id=<?= $movie['item_id'] ?>&list_id=<?= $watchlist_id ?>" 
                                   class="btn-remove-mini" style="flex:1;"
                                   onclick="return confirm('Remove movie from this list?');">
                                    Remove
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>