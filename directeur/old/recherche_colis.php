<?php
// Establish a connection to your MySQL database
$dsn = 'mysql:host=your_host;db_ovl;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Retrieve commune information from the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['communeInput'])) {
    $commune = $_POST['communeInput'];
   echo $commune;
} else {
    echo 'Invalid request';
}
?>
