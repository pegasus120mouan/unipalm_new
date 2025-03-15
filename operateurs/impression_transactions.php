<?php
session_start();
// Désactiver la compression zlib
if (ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', 'Off');
}

// Vider tous les buffers de sortie
while (ob_get_level()) {
    ob_end_clean();
}

// Démarrer un nouveau buffer
ob_start();

// Ajout de la gestion d'erreurs au début du fichier
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition du chemin racine
$root_path = dirname(dirname(__FILE__));

require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['date_debut_transactions']) && isset($_POST['date_fin_transactions'])) {
    $date_debut = $_POST['date_debut_transactions'] . ' 00:00:00';
    $date_fin = $_POST['date_fin_transactions'] . ' 23:59:59';

    // Récupérer le solde initial (avant la date de début)
    $stmt = $conn->prepare("
        SELECT COALESCE(
            (SELECT SUM(CASE 
                WHEN type_transaction = 'Approvisionnement' THEN montant 
                WHEN type_transaction = 'Paiement' THEN -montant 
            END)
            FROM transactions 
            WHERE date_transaction < :date_debut), 0) as solde_initial
    ");
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->execute();
    $solde_initial = $stmt->fetch(PDO::FETCH_ASSOC)['solde_initial'];

    // Récupérer les transactions pour la période
    $sql = "SELECT 
        t.*,
        u.nom as nom_utilisateur,
        DATE(t.date_transaction) as date_only
    FROM transactions t
    LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
    WHERE t.date_transaction BETWEEN :date_debut AND :date_fin
    ORDER BY t.date_transaction ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($transactions)) {
        class PDF extends FPDF {
            function Header() {
                $logo_path = dirname(dirname(__FILE__)) . '/dist/img/logo.png';
                
                // En-tête avec fond gris très clair
                $this->SetFillColor(248, 248, 248);
                $this->Rect(10, 10, 190, 35, 'F');
                
                // Logo
                if (file_exists($logo_path)) {
                    $this->Image($logo_path, 15, 12, 25);
                }
                
                // Informations de l'entreprise
                $this->SetFont('Arial', 'B', 16);
                $this->SetTextColor(0, 100, 0); // Vert foncé pour le nom
                $this->Cell(45); // Espace pour le logo
                $this->Cell(145, 10, 'UNIPALM COOP - CA', 0, 1, 'L');
                
                // Sous-titre et informations
                $this->SetTextColor(100, 100, 100); // Gris pour les détails
                $this->SetFont('Arial', '', 10);
                $this->Cell(45); // Espace pour le logo
                $this->Cell(145, 5, utf8_decode('Société Coopérative Agricole Unie pour le Palmier'), 0, 1, 'L');
                $this->Cell(45);
                $this->Cell(145, 5, 'Siege Social : Divo Quartier millionnaire', 0, 1, 'L');
                $this->Cell(45);
                $this->Cell(145, 5, 'Tel: (00225) 27 34 75 92 36 / 07 49 17 16 32', 0, 1, 'L');
                
                // Ligne de séparation verte
                $this->SetDrawColor(0, 100, 0);
                $this->SetLineWidth(0.5);
                $this->Line(10, 47, 200, 47);
                $this->SetLineWidth(0.2);
                $this->Ln(15);
            }

            function Footer() {
                $this->SetY(-20);
                
                // Ligne verte
                $this->SetDrawColor(0, 100, 0);
                $this->Line(10, $this->GetY(), 200, $this->GetY());
                
                // Pied de page
                $this->SetTextColor(100, 100, 100);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(95, 5, 'Document genere le ' . date('d/m/Y à H:i'), 0, 0, 'L');
                $this->Cell(95, 5, 'Page ' . $this->PageNo(), 0, 1, 'R');
                $this->Cell(0, 5, 'UNIPALM COOP-CA - NCC : 2050R910', 0, 1, 'C');
            }

            function CleanString($str) {
                return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
            }
        }

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 35);

        // Titre du document avec fond vert clair
        $pdf->SetFillColor(240, 255, 240);
        $pdf->SetDrawColor(0, 100, 0);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(0, 100, 0);
        $pdf->Cell(0, 12, 'ETAT DES TRANSACTIONS', 1, 1, 'C', true);
        $pdf->SetTextColor(0);
        $pdf->Ln(5);

        // Période dans un cadre
        $pdf->SetFillColor(248, 248, 248);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, utf8_decode('Période du:'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, date('d/m/y', strtotime($date_debut)) . ' au ' . date('d/m/y', strtotime($date_fin)), 1, 1, 'R', true);
        $pdf->Ln(8);

        // Statistiques dans un cadre élégant
        $nb_transactions = count($transactions);
        $nb_approvisionnements = 0;
        $nb_paiements = 0;
        $total_entrees = 0;
        $total_sorties = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['type_transaction'] == 'approvisionnement') {
                $nb_approvisionnements++;
                $total_entrees += $transaction['montant'];
            } else {
                $nb_paiements++;
                $total_sorties += $transaction['montant'];
            }
        }

        // Cadre des statistiques
        $pdf->SetFillColor(240, 255, 240); // Vert très clair
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, 'STATISTIQUES', 1, 1, 'C', true);
        
        $pdf->SetFillColor(248, 248, 248);
        $pdf->SetFont('Arial', '', 10);
        
        // Nombre total
        $pdf->Cell(65, 6, 'Nombre de transactions:', 'LR', 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, $nb_transactions, 'LR', 1, 'R', true);
        
        // Approvisionnements
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(65, 6, 'Approvisionnements:', 'LR', 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 128, 0);
        $pdf->Cell(0, 6, sprintf('%d (%s FCFA)', $nb_approvisionnements, number_format($total_entrees, 0, ',', ' ')), 'LR', 1, 'R', true);
        
        // Paiements
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(65, 6, 'Paiements:', 'LR', 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(0, 6, sprintf('%d (%s FCFA)', $nb_paiements, number_format($total_sorties, 0, ',', ' ')), 'LR', 1, 'R', true);

        // Séparateur
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Cell(0, 0, '', 'LR', 1);
        
        // Solde de la période
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(65, 6, utf8_decode('Solde de la période:'), 'LBR', 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $solde_periode = $total_entrees - $total_sorties;
        //$prefix = $solde_periode >= 0 ? '+' : '';
        $pdf->SetTextColor($solde_periode >= 0 ? 0 : 200, $solde_periode >= 0 ? 128 : 0, 0);
        $pdf->Cell(0, 6, number_format($solde_periode, 0, ',', ' ') . ' FCFA', 'LBR', 1, 'R', true);
        
        $pdf->SetTextColor(0);
        $pdf->Ln(8);

        // Ajuster les largeurs des colonnes pour donner plus d'espace aux motifs
        $w = array(25, 40, 85, 55);
        
        $pdf->Cell($w[0], 8, 'Date', 1, 0, 'C', true);
        $pdf->Cell($w[1], 8, 'Type', 1, 0, 'C', true);
        $pdf->Cell($w[2], 8, 'Motifs', 1, 0, 'C', true);
        $pdf->Cell($w[3], 8, 'Montant', 1, 1, 'C', true);

        // Données
        $pdf->SetFont('Arial', '', 10);
        $fill = false;

        foreach ($transactions as $transaction) {
            $motif = $transaction['motifs'];

            // Alterner entre blanc et gris très clair
            $pdf->SetFillColor(248, 248, 248);
            
            // Date et Type en taille normale
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($w[0], 7, date('d/m/y', strtotime($transaction['date_transaction'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 7, $pdf->CleanString($transaction['type_transaction']), 1, 0, 'C', $fill);
            
            // Motifs en taille légèrement réduite pour les longs textes
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($w[2], 7, $pdf->CleanString($motif), 1, 0, 'L', $fill);
            
            // Montant en taille normale
            $pdf->SetFont('Arial', '', 10);
            // Afficher le montant en vert pour les entrées et en rouge pour les sorties
            if ($transaction['type_transaction'] == 'approvisionnement') {
                $pdf->SetTextColor(0, 128, 0);
                //$prefix = '+';
            } else {
                $pdf->SetTextColor(200, 0, 0);
                //$prefix = '-';
            }
            $pdf->Cell($w[3], 7, number_format($transaction['montant'], 0, ',', ' ') . ' FCFA', 1, 1, 'C', $fill);
            $pdf->SetTextColor(0);
            
            $fill = !$fill;
        }

        // Ligne de séparation plus épaisse avant les totaux
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Cell(0, 0, '', 'LR', 1);
        
        // Totaux avec fond gris plus foncé
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('Arial', 'B', 10);
        
        // Solde initial
        $pdf->Cell(array_sum($w)-55, 8, 'Solde Initial:', 1, 0, 'R', true);
        if ($solde_initial >= 0) {
            $pdf->SetTextColor(0, 128, 0);
           // $prefix = '+';
        } else {
            $pdf->SetTextColor(200, 0, 0);
            //$prefix = '';
        }
        $pdf->Cell(55, 8, number_format($solde_initial, 0, ',', ' ') . ' FCFA', 1, 1, 'R', true);
        
        // Total des entrées
        $pdf->SetTextColor(0);
        $pdf->Cell(array_sum($w)-55, 8, 'Total Approvisionnements:', 1, 0, 'R', true);
        $pdf->SetTextColor(0, 128, 0);
        $pdf->Cell(55, 8, number_format($total_entrees, 0, ',', ' ') . ' FCFA', 1, 1, 'R', true);
        
        // Total des sorties
        $pdf->SetTextColor(0);
        $pdf->Cell(array_sum($w)-55, 8, 'Total Paiements:', 1, 0, 'R', true);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(55, 8, number_format($total_sorties, 0, ',', ' ') . ' FCFA', 1, 1, 'R', true);
        
        // Ligne de séparation
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->SetLineWidth(0.5);
        $pdf->Cell(array_sum($w), 0, '', 'T');
        $pdf->Ln();
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0);

        // Solde final avec fond vert clair
        $pdf->SetFillColor(240, 255, 240);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(array_sum($w)-55, 8, 'SOLDE FINAL:', 1, 0, 'R', true);
        $pdf->SetFont('Arial', 'B', 12);
        $solde_final = $solde_initial + $total_entrees - $total_sorties;
        //$prefix = $solde_final >= 0 ? '+' : '';
        $pdf->SetTextColor($solde_final >= 0 ? 0 : 200, $solde_final >= 0 ? 128 : 0, 0);
        $pdf->Cell(55, 8, number_format($solde_final, 0, ',', ' ') . ' FCFA', 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        // Signature
        $pdf->Ln(15);
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, utf8_decode('Fait à Divo, le ') . date('d/m/y'), 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, 'UNIPALM COOP-CA', 0, 1, 'R');

        // Génération du PDF
        $file_name = 'Transactions_' . date('d-m-Y', strtotime($date_debut)) . '_au_' . date('d-m-Y', strtotime($date_fin)) . '.pdf';
        
        // Vider le buffer avant de générer le PDF
        if (ob_get_length()) {
            ob_clean();
        }
        
        // Envoi du PDF
        $pdf->Output('I', $file_name);
        exit;
    } else {
        echo "<script>alert('Aucune transaction trouvée pour cette période.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Paramètres manquants.'); window.history.back();</script>";
    exit;
}
?>
