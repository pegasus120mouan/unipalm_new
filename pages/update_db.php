<?php
require_once '../inc/functions/connexion.php';

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if numero_recu column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM recus_demandes LIKE 'numero_recu'");
    $column_exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$column_exists) {
        // Add numero_recu column
        $conn->exec("ALTER TABLE `recus_demandes` ADD COLUMN `numero_recu` varchar(50) NOT NULL AFTER `id`");
        echo "Added numero_recu column\n";
    }

    // Check if primary key exists
    $stmt = $conn->query("SHOW KEYS FROM recus_demandes WHERE Key_name = 'PRIMARY'");
    $has_primary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$has_primary) {
        // Add auto_increment and primary key
        $conn->exec("ALTER TABLE `recus_demandes` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
        echo "Added primary key\n";
    }

    // Check if unique key exists
    $stmt = $conn->query("SHOW KEYS FROM recus_demandes WHERE Key_name = 'numero_recu'");
    $has_unique = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$has_unique) {
        // Add unique key for numero_recu
        $conn->exec("ALTER TABLE `recus_demandes` ADD UNIQUE KEY `numero_recu` (`numero_recu`)");
        echo "Added unique key\n";
    }

    // Generate receipt numbers for existing records
    $stmt = $conn->query("SELECT id FROM recus_demandes WHERE numero_recu = '' OR numero_recu IS NULL");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($records)) {
        $update_stmt = $conn->prepare("UPDATE recus_demandes SET numero_recu = ? WHERE id = ?");
        
        foreach ($records as $record) {
            $numero_recu = 'DEM-' . date('Ymd') . sprintf("%04d", rand(1, 9999));
            $update_stmt->execute([$numero_recu, $record['id']]);
        }
        echo "Updated " . count($records) . " existing records with receipt numbers\n";
    }

    $conn->commit();
    echo "Database update completed successfully\n";

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
