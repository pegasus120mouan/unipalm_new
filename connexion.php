<?php
session_start();

// Activer l'affichage des erreurs en local
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Paramètres de connexion à la base de données
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','unipalmci_gestion_new');

// Fonction pour établir la connexion à la base de données
function getConnexion() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->exec("SET NAMES utf8mb4");
            $conn->exec("SET CHARACTER SET utf8mb4");
            $conn->exec("SET character_set_connection=utf8mb4");
        } catch (PDOException $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
            throw new Exception("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
        }
    }
    
    return $conn;
}

// Pour la compatibilité avec le code existant, on crée aussi une connexion globale
try {
    $conn = getConnexion();
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors de la connexion à la base de données.";
    header('Location: login.php');
    exit;
}
