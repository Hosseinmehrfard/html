<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /login.php');
    exit;
} else {

?>
<html lang="en">
<body>
    <h1>Panel</h1>
</body>
</html>

<?php

    echo '<h3>Welcome to your panel, ' . htmlspecialchars($_SESSION['name']) . '!</h3>';
}
?>