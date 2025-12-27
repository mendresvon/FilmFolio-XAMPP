<?php
// landing.php
session_start();
// If logged in, go to app
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmFolio - Never forget a movie again</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="hero-container">
        <div class="hero-overlay"></div>
        
        <div class="landing-card">
            
            <h1 class="main-title">FilmFolio</h1>
            
            <p class="author-subtitle">By Von Mendres 「馬盛中」</p>
            
            <hr class="divider">

            <h2 class="headline-text">Never forget a movie again.</h2>
            <p class="sub-headline">Easily keep track of movies you want to watch.</p>
            
            <div>
                <a href="login.php" class="btn-login-main">Login</a>
                <p class="signup-link-text">
                    Don't have an account? <a href="register.php">Sign Up</a>
                </p>
            </div>
            
        </div>
        </div>

</body>
</html>