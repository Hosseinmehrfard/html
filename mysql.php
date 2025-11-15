<?php
$host = 'localhost';
$username = 'owasp_user';
$password = 'dbpassword';
$database = 'OwaspDB';
try {
    $connection = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>