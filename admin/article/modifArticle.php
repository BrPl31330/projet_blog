<?php
require_once "../../includes/session.php";
if (!isset($_SESSION["user"]) || !isAdmin()) {
    http_response_code(404);
    die("Page introuvable");
}
// Vérification si on a un id dans l'url
if (isset($_GET["id"]) && !empty($_GET["id"])) {
    // Ici j'ai un id
    // Récupération de l'id dans une variable
    $id = $_GET["id"];
} else {
    // Ici je n'ai pas d'id -> erreur 404
    http_response_code(404);
    die("Aucun id fourni");
}
//Connexion à la base
require_once "../../includes/connect.php";

// Récupération de l'article correspondant à l'id
//Ecriture de la requete
$sql = "SELECT `article`.*, GROUP_CONCAT(`article_categorie`.`categorie_id`) 
AS categorie
FROM `article`
JOIN `article_categorie`
ON `article_categorie`.`article_id` = `article`.`id`
WHERE `id` = :id
GROUP BY `id`";

//Préparation de la requête
$query = $db->prepare($sql);

//Injection des parametres
$query->bindValue(":id", $id, PDO::PARAM_INT);

//Exécution de la requete
$query->execute();

//Récupération des données
$article = $query->fetch();
//var_dump($article);
//die;
//Vérification que l'article existe
if (!$article) {
    http_response_code(404);
    die("L'article n'existe pas");
}

//Vérification que le POST n'est pas vide
if (!empty($_POST)) {
    //Vérification que tous les champs obligatoires sont ok
    if (
        isset($_POST["titre"], $_POST["contenu"], $_POST["categorie"])
        && !empty($_POST["titre"])
        && !empty($_POST["contenu"])
        && !empty($_POST["categorie"])
    ) {
        //Formulaire complet
        $titre = strip_tags($_POST["titre"]);
        $contenu = htmlspecialchars($_POST["contenu"]);
        $cat = $_POST["categorie"];

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] != UPLOAD_ERR_NO_FILE) {
            //on a un ficlier
            // Insérer ici la gestion de l'image
            // Vérifications de type, taille, dimensions
            // On récupère le nom actuel du fichier temporaire
            $fichierTmp = $_FILES["image"]["tmp_name"];

            // Génération d'un nom de fichier
            // On récupère l'extension du fichier uploadé
            $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

            // Génération d'un nom (la chaîne de caractères située avant .extension)
            $nom = md5(uniqid());

            // Fabrication du nom complet
            $fichierDest = "$nom.$extension"; // $nom.".".$extension

            //Vérification si uploads/images existe
            if (!file_exists(__DIR__ . "/../../uploads/image")) {
                mkdir(__DIR__ . "/../../uploads/image", 0777, true);
            }

            // On souhaite uniquement des images png et jpeg
            if ($_FILES["image"]["type"] != "image/png" && $_FILES["image"]["type"] != "image/jpeg") {
                // Ici on n'a pas le bon format, on génère un message d'erreur
                $_SESSION["error"][] = "Format d'image non pris en charge";
            }

            // On limite la taille à 1Mo
            // 1Mo = 1 048 576 octets 
            if ($_FILES["image"]["size"] > 1048576) {
                // Ici le fichier est plus grand que 1Mo
                $_SESSION["error"][] = "Le fichier est trop gros";

                // On arrive ici avec des erreurs, ou pas
                if (empty($_SESSION["error"])) {

                    if (!move_uploaded_file($fichierTmp, __DIR__ . "/../../uploads/image/" . $fichierDest)) {
                        //le tranfert a échoué
                        $_SESSION["error"][] = "Le transfert du fichier a échoué";
                    } else {
                        // Le transfert a réussi
                        // Suppression de l'ancienne image du dossier uploads
                        $ancienneImage = __DIR__ . "/../../uploads/image/" . $article["picture"];
                        $ancienneMini = __DIR__ . "/../../uploads/image/200x200-" . $article["picture"];

                        //Vérification si $ancienneImage existe
                        if (file_exists($ancienneImage)) {
                            // L'image existe
                            unlink($ancienneImage);
                        }

                        //Vérification si $ancienneMini existe
                        if (file_exists($ancienneMini)) {
                            // L'image existe
                            unlink($ancienneMini);
                        }
                        // Suppression de l'ancienne image du dossier uploads
                        // Redimensionnement
                        //******************************************************************** */
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
                        // On peut ensuite copier le carré source dans l'image de destination
                        // Créer une image en mémoire
                        // Equivalent à un nouveau fichier dans un programme de dessin
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
                        //***************************************************** */
                    }
                } else {
                    //on n'a pas de fichier
                    //on conserve le nom actuellememnt dans la base
                    $fichierDest = $article["picture"];
                }
                //on traite l'update qu'à la condition de n'avoir aucune erreur
                if (empty($_SESSION["error"])) {

                    //Ecriture de la requete
                    $sql = "UPDATE `article` SET `titre` = :titre, `contenu` = :contenu, `picture` = `$fichierDest` WHERE `id`={$article["id"]}";

                    //Préparation de la requête
                    $query = $db->prepare($sql);

                    //Injection des paramètres
                    $query->bindValue(":titre", $titre, PDO::PARAM_STR);
                    $query->bindValue(":contenu", $contenu, PDO::PARAM_STR);

                    // On exécute
                    $query->execute();

                    //la gestion des catégories
                    $query = $db->prepare($sql);

                    //on injecte
                    $query->bindValue(":titre", $titre, PDO::PARAM_STR);
                    $query->bindValue(":contenu", $contenu, PDO::PARAM_STR);

                    //on execute
                    $query->execute();

                    //insere la gestion cate//on efface les cate
                    $sql = "DELETE FROM `article_categorie` WHERE `article_id` = {$article["id"]}";

                    //on execute
                    $query = $db->query($sql);

                    //si on veut sevoir combien de ligne sup
                    echo "Lignes supprimées : " . $query->rowCount();

                    //on écrit toutes les caté cochées
                    //Ecriture dans article_catégorie
                    //Boucle sur les listes des catégories cochées
                    foreach ($categorie as $categorie) {
                        //ici j'ai une seule case à cocher à la fois
                        $sql = "INSERT INTO `article_categorie`(`article_id`, `categorie_id`) VALUES {$article["id"]}, :idcategorie)";
                        //on met idArticle directement vient de notre code
                        //Préparation de la requête
                        $query = $db->prepare($sql);

                        //Injection des paramétres
                        $query->bindValue(":idcategorie", $categorie, PDO::PARAM_INT);

                        //Exécution
                        $query->execute();
                    }
                    //Redirection vers index.php
                    header("Location: ../../index.php");
                    //************************************************************ */
                }
            } else {
                // Formulaire incomplet
                $_SESSION["error"][] = "Formulaire incomplet";
            }
        }
    }
}
// Ici on a un article à modifier
// On va chercher la liste complète des catégories dans la base
// On écrit la requête
$sql = "SELECT * FROM `categorie` ORDER BY `name` ASC";

