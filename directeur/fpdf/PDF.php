<?php
require_once 'fpdf.php';

class PDF extends FPDF {
    protected $angle = 0;
    
    function __construct() {
        parent::__construct();
        $this->angle = 0;
    }

    function Header() {}
    function Footer() {}

    function genererRecu($y_start, $logo_path, $paiement, $numero_recu, $numero_document, $type_document, $montant_total_format, $montant_actuel_format, $montant_deja_paye_format, $reste_a_payer_format) {
        // Logo
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, $y_start, 30);
        }
        
        // Titre
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(0, $y_start + 5);
        $this->Cell(210, 10, 'Reçu de Paiement', 0, 1, 'C');
    
        // Numéro de reçu à droite
        $this->SetFont('Arial', '', 9);
        $this->SetXY(160, $y_start + 5);
        $this->Cell(50, 6, 'N° ' . $numero_recu, 0, 1, 'R');
    
        // Informations générales
        $this->SetFont('Arial', '', 10);
        $this->SetXY(60, $y_start + 18);
        $this->Cell(90, 6, 'N° ' . $type_document . ': ' . $numero_document, 0, 1, 'C');
    
        $this->SetXY(60, $y_start + 24);
        $this->Cell(90, 6, 'Date: ' . date('d/m/Y H:i'), 0, 1, 'C');
    
        // Informations Agent
        $y = $y_start + 40;
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, 'Informations Agent', 0, 1, 'L');
    
        $this->SetFont('Arial', '', 10);
        $y += 8;
        $this->SetXY(10, $y);
        $this->Cell(40, 6, "Nom de l'agent:", 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $paiement['agent_nom_complet'], 0, 1, 'L');
    
        $this->SetFont('Arial', '', 10);
        $y += 6;
        $this->SetXY(10, $y);
        $this->Cell(40, 6, 'Contact:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $paiement['agent_contact'], 0, 1, 'L');
    
        // Informations Transport (seulement si c'est un ticket)
        if (isset($paiement['nom_usine']) && $type_document == 'ticket') {
            $y += 12;
            $this->SetFont('Arial', 'B', 12);
            $this->SetXY(10, $y);
            $this->Cell(190, 6, 'Informations Transport', 0, 1, 'L');
    
            $this->SetFont('Arial', '', 10);
            $y += 8;
            $this->SetXY(10, $y);
            $this->Cell(40, 6, 'Usine:', 0, 0, 'L');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(150, 6, $paiement['nom_usine'], 0, 1, 'L');
    
            $this->SetFont('Arial', '', 10);
            $y += 6;
            $this->SetXY(10, $y);
            $this->Cell(40, 6, 'Véhicule:', 0, 0, 'L');
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
        $this->Cell(40, 6, 'Montant payé:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 6, $montant_actuel_format . ' FCFA', 0, 1, 'L');
    
        $y += 6;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(40, 6, 'Reste à payer:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(150, 6, $reste_a_payer_format . ' FCFA', 0, 1, 'L');
    
        // Caissier
        $y += 15;
        $this->SetFont('Arial', '', 10);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, 'Caissier: ' . $paiement['utilisateur_nom_complet'], 0, 1, 'C');
    
        $y += 6;
        $this->SetFont('Arial', 'I', 8);
        $this->SetXY(10, $y);
        $this->Cell(190, 6, 'Ce reçu est généré électroniquement et ne nécessite pas de signature.', 0, 1, 'C');
    }
}