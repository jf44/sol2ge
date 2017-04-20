<?php
// JF 2009  - 2017
// Affiche les positions istantanées des bateaux de SailOnLine en exportant un fichier KML
// KML / KMZ files generations of SailOnLine boats positions
// Do not uses Data Base.
// Boat positions are not kept
// Decompression fichier
// Marques  et polaires / WP and polar
// http://144.76.111.8/webclient/webclient/auth_raceinfo_1018.xml?token=8a293f8c7c53cc894511b442cc49b408
// Trace des bateaux / Baot tracks
// http://www.sailonline.org/webclient/race_1018.xml?token=6de86cea2c3fb6dcde38af806421cc50
// retourne un ficher xml gzipé
// Output a KML (a link to the server) and a KMZ (full data + models + textures) to
// download
// NOTA BENE :
// Editer la variable $surl_serveur_local (ligne 127) si vous déplacez le script vers un serveur distant
// Edit the $url_serveur_local (line 130) if you move the srcipt to a remote server


define ('DEBUG', 0);      // debogage maison !:))
//define ('DEBUG', 1);
define ('DEBUG2', 0);       //  debogage maison !:))

require_once('lang/GetStringClass.php'); // pour le fonction de manipulation de chaines
require_once('sol_include/sol_config.php'); // utilitaires de connexion au serveur SOL
require_once('include/utils.php'); // utilitaires divers

// Gestion des Grib et des traces
require_once('include/GribClass.php'); // pour le fonction de manipulation de grib
require_once('include/Trace.php'); // Pour les trajectoires

// Gestion des fichiers KML / KMZ
require_once('include/zip.php'); // utilise la bibliotheque pclzip
require_once('include/cache_voiliers.php'); // fonction de cache pour les donnees voilier
require_once('include/GeoCalc.class.php'); // pour le calcul de distance par GrandCercle
require_once('include/Voilier.php'); // Définition de la classe Voilier
require_once('include/kml_trajectoire.php'); // Génération KML de la trajectoire
require_once('include/kml_3d.php'); // Génération KML des bateaux comme des modeles 3D
require_once('include/text2image.php'); // Creation d'overlay avec le nom de la course sur G.E.

$archive = true; // Les fichiers créés sont archivés sous forme kmz
$version="0.2-20170416";
$lang='fr'; // par defaut
$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles


/*
// sol_connect.php
// Path and urls for data download
$solhost='http://node1.sailonline.org/';
$webclient = 'webclient/';
$serviceauth = 'authenticate.xml';
$serviceraces='races.xml';
$serviceraceinfo = 'auth_raceinfo_';
$serviceboat = 'boat.xml';
$servicetracks='traces_';

$racenumber='';
$racename='';
$token='';
*/

$okboattype=false;
$okscale=false;
$okracenumber=false;

// Variable pour le téléchargement des fichiers de données
$pathrace='race_info'; // stockage local des courses
$prefixrace='race_';
$extension='.xml';

$filenameraceboats='';
$filenametracks='';

$grib_path='/SOLGribXml';  // sous-dossier pour stocker les fichiers Grib (identique à DCChecker)
$grib_filename='';         //
$grib2load='';

$filenamemarkpolars='';

$weatherurl='';   // url des gribs de a course  :: /webclient/weatherinfo_196.xml
$traceUrl=''; // => /webclient/traces_1018.xml

$boattype = '';   // Let's the soft determines the boat type

// Initialisé par le chargement de la polaire de voiles
$maxindextwa=0; // 181 : indice max des angles acceptable [0..180]
$maxindextws=0; // :: 40 indice max des vitesses acceptables [à priori 0..39]
$t_polaires = array();  // Table [twa][tws] retourne sog

$t_wp = array();  // le tableau des marques de parcours (qui sont des WP sur SailOnLine)
$t_grib = array();

$scale=1;		// valeur d'echelle des voiliers 3D par defaut
$mode='3D'; // affichage 3D des bateau par défaut
$ok3d=true;
$dossier_kml='kml';
$dossier_kmz='kmz';
$dossier_3d='sol3d';
$dossier_3d_cache=$dossier_3d; // initialise avec la date par un appel de fonction
$extension_dae='.dae'; // fichier COLLADA
$extension_kmz='.kmz'; // lue par Google Earth
$extension_kml='.kml'; // lue par Google Earth
$dossier_textures='textures';
$dossier_modeles='models';
$okmarques=true;  // marques de parcours si disponibles
$t_url_serveur=array();
/*
$utiliser_cache=0; // par defaut il y a pas de cache d'une heure sur les donnes des voiliers
$datacache='data'; // dossier de cache des voiliers; doit exister sur le serveur
$ext_data='.dat';	// fichier de donnees sauvegardee
$MAXTAILLECACHE='1024';
*/
$appli='';
$n=0;

$action='';

$voile=1; // Foc par défaut  ; modifié en fonction de l'angle au vent.
$t_voilier = array(); // liste des voiliers chargés
$t_parcours = array(); // liste des coordonnées des voiliers chargés pour generer le parcours du premier au dernier.

if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
	$uri = 'https://';
} else {
	$uri = 'http://';
}
/**
 *  VARIABLE  TO EDIT IF YOU MOVE THE SCRIPT TO A REMOTE SERVER
 **/
$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].get_url_pere($_SERVER['SCRIPT_NAME']);
// DEBUG
// echo "<br>URL : $url_serveur_local\n";

$url_serveur=$url_serveur_local; // par defaut le serveur sur lequel s'exécute le script


$dir_serveur = dirname($_SERVER['SCRIPT_FILENAME']);
// DEBUG
// echo "<br>Répertoire serveur : $dir_serveur\n";
// Nom du script chargé dynamiquement.
$phpscript=substr($_SERVER["PHP_SELF"], strrpos($_SERVER["PHP_SELF"],'/')+1);
$appli=$uri.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];

// heure    minute seconde jour mois annee
//$to_ten_minutes  = mktime(date("G"), date("i")+10, 0, date("m"),date("d"),date("Y"));
$to_one_hour  = mktime(date("G")+1, 0, 0, date("m"),date("d"),date("Y"));
//$from_ten_minutes  = mktime(date("G"), date("i")+10, 0, date("m")  , date("d"), date("Y"));
$from_one_hour  = mktime(date("G"), 0, 0, date("m")  , date("d"), date("Y"));
$date_cache_one_hour=date("Y-m-d H:i:s",$from_one_hour);
//$date_cache_ten_minutes=date("Y-m-d H:i:s",$from_ten_minutes);
//$str_time_cache_ten_minutes = " de dix minutes ";
$str_time_cache_one_hour = " d'une heure ";

