<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

$name = $_SESSION['name'] ?? 'there';
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$createdAt = $_SESSION['created_at'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Panel</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>User Panel</h1>
        <p class="lead">Welcome back, <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>.</p>

        <ul class="user-meta">
            <?php if ($username !== ''): ?>
                <li><span>Username</span><strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></li>
            <?php endif; ?>
            <?php if ($email !== ''): ?>
                <li><span>Email</span><strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong></li>
            <?php endif; ?>
            <?php if ($createdAt !== ''): ?>
                <li><span>Member Since</span><strong><?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></strong></li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
