<?php
// my_watchlists.php
session_start();
require_once 'dbtools.inc.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Get username for "Welcome Back" if desired

// Fetch User's Lists
$sql = "SELECT w.*, COUNT(wi.item_id) as movie_count 
        FROM watchlists w 
        LEFT JOIN watchlist_items wi ON w.watchlist_id = wi.watchlist_id 
        WHERE w.user_id = ? 
        GROUP BY w.watchlist_id 
        ORDER BY w.created_at ASC";

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
    <title>Dashboard - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Inline style for the Modal (Create List Popup) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); align-items: center; justify-content: center; }
        .modal-content { background: #222; padding: 30px; border-radius: 8px; width: 100%; max-width: 400px; text-align: center; border: 1px solid #444; }
        .modal-input { width: 100%; padding: 12px; margin: 15px 0; background: #111; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box; }
        .close-modal { float: right; cursor: pointer; font-size: 1.5rem; color: #aaa; }
    </style>
</head>
<body>

    <div class="dashboard-bg"></div>
    <div class="dashboard-overlay"></div>

    <header>
        <a href="index.php" class="logo">FilmFolio</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="my_watchlists.php" style="color: #e50914;">My Libraries</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="dashboard-content">
        
        <h1 class="dashboard-title">WELCOME BACK</h1>

        <div class="dashboard-header-row">
            <h2>Your Watchlists</h2>
            <a href="#" class="btn-create-new" onclick="openModal()">+ Create New</a>
        </div>

        <div class="lists-grid">
            <?php if (empty($lists)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #ccc; background: rgba(0,0,0,0.5); border-radius: 8px;">
                    <p style="font-size: 1.2rem;">You don't have any lists yet.</p>
                    <p style="color: #aaa;">Create one to start organizing your movies.</p>
                </div>
            <?php else: ?>
                <?php foreach ($lists as $list): ?>
                    <a href="view_list.php?id=<?= $list['watchlist_id'] ?>" class="list-card">
                        <div>
                            <div class="list-name"><?= htmlspecialchars($list['name']) ?></div>
                            <div class="list-count"><?= $list['movie_count'] ?> movies</div>
                        </div>
                        </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 style="margin-top: 0;">Create New List</h2>
            <form action="action_create_list.php" method="POST">
                <input type="text" name="list_name" class="modal-input" placeholder="e.g. 'Date Night'" required>
                <button type="submit" class="btn-create-new" style="width:100%; border:none; cursor:pointer;">Create</button>
            </form>
        </div>
    </div>

    <script>
        // Simple script to toggle the Create List modal
        function openModal() {
            document.getElementById('createModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('createModal').style.display = 'none';
        }
        // Close if clicked outside
        window.onclick = function(event) {
            var modal = document.getElementById('createModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>