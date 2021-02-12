<?php
require_once "../../includes/session.php";
//vérification que l'utilisateur est connecté et Admin
//Sinon on affichera une erreur 404 
if (!isset($_SESSION["user"]) || !isAdmin()) {
    http_response_code(404);
    $_SESSION["error"][] = "Page introuvable";
}

//cette page doit contenir le header, le footer, le formulaire nouvel article
//pour chaque article, on devra avoir le titre, le contenu, les catégories 
//on se connecte à la base

require_once "../../includes/connect.php";

//var_dump($_POST);

//Ecriture de la requete
$sql = "SELECT * FROM `categorie` ORDER BY `name` ASC";

//Execution de la requête
$query = $db->query($sql);

//Récupération des données
$categorie = $query->fetchAll(); //donc foreach

//Vérification que POST n'est pas vide
if (!empty($_POST)) {
    //on effectue le traitement du formulaire
    //on vérifie tous les champs obligatoires
    if (
        isset($_POST["titre"], $_POST["contenu"], $_POST["categorie"], $_FILES["image"])
        && !empty($_POST["titre"])
        && !empty($_POST["contenu"])
        && !empty($_POST["categorie"] && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE)
    ) {
        //le formulaire est complet, traitement 
        //récupération et protection des données (failles XSS)
        $titre = strip_tags($_POST["titre"]);
        //on a des balises HTML
        $contenu = htmlspecialchars($_POST["contenu"]);
        $cats = $_POST["categorie"];

        //************************************************* */


        //Récupération du nom actuel du fichier temporaire
        $fichierTmp = $_FILES["image"]["tmp_name"];

        //Récupération du nom d'origine du fichier uploadé
        //$fichierDest = $_FILES["picture"]["name"];

        //on genere un nom de fichier
        //on recupere l'extension
        $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

        //on génére un nom(la chaine de catactéres située avant l'extension)
        $nom = md5(uniqid());

        //on fabrique le nom complet
        $fichierDest = "$nom.$extension"; //$nom.".".$extension
        //On vérifie si uploads/images existe
        if (!file_exists(__DIR__ . "/../../uploads/image")) {
            mkdir(__DIR__ . "/../../uploads/image", 0777, true);
        }

        //on souhaite uniquement des images png et jpeg
        if ($_FILES["image"]["type"] != "image/png" && $_FILES["image"]["type"] != "image/jpeg") {
            //on n'a pas le bon format, on s'arrête
            $_SESSION["error"][] = "Format d'image non pris en charge";
        }

        //on limite la taille à 1Mo
        //1Mo = 1 048 576 octets
        if ($_FILES["image"]["size"] > 1048576) {
            //le fichier est plus grand que 1Mo
            $_SESSION["error"][] = "Le fichier est trop gros";
        }
        if (empty($_SESSION["error"])) {
            //ici tous les contrôles sont passés, on peut copier le fichier
            //ATTENTION, move_uploaded_file nécéssite des chemins "physiques"
            //on doit donner le chemin complet
            //move_uploaded_file($fichierTmp, __DIR__."/".$fichierDest)
            if (!move_uploaded_file($fichierTmp, __DIR__ . "/../../uploads/image/" . $fichierDest)) {
                $_SESSION["error"][] = "Le transfert du fichier à échoué";
            } else {

                //Manipulation image
                //Création miniature carrée 
                //Bonne pratique : si l'image s'appelle brouette.png
                //La miniature s'appellera 200*200-brouette.png

                //*************************Traitement image formulaire****************************************** */
                $image = __DIR__ . "/../../uploads/image/" . $fichierDest;

                //prendre le plus grand carre possible au milieu de l'image
                //metre dans image 200*200

                //connaitre la taille fichier source
                $info = getimagesize($image);

                //connaitre le côté le plus petit et la taille
                $largeurSource = $info[0];
                $hauteurSource = $info[1];

                //savoir ou se place le coin sup G du carré source
                //on calcule la moitié de la zone restante après retrait du carré
                //image horizontale : largeurimage - largeurcarré / par 2
                //image verticale : hauteurimage - hauteurcarré / 2
                switch ($largeurSource <=> $hauteurSource) {
                    case -1:
                        //Image portrait
                        $tailleCarre = $largeurSource;
                        $src_x = 0;
                        $src_y = ($hauteurSource - $tailleCarre) / 2;
                        break;
                    case 0:
                        //Image carrée
                        $tailleCarre = $largeurSource;
                        $src_x = 0;
                        $src_y = 0;
                        break;
                    case 1:
                        //image paysage
                        $tailleCarre = $hauteurSource;
                        $src_x = ($largeurSource - $tailleCarre) / 2;
                        $src_y = 0;
                        break;
                }

                //création image
                $nouvelleiImage = imagecreatetruecolor(200, 200);

                //chargement image existante en mémoire
                //ATTENTION au mine
                //On teste le type du mime
                switch ($info["mime"]) {
                        // Liste TOUS les types mines autorisés (png et jpeg)
                    case "image/png":
                        $ancienneImage = imagecreatefrompng($image);
                        break;
                    case "image/jpeg":
                        $ancienneImage =  imagecreatefromjpeg($image);
                        break;
                }

                //$ancienneImage = imagecreatefromjpeg($image);

                //copie ancienne image dans nouvelle
                imagecopyresampled(
                    $nouvelleiImage, //image destination
                    $ancienneImage, //image source
                    0, //décalage horiz coin sup G image destination
                    0, //décalage vert coin sup G image destination
                    $src_x, //décalage horiz coin sup G image source
                    $src_y, //décalage vert coin sup G image source
                    200, //largeur zone destination
                    200, //hauteur zone destination
                    $tailleCarre, //largeur zone source
                    $tailleCarre //heuteur zone source
                );

                switch ($info["mime"]) {
                        // Liste TOUS les types mines autorisés (png et jpeg)
                    case "image/png":
                        imagepng($nouvelleiImage, __DIR__ . "/../../uploads/image/200X200-$fichierDest");
                        break;
                    case "image/jpeg":
                        imagejpeg($nouvelleiImage, __DIR__ . "/../../uploads/image/200X200-$fichierDest");
                        break;
                }

                //Destruction des images dans la mémoire
                imagedestroy($nouvelleiImage);
                imagedestroy($ancienneImage);

                //********************************************************************************************************* */

                //Ecriture de la requete
                $sql = "INSERT INTO `article`(`titre`,`contenu`,`picture`, `user_id`)
                            VALUES (:titre, :contenu, '$fichierDest', {$_SESSION["user"]["id"]})";

                //Préparation de la requete
                $query = $db->prepare($sql);

                //Injection des paramètres
                //1 bindvalue par paramètre SQL
                $query->bindValue(":titre", $titre, PDO::PARAM_STR);
                $query->bindValue(":contenu", $contenu, PDO::PARAM_STR);

                //Execution
                $query->execute();

                //Récupération de l'id du dernier insert dans la base
                $idArticle = $db->lastInsertId();

                //Ecriture dans article_catégorie
                //Boucle sur les listes des catégories cochées
                foreach ($cats as $cat) {
                    //ici j'ai une seule case à cocher à la fois
                    $sql = "INSERT INTO `article_categorie`(`article_id`, `categorie_id`) VALUES ($idArticle, :idcategorie)";
                    //on met idArticle directement vient de notre code
                    //Préparation de la requête
                    $query = $db->prepare($sql);

                    //Injection des paramétres
                    $query->bindValue(":idcategorie", $cat, PDO::PARAM_INT);

                    //Exécution
                    $query->execute();
                }
                //Redirection vers index.php
                header("Location: ../../index.php");
            }
        }
    } else {
        //ici formulaire incomplet
        $_SESSION["error"][] = "Formulaire incomplet";
    }
}



