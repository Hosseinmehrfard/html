<?php

session_start();
require_once 'mysql.php';

function get_ip_adress() {
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/*
GUIDE 1: READ USER INPUT
- Keep things beginner-friendly: grab the fields, trim spaces, and set defaults for re-rendering.
*/
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$message = '';
$is_success = false;

/*
GUIDE 2: PROCESS FORM
- Only run the login logic when the page receives a POST request.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginStatus = 0;

    if ($username === '' || $password === '') {
        $message = 'Please fill in both the username and password fields.';
    } else {
        /*
        GUIDE 3: CHECK USER IN DATABASE
        - Look up the user record.
        - If it exists, verify the password hash.
        */
        try {
            $stmt = $connection->prepare(
                'SELECT id, name, username, email, created_at, password_hash
                 FROM users
                 WHERE username = :username
                 LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(); 

            if (!$user) {
                $message = 'That username does not exist.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $message = 'Password is incorrect. Please try again.';
            } else {
                /*
                GUIDE 4: STORE SESSION DATA
                - Regenerate the session ID for security.
                - Save the user details so other pages (like panel.php) can read them.
                */
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['created_at'] = $user['created_at'];
                $_SESSION['last_login'] = date('Y-m-d H:i:s');
                $_SESSION['logged_in'] = true;

                $is_success = true;
                $loginStatus = 1;
                $message = 'Login successful. Redirecting you to your panel in 3 seconds...';
            }
        } catch (PDOException $e) {
            $message = 'Unable to check your credentials right now.';
        }
    }

    // Record the attempt so admins can keep an eye on logins.
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    try {
        $logStmt = $connection->prepare(
            'INSERT INTO login_logs (ip_address, user_agent, referer, login_status, username)
             VALUES (:ip, :ua, :referer, :status, :username)'
        );
        $logStmt->execute([
            ':ip' => $ipAddress,
            ':ua' => $userAgent,
            ':referer' => $referer,
            ':status' => $loginStatus,
            ':username' => $username,
        ]);
    } catch (PDOException $e) {
        error_log('Failed to log login attempt: ' . $e->getMessage());
    }
}

/*
GUIDE 5: RENDER THE PAGE
- Show the message (error or success).
- Hide the form after success and add a meta refresh to panel.php.
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
    <?php if ($is_success): ?>
        <meta http-equiv="refresh" content="3;url=/panel.php">
    <?php endif; ?>
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>Login</h1>

        <?php if ($message !== ''): ?>
            <p class="message <?php echo $is_success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <?php if (!$is_success): ?>
            <form method="POST" action="/login.php" accept-charset="UTF-8" autocomplete="on" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="example"
                           required minlength="1" maxlength="20" pattern="[A-Za-z0-9\-]+"
                           title="Alphanumeric characters and hyphens only"
                           value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Your password"
                           required minlength="8" maxlength="100" />
                </div>

                <div class="form-group">
                    <button type="submit">Login</button>
                </div>
            </form>
            <div class="links links-compact">
                <a class="btn" href="/register.php">Need an account? Register</a>
                <a class="btn" href="/forget_password.php">Forgot password?</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