// Dix minutes non completement implanté. A TERMINER... dans ExisteKML
//$date_cache=$date_cache_ten_minutes;
//$to_next_time_cache = $to_ten_minutes; // Cela pourrait être laissé à l'utilisateur
//$time_cache = $from_ten_minutes;
//$str_time_cache = $str_time_cache_ten_minutes;
// Seul le cache de 60 minutes est implanté car plus simple
$date_cache=$date_cache_one_hour;
$to_next_time_cache = $to_one_hour; // Cela pourrait être laissé à l'utilisateur
$time_cache = $from_one_hour;
$str_time_cache = $str_time_cache_one_hour;


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

if (isset($_COOKIE["solscale"]) && !empty($_COOKIE["solscale"])){
	$scale=$_COOKIE["solscale"];
}

/*
// No more
if (isset($_COOKIE["solboattype"]) && !empty($_COOKIE["solboattype"])){
	$boattype=$_COOKIE["solboattype"];
}
*/

// GET
if (isset($_GET['lang'])){
	$lang=$_GET['lang'];
}

if (isset($_GET['filename'])){
	$filename=$_GET['filename'];
	$action="go";
}

if (isset($_GET['racenumber'])){
	$racenumber=$_GET['racenumber'];
	$okracenumber=true;
}

if (isset($_GET['racename'])){
	$racename=$_GET['racename'];
}

if (isset($_GET['token'])){
	$token=$_GET['token'];
}

if (isset($_GET['scale'])){
	$scale=$_GET['scale'];
    $okscale=true;
}

if (isset($_GET['boattype'])){
	$boattype=$_GET['boattype'];
    $okboattype=true;
}

if ($okboattype && $okscale && $okracenumber){
	$action = 'Go';
}
// POST

if (isset($_POST['lang'])){
	$lang=$_POST['lang'];
}

if (isset($_POST['racenamenumber'])){
	$racenamenumber=$_POST['racenamenumber'];
	if (!empty($racenamenumber)){
		list($racenumber, $racename) = explode('#§#',$racenamenumber);
		$token=''; // Reset
	}
    else {
	    if (isset($_POST['racenumber'])){
			$racenumber=$_POST['racenumber'];
		}
    	if (isset($_POST['racename'])){
			$racename=$_POST['racename'];
		}
	}
}
else {
    if (isset($_POST['racenumber'])){
		$racenumber=$_POST['racenumber'];
	}
    if (isset($_POST['racename'])){
		$racename=$_POST['racename'];
	}
}

if (isset($_POST['token'])){
	$token=$_POST['token'];
}


if (isset($_POST['boattype'])){
	$boattype=$_POST['boattype'];
}

if (isset($_POST['action'])){
	$action=$_POST['action'];
}

// cache temporel sur les donnes des voiliers
if (isset($_POST['utiliser_cache']) && ($_POST['utiliser_cache']!='')){
	$utiliser_cache=$_POST['utiliser_cache'];
}

// 2D ou 3D
if (isset($_POST['mode']) && ($_POST['mode']!='')){
	$mode=$_POST['mode'];
}

// echelle des bateaux
if (isset($_POST['scale']) && ($_POST['scale']!='')){
	$scale=$_POST['scale'];
}

// COOKIES  OUTPUT
/*
if (isset($token) && ($token!="") ){
	setcookie("soltoken", $token);
}
*/

/*
if (isset($boattype) && ($boattype!="") ){
	setcookie("solboattype", $boattype);
}
*/

