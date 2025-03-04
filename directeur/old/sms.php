<?php
// Configuration Twilio
$account_sid = 'AC0749c0929722b635ef2c368419ef1b53';
$auth_token = 'bfc10a113e798c49e5991bb9b7a2bc28';
$twilio_number = '+19195827663';

// Configuration MySQL
$dsn = 'mysql:host=VOTRE_HOST;dbname=VOTRE_BASE_DE_DONNEES';
$username = 'root';
$password = '';

// Connexion à la base de données avec PDO
try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}

// Récupération des informations pour l'envoi du SMS depuis la base de données
$query = "SELECT * FROM messages_a_envoyer WHERE etat = 'en_attente' LIMIT 1";
$stmt = $pdo->query($query);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if ($message) {
    // Envoi du SMS avec Twilio
    require_once 'vendor/autoload.php'; // Chargez la bibliothèque Twilio PHP

    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    $to_number = $message['numero_destinataire'];
    $body = $message['contenu'];

    try {
        $message = $client->messages->create(
            $to_number,
            [
                'from' => $twilio_number,
                'body' => $body,
            ]
        );

        // Mettez à jour l'état du message dans la base de données
        $update_query = "UPDATE messages_a_envoyer SET etat = 'envoye' WHERE id = :id";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':id', $message['id']);
        $update_stmt->execute();
    } catch (Exception $e) {
        echo 'Erreur d\'envoi de SMS : ' . $e->getMessage();
    }
}

// Fermez la connexion à la base de données
$pdo = null;
?>
