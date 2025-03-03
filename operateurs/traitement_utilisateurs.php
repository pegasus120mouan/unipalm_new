<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/verification_password.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $contact = $_POST['contact'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $retype_password = $_POST['retype_password'];
    $hasedpassword=hash('sha256',$password);


    if (!isPasswordComplex($password, $retype_password)) {
        $_SESSION['delete_pop'] = true;
        // Attendre 3 secondes avant la redirection
        sleep(3);
        header('Location: utilisateurs.php');
        exit(0);
    } 
    elseif (!isPhoneNumberValid($contact)) {
        $_SESSION['delete_pop'] = true;
        // Attendre 3 secondes avant la redirection
        sleep(3);
        header('Location: utilisateurs.php');
        exit(0);
    }
    else {
        $query = "INSERT INTO utilisateurs (nom, prenoms,contact,login,avatar,password,role) VALUES (:nom, :prenoms,:contact,:login,'default.jpg',:password,:role)";
        $query_run = $conn->prepare($query);
    
        $data = [
            ':nom' => $nom,
            ':prenoms' => $prenoms,
            ':contact' => $contact,
            ':login' => $login,
            ':password' => $hasedpassword,
            ':role' => $role,
        ];
        $query_execute = $query_run->execute($data);
    
        if($query_execute)
        {
            $_SESSION['popup'] = true;
            // Attendre 3 secondes avant la redirection
            sleep(3);
            header('Location: utilisateurs.php');
            exit(0);
        }
        else
        {
            $_SESSION['message'] = "Not Inserted";
            // Attendre 3 secondes avant la redirection
            sleep(3);
            header('Location: utilisateurs.php');
            exit(0);
        }
    }

    
}

// Suppression d'un utilisateur (méthode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_utilisateur = $_GET['id'];

    try {
        // Vérifier si l'utilisateur existe
        $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id_utilisateur]);
        $utilisateur = $stmt->fetch();

        if (!$utilisateur) {
            $_SESSION['popup'] = true;
            $_SESSION['message'] = "Utilisateur non trouvé !";
            $_SESSION['status'] = "error";
        } else {
            // Supprimer l'utilisateur
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $result = $stmt->execute([$id_utilisateur]);

            if ($result) {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Utilisateur supprimé avec succès !";
                $_SESSION['status'] = "success";
            } else {
                $_SESSION['popup'] = true;
                $_SESSION['message'] = "Erreur lors de la suppression de l'utilisateur";
                $_SESSION['status'] = "error";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['popup'] = true;
        $_SESSION['message'] = "Erreur : " . $e->getMessage();
        $_SESSION['status'] = "error";
    }

    header('Location: utilisateurs.php');
    exit;
}

?>