if (isset($scale) && ($scale!="") ){
	setcookie("solscale", $scale);
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

// Localization
$al= new GetString();
// DEBUG
$tlanglist=$al->getAllLang('./lang',$module);

if ($aFile = $al->setLang('./lang', $lang, $module)){
    require_once($aFile); // linguistic localization
}
require_once("./sol_include/sol_connect.php"); // connexion to the SOLServer

// generical token with  "sol" "sol" login
if (empty($token) && !empty($racenumber)){
	$token=get_sol_token($racenumber);
}


// OUTPUT FILENAMES
$fichier_kml_courant='Sol'.$racenumber; // celui qui est lu par Google Earth; il serait utile de pouvoir modifier ce prefixe depuis le programme
$fichier_kml_cache=$fichier_kml_courant.'_cache'; // celui qui est regénére à chaque appel du programme et archivé

// INPUT FILENAMES FROM sol_config.php
/*
// Path and urls for data download
$solhost='http://node1.sailonline.org/';    // == http://www.sailonline.org
$webclient = 'webclient/';                  // all web services
$serviceauth = 'authenticate.xml';          // login authentification
$servicerace='race_';                       // boats and positions
$serviceactiveraces='races.xml';          	// get all active races
$serviceraceinfo = 'auth_raceinfo_';      	// get race info
$serviceboat = 'boat.xml';                  // a boat position and cog sog
$servicetracks='traces_';                   // all boats tracks (gziped)

$racenumber='';
$racename='';
$token='';
*/

// race server for marks and polars
$filenamemarkpolars=$serviceraceinfo.$racenumber.$extension.'?token='.$token;
// race server for traks
$filenametracks=$servicetracks.$racenumber.$extension.'?token='.$token;
// race server for boats and positions
$filenameraceboats=$servicerace.$racenumber.$extension.'?token='.$token;

myheader();
menu();
menu2(false);

flush();

// Boattype
if (!empty($racenumber) && !empty($token)){
    if ($marques=my_get_content($solhost.$webclient.$filenamemarkpolars)){
		// On va utilier SimpleXML
		$timestamp=time();
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br />\n";
		date_default_timezone_set('UTC');
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br>\n";

        $marques_xml = new SimpleXMLElement($marques);
		if ($marques_xml){
            // Boat type
            if (empty($boattype)){
				$boattype = getBoatType($marques_xml->boat->type, $marques_xml->boat->vpp->name);
				menu2(true);
			}
		}
	}
}

if (($action=='Go') || ($action==$al->get_string('validate'))){
	// Fichier de marques et polaires
    if (empty($marques)){
		$marques=my_get_content($solhost.$webclient.$filenamemarkpolars);
	}
    if (!empty($marques)){
		// On va utilier SimpleXML
		$timestamp=time();
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br />\n";
		date_default_timezone_set('UTC');
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br>\n";

        $marques_xml = new SimpleXMLElement($marques);
		if ($marques_xml){
			if (DEBUG2){
				echo '<br /><pre>'."\n";
				print_r($marques_xml);
    	    	echo '</pre>'."\n";
				//exit;
			}
            $url=$marques_xml->url; //  : /webclient/race_1018.xml
			$weatherurl=$marques_xml->weatherurl; // : /webclient/weatherinfo_196.xml
			$traceUrl=$marques_xml->traceUrl;  // : /webclient/traces_1018.xml
   			if (DEBUG2){
				echo '<br />WeatherUrl: '.$weatherurl.'<br />'."\n";
                echo '<br />URL Météo: '.$solhost.$webclient.$weatherurl.'?token='.$token;
                echo '<br />'."\n";
			}

			// /webclient/weatherinfo_196.xml
			foreach ($marques_xml->course->waypoint as $wp_xml){
                $wp = new stdClass();
                $wp->num=$wp_xml->order;
                $wp->name=$wp_xml->name;
            	$wp->longitude = $wp_xml->lon; // 173.522473
                $wp->latitude = $wp_xml->lat;  // -34.975873
                $wp->any_side = $wp_xml->any_side; // False
				$t_wp[]=$wp;            ;
			}
   			if (false){
				echo '<br /><pre>'."\n";
				print_r($t_wp);
    	    	echo '</pre>'."\n";
			}
			afficheMarques($t_wp);
            echo '<br /><b>'.$al->get_string('boat').'</b>: '.$marques_xml->boat->type.'<br />'."\n";
  			flush();

			// Polaires
			$polaires = new stdClass();
	        $polaires->name = $marques_xml->boat->vpp->name;
    	    $polaires->tws = $marques_xml->boat->vpp->tws_splined;
        	$polaires->twa = $marques_xml->boat->vpp->twa_splined;
			$polaires->bs = $marques_xml->boat->vpp->bs_splined;

            // Boat type
            if (empty($boattype)){
				$boattype = getBoatType($marques_xml->boat->type, $marques_xml->boat->vpp->name);
				menu2(true);
			}

   			if (false){
				echo '<br /><pre>'."\n";
				print_r($polaires);
    	    	echo '</pre>'."\n";
			}
            $t_polaires = getPolaires($polaires);
            echo '<br />'.$al->get_string('polars').' '.$polaires->name.' '.$al->get_string('loaded').'<br />'."\n";
            if (false){
				affichePolaires($polaires->name, $t_polaires);
			}
            flush();
		}
	}
		// Grib
	if (!empty($weatherurl) && !empty($token)){
		if ($meteoinfo=my_get_content($solhost.$webclient.$weatherurl.'?token='.$token)){
			if (!empty($meteoinfo)){
                $meteoinfo_xml = new SimpleXMLElement($meteoinfo);
				// DEBUG
                if (DEBUG2){
					echo '<br /><pre>'."\n";
					print_r($meteoinfo_xml);
    	    		echo '</pre>'."\n";
				}
				$meteo_rec = new stdClass();
                $meteo_rec->id = $meteoinfo_xml->id;
                $meteo_rec->last_update = $meteoinfo_xml->last_update;
                $meteo_rec->url = $meteoinfo_xml->url;
				// Recuperer le fichier grib
                $pos=strrpos($meteoinfo_xml->url,'/');
				$len=strlen($meteoinfo_xml->url);
                $grib_filename= substr($meteoinfo_xml->url,$pos,$len);
                $meteo_rec->filename = $grib_filename;
                // DEBUG2
                if (DEBUG2){
					echo '<br />METEO GRIB<pre>'."\n";
					print_r($meteo_rec);
    	    		echo '</pre>'."\n";
				}

				$grib2load=$dir_serveur.$grib_path.$grib_filename;
                // DEBUG2
                if (DEBUG2){
					echo '<br />METEO GRIB<pre>'."\n";
					echo ($grib2load);
    	    		echo '</pre>'."\n";
				}

				// verifier si en cache
                if (!empty($grib2load) && file_exists($grib2load)){
    				// DEBUG2
                	if (DEBUG2){
						echo '<br /><span class="small">Fichier chargé : <i>'.$grib2load.'</i></span><br />'."\n";
					}
	    			if ($gf = fopen($grib2load, "r")){
                        $grib = fread($gf, filesize($grib2load));
						fclose($gf);
					}
				}
				else{
					// sinon
                	if ($grib = my_get_content($meteo_rec->url)){
						// enregistrer dans le dossier ./gib
		    			if ($gf = fopen($grib2load, "w")){
                        	if (fwrite($gf, $grib) === FALSE){
								echo "<br />".$al->get_string('erreur1')."\n";
							}
							fclose($gf);
						}
					}
				}

				// Decoder le fichier grib
				if (!empty($grib)){
					if ($grib_xml = new SimpleXMLElement($grib)){
                  		 // DEBUG2
                		if (DEBUG2){
							echo '<br />'.$al->get_string('meteo').'<pre>'."\n";
							print_r($grib_xml);
    	    				echo '</pre>'."\n";
						}
						// cartouche entete
						$g_at = new stdClass();
                        $g_at->id = $grib_xml['id'];
                        $g_at->lon_min = $grib_xml['lon_min'];
                        $g_at->lon_max = $grib_xml['lon_max'];
                        $g_at->lat_min = $grib_xml['lat_min'];
                        $g_at->lat_max = $grib_xml['lat_max'];
                        $g_at->lon_n = $grib_xml['lon_n_points'];
						$g_at->lon_inc = $grib_xml['lon_increment'];
                        $g_at->lat_n = $grib_xml['lat_n_points'];
						$g_at->lat_inc = $grib_xml['lat_increment'];
                 		// DEBUG2
                		if (DEBUG2){
							echo '<br />'.$al->get_string('grib').'<pre>'."\n";
							print_r($g_at);
    	    				echo '</pre>'."\n";
							//echo 'EXIT :: SolBoats2Kml :: 491'."\n";
       						//exit;
						}

						foreach ($grib_xml->frames->frame as $frame_xml){
                            $g_frame = new stdClass();
                            $g_frame->date = $frame_xml['target_time'];   // 2017/03/03 09:00:00 UTC
       						if (($timestamp_grib = strtotime($g_frame->date)) === false){
    							echo "The string ($g_frame->date) is bogus";
							}
							else {
           						// DEBUG2
                				if (DEBUG2){
                                    date_default_timezone_set('UTC');
									echo " $g_frame->date == " . date('l dS \o\f F Y h:i:s A', $timestamp_grib)."<br />\n";
								}
                                $g_frame->timestamp = $timestamp_grib;
							}
							// Lire http://solfans.org/blog/uncategorized/confessions-from-the-canaries/
							$g_frame->u = $frame_xml->U;    // composante  Nord / Sud  de TWS
                            $g_frame->v = $frame_xml->V;    // composante  Est / Ouest  de TWS
							$t_grib[] = $g_frame;
						}
						//
                   	// DEBUG2
                		if (DEBUG2){
							echo '<br />'.$al->get_string('meteo').': '.count($t_grib).' '.$al->get_string('nbrec').'<br /><pre>'."\n";
							print_r($t_grib);
    	    				echo '</pre>'."\n";
							//echo '<br />EXIT :: Sol2kml :: 483'."\n";
							//exit;
						}

                        $timestamp_min=$timestamp-3*3600; // Trois heures dans le passé
        				$timestamp_max=$timestamp+6*3600; // Deux heures dans le futur

                        $uneGrib = new Grib();
						// $uneGrib->setGrib_complete($g_at, $t_grib);
						// ne conserver que la fen^tre temporelle courante
                        $uneGrib->setGrib($g_at, $t_grib, $timestamp_min, $timestamp_max); // ne conserver que la fen^tre temporelle courante
						// verification
                   		if (DEBUG2){
							echo '<br />'.$al->get_string('meteo').'<pre>'."\n";
							print_r($uneGrib);
    	    				echo '</pre>'."\n";
						}
                   		if (DEBUG){
							echo '<br />'.$al->get_string('meteo').'<br />'."\n";
							$uneGrib->affGrib(true);
    	    				//exit;
						}
						else{
			                echo '<br />'.$al->get_string('grib').' <i>'.$grib_filename.'</i> '.$al->get_string('charge').'<br />'."\n";
						}

					}
				}
			}
		}
	}

	// Fichiers de Traces
    $traceinflate=null;
 	if ($tracecontents=my_get_content($solhost.$webclient.$filenametracks)){
		if (!empty($tracecontents)){
			// DEBUG
			// Afficher les premiers octets pour verifier si c'est le protocole deflate ou gzip
			$lisible= bin2hex($tracecontents);
			if (DEBUG){
				echo '<br /><pre>'."\n";
				echo substr($lisible,0, 20).'...'."\n";
			    echo '</pre>'."\n";
			}
			$traceinflate= gzBody($tracecontents);
		}
		$n=0;
		if (!empty($traceinflate)){
    			if (DEBUG2){
					echo '<br /><pre>'."\n";
					echo htmlentities($traceinflate);
	        		echo '</pre>'."\n";
				}

				// On va utilier SimpleXML
		        $traces_xml = new SimpleXMLElement($traceinflate);
				if ($traces_xml){
					foreach ($traces_xml->boat as $aboat_xml){
                		$trace = new Trace($aboat_xml->id, $aboat_xml->data);
                  		if (DEBUG2){
							echo '<br />'.$al->get_string('boat').': '.$trace->GetId().'<br />'.$al->get_string('trajectoire').': '.$trace->TrajectoireGE(0)."\n";
							flush();
						}
						$n++;
					}
				}
		}
		if ($n){
			echo '<br /><br /><span class="surligne"><b>'.$n.'</b> '.$al->get_string('boatloaded').'</span><br />'."\n";
		}
	}

    // Données décomprimées
	$inflaterace=null;

 	if ($contents=my_get_content($solhost.$webclient.$filenameraceboats)){
		if (!empty($contents)){
			// Afficher les premiers octets pour verifier si c'est le protocole deflate ou gzip
			if (DEBUG2){
                $lisible= bin2hex($contents);
				echo '<br /><pre>'."\n";
				echo substr($lisible,0, 20).'...'."\n";
			    echo '</pre>'."\n";
			}
			$inflaterace= gzBody($contents);
		}

		// Traiter
		if (empty($inflaterace)){
  			echo '<br /><br />'.$al->get_string('errorinflate')."\n";
		}
		else{
        	if (DEBUG2){
				echo '<br /><pre>'."\n";
				echo htmlentities($inflaterace);
        		echo '</pre>'."\n";
			}
			// On va utilier SimpleXML
        	$race_xml = new SimpleXMLElement($inflaterace);
			if ($race_xml){
                if (DEBUG2){
					echo '<br /><pre>'."\n";
					echo print_r($race_xml);
			    	echo '</pre>'."\n";
                }

				$n=0;
				foreach ($race_xml->boats->boat as $aboat_xml){
                	$aboat_xml->syc=0;
                    $aboat_xml->mmsi=0;
					if ((float)$aboat_xml->log==0.0){
						$aboat_xml->navstatus='At anchor';
					}
					else{
						if ( (float)$aboat_xml->dtg>0.0){
							$aboat_xml->navstatus='Under way sailing';
						}
						else{
	                    	$aboat_xml->navstatus='Arrived';
						}
					}
                	// $cogradian = pi() * ($cogdegre) / 180.0;
                	$aboat_xml->cog = rad2deg((double)$aboat_xml->cog);
                    $aboat_xml->voile = 1;

					// Calculer l'angle au vent
					if ($twstwd = $uneGrib->getTwsTwd($timestamp, $aboat_xml->lon, $aboat_xml->lat)){
						if (DEBUG){
							echo '<br />TwsTwd<pre>'."\n";
							echo print_r($twstwd);
			    			echo '</pre>'."\n";
						}

						// Calculer TWA
                        $aboat_xml->twa=getTwa($aboat_xml->cog, $twstwd->twd);
                        $aboat_xml->sog=getSog($aboat_xml->twa, $twstwd->tws, $t_polaires);
                        $aboat_xml->tws=$twstwd->tws;
                        $aboat_xml->twd=$twstwd->twd;

						// voile
                        $aboat_xml->voile = $voile;  // reporté dans la classe voiliers

					}
					if (DEBUG){
						afficheABoat($aboat_xml);
					}


					// Fabriquer le voilier
                	$un_voilier= new Voilier();
					$un_voilier->setColors3RGB($aboat_xml->color_R, $aboat_xml->color_G, $aboat_xml->color_B);										
					$un_voilier->SetPosition(
                            $aboat_xml->mmsi,     // mmsi
							$aboat_xml->name,
                            $aboat_xml->sy,                         // SYC (SailOnLine Yacht Club)
                            $aboat_xml->id,
							$date,
							$aboat_xml->lat,
							$aboat_xml->lon,
							$aboat_xml->cog,
							$aboat_xml->sog,                        // SOG
                            $aboat_xml->navstatus,
							$aboat_xml->voile,	// Foc=1, Spi=2, c'est dans la classe voilier que la voile est calculée en fonction de twa, tws.
							// On pourrait implanter en fonction des polaires...
							$aboat_xml->twd,                          // TWd
							$aboat_xml->tws,                          // TWS
							$aboat_xml->twa,                          // TWA
                            $aboat_xml->ranking,
							$aboat_xml->dtg, // distance à courir
    						$aboat_xml->dbl,   // distance au premier
							$aboat_xml->log, // distance parcourue
                            $aboat_xml->current_leg,
							$boattype
					);
                    $un_voilier->setVoile();
                	$t_voilier[] = $un_voilier;
					if (false){
						echo $aboat_xml->name.', ';
                    	// echo '<br />'.$al->get_string('gite').": \n";
						// print_r($un_voilier->GiteVoilier(true));
					}
                	flush();
					$n++;
				}
			}
		}
	}
}


if ($n){
	// ordonner les voiliers du premier au dernier
	if (false){
		echo '<br /><br /><span class="surligne"><b>'.$n.'</b> '.$al->get_string('boatloaded').'</span><br />'."\n";
		echo '<br /><pre>'."\n";
		echo print_r($t_voilier);
		echo '</pre>'."\n";
    }

	usort($t_voilier, "callback_rank_compare");

	if (DEBUG2){
		echo '<br />DEBUG :: 720 <br /><span class="surligne"><b>'.$n.'</b> '.$al->get_string('boatloaded').'</span><br />'."\n";
		echo '<br /><pre>'."\n";
		echo print_r($t_voilier);
		echo '</pre><br />'."\n";
        //echo '<br />EXIT 724'."\n";
		//exit;
    }

}

echo '</div>
';

//  Génération des données pour G.E.
echo '<div id="display1">'."\n";
if (empty($t_voilier)){
	echo '<h4>'.$al->get_string('fileexported').'</h4>'."\n";
	echo '<p>'.$al->get_string('help1')."</p>\n";
}
else{
	echo '<h4>'.$al->get_string('newmap').'</h4>'."\n";
	// echo $al->get_string('wait')."\n";
	flush();

 	// Structure d'accueil pour les données
	creer_dossier_kml($archive);
	if ($mode=='3D'){ // 3D systématiquement
		if (isset($t_voilier) && is_array($t_voilier) && (count($t_voilier)>0) ){
			//echo '<p>'.$al->get_string('export1');
			//flush();
            $nom_course="$racename ($racenumber) - ".date("Y/m/d H:i:s T",$timestamp);
            $un_cartouche = new Cartouche($racenumber, $nom_course, "tmp", 'Ebrima', '28', 'aa9900');
            $image_nom_course=$un_cartouche->setTextImage();
			if (DEBUG){
				echo "<br />L'image $image_nom_course est créée.\n";
			}

			// donnees à placer sur un serveur distant : adressage absolue
			// génère l'entete et l'overlay
			$s=GenereEnteteKML_3D($t_voilier[0]->longitude, $t_voilier[0]->latitude, $t_voilier[0]->cog);
			// Génère les styles, marques de parcours
			$s.=GenereMarquesParcoursEtDebutPositionsBateauxKML_3D($scale, $okmarques);
			$i=0;
			while ($i<count($t_voilier)){
				$s.=GenereBateauKML_3D($dossier_kml.'/'.$dossier_3d, $url_serveur, $t_voilier[$i], $scale, $scale*150);
				$i++;
			}
            $s.=GenereTourBateauxKML($t_voilier, $scale);  // génère la visite guidée  du premier au dernier.
			$s.=GenereEnQueueKML_3D();

			// fichier KML chargé dynamiquement
			EnregistreKML_3D($dossier_3d, $s, false, $al);
			GenereKML_3D($dossier_3d, $url_serveur, $t_voilier[0]->longitude, $t_voilier[0]->latitude, $t_voilier[0]->cog); // Fichier kml a appeler depuis GoogleEarth

			// donnees d'archives : adressage relatif
			$s=GenereEnteteKML_3D($t_voilier[0]->longitude, $t_voilier[0]->latitude, $t_voilier[0]->cog);
			$s.=GenereMarquesParcoursEtDebutPositionsBateauxKML_3D($scale, $okmarques);
			$i=0;
			while ($i<count($t_voilier)){
				$s.=GenereBateauKML_3D($dossier_kml.'/'.$dossier_3d_cache, "", $t_voilier[$i], $scale, $scale*150);
				$i++;
			}
            // Visite guidée
    		$s.=GenereTourBateauxKML($t_voilier, $scale);
			$s.=GenereEnQueueKML_3D();
			EnregistreKML_3D($dossier_3d_cache, $s, true, $al);
            // DEBUG
   			if (DEBUG){
				echo "<pre>\n";
				echo htmlspecialchars($s);
				echo "</pre>\n";
				flush();
			}
			// echo '<br />'.$al->get_string('export2')."\n";

			unset($t_voilier);
		}
	}
}



$nom_kml=ExisteKML($mode); // Gestion du cache temporel
echo '</div>'."\n";
enqueue();

// ################################ FUNCTIONS ######################################################


// ------------------
function selectFichier($racenumber, $path, $prefix, $extension){
global $appli;
global $nobj;
global $al;

	if (!empty($racenumber)){
        $pref = $prefix.$racenumber.'_';
	}
	else{
        $pref = $prefix;
	}
	$tf=array();
	$sep = '/';


	$h1=opendir($path);
    $nobj = 0;
    $n = 0;
	$s='';

    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// TEXT
    	       	$g= eregi_replace($extension,"",$f) ;
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
					(substr($g,0,strlen($pref)) == $pref)
					&&
					(strtoupper($g.$extension) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$n ++;
	               	$tf[$f] = $f ;
				}

			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

	if ($n != 0) {
	    asort($tf);
		$s.= '<h5>Sélectionnez un fichier local</h5>'."\n";
		$s.= '<ul>'."\n";
       	while (list($key) = each($tf)) {
	       	$s.= '<li><a  class="small" href="'.$appli.'?filename='.urlencode($path.$sep.$key).'">'.$tf[$key].'</a></li>'."\n";
   		}
   		$s.= '</ul>'."\n";
	}
	else{
    	$s.= $al->get_string('nofile')."\n";
	}
	$s.= '<br /><br/>'."\n";
	return $s;
}


//---------
function myheader(){
	global $appli;
	global $racenumber;
	global $al;
    global $lang;
	global $tlanglist;

	echo '<!DOCTYPE html>
<html  dir="ltr" lang="fr" xml:lang="fr">
<head>
	<title>Sailonline Feed for Google Earth</title>
	<meta name="ROBOTS" content="none,noarchive">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="JF">
	<meta name="description" content="SailOnLine races to G.E."/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="bandeau">
<h1 align="center">'.$al->get_string('title1').'</h1>
';

echo '<p align="center">
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

echo '</div>
';

}

//------------------------------
function afficheBoats($race){
	if (!empty($race)){
 		// DEBUG
		echo '<br /><b>Id de la course</b>: <i>'.$race->id.'</i>'."\n";
		echo '<br /><b>Nom de la course</b>: <i>'.$race->message.'</i>'."\n";
        echo '<br /><b>Alerte </b>: <i>'.$race->alert.'</i>'."\n";
        echo '<br /><b>Boats</b>:<br />'."\n";
		/*
			echo '<pre>'."\n";
      		print_r($race->boats);
            echo '</pre><br />'."\n";
		*/
        echo '<ol>'."\n";
		foreach ($race->boats->boat as $aboat){
			afficheABoat($aboat);
		}
        echo '</ol>'."\n";
	}
}


//------------------------------
function afficheABoat($aBoat){
global $al;
	if (!empty($aBoat)){
        //echo '<br />SimpleXML Element<pre>'."\n";
 		//print_r( $aBoat_xml);
        //echo '</pre>'."\n";
		// DEBUG
        echo '<li><b>'.$aBoat->name.'</b><br /><ul>'."\n";
		echo '<li>Sol id: <i>'.$aBoat->id.'</i>'."\n";
        if ($aBoat->mmsi){
			echo '<li>MMSI: <i>'.$aBoat->mmsi.'</i>'."\n";
		}
        if ($aBoat->syc){
			echo '<li>SYC: <i>'.$aBoat->syc.'</i>'."\n";
		}
        if ($aBoat->navstatus){
			echo '<li>NavStatus: <i>'.$aBoat->navstatus.'</i>'."\n";
		}

		echo '<li>'.$al->get_string('colorhull').': '.$aBoat->color_R.','.$aBoat->color_G.','.$aBoat->color_B.''."\n";
        echo '<li>'.$al->get_string('model').': '.$aBoat->type.''."\n";
        echo '<li>'.$al->get_string('dtg').': '.$aBoat->dtg.''."\n";
        echo '<li>'.$al->get_string('dtf').': '.$aBoat->dbl.''."\n";
        echo '<li>'.$al->get_string('lon').': <i>'.$aBoat->lon.'</i>'."\n";
 		echo '<li>'.$al->get_string('lat').': <i>'.$aBoat->lat.'</i>'."\n";
 		echo '<li>'.$al->get_string('cog').': '.$aBoat->cog.''."\n";
        echo '<li>'.$al->get_string('sog').': '.$aBoat->sog.''."\n";
        echo '<li>'.$al->get_string('twa').': '.$aBoat->twa.''."\n";
        echo '<li>'.$al->get_string('twd').': '.$aBoat->twd.''."\n";
        echo '<li>'.$al->get_string('tws').': '.$aBoat->tws.''."\n";
        echo '<li>'.$al->get_string('sail').': '.$aBoat->voile.''."\n";
 		echo '<li>'.$al->get_string('rank').': '.$aBoat->ranking.''."\n";
 		echo '<li>'.$al->get_string('leg').': '.$aBoat->current_leg.''."\n";
        echo '<li>'.$al->get_string('log').': '.$aBoat->log.''."\n";
        echo '</ul><br /></li>'."\n";
	}
}

//-------------------------
function hexa2_3dec($hexa){
	// rrvvbb -> hexdec(rr);hexdec(vv);hexdec(bb)
    if (list($rr, $vv, $bb) = explode(';', chunk_split ($hexa,2,';'))){
		return (hexdec($rr).';'.hexdec($vv).';'.hexdec($bb));
	}
	return false;
}


// -----------------------
function ExisteKML(){
	// verifie si une generation a ete faite durant l'heure courante
		return ExisteKML_3D();
}


// ---------------------------
function menu(){
global $time_cache;
global $str_time_cache;
global $scale;
global $mode;
global $url_serveur;
global $url_serveur_local;
global $utiliser_cache; // par defaut il y a un cache d'une heure sur les donnes des voiliers
global $token;
global $racenumber;
global $racename;
global $boattype;
global $appli;
global $lang;
global $al;

echo '
<div id="menugauche">

<h4>'.$al->get_string('serverconnect').'</h4>
';
	$params = array();
	$params['url_serveur'] = $url_serveur;
	$params['scale'] = $scale;
	$params['mode'] = $mode;
    $params['boattype'] = $boattype;
 	select_a_race($params );   // modifie $t_race par effet de bord

	echo '
<form action="'.$appli.'" method="post">
* <b><i><label for="text1">'.$al->get_string('racenumber').'</label></i></b><br /><input type="text" class="textInput" id="text1"  name="racenumber" size="4" value="'.$racenumber.'" />
<br />
* <b><i><label for="text2">'.$al->get_string('token').'</label></i></b><br /><input type="text" class="textInput" id="text2"  name="token" size="35" value="'.$token.'" />
<br /><br />
<input type="reset" />
<input id="submitBtn" type="submit" name="action" value="'.$al->get_string('validate').'" />
<i><label for="text4">'.$al->get_string('clicktoload').'</label></i>
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'" />
<input type="hidden" name="racename" id="racename" value="'.$racename.'" />
<input type="hidden" name="mode" id="mode" value="'.$mode.'"/>
<input type="hidden" name="scale" id="scale" value="'.$scale.'"/>
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
<input type="hidden" name="boattype" id="boattype" value="'.$boattype.'"/>
</form>
</div>
';

// Archives
// afficheArchivesKML();
if ($datatodisplay=verifieArchivesKML()){
	echo '<div id="menudroite">
<p><span class="small">'.$al->get_string('info1').'<br /><i>'.$al->get_string('info2').'</i></span></p>'."\n";

	echo '
<h4>'.$al->get_string('mapready').'</h4>
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
';
	}
	echo '</div>
';
}

}

