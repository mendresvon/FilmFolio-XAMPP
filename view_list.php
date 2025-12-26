<?php
// view_list.php
session_start();
require_once 'dbtools.inc.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_watchlists.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$watchlist_id = intval($_GET['id']);

// 2. Verify this list belongs to the logged-in user
// We also get the list Name to display at the top
$sql_check = "SELECT name FROM watchlists WHERE watchlist_id = ? AND user_id = ?";
$stmt_check = mysqli_prepare($link, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $watchlist_id, $user_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if ($row_check = mysqli_fetch_assoc($result_check)) {
    $list_name = $row_check['name'];
} else {
    // List doesn't exist or doesn't belong to user
    die("Error: List not found or access denied.");
}

// 3. Fetch Movies in this List
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($list_name) ?> - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-delete-list {
            background-color: #333;
            color: #ccc;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            border: 1px solid #444;
        }
        .btn-delete-list:hover {
            background-color: #e50914;
            color: white;
            border-color: #e50914;
        }
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
            background-color: #e50914;
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="my_watchlists.php" style="color: #e50914;">My Libraries</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        
        <div class="header-actions">
            <div>
                <a href="my_watchlists.php" style="color: #999; text-decoration: none;">&larr; Back to Libraries</a>
                <h1><?= htmlspecialchars($list_name) ?></h1>
            </div>
            
            <a href="action_delete_list.php?id=<?= $watchlist_id ?>" 
               class="btn-delete-list"
               onclick="return confirm('Are you sure you want to delete this entire list? This cannot be undone.');">
               Delete This List
            </a>
        </div>

        <?php if (empty($movies)): ?>
            <div style="text-align: center; padding: 50px; color: #aaa; border: 1px dashed #333; border-radius: 8px;">
                <p>This list is empty.</p>
                <a href="index.php" style="color: #e50914;">Go add some movies!</a>
            </div>
        <?php else: ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <?php 
                        $poster_url = !empty($movie['poster_path']) 
                            ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] 
                            : "images/no_poster.jpg";
                    ?>
                    
                    <div class="movie-card">
                        <a href="details.php?id=<?= $movie['tmdb_id'] ?>">
                            <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        </a>
                        
                        <div class="movie-info">
                            <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                            
                            <a href="action_remove_item.php?item_id=<?= $movie['item_id'] ?>&list_id=<?= $watchlist_id ?>" 
                               class="remove-btn"
                               onclick="return confirm('Remove movie from this list?');">
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