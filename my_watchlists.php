<?php
// my_watchlists.php
session_start();
require_once 'dbtools.inc.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User's Lists
// We also count how many movies are in each list using a LEFT JOIN
$sql = "SELECT w.*, COUNT(wi.item_id) as movie_count 
        FROM watchlists w 
        LEFT JOIN watchlist_items wi ON w.watchlist_id = wi.watchlist_id 
        WHERE w.user_id = ? 
        GROUP BY w.watchlist_id 
        ORDER BY w.created_at DESC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$lists = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lists[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Watchlists - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Specific Styles for List Cards */
        .lists-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .list-card {
            background-color: #1f1f1f;
            padding: 25px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            transition: transform 0.2s, background 0.2s;
            border: 1px solid #333;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
        }
        .list-card:hover {
            transform: translateY(-5px);
            background-color: #2a2a2a;
            border-color: #e50914;
        }
        .list-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .list-count {
            color: #aaa;
            font-size: 0.9rem;
        }
        .create-box {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .create-input {
            flex: 1;
            padding: 12px;
            background: #111;
            border: 1px solid #444;
            color: white;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="my_watchlists.php" style="color: #e50914;">My Watchlists</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1>My Watchlists</h1>
        </div>

        <div class="create-box">
            <form action="action_create_list.php" method="POST" style="width: 100%; display: flex; gap: 10px;">
                <input type="text" name="list_name" class="create-input" placeholder="Create a new list (e.g., 'Horror Night')" required>
                <button type="submit" class="btn-action">Create</button>
            </form>
        </div>

        <div class="lists-grid">
            <?php if (empty($lists)): ?>
                <p style="color: #aaa; grid-column: 1/-1;">You have no lists yet. Create one above!</p>
            <?php else: ?>
                <?php foreach ($lists as $list): ?>
                    <a href="view_list.php?id=<?= $list['watchlist_id'] ?>" class="list-card">
                        <div>
                            <div class="list-name"><?= htmlspecialchars($list['name']) ?></div>
                            <div class="list-count"><?= $list['movie_count'] ?> movies</div>
                        </div>
                        <div style="text-align: right; color: #e50914; font-size: 1.5rem;">&rarr;</div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>