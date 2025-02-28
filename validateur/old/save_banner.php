<?php
require_once '../inc/functions/connexion.php';
//session_start(); 
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
  
// Partenaires
$description = $_POST['description'];
//$role = $_POST['role'];

    
        // Création du client associé à cet utilisateur
        $stmt = $conn->prepare("INSERT INTO banner (description,nom_pictures,banner_app) VALUES (?, ?, ?)");
        $stmt->execute([$description, 'default-banner.jpg', 'admin']);
    
    
          if($stmt)
           {
               $_SESSION['popup'] = true;
                header('Location: banner_admin.php');
                exit(0);
            }
    
            else
            {
                $_SESSION['delete_pop'] = true;
                header('Location: banner_admin.php');
                exit(0);
            }
}
?>