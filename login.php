<?php
// login.php
session_start();
require_once 'dbtools.inc.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $sql = "SELECT user_id, username, password FROM users WHERE username = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - FilmFolio</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="hero-container">
        <div class="hero-overlay"></div>

        <div class="landing-card" style="max-width: 450px; padding: 40px;">
            
            <h1 class="main-title" style="font-size: 3rem; margin-bottom: 20px;">FilmFolio</h1>
            
            <h2 style="text-align: left; margin-bottom: 20px;">Sign In</h2>
            
            <?php if($error): ?>
                <div style="background: #e87c03; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: left; font-size: 0.9rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <input type="text" name="username" class="glass-input" placeholder="Username" required>
                <input type="password" name="password" class="glass-input" placeholder="Password" required>
                
                <button type="submit" class="glass-btn">Sign In</button>
            </form>

            <div style="text-align: left; margin-top: 10px;">
                <span style="color: #737373;">New to FilmFolio? </span>
                <a href="register.php" class="auth-switch-link">Sign up now.</a>
            </div>
            
            <div style="text-align: left; margin-top: 20px;">
                 <a href="landing.php" style="color: #b3b3b3; font-size: 0.8rem; text-decoration: none;">&larr; Back to Home</a>
            </div>

        </div>
    </div>

</body>
</html>