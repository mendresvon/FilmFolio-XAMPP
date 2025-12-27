<?php
// register.php
session_start();
require_once 'includes/dbtools.inc.php';

$error = "";
$success = "";
$prefill_user = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

// handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // check if username is already taken
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username already taken.";
        } else {
            // hash the password and create account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $insert_stmt = mysqli_prepare($link, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success = "Account created successfully!";
            } else {
                $error = "Error creating account.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - FilmFolio</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="hero-container">
        <div class="hero-overlay"></div>

        <div class="landing-card" style="max-width: 450px; padding: 40px;">
            
            <h1 class="main-title" style="font-size: 3rem; margin-bottom: 20px;">FilmFolio</h1>
            
            <h2 style="text-align: left; margin-bottom: 20px;">Sign Up</h2>

            <?php if($error): ?>
                <div style="background: #e87c03; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: left; font-size: 0.9rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div style="background: #46d369; padding: 15px; border-radius: 4px; margin-bottom: 15px; text-align: center;">
                    <strong><?= $success ?></strong><br><br>
                    <a href="login.php" class="btn-login-main" style="padding: 10px 20px; font-size: 1rem;">Login Now</a>
                </div>
            <?php else: ?>
                <form action="register.php" method="post">
                    <input type="text" name="username" class="glass-input" placeholder="Choose a Username" value="<?= $prefill_user ?>" required>
                    <input type="password" name="password" class="glass-input" placeholder="Choose a Password" required>
                    
                    <button type="submit" class="glass-btn">Register</button>
                </form>

                <div style="text-align: left; margin-top: 10px;">
                    <span style="color: #737373;">Already have an account? </span>
                    <a href="login.php" class="auth-switch-link">Sign in now.</a>
                </div>
            <?php endif; ?>
            
            <div style="text-align: left; margin-top: 20px;">
                 <a href="landing.php" style="color: #b3b3b3; font-size: 0.8rem; text-decoration: none;">&larr; Back to Home</a>
            </div>

        </div>
    </div>

</body>
</html>