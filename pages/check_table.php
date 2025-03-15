<?php
require_once '../inc/functions/connexion.php';

try {
    $stmt = $conn->query("SHOW COLUMNS FROM recus_demandes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
