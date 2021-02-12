<?php
// On importe les "classes" PHPMailer
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// 1 Authentification de l'utilisateur et ouverture de la session
//Appel le fichier qui génére la session
require_once "includes/session.php";

//Vérifier que le token existe
if (isset($_GET["token"]) && !empty($_GET["token"])) {
    //on a un token
    //Vérification dans la base
    //on récupére le token
    $token = $_GET["token"];

    //on se connect
    require_once "includes/connect.php";

    //on écrit la requête
    $sql = "SELECT * FROM `user` WHERE `reset_token` = :token";

    //on prépare
    $query = $db->prepare($sql);

    // on injecte
    $query->bindValue(":token", $token, PDO::PARAM_STR);

    //on exécute
    $query->execute();

    //on récupére le user
    $user = $query->fetch();

    //on vérifie si le USER existe
    if (!$user) {
        //pas de USER -> le token n'existe pas
        header("Location: index.php");
        exit;
    }

    //vérif formulaire complet
    if (
        isset($_POST["pass"], $_POST["secret"], $_POST["reponse"])
        && !empty($_POST["pass"])
        && !empty($_POST["secret"])
        && !empty($_POST["reponse"])
    ) {
        //vérifier question et réponse sécurité correspondent au USER du token
        if ($user["secret_id"] === $_POST["secret"] && $user["secret_reponse"] === $_POST["reponse"]) {
            //la question et la reponse sont identique
            //echo "tout va bien";
            //Hash mot de passe et on le met dans la base
            $pass = password_hash($_POST["pass"], PASSWORD_ARGON2I);
            //UPDATE `user` SET `reset_token` = 'mettreiciletoken', `token_date` = NOW() WHERE `id` = iduser
            $sql = "UPDATE `user` SET `pass` = '$pass', `reset_token` = NULL, `token_date` = NULL WHERE `id` = {$user["id"]}";
            //on execute la requête
            $query = $db->query($sql);

            //********************************************* */
            //On envoie un mail de confirmation
            // On charge l'autoload
            // Ca permet de charger toutes les librairies de vendor
            require_once "vendor/autoload.php";

            //on "instancie" PHPmailer
            //on met le système PHPmailer dans une variable
            $mail = new PHPMailer(true);
            //true permet de voir les messages d'erreur dans la page PHP

            //dans $mail, j'ai phpmailer
            //on utilise un try/catch pour généré les erreurs
            try {
                //on configure le serveur d'envoi
                //On indique qu'on va utiliser un serveur SMTP
                $mail->isSMTP();
                //on indique l'adresse du serveur et son port
                $mail->Host = "localhost";
                $mail->Port = 1025;
                $mail->CharSet = "UTF-8";

                //on définit l'expéditeur et les destinataires
                $mail->setFrom("bruno@projet-blog.fr", "bruno");
                $mail->addAddress($user["email"], "{$user["first_name"]} {$user["name"]}");

                //on définit le contenu(sujet, message, pièces jointes....)
                $mail->isHTML();
                $mail->Subject = "Réinitialisation de mot de passe";
                $mail->Body = "<h1>Votre demande</h1>Vous avez demandé la réinitialisation de votre mot de passe. Ceci est maintenant effectué</p>
                <p>Si vous n'êtes pas à l'origine de cette demande, merci de nous contacter rapidement.</p>";
                $mail->altBody = "ous avez demandé la réinitialisation de votre mot de passe. Ceci est maintenant effectué. Si vous n'êtes pas à l'origine de cette demande, merci de nous contacter rapidement.";

                //on envoie 
                $mail->send();
                //**************************************** */
                //on redirige sur la page de connexion
                header("Location: connexion.php");
                exit;
            } catch (Exception $e) {
                //En cas d'erreur on affiche le message venant de PHPmailer
                $_SESSION["error"][] = "Le message n'est pas parti. Erreur : {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION["error"][] = "Le mot de passe ou la réponse à la question est incorrect";
        }
    } else {
        //formulaire incomplet
        $_SESSION["error"][] = "Formulaire incomplet";
    }
} else {
    //on n'a pas de token ou il est vide
    header("Location: index.php");
}

include_once "includes/header.php";
?>
<h1>réinitialisation mot de passe</h1>
<?php
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

<form method="post">
    <div>
        <!-- ATTENTION exemple pour le mot de passe, éviter de le faire-->
        <label for="pass">Nouveau mot de passe</label>
        <input type="password" name="pass" id="pass" required>
    </div> -->
    <br>
    <div>
        <label for="secret">Question secréte</label>
        <select name="secret" id="secret" required>
            <option value="">-- Sélectionnez une question --</option>
            <option value="1">Nom de jeune fille de votre mère</option>
            <option value="2">La ville où vous avez grandi</option>
            <option value="3">Nom de votre première école</option>
            <option value="4">Nom du premier animal de compagnie</option>
        </select>
    </div>
    <div>
        <label for="reponse">Réponse</label>
        <input type="text" name="reponse" id="reponse" required>
    </div>
    <button type="submit">Réinitialisation mot de passe</button>

</form>
<?php
include_once "includes/footer.php";
