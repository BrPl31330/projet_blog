<?php
//on se connecte à une base de donnée mysql
//on a besoin de 4 info
//serveur : localhost
//le nom utilisateur : root
//mot passe : vide
//le nom de la base : projet_blog
//on définit 4 constantes en php
define("DBHOST", "localhost");
define("DBUSER", "root");
define("DBPASS", "");
define("DBNAME", "projet_blog");

//NE RIEN CHANGER CI-DESSOUS

//on définit le DSN (Data Source Name) de connexion
$dsn = "mysql:dbname=" . DBNAME . ";host=" . DBHOST;

try {
    //on se connecte à la base de donnée en "instanciant" PDO
    $db = new PDO($dsn, DBUSER, DBPASS);

    //on définit le charset à "utf8"
    $db->exec("SET NAMES utf8");

    //on définit la méthode de récupération (fetch) des données
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    //PDOException $e -> on attrape l'erreur provoquée par le new PDO en cas d'échec
    //on affiche le message d'erreur si le new PDO échoue
    die($e->getMessage());
}