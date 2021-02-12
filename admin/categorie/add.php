<?php
require_once "../../includes/session.php";
//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404
if(!isset($_SESSION["user"]) || !isAdmin()){
    http_response_code(404);
    die("Page introuvable");
}
//cette page ajoute une catégorie
//le traitement du formulaire se situera ici, AVANT toute émission de HTML
//on vérifie que le POST existe et n'est pas vide
if(isset($_POST["nom"]) && !empty($_POST["nom"])){
    //ici nom n'est pas vide
    //on recupere les données du formulaire dans une variable (en les protégeant)
    
    //on doit se protéger contre les failles XSS (cross site scripting)
    // 3 méthodes
    //supprimer toute balise html du contenu (strip_tags)
    //neutraliser les caractéres composant les ballises(<,",',>,/...)
    // neutraliser tout caractere spéclial HTML 
    $nom = strip_tags($_POST["nom"]);

    //on se connecte à la base
    require_once "../../includes/connect.php";

    //on écrit le requete
    $sql = "INSERT INTO `categorie`(`name`) VALUES (:nom)";

    //on prepare la requete
    $query = $db->prepare($sql);

    //on injecte les paramétres
    $query->bindValue(":nom", $nom, PDO::PARAM_STR);

    //on execute la requete
    $query->execute();

    //redirection vers index.php
    header("Location: index.php");

}

include_once "../../includes/header.php";

//ici le formulaire HTML
?>
<h2>Ajouter une catégorie</h2>
<form method="post">
    <div>
        <label for="categorie">Nom de la catégorie</label>
        <input type="text" name="nom" id="categorie">
    </div>
    <button type="submit">Créer une catégorie</button>
</form>
<a href="index.php">Retour à la liste des catégories</a>
<?php

include_once "../../includes/footer.php";