// On exécute la requête
$query = $db->query($sql);

// On récupère les données
$categories = $query->fetchAll(); // donc foreach


include_once "../../includes/header.php";


?>
<h2>Modifier un article</h2>
<?php
// Afficher ici le/les message(s) d'erreur
// On vérifie que $_SESSION["error"] existe
if (isset($_SESSION["error"])) {
    // Ici on a au moins 1 message d'erreur
    foreach ($_SESSION["error"] as $error) {
        // On affiche chaque erreur dans une balise p
        echo "<p>$error</p>";
    }
    // On vide les messages d'erreur
    unset($_SESSION["error"]);
}

?>
<!-- Quand on a un champ type file, on est obligés de préciser l'attribut enctype -->
<h1>Modification Article</h1>
<form method="post" enctype="multipart/form-data">
    <h2>Votre article</h2>
    <div>
        <label for="titre">titre de l'article</label>
        <input type="text" id="titre" name="titre" value="<?= $article["titre"] ?>">
    </div>
    <br>
    <div>
        <label for="contenu">Contenu de l'article</label>
        <textarea rows="5" cols="60" wrap="physique" name="contenu" id="contenu"><?= $article["contenu"] ?></textarea><br />
    </div>

    <div>
        <h2>Choisir une/des catégorie(s) ?</h2>
        <?php
        //On crée un tableau contenant les id des catégories à cocher
        $articleCategorie = explode(",", $article["categorie"]);
        foreach ($categories as $categorie) : 
            ?>
            <!-- on ajoute l'attribut "checked" si l'id de la catégorie est dans $articleCategorie -->

            <input type="checkbox" value="<?= $categorie["id"] ?>" name="categorie[]" id="categorie<?= $categorie["id"] ?>" <?= (in_array($categorie["id"], $articleCategorie)) ? "checked" : "" ?>><!-- si oui si non -->
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
include_once "../../includes/footer.php";
