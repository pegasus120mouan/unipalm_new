<?php
include('header.php');

$banner_id = $_GET['id'];

// Assuming $conn is your PDO connection
$sql = "SELECT id, description, nom_pictures FROM banner WHERE id=:banner_id";
$requete = $conn->prepare($sql);
$requete->bindParam(':banner_id', $banner_id, PDO::PARAM_INT);
$requete->execute();
$vueBanner= $requete->fetch(PDO::FETCH_ASSOC);
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
            <img src="../dossiers_banners/<?php echo $vueBanner['nom_pictures']; ?>" class="product-image" alt="Product Image">
          </div>
          <div class="col-12 product-image-thumbs">
            <!-- Thumbnail images -->
            <div class="product-image-thumb active"><img src="../dossiers_banners/<?php echo $vueBanner['nom_pictures']; ?>" alt="Image 1"></div>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <div class="card card-primary card-outline">
            <div class="card-body box-profile">
              <h3 class="profile-username text-center">
                Modifier le banner
              </h3>
              <form action="traitement_images_banners.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $banner_id; ?>">
                <!-- File upload inputs -->
                <input type="file" class="form-control" name="photo" accept="image/*">
  
                <br>
                <input type="submit" class="btn btn-info" value="Modifier le banner">
              </form>
            </div>
          </div>
          <hr>
          <div class="bg-gray py-2 px-3 mt-4">
                            <h2 class="mb-0">
                    <?php 
                        echo $vueBanner['description'];
                    ?>
                </h2>
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
