<?php
// 1 Authentification de l'utilisateur et ouverture de la session
//Appel le fichier qui génére la session
require_once "includes/session.php";
// 2 On vérifie que le formulaire est complet
//********************** */
if (!empty($_POST)) {
    //Envoi formulaire
    
    //Vérification que tous les champs existent et ne sont pas vides
    if (
        isset($_POST["email"], $_POST["password"])
        && !empty($_POST["email"])
        && !empty($_POST["password"])
    ) {
        //le formulaire est complet
        $email = $_POST["email"];
        // le mot de passe tel quel
        $pass = $_POST["password"];
        // 3 On va chercher l'utilisateur dans la base par son email
        //Connection à la base
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
            $_SESSION["error"][] = "L'adresse email et/ou le mot de passe est incorrect";
        } else {

            // 4 On vérifie que les mots de passe (clair et haché) correspondent
            if (!password_verify($pass, $user["password"])) {
                //Mot de passe ne correspond pas
                $_SESSION["error"][] = "L'adresse email et/ou le mot de passe est incorrect";
            } else {

                //Mot de passe correspond

                // 5 On peut ouvrir la session
                //session_start();

                $_SESSION["user"] = [
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "first_name" => $user["first_name"],
                    "nickname" => $user["nickname"],
                    "email" => $user["email"],
                    "role" => json_decode($user["role"]) //Transforme le json en tableau php
                ];

                //on gére la case à cocher 'remember'
                //Vérifié si case cocher
                if(isset($_POST["remember"])){
                    //si oui créer token, créer cookie, et enregistrer dans la base
                    $token = md5(uniqid());

                    //creer cookie valable 7 jours
                    setcookie("remember", $token, strtotime("+7days"));

                    //enregistre dans la base
                    $sql = "UPDATE `user`SET `remember_token` = '$token' WHERE `id` = {$user["id"]}";
                    
                    //Exécution
                    $query = $db->query($sql);

                }//sinon, rien pas de else
                

                //redirection vers profil.php
                header("Location: user/profil.php");
            }
        }
    } else {
        // au moins 1 champs est vide ou inexistant
        $_SESSION["error"][] = "le formulaire est incomplet!";
        //header("Location: connection.php");
    }
    //Adresse mail est ok et dans la base  
}
include_once "includes/header.php";
?>
<h1>Formulaire de connexion</h1>

<?php  
//var_dump($_SESSION["error"]);

//Affichage du/des message(s) d'erreur
//Vérificaion que $_session_error existe
if(isset($_SESSION["error"])){
    //ici au moins 1 message d'erreur
    foreach($_SESSION["error"] as $error){
        //on affiche chaque erreur dans une balise p
        echo "<p>$error</p>";
    }
    //on vide les messages d'erreurs
    unset($_SESSION["error"]);
}
?>

<form method="post">
    <div>
        <label for="email">Email </label>
        <!-- <input type="email" id="email" name="email" value="<?php
        if(isset($_SESSION["form"]["email"])){
            echo $_SESSION["form"]["email"];
        }  ?>" required> -->
        <input type="email" name="email" id="email" value="<?= $_SESSION["form"]["email"] ?? ""  ?>">
    </div>
    <br>
    <div>
    <!-- ATTENTION exemple pour le mot de passe, éviter de le faire-->
        <label for="pass">Mot de passe</label>
        <input type="password" name="password" id="password" value="<?= $_SESSION["form"]["password"] ?? ""  ?>">
        <p><a href="../modifPass.php">Mot de passe oubliè</a></p>
    
    </div>
    <div>
    <input type="checkbox" name="remember" id="remember">
    <label for="remember">Rester connecté</label>
    </div>
    <br>
    <div>
    <button type="submit">Me connecter</button>
    </div>
</form>


<?php
unset($_SESSION["form"]);
//on inclut le header
include_once "includes/footer.php";
