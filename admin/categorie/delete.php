<?php
require_once "../../includes/session.php";
//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404
if(!isset($_SESSION["user"]) || !isAdmin()){
    http_response_code(404);
    die("Page introuvable");
}
//on doit avoir l'id de la catégorie à supprimer
//delete.php?id=3
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

//2 approches
// - vérifie dans la base si une catégorie a cet id et supprimer(2 requetes)
// - ou supprimmer directement(1 requete)

//on se connecte
require_once "../../includes/connect.php";

//on écrit la requête
$sql = "DELETE FROM `categorie` WHERE `id` = :id";

//préparer la requête
$query = $db->prepare($sql);

//on injecte les paramétres
$query->bindValue(":id", $id, PDO::PARAM_INT);

//on exécute
$query->execute();

//on redirige
header("Location: index.php");