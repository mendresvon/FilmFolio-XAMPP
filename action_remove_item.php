<?php
// action_remove_item.php
session_start();
require_once 'dbtools.inc.php';

if (isset($_SESSION['user_id']) && isset($_GET['item_id']) && isset($_GET['list_id'])) {
    $user_id = $_SESSION['user_id'];
    $item_id = intval($_GET['item_id']);
    $watchlist_id = intval($_GET['list_id']);

    // Security: We must ensure the watchlist belongs to the user before deleting the item
    // Use a subquery or a join to verify ownership
    $sql = "DELETE wi FROM watchlist_items wi
            JOIN watchlists w ON wi.watchlist_id = w.watchlist_id
            WHERE wi.item_id = ? AND w.user_id = ?";
            
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $item_id, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Redirect back to the specific list
    header("Location: view_list.php?id=" . $watchlist_id);
    exit();
}

header("Location: my_watchlists.php");
exit();
?>