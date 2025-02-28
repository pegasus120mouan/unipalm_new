 
<?php
                      //  require_once '../inc/functions/connexion.php';
include('header.php');
require_once '../inc/functions/requete/requete_commandes.php';  
                      //  $a=1;

?>

<button type="button" class="btn btn-success">
<a href="point_client.php">Point par client</a>
</button>

<button type="button" class="btn btn-info">
<a href="images_client.php">Images</a>
</button>


<button type="button" class="btn btn-success">
<a href="dashboard.php">Dashboard</a>
</button>


<div class="alert alert-danger">

<?php
// Vérifie si un message de confirmation est présent dans la session
if (isset($_SESSION['message'])) {
echo "<strong>" . $_SESSION['message'] . "</strong>";
//unset($_SESSION['message']); // Supprime le message pour qu'il ne s'affiche qu'une fois
}
?>
</div>


<div class="row">
<button type="button" class="btn btn-warning btn-rounded btn-fw"><a href="#add-commande"
                        data-toggle="modal">Ajouter une commande</a>
                    <i class="typcn typcn-edit btn-icon-append"></i></button>
  <table id="example2" class="table table-bordered table-hover">
        <thead>
            <tr>
                <td>Communes</td>
                <td>Côut Global</td>
                <td>Livraison</td>
                <td>Côut réel</td>
                <td>Client</td>
                <td>Livreur</td>
                <td>Statut</td>
                <td>Date d'entrée</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($commandes as $commande): ?>
            <tr>
                <td><?=$commande['communes']?></td>
                <td><?=$commande['cout_global']?></td>
                <td><?=$commande['livraison']?></td>
                <td><?=$commande['cout_reel']?></td>
                <td><?=$commande['client']?></td>
                <td><?=$commande['livreur']?></td>
                <td><?=$commande['statut']?></td>
                <td><?=$commande['date']?></td>
                
                <td class="actions">
                    <a href="update.php?id=<?=$commande['id']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                    <a href="delete_commandes.php?id=<?=$commande['id']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
        </td>
        
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>  

<!--- Getion des Modals-------->
<div class="modal fade" id="add-commande">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
            
                        <div class="alert alert-danger">
                            <strong>Attention. Tous les champs sont obligatoires<br><br></strong>
                            <ul>
                            </ul>
                        </div>
            
                
                    
                        <div class="row">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <form class="forms-sample" method="post" action="savecommande.php">
                                            <div class="form-group">
                                                <label for="exampleInputName1">Communes</label>
                                                <input type="text" class="form-control" id="exampleInputName1"
                                                    placeholder="Communes" name="communes">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">Côut Global</label>
                                                <input type="text" class="form-control" id="exampleInputEmail3"
                                                    placeholder="Côut Global" name="cout_global">
                                            </div>
                                            <div class="form-group row">
                                                                <label for="select" class="col-3 col-form-label">Coût Livraison</label> 
                                                                <div class="col-9">
                                                                <?php
                                                                echo  '<select id="select" name="livraison" class="custom-select">';
                                                                while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
                                                                    echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
                                                                }
                                                                echo '</select>'
                                                                ?>
                                                                </div>
                                            </div>
                                          
                                            <div class="form-group row">
                                                                <label for="select" class="col-3 col-form-label">Clients</label> 
                                                                <div class="col-9">
                                                                <?php
                                                                echo  '<select id="select" name="client" class="custom-select">';
                                                                while ($row = $requete->fetch(PDO::FETCH_ASSOC)) {
                                                                    echo '<option value="' . $row['nom_boutique'] . '">' . $row['nom_boutique'] . '</option>';
                                                                }
                                                                echo '</select>'
                                                                ?>
                                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                                <label for="select" class="col-3 col-form-label">Livreur</label> 
                                                                <div class="col-9">
                                                                <?php
                                                                echo  '<select id="select" name="livreur" class="custom-select">';
                                                                while ($rowLivreur = $livreurs_selection->fetch(PDO::FETCH_ASSOC)) {
                                                                    echo '<option value="' . $rowLivreur['prenom_livreur'] . '">' . $rowLivreur['prenom_livreur'] . '</option>';
                                                                }
                                                                echo '</select>'
                                                                ?>
                                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                                <label for="select" class="col-3 col-form-label">Statut</label> 
                                                                <div class="col-9">
                                                             
                                                                <select id="select" name="statut" class="custom-select">';
                                                                
                                                                    <option value="Livré">Livré</option>';
                                                                    <option value="Non Livré">Non Livré</option>';
                                                                
                                                                </select>
                                                               
                                                                </div>
                                            </div>
                        
                                            <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
                                            <button class="btn btn-light">Annuler</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </form>
            </div>

        </div>

    </div>
