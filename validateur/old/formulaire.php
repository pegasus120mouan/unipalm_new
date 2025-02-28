<?php
// Configuration Twilio
$account_sid = 'AC0749c0929722b635ef2c368419ef1b53';
$auth_token = 'bfc10a113e798c49e5991bb9b7a2bc28';
$twilio_number = '+19195827663';

// Traitement du formulaire lorsqu'il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le message depuis le champ textarea
    $messageContent = $_POST['message'];

    // Envoi du SMS avec Twilio
    require_once '../vendor/autoload.php'; // Chargez la bibliothèque Twilio PHP

    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    $to_number = '+2250787703000'; // Remplacez par le numéro de téléphone réel

    try {
        $message = $client->messages->create(
            $to_number,
            [
                'from' => $twilio_number,
                'body' => $messageContent,
            ]
        );

        // Vous pouvez ajouter ici la logique de traitement supplémentaire si nécessaire
        echo "Message envoyé avec succès!";
    } catch (Exception $e) {
        echo 'Erreur d\'envoi de SMS : ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'envoi de message</title>
</head>
<body>
    <h2>Formulaire d'envoi de message</h2>

    <form method="post" action="">
        <label for="message">Message à envoyer :</label><br>
        <textarea id="message" name="message" rows="4" cols="50"></textarea><br>

        <input type="submit" value="Envoyer">
    </form>
</body>
</html>
