<?php
require_once '../inc/functions/connexion.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour effectuer cette action.");
}

try {
    // Démarrer la transaction
    $conn->beginTransaction();

    // Vérifier si la colonne numero_recu existe
    $stmt = $conn->query("SHOW COLUMNS FROM recus_demandes LIKE 'numero_recu'");
    $column_exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$column_exists) {
        // Ajouter la colonne numero_recu
        $conn->exec("ALTER TABLE `recus_demandes` ADD COLUMN `numero_recu` varchar(50) NOT NULL AFTER `id`");
        echo "Colonne numero_recu ajoutée\n";
    }

    // Vérifier si la clé primaire existe
    $stmt = $conn->query("SHOW KEYS FROM recus_demandes WHERE Key_name = 'PRIMARY'");
    $has_primary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$has_primary) {
        // Ajouter l'auto-increment et la clé primaire
        $conn->exec("ALTER TABLE `recus_demandes` MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
        echo "Clé primaire ajoutée\n";
    }

    // Mettre à jour les enregistrements existants avec un numéro de reçu généré
    $stmt = $conn->query("SELECT id, date_paiement FROM recus_demandes WHERE numero_recu IS NULL OR numero_recu = ''");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($records)) {
        $update_stmt = $conn->prepare("UPDATE recus_demandes SET numero_recu = ? WHERE id = ?");
        
        foreach ($records as $record) {
            $numero_recu = 'DEM-' . date('Ymd', strtotime($record['date_paiement'])) . sprintf("%04d", $record['id']);
            $update_stmt->execute([$numero_recu, $record['id']]);
        }
        echo "Mise à jour de " . count($records) . " reçus existants\n";
    }

    // Valider la transaction
    $conn->commit();
    echo "Mise à jour de la base de données terminée avec succès\n";

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Erreur : " . $e->getMessage());
}
