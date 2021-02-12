<?php
require_once "includes/session.php";
//doit permettre à un visiteur de s'inscrire
//password_hash -> inscription, génère un hash de mot de passe
// 2 paraméters : le mot de passe et l'algo
$mdp = password_hash("Bonjour", PASSWORD_ARGON2I);

//on vérifie le mot de passe au moment de la connection
// 2 paramétres : mot de passe saisi et le mot passe haché
//$verif = password_verify("Bonjour", $pass);
//var_dump($_POST); //pour vérifier les erreurs IMPORTANT
//on vérifie que POST est pas vide
if (!empty($_POST)) {

    //on vérifie que tous les champs existent et ne sont pas vide
    if (
        isset($_POST["nom"], $_POST["prenom"], $_POST["pseudo"], $_POST["email1"], $_POST["email2"], $_POST["secret"], $_POST["reponse"], $_POST["pass"], $_POST["rgpd"])
        && !empty($_POST["nom"])
        && !empty($_POST["prenom"])
        && !empty($_POST["pseudo"])
        && !empty($_POST["email1"])
        && !empty($_POST["email2"])
        && !empty($_POST["secret"])
        && !empty($_POST["reponse"])
        && !empty($_POST["pass"])
        && !empty($_POST["rgpd"])
    ) {
        //le formulaire est complet, on pourra le traiter
        //la case RGPD est forcément cochée
        //récupére et protége les données
        $nom = strip_tags($_POST["nom"]);
        $prenom = strip_tags($_POST["prenom"]);
        $pseudo = strip_tags($_POST["pseudo"]);

        //on modifie pas les emails
        $email1 = $_POST["email1"];
        $email2 = $_POST["email2"];

        $secretId = $_POST["secret"];
        $secretReponse = $_POST["reponse"];
        //on hashes le mot de passe tel quel
        $pass = password_hash($_POST["pass"], PASSWORD_ARGON2I); 

        //on vérifie que email1 est 1 email
        if (!filter_var($email1, FILTER_VALIDATE_EMAIL)) {
            //email 1 n'est pas valide
            die("mail invalide");
        } 
        //on compare les 2 emails
        if ($email1 != $email2) {
            die("les deux emails doivent être identiques");
        }

        //on peut inscrire l'utilisateur
        //on se connecte
        require_once "includes/connect.php";

        //on écrit le requete
        $sql = "INSERT INTO `user`(`name`,`first_name`,`email`,`nickname`,`password`, `rgpd`, `role`, `secret_id`, `secret_reponse`) 
            VALUES (:nom, :prenom,:email, :pseudo, :pass, 1, '[\"ROLE_USER\"]', :secretId, :secretReponse)";

        //on prepare la requete
        $query = $db->prepare($sql);

        //on injecte les paramètres
        //1 bindvalue par paramètre SQL
        $query->bindValue(":nom", $nom, PDO::PARAM_STR);
        $query->bindValue(":prenom", $prenom, PDO::PARAM_STR);
        $query->bindValue(":pseudo", $pseudo, PDO::PARAM_STR);
        $query->bindValue(":email", $email1, PDO::PARAM_STR);
        $query->bindValue(":pass", $pass, PDO::PARAM_STR);
        $query->bindValue(":secretId", $secretId, PDO::PARAM_STR);
        $query->bindValue(":secretReponse", $secretReponse, PDO::PARAM_STR);

        //on execute la requete
        if (!$query->execute()) {
            die("un petit problème est survenu");
        }

        //redirection vers index.php
        header("Location: index.php");
    } else {
        // au moins 1 champs est vide ou inexistant
        die("le formulaire est incomplet!");
    }
}


include_once "includes/header.php";

?>
<h2>Inscription</h2>
<p>Tous les champs sont obligatoires</p>
<form method="post">
    <div>
        <label for="name">Nom </label>
        <input type="text" id="name" name="nom" required>
    </div>
    <br>
    <div>
        <label for="firstname">Prénom </label>
        <input type="text" name="prenom" id="firstname" required>
    </div>
    <br>
    <div>
        <label for="nickname">Pseudo</label>
        <input type="text" name="pseudo" id="nickname" required>
    </div>
    <br>
    <div>
        <label for="email1">Email</label>
        <input type="email" name="email1" id="email1" required>
    </div>
    <br>
    <div>
        <label for="email2">Confirmer email</label>
        <input type="email" name="email2" id="email2" required>
    </div>
    <br>
    <div>
    <p></p>
        <label for="secret">Question secréte</label>
        <select name="secretId" id="secret" required>
            <option value="">-- Sélectionnez une question --</option>
            <option value="1">Nom de jeune fille de votre mère</option>
            <option value="2">La ville où vous avez grandi</option>
            <option value="3">Nom de votre première école</option>
            <option value="4">Nom du premier animal de compagnie</option>
        </select>
    </div>
    <br>
    <div>
        <label for="reponse">Réponse</label>
        <input type="text" name="secretReponse" id="reponse" required>
    </div>
    <br>
    <div>
        <label for="pass">Mot de passe</label>
        <input type="password" name="pass" id="pass" required>
    </div>
    <br>
    <div>
        <input type="checkbox" name="rgpd" id="rgpd" required>
        <label for="rgpd">J'accepte la collecte de mes données personnelles dans le cadre exclusif de ce formulaire d'inscription. J'ai bien noté que ces données ne seront pas cédées à des entreprises tierces et seront détruites lors de ma désinscription de ce site.</label>
    </div>
    <br>
    <button type="submit">Valider</button>
</form>
<a href="index.php">Revenir à l'accueil</a>


<?php
//on inclut le header
include_once "includes/footer.php";
