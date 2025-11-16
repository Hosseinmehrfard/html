<?php
session_start();
require_once 'mysql.php';
require_once 'functions.php';

if (isset($_POST['submit'])) {
    $password = $_POST['password'] ?? '';
    $token = $_POST['token'] ?? '';

    if ($password === '' || $token === '') {
        print('Password is required');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $connection->prepare('UPDATE users SET password_hash = :password WHERE reset_token = :token');
        $stmt->execute([
            ':password' => $hashed_password,
            ':token' => $token,
        ]);
    } catch (PDOException $e) {
        header('Location: msg.php?msg=Failed to update password&type=error&goto=reset_password.php');
        exit();
    }

    if ($stmt->rowCount() > 0) {
        try {
            $stmt = $connection->prepare('UPDATE users SET reset_token = NULL WHERE reset_token = :token');
            $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            // Ignore cleanup failure; main password update already succeeded.
        }

        header('Location: msg.php?msg=Password updated successfully&type=success&goto=login.php');
        exit();
    }

    header('Location: msg.php?msg=Failed to update password&type=error&goto=reset_password.php');
    exit();
}

$token = $_GET['token'] ?? '';
if ($token !== '') {
    try {
        $stmt = $connection->prepare(
            'SELECT id FROM users WHERE reset_token = :token AND reset_token IS NOT NULL LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
    } catch (PDOException $e) {
        header('Location: msg.php?msg=Invalid token&type=error&goto=reset_password.php');
        exit();
    }

    if ($stmt->rowCount() === 0) {
        header('Location: msg.php?msg=Invalid token&type=error&goto=reset_password.php');
        exit();
    }
} else {
    header('Location: msg.php?msg=Token required&type=error&goto=forget_password.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>Reset Password</h1>
        <form method="POST" action="/reset_password.php" accept-charset="UTF-8" autocomplete="on" novalidate>
            <div class="form-group">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password"
                       placeholder="********" required minlength="1" maxlength="120" />
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Reset Password</button>
            </div>
        </form>
    </div>
</body>
</html>