// ---------------------------
function menu2($okboattype){
global $time_cache;
global $str_time_cache;
global $scale;
global $mode;
global $url_serveur;
global $url_serveur_local;
global $utiliser_cache; // par defaut il y a un cache d'une heure sur les donnes des voiliers
global $token;
global $racenumber;
global $racename;
global $boattype;
global $appli;
global $lang;
global $al;


echo '
<div id="menucentre">
<h4>'.$al->get_string('display').'</h4>
<form action="'.$appli.'" method="post" name="saisie_mode" id="saisie_mode"/>
<b>'.$al->get_string('display3d').'</b>
<span class="small">'.$al->get_string('display3dinfo').'</span>
<br /><b>'.$al->get_string('boatscale').'</b> :
<input type="text" name="scale" size="1" maxsize="3" value="'.$scale.'"/>
(<span class="small">[<i>0.1</i>, <i>20.0</i>]</span>)
<br /><br />
';
if ($okboattype){
echo '
<br /><b>'.$al->get_string('boattype').'</b> ('.$al->get_string('currentype').':<i> '.$boattype.'</i>)<br />
<select name="boattype" id="boattype" size="4" />
';
		if ($boattype=='monocoque') {
        	echo '<option value="monocoque" SELECTED>'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran">'.$al->get_string('catamaran').'</option>';
        	echo '<option value="trimaran">'.$al->get_string('trimaran').'</option>';
            echo '<option value="motorboat">'.$al->get_string('motorboat').'</option>';
        	echo '<option value="greatboat">'.$al->get_string('greatboat').'</option>';
		}
		else if ($boattype=='catamaran') {
        	echo '<option value="monocoque">'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran" SELECTED>'.$al->get_string('catamaran').'</option>';
        	echo '<option value="trimaran">'.$al->get_string('trimaran').'</option>';
            echo '<option value="motorboat">'.$al->get_string('motorboat').'</option>';
        	echo '<option value="greatboat">'.$al->get_string('greatboat').'</option>';
		}
		else if ($boattype=='trimaran') {
        	echo '<option value="monocoque">'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran">'.$al->get_string('catamaran').'</option>';
        	echo '<option value="trimaran" SELECTED>'.$al->get_string('trimaran').'</option>';
            echo '<option value="motorboat">'.$al->get_string('motorboat').'</option>';
        	echo '<option value="greatboat">'.$al->get_string('greatboat').'</option>';
		}
		else if ($boattype=='motorboat') {
        	echo '<option value="monocoque">'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran">'.$al->get_string('catamaran').'</option>';
            echo '<option value="trimaran">'.$al->get_string('trimaran').'</option>';
        	echo '<option value="motorboat" SELECTED>'.$al->get_string('motorboat').'</option>';
        	echo '<option value="greatboat">'.$al->get_string('greatboat').'</option>';
		}
		else if ($boattype=='greatboat') {
        	echo '<option value="monocoque">'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran">'.$al->get_string('catamaran').'</option>';
            echo '<option value="trimaran">'.$al->get_string('trimaran').'</option>';
        	echo '<option value="motorboat">'.$al->get_string('motorboat').'</option>';
        	echo '<option value="geatboat" SELECTED>'.$al->get_string('greatboat').'</option>';
		}
		else{
        	echo '<option value="monocoque">'.$al->get_string('monocoque').'</option>';
        	echo '<option value="catamaran">'.$al->get_string('catamaran').'</option>';
            echo '<option value="trimaran">'.$al->get_string('trimaran').'</option>';
            echo '<option value="motorboat">'.$al->get_string('motorboat').'</option>';
        	echo '<option value="greatboat">'.$al->get_string('greatboat').'</option>';
		}
	echo '
</select>
<br /><br />
';
}
echo '
<input type="reset" />
<input type="submit" value="'.$al->get_string('validate').'"/>
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="mode" id="mode" value="'.$mode.'"/>
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'"/>
<input type="hidden" name="racename" id="racename" value="'.$racename.'"/>
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
</form>
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


//------------------------
function creer_dossier_kml($archive=false){
// Crée un dossier unique pour archiver les donnees KML
global $dir_serveur;
global $dossier_kml;
global $dossier_kmz;
global $dossier_3d;
global $dossier_3d_cache;
global $dossier_textures;
global $dossier_modeles;

	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}
	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d.'/'.$dossier_modeles;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}
	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d.'/'.$dossier_modeles.'/'.$dossier_textures;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}

	if ($archive){
		$dossier_3d_cache=$dossier_3d.'_'.date("YmdH");
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d_cache;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d_cache.'/'.$dossier_modeles;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d_cache.'/'.$dossier_modeles.'/'.$dossier_textures;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
	 }
}


