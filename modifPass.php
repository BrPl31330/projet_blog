<?php
//installation phpmailler dans le projet, par composer
//ouvrir terminal visual studio
//taper composer require phpmailer/phpmailer
//valider
//si erreur souligne rouge ->affichage->palette->intelephence->indexWorkSpace
// On importe les "classes" PHPMailer
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// On authentifie l'utilisateur et on ouvre sa session
// On appelle le fichier qui gère la session
require_once "includes/session.php";


//********************************************* */
if (!empty($_POST)) {
    //Envoi formulaire

    //Vérification que tous les champs existent et ne sont pas vides
    if (
        isset($_POST["email"]) && !empty($_POST["email"])
    ) {

        //le formulaire est complet
        $email = $_POST["email"];
        //on hashes le mot de passe tel quel

        require_once "includes/connect.php";

        //Ecriture de la requete
        $sql = "SELECT * FROM `user` WHERE `email` = :email";

        //Préparation de la requete
        $query = $db->prepare($sql);

        //Injection des paramètres
        $query->bindValue(":email", $email, PDO::PARAM_STR);

        //Exécution de la requete
        $query->execute();

        //Récupération des données
        $user = $query->fetch();
        //Vérification utilisateur dans la base
        if (!$user) {
            //ICI Pas de user connu
            $_SESSION["error"][] = "Une erreur est survenue";
        } else {
            //ici le USER est connu
            //Email correspond à un USER

            //Création du token
            $token = md5(uniqid());

            //on insére ce token en base et on envoie le mail

            //UPDATE `user` SET `reset_token` = 'mettreiciletoken', `token_date` = NOW() WHERE `id` = iduser
            $sql = "UPDATE `user` SET `reset_token` = '$token', `token_date` = NOW() WHERE `id` = {$user["id"]}";

            //on execute la requête
            $query = $db->query($sql);

            //on envoie l'email
            //on charge l'autoload
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
                $mail->addAddress($user["email"], "{$user["first_name"]}");

                //on définit le contenu(sujet, message, pièces jointes....)
                $mail->isHTML();
                $mail->Subject = "Réinitialisation de mot de passe";
                $mail->Body = "<h1>Votre demande</h1>Vous avez demandé la réinitialisation de votre mot de passe. Votre lien : <a href='" . URL . "/nouveauPass.php?token=$token'>Cliquez ici</a></p>";
                $mail->altBody = "Vous avez demandé la réinitialisation de votre mot de passe. Votre lien : " . URL . "/nouveauPass.php?token=$token";

                //on envoie 
                $mail->send();
                $_SESSION["error"][] = "Un message contenant un lien de réinitialisation a été envoyé";
                header("Location: modifPass.php");

                exit;
            } catch (Exception $e) {
                //En cas d'erreur on affiche le message venant de PHPmailer
                $_SESSION["error"][] = "Le message n'est pas parti. Erreur : {$mail->ErrorInfo}";
            }
        }
    } else {
        $_SESSION["error"][] = "Formulaire incomplet";
    }
}
include_once "includes/header.php"
?>

<h1>Formulaire mot de passe oubliè</h1>
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
<div>
    <p>Lien pour réinitialiser le mot de passe, entrez votre mot de passse ci-dessous</p>
</div>

<form method="post">
    <div>
        <label for="email">Email </label>
        <input type="email" name="email" id="email" value="<?= $_SESSION["form"]["email"] ?? "" ?>">
    </div>
    <br>

    <div>
        <button type="submit">Valider</button>
    </div>
    <br>

</form>
<?php
include_once "includes/footer.php";
