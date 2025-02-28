<?php 
session_start(); 

// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','unipalm_db');

// Fonction pour établir la connexion à la base de données
function getConnexion() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("SET NAMES utf8mb4");
            $conn->exec("SET CHARACTER SET utf8mb4");
            $conn->exec("SET character_set_connection=utf8mb4");
        } catch (PDOException $e) {
            exit("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Pour la compatibilité avec le code existant, on crée aussi une connexion globale
try {
    $conn = getConnexion();
} catch (PDOException $e) {
    exit("Erreur : " . $e->getMessage());
}
?>