//----------------------------------------
class UploadException extends Exception
{
    public function __construct($code) {
        $message = $this->codeToMessage($code);
        parent::__construct($message, $code);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }
}


// ------------------------------
function afficheBoatModel($bModel){
$s='';
	if (!empty($bModel)){
		$s.=' <b>'.$bModel.'<b>'."\n";
 	}
	return $s;
}


// ------------------------------
function getPolaires($polaires){
	global $maxindextwa;
	global $maxindextws;
	global $al;

$t_twa=array();
$t_tws=array();
$t_bs=array();
$t_pol=array(array());
	// DEBUG2
	if (DEBUG2){
		echo '<br />'.$al->get_string('getpolar').'<pre>'."\n";
      	print_r($polaires);
        echo '</pre><br />'."\n";
	}
    $polaires->twa=trim($polaires->twa);
	$t_twa = explode(' ',$polaires->twa);
    $maxindextwa = count($t_twa);
	// DEBUG2
	if (DEBUG2){
		echo '<br />TWA<pre>'."\n";
      	print_r($t_twa);
        echo '</pre><br />'."\n";
	}

    $polaires->tws=trim($polaires->tws);
    $t_tws = explode(' ',$polaires->tws);
    $maxindextws = count($t_tws);
	if (DEBUG2){
		echo '<br />TWS<pre>'."\n";
      	print_r($t_tws);
        echo '</pre><br />'."\n";
	}

    $polaires->bs=trim($polaires->bs);
	$t_bs = explode(';',$polaires->bs);
	if (DEBUG2){
		echo '<br />TBS<pre>'."\n";
      	print_r($t_bs);
        echo '</pre><br />'."\n";
	}

	$i=0;
	for($i=0; $i<count($t_bs); $i++){
		if ($t_bs[$i]){
        	$t_pol[$i] = explode(' ', $t_bs[$i]);
		}
	}
	// DEBUG2
	if (DEBUG2){
		echo '<br />T_POL<pre>'."\n";
      	print_r($t_pol);
        echo '</pre><br />'."\n";
	}
	return  $t_pol;
}

