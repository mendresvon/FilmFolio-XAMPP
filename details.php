<?php
// details.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Handle API errors
if (!$movie || isset($movie['success']) && $movie['success'] === false) {
    die("Movie not found.");
}

// Prepare Poster URL
if (!empty($movie['poster_path'])) {
    $poster_url = "https://image.tmdb.org/t/p/w500" . $movie['poster_path'];
    // High-res for background
    $bg_poster_url = "https://image.tmdb.org/t/p/original" . $movie['poster_path']; 
} else {
    $poster_url = "images/no_poster.jpg";
    $bg_poster_url = "images/no_poster.jpg";
}


// 3. Fetch Local Reviews
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

// 4. FETCH USER'S LISTS (For the Dropdown)
$user_lists = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_lists = "SELECT watchlist_id, name FROM watchlists WHERE user_id = ? ORDER BY name ASC";
    $stmt_lists = mysqli_prepare($link, $sql_lists);
    mysqli_stmt_bind_param($stmt_lists, "i", $user_id);
    mysqli_stmt_execute($stmt_lists);
    $result_lists = mysqli_stmt_get_result($stmt_lists);
    while ($row = mysqli_fetch_assoc($result_lists)) {
        $user_lists[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($movie['title']) ?> - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<?php if(isset($_GET['added'])): ?>
    <div style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); background-color: #46d369; color: #fff; padding: 15px 30px; border-radius: 50px; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-weight: 600;">
        Movie added to your list successfully!
    </div>
<?php endif; ?>

<div class="details-background" style="background-image: url('<?= $bg_poster_url ?>');"></div>

<div class="details-container">
    
    <div class="details-hero-card">
        <img src="<?= $poster_url ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
        
        <div class="details-content">
            <h1 class="details-title"><?= htmlspecialchars($movie['title']) ?></h1>
            
            <div class="details-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?= $movie['release_date'] ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-star"></i>
                    <span><?= $movie['vote_average'] ?>/10</span>
                </div>
            </div>

            <div class="details-overview-title">Overview</div>
            <p class="details-overview-text"><?= nl2br(htmlspecialchars($movie['overview'])) ?></p>

            <?php if(isset($_SESSION['user_id'])): ?>
                
                <?php if(empty($user_lists)): ?>
                    <div style="margin-top: 20px;">
                        <a href="my_watchlists.php" class="btn-save-modern" style="background-color: #444;">Create a List to Save Movies</a>
                    </div>
                <?php else: ?>
                    <form action="action_add_watchlist.php" method="POST" class="watchlist-form-modern" style="margin-top: 20px;">
                        <input type="hidden" name="tmdb_id" value="<?= $tmdb_id ?>">
                        
                        <span class="watchlist-label-modern">Add to:</span>
                        <select name="watchlist_id" class="watchlist-select-modern">
                            <?php foreach($user_lists as $list): ?>
                                <option value="<?= $list['watchlist_id'] ?>"><?= htmlspecialchars($list['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn-save-modern">Save</button>
                    </form>
                <?php endif; ?>

            <?php else: ?>
                <a href="login.php" class="btn-save-modern" style="background-color: var(--netflix-red); margin-top: 20px; display: inline-block;">Login to Add to Watchlist</a>
            <?php endif; ?>
        </div>
    </div>

    <h3 class="reviews-title">User Reviews</h3>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="review-form-card">
            <h4>Write a Review</h4>
            <form action="action_add_review.php" method="POST">
                <input type="hidden" name="tmdb_id" value="<?= $tmdb_id ?>">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Rating:</label>
                    <select name="rating" required class="modern-select">
                        <option value="5">⭐⭐⭐⭐⭐ (5/5) - Excellent</option>
                        <option value="4">⭐⭐⭐⭐ (4/5) - Very Good</option>
                        <option value="3">⭐⭐⭐ (3/5) - Good</option>
                        <option value="2">⭐⭐ (2/5) - Fair</option>
                        <option value="1">⭐ (1/5) - Poor</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Your Review:</label>
                    <textarea name="review_text" rows="4" placeholder="What did you think of this movie?" required class="modern-textarea"></textarea>
                </div>
                
                <button type="submit" class="btn-save-modern">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <p style="margin-bottom: 30px;"><a href="login.php" style="color: var(--netflix-red); text-decoration: none; font-weight: 700;">Login</a> to write a review.</p>
    <?php endif; ?>

    <?php if(empty($reviews)): ?>
        <p style="color: #aaa; font-style: italic;">No reviews yet. Be the first to share your thoughts!</p>
    <?php else: ?>
        <?php foreach($reviews as $review): ?>
            <div class="review-card-modern">
                <div class="review-header-modern">
                    <div class="reviewer-name"><?= htmlspecialchars($review['username']) ?></div>
                    <div class="review-stars">
                        <?= str_repeat('<i class="fas fa-star"></i> ', $review['rating']) ?> 
                        <span style="color: #666; font-size: 0.9rem;">(<?= $review['rating'] ?>/5)</span>
                    </div>
                </div>
                <p class="review-text-modern"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                <small class="review-date">Posted on <?= date('F j, Y, g:i a', strtotime($review['created_at'])) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>