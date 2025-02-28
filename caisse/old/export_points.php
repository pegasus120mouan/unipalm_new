<?php
require_once '../inc/functions/connexion.php';
require '../vendor/autoload.php';  // Assurez-vous que PhpSpreadsheet est correctement chargé

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes de colonnes
$sheet->setCellValue('A1', 'Livreur');
$sheet->setCellValue('B1', 'Recettes');
$sheet->setCellValue('C1', 'Dépenses');
$sheet->setCellValue('D1', 'Gain');
$sheet->setCellValue('E1', 'Date');

// Récupération des données
$stmt = $conn->prepare("SELECT CONCAT(utilisateurs.nom, ' ', utilisateurs.prenoms) AS livreur_nom, recette, depense, gain_jour, date_commande
                        FROM points_livreurs 
                        JOIN utilisateurs ON points_livreurs.utilisateur_id = utilisateurs.id 
                        AND utilisateurs.role = 'livreur' 
                        ORDER BY date_commande DESC");
$stmt->execute();
$point_livreurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remplir les données
$row = 2;  // Commence à la ligne 2 pour éviter les en-têtes
foreach ($point_livreurs as $point) {
    $sheet->setCellValue('A' . $row, $point['livreur_nom']);
    $sheet->setCellValue('B' . $row, $point['recette']);
    $sheet->setCellValue('C' . $row, $point['depense']);
    $sheet->setCellValue('D' . $row, $point['gain_jour']);
    $sheet->setCellValue('E' . $row, $point['date_commande']);
    $row++;
}

// Définir le nom du fichier et les en-têtes pour le téléchargement
$filename = 'points_livraison_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Créer le fichier CSV et le télécharger
$writer = new Csv($spreadsheet);
$writer->save('php://output');
exit;
