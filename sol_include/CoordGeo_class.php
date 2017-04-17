<?php
// Conversion de coordonnées géographiques

// -----------------------------------
// la classe Coordonnees
class Coordonnees{
    // coordonnees sexagesimales d'un point dans l'espace géodésique
	var $lon;
	var $lat;
    // coordonnees décimales d'un point dans l'espace géodésique
	var $londec;
	var $latdec;
	var $alt; // altitude

	function Coordonnees($lon, $lat, $alt=0){
        $this->SetToCoord($lon, $lat);
		$this->alt=$alt;
	}

	function GetLon(){
		return $this->lon;
	}
	function GetLat(){
		return $this->lat;
	}
	function GetAlt(){
		return $this->alt;
	}

	function GetLonDec(){
		return $this->londec;
	}
    function GetLatDec(){
		return $this->latdec;
	}

	function SetLon($lon){
		$this->lon=$lon;
	}
	function SetLat($lon){
		$this->lat=$lat;
	}

    function SetAlt($altitude){
		$this->alt=$altitude;
	}

/**
 * Transforme une chaine geo en triplet degre, minute, seconde et type (N,S,E,W)
 *
 **/
//----------
function GetGeoCode($str_position){

	// Determiner Longitude (E|W|O)
	// Latitude (N|S)
	// DEBUG
    $str_position_out='';

	$geocode = new stdClass();
	$geocode->type='';
	$geocode->degre='';
	$geocode->minute='';
	$geocode->seconde='';

	if (!empty($str_position)){
		$search  = array("’", ' ', '"', 'W');
		$replace = array("'", '', '', 'O');
		$str_position=str_replace($search,$replace,$str_position); // Supprimer les espace et autres caractères indésirables

        if (strpos($str_position,'E') !== false){
			//echo "Traitement Est<br />\n";
            $str_position_out = "E";
            $geocode->type='E';
            $str_position=str_replace('E','',$str_position);
		}
		elseif (strpos($str_position,'O') !== false){
            //echo "Traitement Ouest<br />\n";
            $str_position_out = "O";
            $geocode->type='O';
			$str_position=str_replace('W','',$str_position);   // W -> O
            $str_position=str_replace('O','',$str_position);
		}
		elseif (strpos($str_position,'S') !== false){
        	//echo "Traitement Sud<br />\n";
            $str_position_out = "S";
            $geocode->type='S';
            $str_position=str_replace('S','',$str_position);
		}
		elseif (strpos($str_position,'N') !== false){
   			//echo "Traitement Nord<br />\n";
            $str_position_out = "N";
            $geocode->type='N';
            $str_position=str_replace('N','',$str_position);
		}
		else{
			return null;
		}

		// Recombiner  en utilisant la librairie des fonctions multi bytes
	    // $degres =  mb_strstr($str_position, "&deg;", true); Non supporte chez FREE
		$degres ='';
	    $minutes_secondes = '';
		$minutes = '';
		$secondes = '';

    	$len_degch=strlen("&deg;");
	    $degrepos = strpos($str_position, "&deg;",0);
		if ($degrepos!==false){
        	$degres = substr($str_position, 0, $degrepos);
	        $minutes_secondes = substr($str_position, $degrepos+$len_degch, strlen($str_position));
 		}
    	$len_minch=strlen( "'");
	    $minutepos = strpos($minutes_secondes, "'",0);
		if ($minutepos!==false){
        	$minutes = substr($minutes_secondes, 0, $minutepos);
	        $secondes = substr($minutes_secondes, $minutepos+$len_minch, strlen($minutes_secondes));
		}

    	//echo '<br \>ELEMENTS :: Degrés: "'.$degres.'" Minutes_secondes: "'.$minutes_secondes.'" Minutes: "'.$minutes.'" Secondes: "'.$secondes.'" <br />'."\n";

		//$str_position_out.=$degres.'&deg;'.$minutes."'".$secondes;
 		//echo ' --> "'.$str_position_out.'" <br />'."\n";

 		$geocode->degre=$degres;
		$geocode->minute=$minutes;
		$geocode->seconde=$secondes;
	}
   	return  $geocode;
}



//----------
function GetGeoCode_mb($str_position){
// problèmes à répétition sur Free
// Determiner Longitude (E|W|O)
// Latitude (N|S)
	// DEBUG
    $str_position_out='';

	$geocode = new stdClass();
	$geocode->type='';
	$geocode->degre='';
	$geocode->minute='';
	$geocode->seconde='';

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

if (!empty($str_position)){
	$search  = array(' ', '"', 'W');
	$replace = array('', '', 'O');
	$str_position=str_replace($search,$replace,$str_position); // Supprimer les espace et autres caractères indésirables

        if (strpos($str_position,'E') !== false){
			//echo "Traitement Est<br />\n";
            $str_position_out = "E";
            $geocode->type='E';
            $str_position=str_replace('E','',$str_position);
		}
		elseif (strpos($str_position,'O') !== false){
            //echo "Traitement Ouest<br />\n";
            $str_position_out = "O";
            $geocode->type='O';
			$str_position=str_replace('W','',$str_position);   // W -> O
            $str_position=str_replace('O','',$str_position);
		}
		elseif (strpos($str_position,'S') !== false){
        	//echo "Traitement Sud<br />\n";
            $str_position_out = "S";
            $geocode->type='S';
            $str_position=str_replace('S','',$str_position);
		}
		elseif (strpos($str_position,'N') !== false){
   			//echo "Traitement Nord<br />\n";
            $str_position_out = "N";
            $geocode->type='N';
            $str_position=str_replace('N','',$str_position);
		}
		else{
			return null;
		}

	// Recombiner  en utilisant la librairie des fonctions multi bytes
    // $degres =  mb_strstr($str_position, "&deg;", true); Non supporte chez FREE
	$degres ='';
    $minutes_secondes = '';
	$minutes = '';
	$secondes = '';

	//if ($degrepos = mb_strpos($str_position, "&deg;")!==false){
		//echo "<br \>DEGRE à la position $degrepos\n";
        if ($t_degre=mb_split("&deg;", $str_position)){
			$degres=$t_degre[0];
            $minutes_secondes=$t_degre[1];
		}
	//}

	//if ($minutepos = mb_strpos($minutes_secondes, "'")!==false){
		//echo "<br \>MINUTE à la position $minutepos\n";
        if ($t_minute=mb_split("'", $minutes_secondes)){
			$minutes=$t_minute[0];
            $secondes=$t_minute[1];
		}
	//}

    //echo ' ELEMENTS :: Degrés: "'.$degres.'" Minutes_secondes: "'.$minutes_secondes.'" Minutes: "'.$minutes.'" Secondes: "'.$secondes.'" <br />'."\n";

	//$str_position_out.=$degres.'&deg;'.$minutes."'".$secondes;
 	//echo ' --> "'.$str_position_out.'" <br />'."\n";

    	$geocode->degre=$degres;
		$geocode->minute=$minutes;
		$geocode->seconde=$secondes;
	}
     return  $geocode;
}



//------------------------------
function GeoCode2Str($geocode, $type=false){
	$str='';
	if ($type){
    	return $geocode->degre."&deg;".$geocode->minute."'".$geocode->seconde." ".$geocode->type;
	}
	else{
		return $geocode->type." ".$geocode->degre."&deg;".$geocode->minute."'".$geocode->seconde;
	}
}


//------------------------------
function GeoCode2Dec($geocode, $type=false){
	if (!empty($geocode)){
        $coord_decimal = $geocode->degre + ($geocode->minute / 60) + ($geocode->seconde / 3600.0);
		if (($geocode->type == 'S') || ($geocode->type == 'O') || ($geocode->type == 'W')){
            $coord_decimal = -$coord_decimal;
		}
		if ($type      ){
			switch ($geocode->type) {
				case "N" :
				case "S" :
					$coord_decimal .= " Latitude";
					break;
				default :
                	$coord_decimal .= " Longitude";
				break;
			}
		}
	}
	return $coord_decimal;
}

/*
// Récupéré sur l'Internet
On entend souvent ce type de récrimination : "En saisissant les coordonnées GPS d'un point sur une carte Google Maps,
je me retrouve au beau milieu de l'océan atlantique !"
Les périphériques GPS transmettent par défaut les coordonnées en sexagésimal (
système de numération utilisant la base 60) alors que les cartes Google Maps utilisent le système décimal.
L'unité du sexagésimal est le degré (360 degrés), puis la minute (60 minutes = 1 degré)
puis la seconde (60 secondes = 1 minute).
Une solution possible consiste alors à convertir les degrés sexagésimaux en degrés décimaux.
Prenons un exemple :
Soit une latitude de 46°10'28" (46 degrés, 10 minutes et 28 secondes).
Exprimée en degrés décimaux, la latitude sera égale à : 46 + (10 / 60) + (28 / 3600) soit 46.1744444.
On peut donc écrire cette formule : latitude (degrés décimaux) = degrés + (minutes / 60) + (secondes / 3600).
En sens inverse, voici le déroulement des opérations :
46.1744444 - 0.1744444 = 46 ;
0.174444 * 60 = 10.46664 ;
10.46664 - 0.46664 = 10 ;
0.46664 * 60 = 27.984.
On obtient alors ce résultat : 46° 10' 27.984".
*/

/**
 * Convertit coordonnées décimale et sexagesimale
 *  input : une coordonnée décimale $geodec
 *  input : un boolean true si latitude vrai
 *  output un objet geocode
 **/

//------------------------------
function Geodec2Code($geodec, $latitude=true){
//if (DEBUG){
//	echo '<pre>';
//	print_r($geodec);
//	echo '</pre>'."\n";
//}
// Caster l'objet
//$geodec=(float)$geodec;
if (DEBUG){
	echo '<pre>';
	print_r($geodec);
	echo '</pre>'."\n";
}
if ( $geodec<0){
	$neg=-1.0;
}
else{
    $neg=1.0;
}
$geodec*=$neg;
	$degres = (int) $geodec;
	$ms = $geodec  - $degres;
	$ms60 = $ms * 60;
    $minute = (int) $ms60;
    $seconde = ($ms60 - $minute) * 60;
	$geocode= new stdClass();
    $geocode->degre=$degres;
    $geocode->minute=$minute;
    $geocode->seconde=sprintf("%01.0f", $seconde);
    if ($latitude){
		if ($neg<0.0) $geocode->type='S';
		else $geocode->type='N';
	}
	else{
		if ($neg<0.0) $geocode->type='W';
		else $geocode->type='E';
	}
if (DEBUG){
	echo '<pre>';
	print_r($geocode);
	echo '</pre>'."\n";
}
	return $geocode;
}

/**
 *  @input : un couple longitude, latitude dans n'importe quel format
 * @output : un objet dela classe Coordonnees
 *
 **/
function SetToCoord($lon, $lat){

	if (!empty($lon) && !empty($lat)){
		if (preg_match("/&deg;/",$lon)){
			$this->long=$lon;
            $this->londec=$this->GeoCode2Dec($this->GetGeoCode($lon), false);
		}
		else{
			$this->lon=$this->GeoCode2Str($this->GeoDec2Code($lon, false), true);
            $this->londec=$lon;
		}

		if (preg_match("/&deg;/",$lat)){
			$this->lat=$lat;
            $this->latdec=$this->GeoCode2Dec($this->GetGeoCode($lat), false);
		}
		else{
    		$this->lat=$this->GeoCode2Str($this->GeoDec2Code($lat, true), true);
            $this->latdec=$lat;
    	}
	}
}


function DisplayCoordonnees(){
		echo 'Longitude: '.$this->lon.' ('.$this->londec.') Latitude: '.$this->lat.' ('.$this->latdec.')'."<br/>\n";
}
}    // Class
?>