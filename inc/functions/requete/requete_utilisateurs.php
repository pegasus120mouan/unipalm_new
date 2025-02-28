<?php
     
function getUtilisateurs($conn) {
    $stmt = $conn->prepare(
        "SELECT * from utilisateurs"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function searchUtilisateursByLogin($conn, $login) {
    $stmt = $conn->prepare(
        "SELECT * FROM utilisateurs WHERE login LIKE :login"
    );
    
    $stmt->execute(['login' => "%$login%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>