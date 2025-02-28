<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateurs.php';

// Récupérer les utilisateurs
$utilisateurs = getUtilisateurs($conn);

// Définir les en-têtes pour le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=liste_utilisateurs_' . date('Y-m-d') . '.csv');

// Créer le flux de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
fputcsv($output, array('Nom', 'Prénoms', 'Contact', 'Login', 'Rôle', 'Statut compte'));

// Données
foreach ($utilisateurs as $utilisateur) {
    $statut = ($utilisateur['statut_compte'] == 1) ? 'Actif' : 'Inactif';
    fputcsv($output, array(
        $utilisateur['nom'],
        $utilisateur['prenoms'],
        $utilisateur['contact'],
        $utilisateur['login'],
        $utilisateur['role'],
        $statut
    ));
}

fclose($output);
exit();