// ------------------------------
function affichePolaires($nom, $t_pol){
	global $maxindextwa;
	global $maxindextws;
	global $al;

	echo '<br />'.$al->get_string('polar').' <b>'.$nom.'</b><br />'."\n";
	echo "<pre>\n";
	echo ("TWA\TWS\t");
	for ($tws=0; $tws<$maxindextws; $tws++){
		printf(" %8d\t",$tws);
	}
	echo "\n";
	for ($twa=0; $twa<$maxindextwa; $twa++){
  		printf("%-8d\t",$twa);
  		for ($tws=0; $tws<40; $tws++){
			printf(" %-2.6F\t", $t_pol[$twa][$tws]);
		}
		echo "\n";
	}
	echo "\n";
    echo "</pre>\n";
}



 // ------------------------------
function afficheWP($wp){
$s='';
	if (!empty($wp)){
		$s.=' N°: <i>'.$wp->num.'</i> <b>'.$wp->name.'</b> '.$wp->longitude.', '.$wp->latitude.' ['.$wp->any_side."]\n";
 	}
	return $s;
}

// ------------------------------
function afficheMarques($t_wp, $liste=false){
global $racenumber;
global $racename;
global $al;

    echo '<h5>'.$al->get_string('marks').'</h5><p><b>'.$racename.'</b> ('.$al->get_string('racenumber').' <i>'.$racenumber.'</i>)<br />'."\n";

	if (!empty($t_wp)){
		if ($liste) echo '<ul>';
        //echo '<br /><pre>'."\n";
 		//print_r( t_wp);
        //echo '</pre>'."\n";
		// DEBUG
		foreach ($t_wp as $awp){
			if ($liste) echo '<li>'.afficheWP($awp).'</li>'."\n"; else echo afficheWP($awp).' '."\n";
		}
        if ($liste) echo '</ul>'."\n";
	}
}

