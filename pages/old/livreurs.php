 
<?php
                      // require_once '../inc/functions/connexion.php';
include('header.php');
                       // $a=1;
$stmt = $conn->prepare("SELECT * FROM livreurs");
$stmt->execute();
 $livreurs = $stmt->fetchAll();
foreach($livreurs as $livreur)
                       
                        
?>
<div class="row">
<button type="button" class="btn btn-warning btn-rounded btn-fw"><a href="#add-livreur"
                        data-toggle="modal">Ajouter un livreur</a>
                    <i class="typcn typcn-edit btn-icon-append"></i></button>
  <table id="example2" class="table table-bordered table-hover">
        <thead>
            <tr>
                <td>#</td>
                <td>Nom livreur</td>
                <td>Prenom livreur</td>
                <td>Contact 1</td>
                <td>whatsapp</td>
                <td>Lieu d'habitation</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($livreurs as $livreur): ?>
            <tr>
                <td><?=$livreur['id']?></td>
                <td><?=$livreur['nom_livreur']?></td>
                <td><?=$livreur['prenom_livreur']?></td>
                <td><?=$livreur['contact_livreur']?></td>
                <td><?=$livreur['contact_whapp']?></td>
                <td><?=$livreur['lieu_habitation']?></td>
                <td class="actions">
                    <a href="livreurs_update.php?id=<?=$livreur['id']?>" class="edit"><i class="fas fa-pen fa-xs"></i></a>
                    <a href="livreurs_delete.php?id=<?=$livreur['id']?>" class="trash"><i class="fas fa-trash fa-xs"></i></a>
        </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!--- Getion des Modals-------->
<div class="modal fade" id="add-livreur">
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
                                        
                                        <form class="forms-sample" method="post" action="savelivreur.php">
                                            <div class="form-group">
                                                <label for="exampleInputName1">Nom du livreur</label>
                                                <input type="text" class="form-control" id="exampleInputName1"
                                                    placeholder="Nom du livreur" name="nom_livreur">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputEmail3">Prenom du livreur</label>
                                                <input type="text" class="form-control" id="exampleInputEmail3"
                                                    placeholder="Prenom du livreur" name="prenom_livreur">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputPassword4">Contact 1 du client </label>
                                                <input type="text" class="form-control" id="exampleInputPassword4"
                                                    placeholder="Contact" name="contact_livreur">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Contact 2 du client </label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="whatsapp" name="contact_whapp">
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleInputCity1">Lieu d'habitation</label>
                                                <input type="text" class="form-control" id="exampleInputCity1"
                                                    placeholder="Lieu d'habitation" name="lieu_habitation">
                                            </div>
                                            <button type="submit" class="btn btn-primary mr-2" name="saveLivreur">Enregister</button>
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
