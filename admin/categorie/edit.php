<?php
require_once "../../includes/session.php";
//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404
if (!isset($_SESSION["user"]) || !isAdmin()) {
    http_response_code(404);
    die("Page introuvable");
}
// On commence par vérifier si on a un id dans l'url
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    // Ici j'ai un id
    // Je récupère l'id dans une variable
    $id = $_GET["id"];
} else {
    // Ici je n'ai pas d'id -> erreur 404
    http_response_code(404);
    die("Aucun id fourni");
}

// Ici on est certains d'avoir un id (même inexistant dans la base)
// On va pouvoir récupérer la catégorie correspondant à l'id
//on se connecte à la base
require_once "../../includes/connect.php";
//on ecrit la requete
$sql = "SELECT * FROM `categorie` WHERE `id` = :id";

//on prepare la requete
$query = $db->prepare($sql);

//on injecte les parametres
$query->bindValue(":id", $id, PDO::PARAM_INT);

//on execute la requete
$query->execute();

//on recupere les données
$categorie = $query->fetch(); //une seule categorie au maximum

//on verifie si on a les données (categorie vide éventuellement)
if (!$categorie) {
    http_response_code(404);
    die("La categorie n'existe pas");
}
//********************************************************************************************************* */
//on ecrit le traitement du formulaire
//on verifie que le post existe et n'est pas vide
if (isset($_POST["nom"]) && !empty($_POST["nom"])) {
    //on a les données ds formulaire
    //on récupére les données en protégeant (failles XSS)
    $nom = strip_tags($_POST["nom"]);

    //on ecrit la requete (UPDATE)
    $sql = "UPDATE `categorie` SET `name` = :nom WHERE `id` = {$categorie["id"]}";

    //on prepare la requete
    $query = $db->prepare($sql);

    //on injecte les parametres
    $query->bindValue(":nom", $nom, PDO::PARAM_STR);

    //on execute
    $query->execute();

    //on redirige vers l'index
    header("Location: index.php");
}

include_once "../../includes/header.php";
// Ici on met le formulaire HTML
?>
<h2>Modifier une catégorie</h2>
<form method="post">
    <div>
        <label for="categorie">Nom de la catégorie</label>
        <input type="text" name="nom" id="categorie" value="<?= $categorie["name"] ?>">
    </div>
    <button type="submit">Modifier la catégorie</button>
</form>
<a href="index.php">Retour à la liste des catégories</a>
<?php
include_once "../../includes/footer.php";
