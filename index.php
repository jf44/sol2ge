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
$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles
$dossier_kml='kml';
$extension_kml='.kml'; // lue par Google Earth
$dossier_kmz='kmz';
$extension_kmz='.kmz'; // lue par Google Earth

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

// COOKIES INPUT

if (isset($_COOKIE["sollang"]) && !empty($_COOKIE["sollang"])){
	$lang=$_COOKIE["sollang"];
}

if (isset($_COOKIE["solracenumber"]) && !empty($_COOKIE["solracenumber"])){
	$racenumber=$_COOKIE["solracenumber"];
}

if (isset($_COOKIE["solracename"]) && !empty($_COOKIE["solracename"])){
	$racename=$_COOKIE["solracename"];
}

// GET
if (isset($_GET['lang'])){
	$lang=$_GET['lang'];
}

if (isset($_GET['racenumber'])){
	$racenumber=$_GET['racenumber'];
}

if (isset($_GET['racename'])){
	$racename=$_GET['racename'];
}

if (isset($_GET['token'])){
	$token=$_GET['token'];
}

// POST

if (isset($_POST['lang'])){
	$lang=$_POST['lang'];
}

if (isset($racename) && !empty($racename) ){
	setcookie("solracename", $racename);
}
if (isset($racenumber) && ($racenumber!="") ){
	setcookie("solracenumber", $racenumber);
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

require_once("./sol_include/sol_connect.php"); // utilitaires de connexion au serveur SOL

entete();

echo '<div id="bigdisplay">
<h2>'.$al->get_string('teaser').'</h2>
<p>
<a href="md2html.php?lang='.$lang.'&amp;filename=README.md">Readme Doc</a> - <a href="md2html.php?lang='.$lang.'&amp;filename=DEVELOPER.md">Developer Doc</a> - <a href="images/index.php">Images</a>
</p>
<p><img src="images/sol_BostonNewport_20170330_2.jpg" alt="Boston Newport 2017" title="Boston Newport 2017" heigth="400" width="800">
</p>

';

// afficheArchivesKML();
if ($datatodisplay=verifieArchivesKML()){
	echo '<h4>'.$al->get_string('archives').'</h4>
<p><span class="small">'.$al->get_string('info1').'<br /><i>'.$al->get_string('info2').'</i></span></p>'."\n";

	echo '
<h5>'.$al->get_string('mapready').'</h5>
';

    displayArchivesKML($datatodisplay);
	if ($datatodisplay->nkml){
		echo '
<button id="rollButtonkml" type="button" onclick="rollKml()">++KML</button>
';
		echo '<div id="divkml">
<script type="text/javascript">
displayPagekml();
</script>
</div>
';
	}

	if ($datatodisplay->nkmz){
		echo '
<button id="rollButtonkmz" type="button" onclick="rollKmz()">++KMZ</button>
';
		echo '<div id="divkmz">
<script type="text/javascript">
displayPagekmz();
</script>
</div>
</div>
';
	}
}

enqueue();


// ----------
function get_readmefile(){
	if (file_exists('README.md')){
		return file_get_contents('README.md');
	}
	else{
 		if (file_exists('redame.txt')){
			return  file_get_contents('readme.txt');
		}
		else{
        	return ('**Not any README.md file here**'."\n");
		}
	}
}

//---------
function entete(){
	global $appli;
	global $racenumber;
	global $racename;
	global $token;
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
<h1 align="center">'.$al->get_string('titleindex').'</h1>
<p align="center">
';
	if (!empty($tlanglist)){
		foreach ($tlanglist as $alang){
			if ($alang==$lang){
				echo ' <b>'.$al->get_string($alang).'</b> &nbsp; - ';
			}
			else{
				echo '<a href="'.$appli.'?lang='.$alang.'&racenumber='.$racenumber.'">'.$al->get_string($alang).'</a> &nbsp; - ';
			}
		}
	}
	onelinemenu();
	echo '</p>
</div>
';
}



// ------------------
function verifieArchivesKML(){

global $dossier_kml;
global $extension_kml;
global $dossier_kmz;
global $extension_kmz;
global $al;

// DEBUG
// echo "<br>Fichier KML courant : $fichier_kml_courant\n";
	$tikml=array();
	$tikmz=array();
	$traceskml=array();
	$traceskmz=array();
	$sep = '/';
    $nobj = 0;
    $nkml = 0;
	$nkmz = 0;
	$ndir = 0;

	$path = './'.$dossier_kml;
	$h1=opendir($path);

    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// KML
    	       	$g= eregi_replace($extension_kml,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
/*
					(strtoupper($g) != strtoupper($fichier_kml_courant)) // le fichier par defaut n'est pas affiché
					&&
					(strtoupper($g) != strtoupper($fichier_kml_cache)) // le fichier de cache n'est pas affiché
					&&
					(substr($g,0,1) == substr($fichier_kml_courant,0,1)) // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					&&
*/
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(strtoupper($g.$extension_kml) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$nkml ++;
	               	$tikml[$f] = $f ;
				}
			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

    $path = './'.$dossier_kmz;
 	$h2=opendir($path);

    while ($f = readdir($h2) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// KML
    	       	$g= eregi_replace($extension_kmz,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
/*
					(strtoupper($g) != strtoupper($fichier_kml_courant)) // le fichier par defaut n'est pas affiché
					&&
					(strtoupper($g) != strtoupper($fichier_kml_cache)) // le fichier de cache n'est pas affiché
					&&
					(substr($g,0,1) == substr($fichier_kml_courant,0,1)) // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					&&
*/
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(strtoupper($g.$extension_kmz) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$nkmz ++;
	               	$tikmz[$f] = $f ;
				}
			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h2);

	if ($nobj>0){
		$data = new stdClass();
        $data->nkml = $nkml;
        $data->tikml = $tikml;
        $data->nkmz = $nkmz;
        $data->tikmz = $tikmz;
        return $data;
	}

	return NULL;

}

//-------------------
function displayArchivesKML($data){
global $dossier_kml;
global $extension_kml;
global $dossier_kmz;
global $extension_kmz;
global $al;

$sep = '/';
$path = '.';

	if (!empty($data) && isset($data->nkml) && isset($data->nkmz)){

 		if (($data->nkml > 0) || ($data->nkmz > 0)){
			// Javascript
        	echo '<script type="text/javascript">'."\n";
			echo '
// Display KML files
var indexkml = 0;
var tjkml = new Array();
';

			if ( $data->nkml > 0){
            	//echo '<b>'.$al->get_string('kmlfile').'</b><br />'."\n";
		        rsort($data->tikml);
				$j=0;
				while (list($key) = each($data->tikml)) {
					echo 'tjkml['.$j.'] = "<a  class=\"small\" href=\"'.$path.$sep.$dossier_kml.$sep.$data->tikml[$key].'\">'.$data->tikml[$key].'</a>  &nbsp; &nbsp; &nbsp; ";'."\n";
					$j++;
    			}
			}

			// fonctions
			echo '
function displayPagekml() {
	var skml = \'\';
	if ( tjkml.length< 20){
   		for (i=0;i<tjkml.length;i++){
			skml+= tjkml[i] + " ";
		}
	}
	else{
		var $aff =  Math.min (indexkml+20, tjkml.length);
		var aff2 =  Math.min (20 - ($aff - indexkml), tjkml.length);
        for (i=indexkml;i<$aff;i++){
			skml+= tjkml[i] + " ";
		}
        //skml+= \'<br>\'+aff2+\'<br>\';
		if (aff2>0){
        	for (i=0;i<aff2;i++){
				skml+= tjkml[i] + " ";
			}
		}
	}
	document.getElementById(\'divkml\').innerHTML=skml;
}
';

			echo '
function rollKml() {
    indexkml=++indexkml  % tjkml.length;  // pre-increment is better
    displayPagekml();
}
';

            echo '
// Display KMZ files
var indexkmz = 0;
var trkmz = new Array();
var tjkmz = new Array();

';
			if ( $data->nkmz > 0){
				//echo '<br /><br /><b>'.$al->get_string('kmzfile').'</b><br />'."\n";
		        rsort($data->tikmz);
				// Lister les courses
				/*
				$j=0;
				$k=0;
				while (list($key) = each($tikmz)) {
					if ($race=substr($tikmz[$key],0,strpos($tikmz[$key],'_')) !== false) {
						if (!isset($traceskmz[$race])){
                            $traceskmz[$race]=$race;
							echo '$t_rkmz['.$k.'] = "'.$race.'";';
							$k++;
						}
					}
				}
				*/
				$j=0;
				while (list($key) = each($data->tikmz)) {
		        	//echo '<a  class="small" href="'.$path.$sep.$key.'">'.$tikmz[$key].'</a>  &nbsp; &nbsp; &nbsp; '."\n";
					echo 'tjkmz['.$j.'] = "<a  class=\"small\" href=\"'.$path.$sep.$dossier_kmz.$sep.$data->tikmz[$key].'\">'.$data->tikmz[$key].'</a>  &nbsp; &nbsp; &nbsp; ";'."\n";
					$j++;
    			}
			}
			// fonctions
  			echo '
function displayRacekmz() {
	var rkmz = \'\';
	for (i=0;i<trkmz.length;i++){
		rkmz+= trkmz[i] + " ";
	}
    document.getElementById(\'divrkmz\').innerHTML=rkmz;
}

function displayPagekmz() {
	var skmz = \'\';
	if ( tjkmz.length< 20){
   		for (i=0;i<tjkmz.length;i++){
			skmz+= tjkmz[i] + " ";
		}
	}
	else{
		var $aff =  Math.min (indexkmz+20, tjkmz.length);
		var aff2 =  Math.min (20 - ($aff - indexkmz), tjkmz.length);
        for (i=indexkmz;i<$aff;i++){
			skmz+= tjkmz[i] + " ";
		}
        //skmz+= \'<br>\'+aff2+\'<br>\';
		if (aff2>0){
        	for (i=0;i<aff2;i++){
				skmz+= tjkmz[i] + " ";
			}
		}
	}
	document.getElementById(\'divkmz\').innerHTML=skmz;
}
';

			echo '
var rollKmz = function () {
    indexkmz=(indexkmz+3)  % tjkmz.length;  // pre-increment is better
    displayPagekmz();
}
';
/*
            echo '
//when the user presses the button it will display  the array
    document.getElementById(\'rollButtonkmz\').addEventListener(\'click\', rollKmz());
';
*/
			echo '</script>'."\n";
		}
	}
	else{
        echo '<p>'.$al->get_string('nofilekml').'</p>'."\n";
	}

}



?>