//------------------------------
function afficheTraces($race_xml){
	global $al;
 	if (!empty($race_xml)){
        //echo '<br />SimpleXML Element<pre>'."\n";
 		//print_r( $race_xml);
        //echo '</pre>'."\n";
		// DEBUG
		echo '<br /><b>'.$al->get_string('raceid').'</b>: <i>'.$race_xml->id.'</i>'."\n";
		echo '<br /><b>'.$al->get_string('racename').'</b>: <i>'.$race_xml->message.'</i>'."\n";
        echo '<br /><b>'.$al->get_string('warning').'</b>: <i>'.$race_xml->alert.'</i>'."\n";
        echo '<br /><b>'.$al->get_string('boats').'</b>:<br />'."\n";
		/*
			echo '<pre>'."\n";
      		print_r($race_xml->boats);
            echo '</pre><br />'."\n";
		*/
        echo '<ol>'."\n";
		foreach ($race_xml->boats->boat as $aboat){
			afficheLaTrace($aboat);
		}
        echo '</ol>'."\n";
	}
}


// ----------------------------------
function getTwa($cog, $twd){
// retourne la TWA de ]-180 .. + 180] : negatif : bâbord, positif tribord
	// TWD est exprimé en degre de [0 .. 360[
	// TWD est la direction d'ou vient le vent
	// COG est exprimé en degré de [0 .. 360[
    $twa = $twd - $cog;
	// conversion de $twa en -180 , 180
	if ($twa < -180.0){
        $twa = 360.0 + $twa;
	}
	else if ($twa>180.0){
		$twa = $twa - 360.0;
	}
	return ($twa);
}


