<?php

require_once 'mysql.php';

/*
GUIDE 1: GET FORM DATA
- Beginners can keep it simple: read each field, trim spaces, and store default values.
*/
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$invitation_code = trim($_POST['invitation_code'] ?? '');

$error = '';
$success = '';
$should_redirect = false;

/*
GUIDE 2: VALIDATE INPUT
- Stop early when something is wrong so the rest of the code stays easy to follow.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $error = 'Please enter your full name.';
    } elseif ($username === '') {
        $error = 'Please choose a username.';
    } elseif (!preg_match('/^[A-Za-z0-9\-]{1,20}$/', $username)) {
        $error = 'Username must be 1-20 characters using letters, numbers, or hyphens.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password === '' || strlen($password) < 4) {
        $error = 'Password must be at least 4 characters long.';
    } elseif ($invitation_code === '') {
        $error = 'Invitation code is required.';
    } else {
        /*
        GUIDE 3: CHECK INVITATION CODE
        - Make sure the code exists and has not been used.
        */
        try {
            $stmt = $connection->prepare(
                'SELECT id, used FROM invitation_codes WHERE invitation_code = :code LIMIT 1'
            );
            $stmt->execute([':code' => $invitation_code]);
            $invitation = $stmt->fetch();

            if (!$invitation) {
                $error = 'Invitation code is invalid or expired.';
            } elseif ((int)$invitation['used'] === 1) {
                $error = 'Invitation code has already been used.';
            } else {
                /*
                GUIDE 4: SAVE USER
                - Hash the password.
                - Insert the user in the database.
                - Mark the invitation code as used.
                */
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $timestamp = date('Y-m-d H:i:s');

                try {
                    $connection->beginTransaction();

                    $insertUser = $connection->prepare(
                        'INSERT INTO users (name, username, email, password_hash, invitation_code, created_at, updated_at)
                         VALUES (:name, :username, :email, :password_hash, :invitation_code, :created_at, :updated_at)'
                    );
                    $insertUser->execute([
                        ':name' => $name,
                        ':username' => $username,
                        ':email' => $email,
                        ':password_hash' => $hashedPassword,
                        ':invitation_code' => $invitation_code,
                        ':created_at' => $timestamp,
                        ':updated_at' => $timestamp,
                    ]);

                    $markCode = $connection->prepare(
                        'UPDATE invitation_codes SET used = 1 WHERE invitation_code = :code'
                    );
                    $markCode->execute([':code' => $invitation_code]);

                    $connection->commit();

                    $success = 'Registration complete! Redirecting you to the login page in 3 seconds.';
                    $should_redirect = true;
                } catch (PDOException $e) {
                    if ($connection->inTransaction()) {
                        $connection->rollBack();
                    }
                    $error = 'Could not save your account right now.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Unable to check the invitation code right now.';
        }
    }
}

/*
GUIDE 5: SHOW THE FORM
- Display any error / success message.
- Keep the form fields filled with what the user typed (except the password).
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
    <?php if ($should_redirect): ?>
        <meta http-equiv="refresh" content="3;url=/login.php">
    <?php endif; ?>
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>Register</h1>

        <?php if ($error !== ''): ?>
            <p class="message error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif ($success !== ''): ?>
            <p class="message success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($success === ''): ?>
            <form method="POST" action="/register.php" accept-charset="UTF-8" autocomplete="on" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" placeholder="Your name"
                           required minlength="1" maxlength="100"
                           value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="example"
                           required minlength="1" maxlength="20" pattern="[A-Za-z0-9\-]+"
                           title="Alphanumeric characters and hyphens only"
                           value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" placeholder="example@example.com"
                           required minlength="1" maxlength="100"
                           value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Your password"
                           required minlength="4" maxlength="24" />
                </div>

                <div class="form-group">
                    <label for="invitation_code">Invitation Code</label>
                    <input id="invitation_code" name="invitation_code" type="text" placeholder="ABCD!@#1234"
                           required minlength="4" maxlength="200" pattern="[A-Za-z0-9\-]+"
                           title="Alphanumeric characters and hyphens only"
                           value="<?php echo htmlspecialchars($invitation_code, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>

                <div class="form-group">
                    <button type="submit">Register</button>
                </div>
            </form>

            <div class="links links-compact">
                <a class="btn" href="/login.php">Already registered? Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
