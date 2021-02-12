<?php
//define("URL", "http://projet_blog.test");
define("URL", "https://localhost/projet_blog");
session_start();

//ici on restaure la session si le cookie remember existe ET que la session n'existe pas encore
if (isset($_COOKIE["remember"]) && !isset($_SESSION["user"])) {
    //echo "j'ai un cookie";
    //cookie existe, on peut restaurer une session
    //Récupération du USER correspondant au token du cookie

    //Connection à la base
    require_once "connect.php";

    //Ecriture de la requete
    $sql = "SELECT * FROM `user` WHERE `remember_token` = :token";

    //Préparation de la requete
    $query = $db->prepare($sql);

    //Injection des paramètres
    $query->bindValue(":token", $_COOKIE["remember"], PDO::PARAM_STR);

    //Exécution de la requete
    $query->execute();

    //Récupération des données
    $user = $query->fetch();

    // 2 options : on un user ou pas
    // Vérification user
    if ($user) {
        //On restaure la session
        $_SESSION["user"] = [
            "id" => $user["id"],
            "name" => $user["name"],
            "first_name" => $user["first_name"],
            "nickname" => $user["nickname"],
            "email" => $user["email"],
            "role" => json_decode($user["role"]) //Transforme le json en tableau php
        ];
    }
}

//documentation (ci-dessous)
/**
 * Cette fonction retournera true si user a le rôle "Admin"
 * @param array $user
 * @return bool
 */
function isAdmin()
{
    //le rôle est un tableau dans $_SESSION["user"]["rôle"]
    //on cherche "ROLE_ADMIN" dans le tableau
    if (in_array("ROLE_ADMIN", $_SESSION["user"]["role"])) {
        //on la trouvé
        return true; //le return stoppe la fonction
    }
    //on ne l'a pas trouvé
    return false;
}
