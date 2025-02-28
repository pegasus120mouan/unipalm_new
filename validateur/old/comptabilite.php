<?php
if (isset($_POST['client']) && isset($_POST['date'])) {
    $client = $_POST['client'];
    $date = $_POST['date'];

    // Étape 1 : Etablir la connexion à la base de données
    $serveur = "localhost";
    $nomUtilisateur = "root";
    $motDePasse = "";
    $nomBaseDeDonnees = "ovldelivery_db";

    try {
        $connexion = new PDO("mysql:host=$serveur;dbname=$nomBaseDeDonnees", $nomUtilisateur, $motDePasse);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connexion -> exec("set names utf8");

        // Étape 2 : Exécuter la requête SQL pour récupérer le total en fonction du client et de la date
        $sql = "SELECT SUM(cout_reel) AS total_commandes FROM commandes WHERE client = :client and date = :date and statut='Livré'";
        $requete = $connexion->prepare($sql);
        $requete->bindParam(':client', $client);
        $requete->bindParam(':date', $date);
        $requete->execute();
        $resultat = $requete->fetch(PDO::FETCH_ASSOC);

        // Insérer le résultat dans la table "total_client"
        if ($resultat) {
            $total = $resultat['total_commandes'];
            
            $insertSql = "INSERT INTO total_commandes (total_montant,nom_client, date_commande) VALUES (:total, :client, :date)";
            $insertRequete = $connexion->prepare($insertSql);
            $insertRequete->bindParam(':total', $total);
            $insertRequete->bindParam(':client', $client);
            $insertRequete->bindParam(':date', $date);
            $insertRequete->execute();

            echo "Total des commandes pour le client $client à la date $date : $total. Résultat inséré dans la table total_client.";
        } else {
            echo "Aucun enregistrement trouvé pour le client $client à la date $date.";
        }

        // Étape 3 : Fermer la connexion à la base de données
        $connexion = null;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
} else {
    echo "Veuillez sélectionner un client et une date.";
}
