<?php
function writeLog($message) {
    $logFile = __DIR__ . '/../../logs/debug.log';
    $logDir = dirname($logFile);
    
    // Créer le dossier logs s'il n'existe pas
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Format du message
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] $message\n";
    
    // Écrire dans le fichier
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}
