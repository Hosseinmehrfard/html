<?php
session_start();

$message = trim($_GET['msg'] ?? 'Action completed.');
$type = $_GET['type'] ?? 'info';
$goto = trim($_GET['goto'] ?? '');

$validTypes = ['success', 'error', 'info'];
if (!in_array($type, $validTypes, true)) {
    $type = 'info';
}

$redirectUrl = '';
if ($goto !== '') {
    if (preg_match('/^https?:\/\//i', $goto)) {
        $redirectUrl = $goto;
    } else {
        $redirectUrl = '/' . ltrim($goto, '/');
    }
}

if ($redirectUrl !== '') {
    header('Refresh: 3; url=' . $redirectUrl);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message</title>
    <link rel="stylesheet" href="//static.owaspzero-x.ir/styles.css">
</head>
<body>
    <div class="home-link">
        <a class="btn" href="/index.php"><span class="icon">&#8962;</span>Home</a>
    </div>
    <div class="card">
        <h1>Status</h1>
        <p class="message <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>

        <?php if ($redirectUrl !== ''): ?>
            <p class="small">
                You will be redirected shortly.
            </p>
            <div class="links links-compact">
                <a class="btn" href="<?php echo htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8'); ?>">
                    Continue now
                </a>
            </div>
        <?php else: ?>
            <div class="links links-compact">
                <a class="btn" href="/index.php">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
