<?php
session_start();
include('connexion.php');
// Validating Session
if(strlen($_SESSION['login'])==0)
{
header('location:index.php');
}
else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PDO | Welcome Page</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <style type="text/css">
          .center {text-align: center; margin-left: auto; margin-right: auto; margin-bottom: auto; margin-top: auto;}
    </style>
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="span12">
      <div class="hero-unit center">
<?php
// Code for fecthing user full name on the bassis of username or email.
$login=$_SESSION['login'];
$query=$dbh->prepare("SELECT  nom FROM users WHERE (nom=:nom || prenoms=:prenoms)");
      $query->execute(array(':login'=> $username));
       while($row=$query->fetch(PDO::FETCH_ASSOC)){
        $username=$row['FullName'];
       }
       ?>
          <h1>Welcome Back <font face="Tahoma" color="red"><?php echo $username;?> ! </font></h1>
          <br />
          <p>Lorem ipsum dolor sit amet, sit veniam senserit mediocritatem et, melius aperiam complectitur an qui. Ut numquam vocibus accumsan mel. Per ei etiam vituperatoribus, ne quot mandamus conceptam has, pri molestiae constituam quaerendum an. In molestiae torquatos eam.
          </p>
          <a href="logout.php" class="btn btn-large btn-info"><i class="icon-home icon-white"></i> Log me out</a>
        </div>
        <br />
      </div>
        <br />
        <!-- By ConnerT HTML & CSS Enthusiast -->
    </div>
  </div>
</div>
<script type="text/javascript">
</script>
</body>
</html>
<?php } ?>