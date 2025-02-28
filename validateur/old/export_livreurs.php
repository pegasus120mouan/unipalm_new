<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_utilisateurs.php'; 
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Créer une nouvelle feuille de calcul
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Récupérer les données des livreurs
$utilisateurs = $liste_livreur->fetchAll();

// Entêtes de colonne
$sheet->setCellValue('A1', 'Nom');
$sheet->setCellValue('B1', 'Prénoms');
$sheet->setCellValue('C1', 'Contact');
$sheet->setCellValue('D1', 'Login');
$sheet->setCellValue('E1', 'Statut du compte');

// Remplir les données
$row = 2;
foreach ($utilisateurs as $utilisateur) {
    $sheet->setCellValue('A' . $row, $utilisateur['nom']);
    $sheet->setCellValue('B' . $row, $utilisateur['prenoms']);
    $sheet->setCellValue('C' . $row, $utilisateur['contact']);
    $sheet->setCellValue('D' . $row, $utilisateur['login']);
    $sheet->setCellValue('E' . $row, ($utilisateur['statut_compte'] == 1) ? 'Actif' : 'Inactif');
    $row++;
}

// Créer et télécharger le fichier CSV
$writer = new Csv($spreadsheet);
$filename = 'Liste_des_livreurs.csv';

// Envoyer les en-têtes pour le téléchargement
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
