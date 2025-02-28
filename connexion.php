<?php
// Paramètres de connexion à la base de données
$host = 'localhost';      // Hôte de la base de données
$dbname = 'unipalm_db';  // Nom de la base de données
$username = 'root';       // Nom d'utilisateur MySQL
$password = '';          // Mot de passe MySQL (vide par défaut pour Laragon)

try {
    // Création de la connexion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuration des attributs PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Message de succès (à commenter en production)
    // echo "Connexion à la base de données réussie";
    
} catch(PDOException $e) {
    // Log l'erreur dans un fichier de log
    error_log("Erreur de connexion à la base de données: " . $e->getMessage(), 0);
    
    // Message d'erreur pour l'utilisateur
    $error_message = "Désolé, nous ne pouvons pas établir la connexion à la base de données pour le moment. Veuillez réessayer plus tard ou contacter l'administrateur.";
    
    // En environnement de développement, ajouter les détails de l'erreur
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $error_message .= "\nDétails de l'erreur : " . $e->getMessage();
    }
    
    die($error_message);
}
