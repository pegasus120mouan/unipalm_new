<?php
require_once './inc/functions/connexion.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $login = $_POST['login'];
        $password = $_POST['password'];
        $hasedpassword=hash('sha256',$password);

        if(empty($login) || empty($password))
        {
          $_SESSION['credentials_refused'] = true;
            header('Location: index.php');
            exit(0);
        }else {

          $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE login = :login AND password = :password AND statut_compte=1");
          $stmt->bindParam(':login', $login, PDO::PARAM_STR);
          
          $stmt->bindParam(':password', $hasedpassword, PDO::PARAM_STR);
          $stmt->execute();
  
        
          $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
          if ($user) {
             
              $_SESSION['user_id'] = $user['id'];
              $_SESSION['nom'] = $user['nom'];
              $_SESSION['prenoms'] = $user['prenoms'];
              $_SESSION['user_role'] = $user['role'];
              $_SESSION['avatar'] = $user['avatar'];
  
          
              switch ($user['role']) {
                  case 'admin':
                    header("location: ../pages/tickets.php");
                      break;
                  case 'operateur':
                    header("location: ../operateurs/tickets.php");
                      break;
                  case 'validateur':
                    header("location: ../validateur/tickets.php");
                      break;
                  default:
                      header('Location: ../caisse/tickets.php');
                      break;
              }
              exit(0);
          } else {
              
            $_SESSION['connexion_refused'] = true;
            header('Location: index.php');
            exit(0);
          }


        }

       
        

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion</title>

  <!-- Google Font: Source Sans Pro -->

  <link rel="icon" href="dist/img/logo.png" type="image/x-icon">
  <link rel="shortcut icon" href="dist/img/logo.png" type="image/x-icon">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../../plugins/toastr/toastr.min.css">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <a href="index.php" class="h1"><b>
            <img src="../../dist/img/logo.png" alt="User Avatar" class="img-size-80 mr-3 img-circle">
        </a>
      </div>
      <div class="card-body">
        <p class="login-box-msg">Veuillez vous loguer</p>

        <form action="" method="post">
          <div class="input-group mb-3">
            <input type="login" class="form-control" placeholder="Login" name="login">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Password" name="password">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block" name="btn_login">Se connecter</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="../../plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../../dist/js/adminlte.min.js"></script>
  <script src="../../plugins/toastr/toastr.min.js"></script>

  <?php

if (isset($_SESSION['connexion_refused']) && $_SESSION['connexion_refused'] ==  true) {
?>
  <script>
    toastr.error('Votre login ou mot de passe est incorrect.')
  </script>

<?php
  $_SESSION['popup'] = false;
}
?>

<?php

if (isset($_SESSION['credentials_refused']) && $_SESSION['credentials_refused'] ==  true) {
?>
  <script>
    toastr.error('Veuillez entrer vos identifiants.')
  </script>

<?php
  $_SESSION['popup'] = false;
}
?>
  
</body>

</html>