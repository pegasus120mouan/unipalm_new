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
    protected function cleanText($text) {
        // Convertir les caractères spéciaux en ISO-8859-1 (LATIN1)
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
    }

    function Header() {
        // Logo
        if (file_exists('../dist/img/logo.png')) {
            $this->Image('../dist/img/logo.png', 15, 5, 35);
        }
        
        // En-tête de l'entreprise
        $this->SetY(15);
        $this->SetX(55);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 8, $this->cleanText('UNIPALM COOP - CA'), 0, 1, 'C');
        
        $this->SetX(55);
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(68, 68, 68); // Gris foncé
        $this->Cell(0, 6, $this->cleanText('Societe Cooperative Agricole Unie pour le Palmier'), 0, 1, 'C');
        
        // Informations de contact avec icône
        $this->SetX(55);
        $this->SetFont('Arial', '', 10);
       
        // Titre du reçu avec fond noir
        $this->Ln(20);
        $this->SetFillColor(0, 0, 0); // Noir
        $this->SetTextColor(255, 255, 255); // Texte blanc
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, $this->cleanText('RECU DE PAIEMENT'), 0, 1, 'C', true);
        
        // Retour au texte noir pour le reste du document
        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-35);
        
        // Informations du footer
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(68, 68, 68); // Gris foncé
        $this->Cell(0, 4, $this->cleanText('Siege Social : Divo Quartier millionnaire non loin de l\'hotel Boya'), 0, 1, 'C');
        $this->Cell(0, 4, $this->cleanText('NCC : 2050R910 / TÉL : (00225) 27 34 75 92 36 / 07 49 17 16 32'), 0, 1, 'C');
        
        // Séparateur
        $this->Cell(0, 2, '', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 4, $this->cleanText('Ce reçu est un document officiel de UNIPALM COOP - CA'), 0, 1, 'C');
        
        // Numéro de page
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $this->cleanText($txt), $border, $ln, $align, $fill, $link);
    }

    function Text($x, $y, $txt) {
        parent::Text($x, $y, $this->cleanText($txt));
    }

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
$pdf->SetY(65);

// Cadre pour les informations
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetFillColor(249, 249, 249);
$pdf->SetLineWidth(0.3);
$pdf->Rect(10, $pdf->GetY(), 190, 50, 'DF');

// Informations de la demande
$pdf->SetY($pdf->GetY() + 5);
$leftMargin = 15;
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX($leftMargin);
$pdf->Cell(70, 8, 'Numéro de demande:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, $recu['numero_demande_original'], 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Date de la demande:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, date('d/m/Y', strtotime($recu['date_demande'])), 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Montant total:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, number_format($recu['montant_total'], 0, ',', ' ') . ' FCFA', 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Montant déjà payé:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, number_format($recu['montant_payer'], 0, ',', ' ') . ' FCFA', 0, 1);

$pdf->Ln(10);

// Cadre pour les informations de paiement
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetFillColor(249, 249, 249);
$pdf->Rect(10, $pdf->GetY(), 190, 50, 'DF');

// Informations du paiement
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Date de paiement:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, date('d/m/Y H:i', strtotime($recu['date_paiement'])), 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Montant payé:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, number_format($recu['montant'], 0, ',', ' ') . ' FCFA', 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Source de paiement:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, $recu['source_paiement'], 0, 1);

$pdf->SetX($leftMargin);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(70, 8, 'Caissier:', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, $recu['caissier_name'], 0, 1);

// Espace pour la signature
$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 10, 'Signature du caissier:', 0, 1, 'R');

// Cadre pour la signature
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(140, $pdf->GetY(), 60, 30, 'D');

// Générer le PDF avec gestion des erreurs
try {
    ob_clean(); // Nettoyer le buffer de sortie
    $pdf->Output('I', 'recu_' . $recu['numero_demande'] . '.pdf');
} catch (Exception $e) {
    die("Erreur lors de la génération du PDF : " . $e->getMessage());
}
