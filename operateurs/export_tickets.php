<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

// Récupérer les paramètres de recherche
$search_usine = $_GET['usine'] ?? null;
$search_date = $_GET['date_creation'] ?? null;
$search_chauffeur = $_GET['chauffeur'] ?? null;
$search_agent = $_GET['agent_id'] ?? null;

// Récupérer les tickets
if ($search_usine || $search_date || $search_chauffeur || $search_agent) {
    $tickets = searchTickets($conn, $search_usine, $search_date, $search_chauffeur, $search_agent);
} else {
    $tickets = getTickets($conn);
}

// Définir les en-têtes pour le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=tickets_export_' . date('Y-m-d') . '.csv');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
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
]);

// Fonction pour formater la date
function formatDate($date) {
    return $date ? date('d/m/Y', strtotime($date)) : '';
}

// Données
foreach ($tickets as $ticket) {
    fputcsv($output, [
        formatDate($ticket['date_ticket']),
        $ticket['numero_ticket'],
        $ticket['nom_usine'],
        $ticket['agent_nom_complet'],
        $ticket['matricule_vehicule'],
        $ticket['poids'],
        $ticket['utilisateur_nom_complet'],
        formatDate($ticket['created_at']),
        $ticket['prix_unitaire'],
        formatDate($ticket['date_validation_boss']),
        $ticket['montant_paie'],
        formatDate($ticket['date_paie'])
    ]);
}

fclose($output);
exit;
?>
