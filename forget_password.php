<?php
session_start();
require_once 'mysql.php';
require_once 'functions.php';

$message = '';
$is_error = false;

if (isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    if ($username === '') {
        $message = 'Username is required.';
        $is_error = true;
    } else {
        $random_token = random_token();

        try {
            $stmt = $connection->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $message = 'Database error while checking username.';
            $is_error = true;
        }

        if (!$user && !$is_error) {
            $message = 'Username not found.';
            $is_error = true;
        }

        if (!$is_error) {
            try {
                $stmt = $connection->prepare('UPDATE users SET reset_token = :token WHERE username = :username');
                $stmt->execute([
                    ':token' => $random_token,
                    ':username' => $username,
                ]);
            } catch (PDOException $e) {
                header('Location: msg.php?msg=Failed to send reset token&type=error&goto=forget_password.php');
                exit();
            }

            if ($stmt->rowCount() > 0) {
                $reset_token_url = 'http://owaspzero-x.ir/reset_password.php?token=' . urlencode($random_token);

                $to = $user['email'];
                $subject = 'Reset Password';
                $messageBody = 'Click the link below to reset your password: ' . $reset_token_url;
                $headers = 'From: OWASPZero-X <no-reply@owaspzero-x.ir>';
                mail($to, $subject, $messageBody, $headers);

                header('Location: msg.php?msg=Reset token sent to email&type=success&goto=login.php');
                exit();
            }

            header('Location: msg.php?msg=Failed to send reset token&type=error&goto=forget_password.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>Forgot Password</h1>
        <p>Enter your username to request a reset link.</p>

        <?php if ($message !== ''): ?>
            <p class="message <?php echo $is_error ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/forget_password.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" 
                       value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>" required />
            </div>
            <div class="form-group">
                <button type="submit" name="submit" value="1">Send Reset Link</button>
            </div>
        </form>

        <div class="links links-compact">
            <a href="/login.php">Back to login</a>
            <a href="/register.php">Create account</a>
        </div>
    </div>
</body>
</html>
