<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 

// Récupération des données soumises via le formulaire
$id_engin = $_POST['id'];
$annee_fabrication = $_POST['annee_fabrication'];
$plaque_immatriculation = $_POST['plaque_immatriculation'];
$numero_chassis = $_POST['numero_chassis'];
$couleur = $_POST['couleur'];
$marque = $_POST['marque'];
$type_engin = $_POST['type_engin'];
$date_ajout = $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE engins
        SET type_engin = :type_engin,
            annee_fabrication = :annee_fabrication, 
            plaque_immatriculation = :plaque_immatriculation, 
            numero_chassis = :numero_chassis,
            couleur = :couleur, 
            date_ajout = :date_ajout,
            marque = :marque 
        WHERE engin_id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id' => $id_engin,
    ':type_engin' => $type_engin,
    ':annee_fabrication' => $annee_fabrication,
    ':plaque_immatriculation' => $plaque_immatriculation,
    ':numero_chassis' => $numero_chassis,
    ':couleur' => $couleur,
    ':date_ajout' => $date_ajout,
    ':marque' => $marque
));

// Redirection vers une page de confirmation ou de retour
if ($query_execute) {
    // $_SESSION['message'] = "Mise à jour réussie";
    $_SESSION['popup'] = true;
    header('Location: ../listes_engins.php');
    exit(0);
} else {
    $_SESSION['delete_pop'] = true;
    header('Location: ../listes_engins.php');
    exit(0);
}

?>
