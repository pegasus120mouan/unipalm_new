<?php
                        // require_once '../inc/functions/connexion.php';
 include('header.php');
                       // $a=1;
//$nombreParPage = 10;
//$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
//$offset = ($pageCourante - 1) * $nombreParPage;

//$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC limit $offset,$pageCourante ");
$stmt = $conn->prepare("SELECT * FROM utilisateurs");
$stmt->execute();
$utilisateurs= $stmt->fetchAll();

?>
  <table id="example2" class="table table-bordered table-hover">
  
        <thead>
            <tr>
                <td>#</td>
                <td>Nom</td>
                <td>Prenom</td>
                <td>Photo</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $utilisateur): ?>
            <tr>
                <td><?=$utilisateur['id']?></td>
                <td><?=$utilisateur['nom']?></td>
                <td><?=$utilisateur['prenom']?></td>
                <td>
                <a href="client_profile.php?id=<?=$utilisateur['id']?>" class="edit"><img src="../pages/dossier_photos/<?php echo $utilisateur['photo']; ?>" alt="Photo" width="50" height="50"> </a>
            </td>

                <td class="actions">
                    <a href="client_update.php?id=<?=$utilisateur['id']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                    <a href="client_delete.php?id=<?=$utilisateur['id']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
        </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<!DOCTYPE html>
<html>
<head>
    <title>Formulaire d'inscription</title>
</head>
<body>
    <form action="traitement_images.php" method="post" enctype="multipart/form-data">
     <input type="file" name="photo" accept="image/*">
        <input type="submit" value="modifier mon image de profil">
    </form>
</body>
</html>
