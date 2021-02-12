<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= URL ?>/CSS/style.css">
    <title>Projet Blog</title>
    <style>
       

    </style>
</head>

<body>
    <header>

        <img src="<?= URL ?>/image/banniere.jpg" alt="" height="200rem" width="100%">
        <h1>Projet Blog</h1>
        <nav id="nav">
            <?php if (isset($_SESSION["user"])) : ?>
                <!-- //on a une session user -->
                Bonjour <?= $_SESSION["user"]["first_name"] ?>
                <li><a href="<?= URL ?>/deconnexion.php">Déconnexion</a></li>

            <?php else : ?>
                <!-- //On n'a pas de session user -->

                <li><a href="<?= URL ?>/inscription.php">Inscription</a></li>
                <li><a href="<?= URL ?>/connexion.php">Connexion</a></li>

            <?php endif; ?>
        </nav>





        <!-- Un utilisateur "anonyme" vera inscription et connexion-->
        <!-- Un utilisateur authentifié verra Déconnexion-->

    </header>