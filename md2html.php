<?php
// index.php
// JF 2009  - 2017

//define ('DEBUG', 0);      // debogage maison !:))
define ('DEBUG', 1);
define ('DEBUG2', 0);
require_once('include/utils.php'); // utilitaires divers
require_once('lang/GetStringClass.php'); // localisation
require_once('sol_include/sol_config.php'); // utilitaires de connexion au serveur SOL


$version="0.1-20170331";
$lang='en'; // par defaut
$filename='README.md';

$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles

if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
	$uri = 'https://';
} else {
	$uri = 'http://';
}
//$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].get_url_pere($_SERVER['SCRIPT_NAME']);
$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].get_url_pere($_SERVER['SCRIPT_NAME']);
// DEBUG
//echo "<br>URL : $url_serveur_local<br />\n";
$dir_serveur = dirname($_SERVER['SCRIPT_FILENAME']);
// DEBUG
//echo "<br>Répertoire serveur : $dir_serveur<br />\n";
// Nom du script chargé dynamiquement.
$phpscript=substr($_SERVER["PHP_SELF"], strrpos($_SERVER["PHP_SELF"],'/')+1);
$appli=$uri.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
//echo $appli;
//exit;

// COOKIES INPUT


if (isset($_COOKIE["sollang"]) && !empty($_COOKIE["sollang"])){
	$lang=$_COOKIE["sollang"];
}

// GET
if (isset($_GET['lang'])){
	$lang=$_GET['lang'];
}

if (isset($_GET['filename'])){
	$filename=$_GET['filename'];
}

if (isset($lang) && ($lang!="") ){
	setcookie("sollang", $lang);
}




// Localisation linguistique
$al= new GetString();
$tlanglist=$al->getAllLang('./lang',$module);
if ($aFile = $al->setLang('./lang', $lang, $module)){
    require_once($aFile); // pour la localisation linguistique
}

entete();
echo '<div id="console">
';
if (empty($filename)){
    $filename='README.md';
}
	echo '<article id=\'parsed\'><noscript id=\'md\'>';
	echo get_readmefile($filename);
	echo '</noscript></article>
';

	echo '
<script src=js/mmd/mmd.min.js></script>
<script>

onload = function()
{
	var $ = function(id){ return document.getElementById(id); };
    var src = $(\'md\').textContent;
    var then = Date.now();
    var html = mmd(src);
    var now = Date.now();

    // document.title += \' Just parsed \'+ src.length +\' md -> \'+ html.length +\'b html in \'+ (now-then) +\'ms\';
    $(\'parsed\').innerHTML = html;
}

</script>

';
echo '</div>
';

enqueue();


// ----------
function get_readmefile($filename){
	if (file_exists($filename)){
		return file_get_contents($filename);
	}
	else{
       	return ("# ERROR\n\n**Not any $filename down there**\n");
	}
}

//---------
function entete(){
	global $appli;
	global $filename;
	global $al;
    global $lang;
	global $tlanglist;

	echo '<!DOCTYPE html>
<html  dir="ltr" lang="fr" xml:lang="fr">
<head>
	<title>Sailonline Tools</title>
	<meta name="ROBOTS" content="none,noarchive">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="JF">
	<meta name="description" content="SailOnLine Tools"/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
';
	echo '<div id="bandeau">
<h1 align="center">'.$filename.'</h1>
<p align="center">
<a href="index.php?lang='.$lang.'">'.$al->get_string('home').'</a> - <a href="'.$appli.'?lang='.$lang.'&amp;filename=README.md">Readme Doc</a> - <a href="'.$appli.'?lang='.$lang.'&amp;filename=DEVELOPER.md">Developer Doc</a>
</p>
</div>
';
}


?>