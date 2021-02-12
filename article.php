<?php
require_once "includes/session.php";
//On affiche 1 seul article
//URM : http://projet_blog.test/article.php?id=15778
//on récupére l'id dans $_GET

//verifie $_GET contient données
//isset vérifie les variables sont définies
//verif si champs non vides
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    // j'ai un id et il n'est pas vide
    //on récupére l'id
    $id = $_GET["id"];
} else {
    //pas de id ou id vide
    //on génére page 404 et on arrete code
    http_response_code(404);
    die("Page introuvable");
}


//on se connecte à la base
require_once "includes/connect.php";



//on va chercher les données
//on ecrit la requete
$sql = "SELECT `article`.*, `user`.`nickname`, GROUP_CONCAT(`categorie`.`name`) AS categories
FROM `article` 
JOIN `user` 
ON `user`.`id` = `article`.`user_id`
JOIN `article_categorie` 
ON `article`.`id` = `article_categorie` . `article_id`
JOIN `categorie` 
ON `categorie`.`id` = `article_categorie`.`categorie_id`
WHERE `article`.`id`= :id
GROUP BY `article`.`id`"; //:id est un paramétre SQL (variable SQL)

//on exécute la requête
//paramétre exterieur ? OUI = :id
//on ne peut plus utiliser query()
//on utilise une requete préparee
$query = $db->prepare($sql); //on prépare l'execution de la requete
//on ajoute les parametres
$query->bindValue(":id", $id, PDO::PARAM_INT);

//on execute la requete
$query->execute();

//on récupére les données
$article = $query->fetch();

//2 options : soit article vide, soit contient 1 article
//on verifie si $article est vide
//si oui, erreur 404
if (!$article) { //pas d'article !!!
    //on envoi le code 404
    http_response_code(404);
    die("l'article n'existe pas mon p'tit gars");
    //die();
}

//on traite formulaire
if (!empty($_POST)) {
    if (isset($_POST["contenu"]) && !empty($_POST["contenu"])) {
        //formulaire complet
        //récupére et sécurise les données
        $content = htmlspecialchars($_POST["contenu"]);
        //on écrit la requête
        $sql = "INSERT INTO `comment` (`content`, `user_id`, `article_id`)
        VALUES (:content, {$_SESSION["user"]["id"]}, {$article["id"]})";

        //on prepare la requete
        $query = $db->prepare($sql);

        //on injecte
        $query->bindValue(":content", $content, PDO::PARAM_STR);

        //on execute
        $query->execute();

        //on redire
        header("Location: article.php?id=" . $article["id"]);
    } else {
        $_SESSION["error"][] = "formulaire incomplet";
    }
}


//Requête commentaire
//on va chercher les données
$sql = "SELECT `comment`.*, `user`.`nickname` FROM `comment`
JOIN `user` ON `user`.`id` = `comment`.`user_id`
WHERE `article_id` = {$article["id"]}"; //protection maximum, le pire à éviter $id

//on exécute la requête
$query = $db->query($sql);

//on récupére les commentaires
$comments = $query->fetchAll(); //après fetchAll -> foreach

//On affiche les données
//on inclut le header
include_once "includes/header.php";
?>
<article>
    <h1><?php echo $article["titre"] ?></h1>
    <p><img src="uploads/image/200x200-<?= $article["picture"] ?>" alt="<?= $article["titre"] ?>"></p>
    <p>Article écrit par <?= $article["nickname"] ?> le <?= date("d/m/Y à H:i:s", strtotime($article["created_at"])) ?> dans <?= $article["categories"] ?></p>
    <div><?= htmlspecialchars_decode($article["contenu"]) ?></div>
    <br>
</article>
<!-- afficher les commentaires de l'article -->


<h2>Commentaires</h2>

<?php
if (!empty($comment)) {
    //ici au moins 1 commentaire
    foreach ($comments as $comment) {
        echo "
            <article>
            <p>Commentaire écrit par {$comment["nickname"]} le " . date("d/m/Y à H:i:s", strtotime($comment["created_at"])) . "</p>
            <p>".htmlspecialchars_decode($comment["content"])."</p>
            </article>
            ";
    }
} else {
    echo "pas de commentaires";
}

?>
<h1>Ajouter un commentaire</h1>
<!-- afficher un formulaire permettant d'ajouter un commentaire si on est connecté -->
<!-- on vérifie si le USER est connecté -->
<?php
if (!isset($_SESSION["user"])) {
    //pas connecté
    echo "<p>Pour ajouter un commentaire, vous devez être connecté(e).<a href='" .URL. "/connexion.php'>Me connecter</a></p>";
} else {
    //on est connecté
?>
    <form method="post">
        <br>
        <div>
            <label for="contenu">Contenu</label>
            <textarea rows="5" cols="60" wrap="physique" name="contenu" id="contenu"></textarea>
        </div>
        <br>
        <div>
            <button type="submit">Enregistrer</button>
        </div>
    </form>

<?php
}
include_once "includes/footer.php";
