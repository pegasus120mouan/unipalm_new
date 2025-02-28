<?php
include('header.php');

$id_engin = $_GET['id'];

// Assuming $conn is your PDO connection
$sql = "SELECT image_1, image_2, image_3, image_4,numero_chassis, plaque_immatriculation 
FROM engins WHERE engin_id=:engin_id";
$requete = $conn->prepare($sql);
$requete->bindParam(':engin_id', $id_engin, PDO::PARAM_INT);
$requete->execute();
$vueImage = $requete->fetch(PDO::FETCH_ASSOC);

?>

<!-- Main content -->
<section class="content">

  <!-- Default box -->
  <div class="card card-solid">
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-sm-6">
          <div class="col-12">
            <!-- Display main product image -->
            <img src="../dossiers_motos/<?php echo $vueImage['image_1']; ?>" class="product-image" alt="Product Image">
          </div>
          <div class="col-12 product-image-thumbs">
            <!-- Thumbnail images -->
            <div class="product-image-thumb active"><img src="../dossiers_motos/<?php echo $vueImage['image_1']; ?>" alt="Image 1"></div>
            <div class="product-image-thumb"><img src="../dossiers_motos/<?php echo $vueImage['image_2']; ?>" alt="Image 2"></div>
            <div class="product-image-thumb"><img src="../dossiers_motos/<?php echo $vueImage['image_3']; ?>" alt="Image 3"></div>
            <div class="product-image-thumb"><img src="../dossiers_motos/<?php echo $vueImage['image_4']; ?>" alt="Image 4"></div>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <div class="card card-primary card-outline">
            <div class="card-body box-profile">
              <h3 class="profile-username text-center">
                Modifier les images de l'engin
              </h3>
              <form action="traitement_images_motos.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id_engin; ?>">
                <!-- File upload inputs -->
                <input type="file" class="form-control" name="photo1" accept="image/*">
                <input type="file" class="form-control" name="photo2" accept="image/*">
                <input type="file" class="form-control" name="photo3" accept="image/*">
                <input type="file" class="form-control" name="photo4" accept="image/*">
                <br>
                <input type="submit" class="btn btn-info" value="Modifier les images de la moto">
              </form>
            </div>
          </div>
          <hr>
          <div class="bg-gray py-2 px-3 mt-4">
                            <h2 class="mb-0">
                    <?php 
                    if ($vueImage['numero_chassis'] !== NULL) {
                        echo $vueImage['numero_chassis'];
                    } else {
                        echo "numero chassis indisponible";
                    }
                    ?>
                </h2>
            <h4 class="mt-0"><small><?php echo $vueImage['plaque_immatriculation']; ?></small></h4>
          </div>
        </div>
      </div>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->

</section>
<!-- /.content -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script>
  $(document).ready(function() {
    // Handle thumbnail clicks
    $('.product-image-thumb').on('click', function() {
      var $image_element = $(this).find('img');
      $('.product-image').prop('src', $image_element.attr('src'));
      $('.product-image-thumb.active').removeClass('active');
      $(this).addClass('active');
    });
  });
</script>
</body>
</html>
