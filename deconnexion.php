<?php
session_start();
//ATTENTION déconnection du user n'est pas une destruction de la session
unset($_SESSION["user"]);
//supprime le cookie remember
setcookie("remember", "", 1);

header("Location: index.php");