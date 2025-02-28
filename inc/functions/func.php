<?php

//require('connexion.php');

function enregistrerUtilisateur($nomUtilisateur, $prenomsUtilisateur, $villeUtilisateur) {
    try {
        $connexion = new PDO("mysql:host=localhost;dbname=ovldelivery_db", "root", "");
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Utilisez une requête préparée pour insérer l'utilisateur dans la base de données
        $requete = $connexion->prepare("INSERT INTO pegasus (nom_utilisateur, prenoms_utilisateur,ville_utilisateur) VALUES (:nomUtilisateur, :motDePasse)");
        $requete->bindParam(':nom_utilisateur', $nomUtilisateur);
        $requete->bindParam(':prenoms_utilisateur', $prenomsUtilisateur);
        $requete->bindParam(':ville_utilisateur', $villeUtilisateur);

        $requete->execute();

        echo "Utilisateur enregistré avec succès.";
    } catch (PDOException $e) {
        echo "Erreur d'enregistrement de l'utilisateur : " . $e->getMessage();
    }
}




?>