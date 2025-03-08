<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

// Vérification de la connexion utilisateur
//session_start();
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé");
}

// Récupérer les dates
$date_debut = $_GET['date_debut'] ?? null;
$date_fin = $_GET['date_fin'] ?? null;

if (!$date_debut || !$date_fin) {
    die("Les dates de début et de fin sont requises");
}

// Convertir les dates au format SQL
$date_debut_sql = date('Y-m-d', strtotime($date_debut));
$date_fin_sql = date('Y-m-d', strtotime($date_fin));

// Vérifier que la date de fin est supérieure à la date de début
if (strtotime($date_fin_sql) < strtotime($date_debut_sql)) {
    die("La date de fin doit être supérieure à la date de début");
}

try {
    // Préparer la requête SQL avec la condition de date
    $sql = "SELECT t.*, 
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine,
            us.id_usine
            FROM tickets t
            INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines us ON t.id_usine = us.id_usine
            WHERE DATE(t.created_at) BETWEEN :date_debut AND :date_fin
            ORDER BY t.created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':date_debut' => $date_debut_sql,
        ':date_fin' => $date_fin_sql
    ]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tickets)) {
        die("Aucun ticket trouvé pour la période sélectionnée");
    }

    // Définir les en-têtes pour le téléchargement
    header('Content-Type: text/csv; charset=utf-8');
    $filename = 'tickets_export_du_' . date('d-m-Y', strtotime($date_debut)) . '_au_' . date('d-m-Y', strtotime($date_fin)) . '.csv';
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Créer le fichier CSV
    $output = fopen('php://output', 'w');

    // Ajouter le BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // En-têtes des colonnes en français
    fputcsv($output, [
        'Date ticket',
        'Numéro Ticket',
        'Usine',
        'Chargé de mission',
        'Véhicule',
        'Poids',
        'Créé par',
        'Date création',
        'Prix Unitaire',
        'Date validation',
        'Montant',
        'Date Paiement'
    ], ';'); // Use semicolon as delimiter for better Excel compatibility in French locale

    // Fonction pour formater la date en format français
    function formatDate($date) {
        return $date ? date('d/m/Y', strtotime($date)) : '-';
    }

    // Fonction pour formater les valeurs numériques en format français
    function formatNumber($value) {
        if ($value === null || $value === '') {
            return '-';
        }
        return str_replace('.', ',', number_format((float)$value, 2, '.', ' '));
    }

    // Données
    foreach ($tickets as $ticket) {
        fputcsv($output, [
            formatDate($ticket['date_ticket']),
            $ticket['numero_ticket'] ?? '-',
            $ticket['nom_usine'] ?? '-',
            $ticket['agent_nom_complet'] ?? '-',
            $ticket['matricule_vehicule'] ?? '-',
            formatNumber($ticket['poids']),
            $ticket['utilisateur_nom_complet'] ?? '-',
            formatDate($ticket['created_at']),
            formatNumber($ticket['prix_unitaire']),
            formatDate($ticket['date_validation_boss']),
            formatNumber($ticket['montant_paie']),
            formatDate($ticket['date_paie'])
        ], ';'); // Use semicolon as delimiter for better Excel compatibility in French locale
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    die("Une erreur est survenue lors de l'exportation: " . $e->getMessage());
}
?>
