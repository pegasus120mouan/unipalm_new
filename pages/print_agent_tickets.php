<?php
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

require($root_path . '/fpdf/fpdf.php');
require_once $root_path . '/inc/functions/connexion.php';

if (!isset($_POST['id_chef']) || empty($_POST['id_chef']) || 
    !isset($_POST['date_debut']) || empty($_POST['date_debut']) ||
    !isset($_POST['date_fin']) || empty($_POST['date_fin'])) {
    echo "<script>alert('Veuillez remplir tous les champs.'); window.history.back();</script>";
    exit;
}

// Récupération des paramètres
$params = [
    'id_chef' => intval($_POST['id_chef']),
    'date_debut' => $_POST['date_debut'] . ' 00:00:00',
    'date_fin' => $_POST['date_fin'] . ' 23:59:59'
];

// Construire la requête de base
$query = "SELECT 
    t.*, 
    CONCAT(a.nom, ' ', a.prenom) AS nom_agent, 
    v.matricule_vehicule, 
    v.type_vehicule, 
    u.nom_usine AS usine_nom,
    c.nom as nom_chef,
    c.prenoms as prenoms_chef
FROM tickets t
INNER JOIN agents a ON t.id_agent = a.id_agent
INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
INNER JOIN usines u ON t.id_usine = u.id_usine
INNER JOIN chef_equipe c ON a.id_chef = c.id_chef
WHERE a.id_chef = :id_chef
AND t.date_ticket BETWEEN :date_debut AND :date_fin";

// Ajouter le filtre d'agent si nécessaire
if (isset($_POST['id_agent']) && !empty($_POST['id_agent']) && $_POST['id_agent'] !== '0') {
    $query .= " AND t.id_agent = :id_agent";
    $params['id_agent'] = intval($_POST['id_agent']);
}

// Ajouter le filtre d'usine si nécessaire
if (isset($_POST['id_usine']) && !empty($_POST['id_usine']) && $_POST['id_usine'] !== '0') {
    $query .= " AND t.id_usine = :id_usine";
    $params['id_usine'] = intval($_POST['id_usine']);
}

// Ajouter l'ordre de tri
$query .= " ORDER BY u.nom_usine ASC, t.date_ticket DESC, a.nom, a.prenom";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tickets)) {
        echo "<script>alert('Aucun ticket trouvé pour les critères sélectionnés.'); window.history.back();</script>";
        exit;
    }

    class PDF extends FPDF {
        function Header() {
            $logo_path = dirname(dirname(__FILE__)) . '/dist/img/logo.png';
            if (file_exists($logo_path)) {
                $this->Image($logo_path, 10, 10, 30);
            }
            
            // Titre de l'entreprise en noir
            $this->SetTextColor(0);
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'UNIPALM COOP - CA', 0, 1, 'C');
            
            // Sous-titre en noir
            $this->SetTextColor(0);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 5, utf8_decode('Société Coopérative Agricole Unie pour le Palmier'), 0, 1, 'C');
            
            $this->Ln(15);
        }

        function Footer() {
            $this->SetY(-20);
            
            // Ligne verte
            $this->SetDrawColor(144, 238, 144);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            
            // Texte en noir
            $this->SetTextColor(0);
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 5, 'Siege Social : Divo Quartier millionnaire non loin de l\'hotel Boya', 0, 1, 'C');
            $this->Cell(0, 5, 'NCC : 2050R910 / TEL : (00225) 27 34 75 92 36 / 07 49 17 16 32', 0, 1, 'C');
        }

        function CleanString($str) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 35);

    // Titre du document
    $pdf->SetFont('Arial', 'BU', 16);
    $pdf->SetTextColor(0);
    $pdf->Cell(0, 12, 'LISTE DES TICKETS PAR AGENT', 0, 1, 'C', false);
    $pdf->Ln(5);

    // Informations du chef d'équipe
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(50, 8, utf8_decode('Chef d\'équipe:'), 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, $pdf->CleanString(strtoupper($tickets[0]['nom_chef'] . ' ' . $tickets[0]['prenoms_chef'])), 0, 1);

    // Période
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(50, 8, utf8_decode('Période du:'), 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, date('d/m/Y', strtotime($_POST['date_debut'])) . ' au ' . date('d/m/Y', strtotime($_POST['date_fin'])), 0, 1);
    $pdf->Ln(5);

    // En-têtes du tableau
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetDrawColor(0);

    $w = array(20, 50, 55, 25, 25, 20);
    
    $pdf->Cell($w[0], 8, 'Date', 1, 0, 'C', true);
    $pdf->Cell($w[1], 8, 'Agent', 1, 0, 'C', true);
    $pdf->Cell($w[2], 8, utf8_decode('N° Ticket'), 1, 0, 'C', true);
    $pdf->Cell($w[3], 8, 'Usine', 1, 0, 'C', true);
    $pdf->Cell($w[4], 8, utf8_decode('Véhicule'), 1, 0, 'C', true);
    $pdf->Cell($w[5], 8, 'Poids (kg)', 1, 1, 'C', true);

    // Données
    $pdf->SetFont('Arial', '', 8);
    $total_poids = 0;
    $nombre_tickets = 0;
    $fill = true;

    foreach ($tickets as $ticket) {
        $pdf->Cell($w[0], 7, date('d/m/y', strtotime($ticket['date_ticket'])), 1, 0, 'C', $fill);
        $pdf->Cell($w[1], 7, $pdf->CleanString($ticket['nom_agent']), 1, 0, 'L', $fill);
        $pdf->Cell($w[2], 7, $ticket['numero_ticket'], 1, 0, 'C', $fill);
        $pdf->Cell($w[3], 7, $pdf->CleanString($ticket['usine_nom']), 1, 0, 'C', $fill);
        $pdf->Cell($w[4], 7, $pdf->CleanString($ticket['matricule_vehicule']), 1, 0, 'C', $fill);
        $pdf->Cell($w[5], 7, number_format($ticket['poids'], 0, ',', ' '), 1, 1, 'R', $fill);
        
        $total_poids += $ticket['poids'];
        $nombre_tickets++;
        $fill = !$fill;
    }

    // Total
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(array_sum($w)-30, 8, 'TOTAL (' . $nombre_tickets . ' tickets)', 1, 0, 'R', true);
    $pdf->Cell(30, 8, number_format($total_poids, 0, ',', ' '), 1, 1, 'R', true);

    // Signature
    $pdf->Ln(15);
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, utf8_decode('Fait à Divo, le ') . date('d/m/y'), 0, 1, 'R');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 10, 'UNIPALM COOP-CA', 0, 1, 'R');

    // Génération du PDF
    $file_name = 'Tickets_' . $pdf->CleanString($tickets[0]['nom_chef']) . '_' . date('d-m-Y') . '.pdf';
    
    // Vider le buffer avant de générer le PDF
    if (ob_get_length()) {
        ob_clean();
    }
    
    // Envoi du PDF
    $pdf->Output('I', $file_name);
    exit;
} catch (PDOException $e) {
    echo "<script>alert('Erreur lors de la récupération des données.'); window.history.back();</script>";
    exit;
}
?>
