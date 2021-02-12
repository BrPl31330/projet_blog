<?php
require_once "../../includes/session.php";
//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404
if(!isset($_SESSION["user"]) || !isAdmin()){
    http_response_code(404);
    die("Page introuvable");
}
//accessible par /admin/catégorie
//ce fichier affichera la liste des catégories
//dans la page nous aurons :
//1 tableau listant les catégories par ordre alphabétique
//pour chague catégorie un lien modifier et un lien supprimer
//au dessus du tableau, un lien pour ajouter une catégorie

//on se connect à la base
require_once "../../includes/connect.php";

//on inclut le header
include_once "../../includes/header.php";

//on recupére les données
//on écrit la requete
$sql = "SELECT * FROM `categorie` ORDER BY `name` ASC";

//on execute la requete
$query = $db->query($sql);

//on recupere les données
$categories = $query->fetchAll(); //=>foreach

//on affiche les données dans le HTML
?>
<h2>Liste des catégories</h2>
<a href="add.php">Ajouter une catégorie</a>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $categorie) : ?>
            <tr>
                <td><?= $categorie["id"] ?></td>
                <td><?= $categorie["name"] ?></td>
                <td><a href="edit.php?id=<?=$categorie["id"]?>">Modifier</a>.<a href="delete.php?id=<?=$categorie["id"]?>">supprimer</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php
//on inclut le header
include_once "../../includes/footer.php";