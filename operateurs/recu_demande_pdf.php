<?php
// Désactiver l'affichage des erreurs pour éviter toute sortie avant le PDF
error_reporting(0);
ini_set('display_errors', 0);

// S'assurer qu'aucune sortie n'a été envoyée
if (headers_sent()) {
    die("Impossible d'envoyer le PDF : des données ont déjà été envoyées au navigateur.");
}

// Vider tout tampon de sortie existant
ob_clean();

require_once '../inc/functions/connexion.php';
require_once '../fpdf/fpdf.php';
//session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Utilisateur non connecté");
}

// Vérifier si l'ID du reçu est fourni
if (!isset($_GET['id'])) {
    die("ID du reçu manquant");
}

// Récupérer les informations du reçu
$stmt = $conn->prepare("
    SELECT 
        r.*,
        d.montant as montant_total,
        d.montant_payer,
        d.numero_demande as numero_demande_original,
        d.date_demande,
        CONCAT(u.nom, ' ', u.prenoms) as caissier_name
    FROM recus_demandes r
    INNER JOIN demande_sortie d ON r.demande_id = d.id_demande
    INNER JOIN utilisateurs u ON r.caissier_id = u.id
    WHERE r.id = ?
");

$stmt->execute([$_GET['id']]);
$recu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recu) {
    die("Reçu non trouvé");
}

// En-têtes pour forcer l'affichage du PDF
header('Cache-Control: private');
header('Content-Type: application/pdf');
header('Content-Transfer-Encoding: binary');
header('Content-Disposition: inline; filename="Recu_' . $recu['numero_recu'] . '.pdf"');

// Fonction pour formater les montants
function formatMontant($montant) {
    if ($montant === null || $montant === '') return '0';
    return number_format($montant, 0, ',', ' ');
}

// Créer une classe personnalisée héritant de FPDF
class PDF extends FPDF {
    // Fonction pour convertir les caractères spéciaux
    protected function cleanText($text) {
        // Convertir les caractères spéciaux en ISO-8859-1 (LATIN1)
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
    }

    function Header() {
        // Logo
        if (file_exists('../dist/img/logo.png')) {
            $this->Image('../dist/img/logo.png', 10, 10, 30);
        }
        
        // Titre
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 15, $this->cleanText('REÇU DE PAIEMENT'), 0, 1, 'C');
        
        // Ligne de séparation
        $this->Line(10, 30, 200, 30);
        
        // Espace après l'en-tête
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Surcharge de la méthode Cell pour gérer automatiquement les caractères spéciaux
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->cleanText($txt), $border, $ln, $align, $fill, $link);
    }

    // Surcharge de la méthode Text pour gérer automatiquement les caractères spéciaux
    function Text($x, $y, $txt) {
        parent::Text($x, $y, $this->cleanText($txt));
    }

    // Surcharge de la méthode Write pour gérer automatiquement les caractères spéciaux
    function Write($h, $txt, $link='') {
        parent::Write($h, $this->cleanText($txt), $link);
    }
}

// Créer un nouveau document PDF
$pdf = new PDF();

// Définir les marges (gauche, haut, droite) en mm
$pdf->SetMargins(10, 40, 10);

// Ajouter une nouvelle page
$pdf->AddPage();

// Position après l'en-tête
$pdf->SetY(40);

// Informations de la demande
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Numéro de demande:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $recu['numero_demande_original'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Date de la demande:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($recu['date_demande'])), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Montant total:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, number_format($recu['montant_total'], 0, ',', ' ') . ' FCFA', 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Montant déjà payé:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, number_format($recu['montant_payer'], 0, ',', ' ') . ' FCFA', 0, 1);

// Ligne de séparation
$pdf->Line(10, $pdf->GetY() + 5, 200, $pdf->GetY() + 5);
$pdf->Ln(10);

// Informations du paiement
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Date de paiement:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, date('d/m/Y H:i', strtotime($recu['date_paiement'])), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Montant payé:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, number_format($recu['montant'], 0, ',', ' ') . ' FCFA', 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Source de paiement:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $recu['source_paiement'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Caissier:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $recu['caissier_name'], 0, 1);

// Espace pour la signature
$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, 'Signature du caissier:', 0, 1, 'R');
$pdf->Cell(0, 20, '', 0, 1, 'R');
$pdf->Cell(0, 10, '_______________________', 0, 1, 'R');

// Générer le PDF avec gestion des erreurs
try {
    ob_clean(); // Nettoyer le buffer de sortie
    $pdf->Output('I', 'recu_' . $recu['numero_demande'] . '.pdf');
} catch (Exception $e) {
    die("Erreur lors de la génération du PDF : " . $e->getMessage());
}
