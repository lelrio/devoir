<?php

//fichier de fonctionnalités communes à plusieurs scripts

//connexion à la base de données
try{
	$db = new PDO('mysql:host=localhost;dbname=Blog;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (Exception $exception)
{
	die( 'Erreur : ' . $exception->getMessage() );
}

//ouverture de session pour connexions utilisateurs et admins
session_start();

?>
