<?php
require_once "includes/session.php";
//cette page doit contenir le header, le footer, la liste compléte des articles du blog
//pour chaque article, on devra voir le titre, la date, le contenu, le pseudo de l'auteur et le nom de la  ou les catégories
//1ere étape : ecrire la requete sql qui permet d'avoir toutes les données demandées

//on veut récuperer la liste des articles
//on écrit la requête SQL correspondante

/*SELECT `article`.*, `user`.`nickname`, GROUP_CONCAT(`categorie`.`name`) AS categories
FROM `article`
JOIN `user`
ON `user`.`id` = `article`.`user_id`
JOIN `article_categorie`
ON `article`.`id` = `article_categorie`.`article_id`
JOIN `categorie`
ON `categorie`.`id` = `article_categorie`.`categorie_id`
GROUP BY `article`.`id`*/

require_once "includes/connect.php";

//on inclut le header
include_once "includes/header.php";

//ici on affichera les articles
//on recupére les données
//on écrit la requete
$sql = "SELECT `article`.*, `user`.`nickname`, GROUP_CONCAT(`categorie`.`name`) AS categories
FROM `article` JOIN `user` 
ON `user`.`id` = `article`.`user_id`
JOIN `article_categorie` 
ON `article`.`id` = `article_categorie` . `article_id`
JOIN `categorie` 
ON `categorie`.`id` = `article_categorie`.`categorie_id`
GROUP BY `article`.`id`";

//on execute la requete (données extérieures ? NON)
$query = $db->query($sql);

//on récupére les données
$articles = $query->fetchAll(); 

//on les affiche en HTML
?>
<h2>Listes des articles</h2>

<?php foreach($articles as $article): ?>

<article>
    <h1><a href="article.php?id=<?= $article["id"] ?>"><?php echo $article["titre"] ?></a></h1>
    <p>Article écrit par <?= $article["nickname"] ?> le <?= date("d/m/Y à H:i:s", strtotime($article["created_at"])) ?> dans <?= $article["categories"] ?></p>
    
    <div><?= $article["contenu"] ?></div> 
</article>


<?php
endforeach;

//date("d/m/Y à H:i:s", strtotime($article["created_at"]))
//date : fonction date
//1er paramétre : format de sortie date attendu

//on inclut le footer
include_once "includes/footer.php";


