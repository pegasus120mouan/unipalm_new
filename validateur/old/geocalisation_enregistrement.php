<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Récupération des données du formulaire
        $id_engin = $_POST['id_engin'] ?? null;
        $id_utilisateur = $_POST['utilisateur_id'] ?? null;
        $date_enregistrement= date("Y-m-d");


        // Préparation de la requête SQL
        $query = "INSERT INTO position (engin_id, utilisateur_id,date_enregistrement) VALUES (:engin_id, :id_utilisateur,:date_enregistrement)";
        $query_run = $conn->prepare($query);

        // Lier les données avec la requête
        $data = [
            ':engin_id' => $id_engin,
            ':id_utilisateur' => $id_utilisateur,  // Utilisation correcte de $id_utilisateur
            ':date_enregistrement' => $date_enregistrement,  // Utilisation correcte de $id_utilisateur
        ];

        // Exécution de la requête
        $query_execute = $query_run->execute($data);

        if ($query_execute) {
            // Si l'insertion est réussie
            $_SESSION['popup'] = true;
            header('Location: listes_engins.php');
            exit(0);
        } else {
            // En cas d'échec de l'insertion
            throw new Exception("Échec de l'insertion dans la base de données.");
        }
    } catch (Exception $e) {
        // Gestion des erreurs
        $_SESSION['error_message'] = $e->getMessage();
        $_SESSION['delete_pop'] = true;
        header('Location: listes_engins.php');
        exit(0);
    }
}
?>