//-------------------------------------
function getSog($twa, $tws, $t_polaires){
	global $maxindextwa;
	global $maxindextws;
	$index_twa = (int) round(abs($twa));
	if ($index_twa >= $maxindextwa){
		$index_twa = $maxindextwa-1;
	}
    $index_tws = (int) round($tws);
	if ($index_tws >= $maxindextws){
		$index_tws = $maxindextws-1;
	}
	return $t_polaires[$index_twa][$index_tws];
 }

 // -----------------------------------
 function callback_rank_compare($a, $b){
	if (isset($a->classement) && isset($b->classement)){
		if ((int)$a->classement == (int)$b->classement){
			return 0;
		}
		else {
            return ((int)$a->classement < (int)$b->classement) ? -1 : 1;
		}
	}
	return 0;
 }

 // -----------------------------------
 function getBoatType($btype, $polname){
	if ((preg_match("/catamaran/i",$btype)===true) || (preg_match("/catamaran/i",$polname)==true)){
		return 'catamaran';
	}
	else if ((preg_match("/trimaran/i",$btype)===true) || (preg_match("/trimaran/i",$polname)===true)){
		return 'trimaran';
	}
	else if ((preg_match("/great/i",$btype)===true) || (preg_match("/great/i",$polname)===true)){
        return 'greatboat';
	}
    return 'monocoque';
 }

?>