include_once "../../includes/header.php ";

?>
<h1>Ajout nouvel article</h1>
<?php
//var_dump($_SESSION["error"]);
//unset($_SESSION["error"]);
//Affichage du/des message(s) d'erreur
//Vérificaion que $_session_error existe
if (isset($_SESSION["error"])) {
    //ici au moins 1 message d'erreur
    foreach ($_SESSION["error"] as $error) {
        //on affiche chaque erreur dans une balise p
        echo "<p>$error</p>";
    }
    //on vide les messages d'erreurs
    unset($_SESSION["error"]);
}
?>
<!-- quand on a un champ type file, on est obligés de préciser l'attibut enctype -->
<form method="post" enctype="multipart/form-data">
    <h2>Votre article</h2>
    <div>
        <label for="titre">titre de l'article</label>
        <input type="text" id="titre" name="titre">
    </div>
    <br>
    <div>
        <label for="contenu"></label>
        <textarea rows="5" cols="60" wrap="physique" name="contenu">Votre article</textarea><br />
    </div>

    <div>
        <h2>Choisir une/des catégorie(s) ?</h2>
        <?php foreach ($categorie as $categorie) : ?>
            <input type="checkbox" value="<?= $categorie["id"] ?>" name="categorie[]" id="categorie<?= $categorie["id"] ?>">
            <!-- pour récupérer toutes les case à cocher il faut que name soit un tableau -->
            <label for="categorie<?= $categorie["id"] ?>"><?= $categorie["name"] ?></label>
        <?php endforeach; ?>
        <div>
            <h3>Image à la une</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="image" id="image" accept="image/png,image/jpeg" multiple>
                <small>Taille de fichier limitée à 1Mo</small>
                <button type="submit">Valider</button>
            </form>
        </div>
    </div>
</form>
<?php

//on inclut le footer
include_once "../../includes/footer.php";
