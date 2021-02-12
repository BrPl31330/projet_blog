<?php
require_once "../includes/session.php";

//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404
if(!isset($_SESSION["user"])){
    header("Location: ../connection.php");
}


include_once "../includes/header.php";
?>
<h1>Profil de <?= $_SESSION["user"]["first_name"]?><?= $_SESSION["user"]["name"]?></h1>
<figure>
<img src="../uploads/image/profil.jpg" alt="profil" height="150" width="150" >
<!-- <figcaption>Photo profil</figcaption> -->
</figure>

<h2>Informations personnelles</h2>
<div>
<p>email : <?= $_SESSION["user"]["email"]?></p>
<p>Nom : <?= $_SESSION["user"]["name"]?></p>
<p>Prénom : <?= $_SESSION["user"]["first_name"]?></p>
</div>

<?php
include_once "../includes/footer.php";