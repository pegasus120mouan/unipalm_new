<?php
// Désactiver l'affichage des erreurs pour éviter toute sortie avant le PDF
error_reporting(0);
ini_set('display_errors', 0);

// S'assurer qu'aucune sortie n'a été envoyée
if (headers_sent()) {
    die("Impossible d'envoyer le PDF : des données ont déjà été envoyées au navigateur.");
}

// Vider tout tampon de sortie existant
while (ob_get_level()) {
    ob_end_clean();
}

// Définition du chemin racine
$root_path = dirname(dirname(__FILE__));

// En-têtes pour forcer l'affichage du PDF
header('Cache-Control: public');
header('Content-Type: application/pdf');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Content-Disposition: inline; filename="Recu_' . date('Ymd') . sprintf("%04d", rand(1, 9999)) . '.pdf"');

require($root_path . '/fpdf/fpdf.php');
require_once $root_path . '/inc/functions/connexion.php';

// Fonction pour formater les montants
function formatMontant($montant) {
    if ($montant === null || $montant === '') return '0';
    return number_format($montant, 0, ',', ' ');
}

// Créer une classe personnalisée héritant de FPDF
class PDF extends FPDF {
    protected $angle = 0; // Initialisation de la propriété angle
    
    function __construct() {
        parent::__construct();
        $this->angle = 0;
    }

    // Surcharge des méthodes Header et Footer pour les désactiver
    function Header() {}
    function Footer() {}

    function genererRecu($y_start, $logo_path, $paiement, $numero_recu, $numero_document, $type_document, $montant_total_format, $montant_actuel_format, $montant_deja_paye_format, $reste_a_payer_format) {
        // Logo
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, $y_start, 30); // Logo à gauche
        }
        
        // Titre
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(0, $y_start + 5);
        $this->Cell(210, 10, utf8_decode('Reçu de Paiement'), 0, 1, 'C');
    
        // Numéro de reçu à droite
        $this->SetFont('Arial', '', 9);
        $this->SetXY(160, $y_start + 5);
        $this->Cell(50, 6, utf8_decode('N° ' . $numero_recu), 0, 1, 'R');

    
        // Informations générales
        $this->SetFont('Arial', '', 10);
        $this->SetXY(60, $y_start + 18);
        $this->Cell(90, 6, utf8_decode('N° ' . $type_document) . ': ' . $numero_document, 0, 1, 'C');
    
        $this->SetXY(60, $y_start + 24);
        $this->Cell(90, 6, 'Date: ' . date('d/m/Y H:i'), 0, 1, 'C');
    
        // Informations Agent
        $y = $y_start + 40;
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, utf8_decode('Informations Agent'), 0, 1, 'L');
    
        $this->SetFont('Arial', '', 10);
        $y += 8;
        $this->SetXY(10, $y);
        $this->Cell(40, 6, utf8_decode('Nom de l\'agent:'), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, utf8_decode($paiement['agent_nom']), 0, 1, 'L');
    
        $this->SetFont('Arial', '', 10);
        $y += 6;
        $this->SetXY(10, $y);
        $this->Cell(40, 6, 'Contact:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $paiement['agent_contact'], 0, 1, 'L');
    
        // Informations Transport
        if (isset($paiement['nom_usine'])) {
            $y += 12;
            $this->SetFont('Arial', 'B', 12);
            $this->SetXY(10, $y);
            $this->Cell(190, 6, 'Informations Transport', 0, 1, 'L');
    
            $this->SetFont('Arial', '', 10);
            $y += 8;
            $this->SetXY(10, $y);
            $this->Cell(40, 6, 'Usine:', 0, 0, 'L');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(150, 6, utf8_decode($paiement['nom_usine']), 0, 1, 'L');
    
            $this->SetFont('Arial', '', 10);
            $y += 6;
            $this->SetXY(10, $y);
            $this->Cell(40, 6, utf8_decode('Véhicule:'), 0, 0, 'L');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(150, 6, $paiement['matricule_vehicule'], 0, 1, 'L');
        }
    
        // Ligne de séparation
        $y += 12;
        $this->Line(10, $y, 200, $y);
    
        // Montants
        $y += 5;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(40, 6, 'Montant total:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $montant_total_format . ' FCFA', 0, 1, 'L');
    
        $y += 6;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(40, 6, utf8_decode('Montant payé:'), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $montant_actuel_format . ' FCFA', 0, 1, 'L');
    
        $y += 6;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(40, 6, utf8_decode('Reste à payer:'), 0, 0, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(150, 6, $reste_a_payer_format . ' FCFA', 0, 1, 'L');
    
        // Caissier
        $y += 15;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, 'Caissier: ' . utf8_decode($paiement['caissier_nom']), 0, 1, 'C');
    
        $y += 6;
        $this->SetFont('Arial', 'I', 8);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, utf8_decode('Ce reçu est généré électroniquement et ne nécessite pas de signature.'), 0, 1, 'C');
    }
    
    
    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
    
    function Rotate($angle, $x=-1, $y=-1) {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
}

// Vérifier si l'ID du paiement est fourni
if (!isset($_GET['id_ticket']) && !isset($_GET['id_bordereau'])) {
    header("Location: paiements.php");
    exit;
}

