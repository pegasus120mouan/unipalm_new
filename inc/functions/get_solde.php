<?php
function getSoldeCaisse() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT COALESCE(MAX(solde), 0) as solde FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['solde']);
    } catch (Exception $e) {
        writeLog("Erreur lors de la récupération du solde : " . $e->getMessage());
        return 0;
    }
}
