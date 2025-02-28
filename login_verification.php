<?php
	session_start();
 
	require('connexion.php');
    $message = ""; 
    if(isset($_POST["btn_login"]))  
    {  
         if(empty($_POST["login"]) || empty($_POST["password"]))  
         {  
              $message = '<label>All fields are required</label>';  
         }  
         else  
         {  
              $query = "SELECT * FROM users WHERE login = :login AND password = :password";  
              $statement = $conn->prepare($query);  
              $statement->execute(  
                   array(  
                        'login'     =>     $_POST["login"],  
                        'password'     =>     $_POST["password"]  
                   )  
              );  
              $count = $statement->rowCount();  
              if($count > 0)  
              {  
                   $_SESSION["login"] = $_POST["login"];  
                   header("location:welcome.php");  
              }  
              else  
              {  
                   $message = '<label>Wrong Data</label>';  
              }  
         }  
    }
?>