// Mode réimpression : on utilise la table recus_paiements
$reimprimer = isset($_GET['reimprimer']) && $_GET['reimprimer'] == 1;

if ($reimprimer) {
    // Récupérer le dernier reçu pour ce document
    if (isset($_GET['id_ticket'])) {
        $stmt = $conn->prepare("
            SELECT * FROM recus_paiements 
            WHERE type_document = 'ticket' AND id_document = ? 
            ORDER BY date_creation DESC LIMIT 1
        ");
        $stmt->execute([$_GET['id_ticket']]);
    } else {
        $stmt = $conn->prepare("
            SELECT * FROM recus_paiements 
            WHERE type_document = 'bordereau' AND id_document = ? 
            ORDER BY date_creation DESC LIMIT 1
        ");
        $stmt->execute([$_GET['id_bordereau']]);
    }
    
    $recu = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recu) {
        header("Location: paiements.php");
        exit;
    }
    
    // Utiliser les données du reçu
    $montant_actuel = $recu['montant_paye'];
    $montant_total = $recu['montant_total'];
    $montant_deja_paye = $recu['montant_precedent'];
    $reste_a_payer = $recu['reste_a_payer'];
    $numero_recu = $recu['numero_recu'];
    
    $paiement = [
        'agent_nom' => $recu['nom_agent'],
        'agent_contact' => $recu['contact_agent'],
        'nom_usine' => $recu['nom_usine'],
        'matricule_vehicule' => $recu['matricule_vehicule'],
        'caissier_nom' => $recu['nom_caissier']
    ];
    
    $numero_document = $recu['numero_document'];
    $type_document = ucfirst($recu['type_document']);
} else {
    // Nouveau reçu : vérifier la session
    if (!isset($_SESSION['montant_paiement']) || !isset($_SESSION['numero_recu'])) {
        header("Location: paiements.php");
        exit;
    }

    $montant_actuel = floatval($_SESSION['montant_paiement']);
    $numero_recu = $_SESSION['numero_recu'];
    unset($_SESSION['montant_paiement']);
    unset($_SESSION['numero_recu']);

    // Récupérer les informations du paiement
    if (isset($_GET['id_ticket'])) {
        $stmt = $conn->prepare("
            SELECT 
                t.*,
                CONCAT(a.nom, ' ', a.prenom) as agent_nom,
                a.contact as agent_contact,
                us.nom_usine,
                v.matricule_vehicule,
                CONCAT(u.nom, ' ', u.prenoms) as caissier_nom
            FROM tickets t
            LEFT JOIN agents a ON t.id_agent = a.id_agent
            LEFT JOIN usines us ON t.id_usine = us.id_usine
            LEFT JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
            WHERE t.id_ticket = ?
        ");
        $stmt->execute([$_GET['id_ticket']]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numero_document = $paiement['numero_ticket'];
        $type_document = 'Ticket';
        
        // Calculer les montants
        $montant_total = floatval($paiement['montant_paie']);
        $montant_deja_paye = floatval($paiement['montant_payer']) - $montant_actuel;
        $reste_a_payer = $montant_total - floatval($paiement['montant_payer']);
    } else {
        $stmt = $conn->prepare("
            SELECT 
                b.*,
                CONCAT(a.nom, ' ', a.prenom) as agent_nom,
                a.contact as agent_contact,
                CONCAT(u.nom, ' ', u.prenoms) as caissier_nom
            FROM bordereau b
            LEFT JOIN agents a ON b.id_agent = a.id_agent
            LEFT JOIN utilisateurs u ON b.id_utilisateur = u.id
            WHERE b.id_bordereau = ?
        ");
        $stmt->execute([$_GET['id_bordereau']]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numero_document = $paiement['numero_bordereau'];
        $type_document = 'Bordereau';
        
        // Calculer les montants
        $montant_total = floatval($paiement['montant_total']);
        $montant_deja_paye = floatval($paiement['montant_payer']) - $montant_actuel;
        $reste_a_payer = $montant_total - floatval($paiement['montant_payer']);
    }
}

// Si le paiement n'existe pas
if (!$paiement) {
    header("Location: paiements.php");
    exit;
}

// S'assurer que les montants ne sont pas négatifs
$montant_deja_paye = max(0, $montant_deja_paye);
$reste_a_payer = max(0, $reste_a_payer);

// Formater les montants
$montant_total_format = formatMontant($montant_total);
$montant_actuel_format = formatMontant($montant_actuel);
$montant_deja_paye_format = formatMontant($montant_deja_paye);
$reste_a_payer_format = formatMontant($reste_a_payer);

// Créer le PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Chemin vers le logo
$logo_path = $root_path . '/dist/img/logo.png';

// Générer deux exemplaires
$pdf->genererRecu(10, $logo_path, $paiement, $numero_recu, $numero_document, $type_document, $montant_total_format, $montant_actuel_format, $montant_deja_paye_format, $reste_a_payer_format);
$pdf->genererRecu(150, $logo_path, $paiement, $numero_recu, $numero_document, $type_document, $montant_total_format, $montant_actuel_format, $montant_deja_paye_format, $reste_a_payer_format);

// Sortie du PDF
$pdf->Output('I', 'Recu_' . $numero_recu . '.pdf');
?>
