<?php
// Inclure le fichier de connexion à la base de données
require_once '../inc/functions/connexion.php';

// Nombre d'enregistrements à afficher par page
$elementsParPage = 20;

// Récupérer le nombre total d'enregistrements dans la table commandes
$sqlTotal = "SELECT COUNT(*) AS total FROM commandes";

try {
    // Préparer la requête
    $stmtTotal = $conn->prepare($sqlTotal);

    // Exécuter la requête
    $stmtTotal->execute();

    // Récupérer le résultat
    $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    // Calculer le nombre total de pages
    $nombreDePages = ceil($resultTotal['total'] / $elementsParPage);
} catch (PDOException $e) {
    // Gérer les erreurs PDO ici
    echo 'Erreur PDO : ' . $e->getMessage();
}

// Récupérer le numéro de la page courante depuis l'URL
$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculer l'offset pour la requête SQL
$offset = ($pageCourante - 1) * $elementsParPage;

// Requête SQL pour récupérer les données paginées
$sqlData = "SELECT * FROM commandes LIMIT :offset, :elementsParPage";

try {
    // Préparer la requête
    $stmtData = $conn->prepare($sqlData);

    // Binder les paramètres
    $stmtData->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmtData->bindParam(':elementsParPage', $elementsParPage, PDO::PARAM_INT);

    // Exécuter la requête
    $stmtData->execute();

    // Récupérer les résultats
    $commandes = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    // Afficher les données
    foreach ($commandes as $commande) {
        // Afficher les données de chaque commande
        echo 'Commande ID : ' . $commande['id'] . '<br>';
        // ... afficher d'autres données ...
        echo '<hr>';
    }

    // Afficher la pagination
    for ($page = 1; $page <= $nombreDePages; $page++) {
        echo '<a href="?page=' . $page . '">' . $page . '</a> ';
    }
} catch (PDOException $e) {
    // Gérer les erreurs PDO ici
    echo 'Erreur PDO : ' . $e->getMessage();
}

// Fermer la connexion
$conn = null;
?>
