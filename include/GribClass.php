<?php

// Une clase pour renvoyer TWS et TWD à une position lon, lat

// Format des fichiers Grib XML pour SailOnLine
// n frames temporelles
// chaque frame temporelle est composée d'une date UTC
// et de deux series de valeurs qui sont les composantes du vecteur TWS
// <U> </U>  : composante "verticale (projection sur le méridien (axe des latitudes) Nord / Sud
// <V> </V>  : composante "horizontale" (projection sur la parallèle (axe des longitudes) Est / Ouest
// chaque ligne <U> </U> est constitu&ée de long_n * lat_n valeurs avec un séparateur ' '
// Toutes les lat_n valeurs un séparateur ';'
// Donc la forme générale est
/*
<weathersystem id="196" issue_time="2017/03/03 06:00:00"
last_updated="2017/03/03 10:28:46"
lon_min="166" lon_max="170"
lat_min="-42" lat_max="-40"
lon_n_points="9" lon_increment="0.5"
lat_n_points="5" lat_increment="0.5">
	<frames>
		<frame target_time="2017/03/03 09:00:00">
			<U>8.08 6.89 5.69 4.52 3.47; 2.72 1.24 1.78 7.6 7.05; 6.58 5.99 5.2 4.3 2.87; 1.26 0.12 -0.7 -2.1 -3.3; -4.4 -5.14 -6.07 -6.6 -7.15; -7.68 -8 -8.37 -8.53 0.06; 8.01 7.17 5.92 4.76 3.9; 3.21 1.11 4.24 8.21 7.23; 6.84 6.21 5.46 4.36 4.36;</U>
			<V>5.5 6.26 6.56 6.87 7.14; 7.49 7.69 7.43 1.67 -0.85; -0.76 -0.43 -0.17 -0.12 -0.14; -0.07 -0.38 -0.87 -1.04 -1.31; -1.03 -0.71 -0.04 -0.25 -0.12; -0.08 -0.26 0.41 0.21 0.21; 10.0 6.43 6.78 7.2 7.42; 7.54 7.89 6.43 0.13 -0.64; -0.46 -0.05 0.19 0.18 0.12;</V>
		</frame>
		<frame target_time="2017/03/03 12:00:00">
			<U>8.32 7.65 6.99 5.85 4.67; 3.38 2.32 1.32 0.18 4.42; 7.28 6.33 5.57 4.58 3.1; 1.34 0.14 -0.71 -2.11 -3.27; -4.61 -5.41 -6.49 -7.15 -7.63; -8.14 -8.41 -8.92 -9.37 -8.53; 0.06 7.91 7.06 5.97 4.91; 3.76 2.93 2.15 1.43 6.5; 7.31 6.65 5.93 4.76 3.39;</U>
			<V>5.03 5.82 6.29 6.33 6.55; 6.85 6.89 6.61 6.58 4.18; -0.79 -0.65 -0.31 -0.19 -0.16; -0.13 -0.19 -0.52 -0.62 -1.38; -1.1 -0.74 -0.08 -0.53 -0.24; -0.25 0.14 0.17 0.51 0.21; 10.0 6.06 6.78 6.81 6.87; 7.16 7.01 6.76 6.68 2.85; -0.9 -0.25 -0.03 0.05 0.09;</V>
		</frame>
		...
		...
	<frames>
</weathersystem>

Coordonnées géographiques en degrés décimaux. Mais il faut seméfier car les données
en longitudes sont représentées
de 0° (Greenwich EST) à 360° (Greenwich OUEST)
alors que les coordonnées géographiques de Google Earth sont de (-180 à 180)
avec Greenwich 0°
Les latitudes sont de 90° (Pôle Nord) à -90° (Pôle sud) comme dans GE

Les temps sont en UTC (Greenwich)
*/



class Grib{

	var $id;       // identifiant du grib, n'a pas d'utilité mais bof
	var $lon_min;  // position du coin inférieure en longitude
	var $lon_max;
	var $lon_delta;
	var $lon_min360;
    var $lon_max360; // tenir compte de la représentation -180 .. 180 et passer  à 0 .. 360
	var $applique360=false;
	var $lat_min;  // position du coin inférieure en  latitude
	var $lat_max;
	var $lat_delta;
	var $lon_n;    // nombre de cases en longitudes
	var $lon_inc;  // incrément en longitudes
	var $lat_n;    // nombre de cases en latitudes
	var $lat_inc;  // incrément en latitudes

	var $t_grib=array();

	//---------------------------------------
	public function setGrib($header, $data, $timestamp_min, $timestamp_max){
		// Ne conserver que la fenêtre temporelle utile
		// pour éviter d'exploser la mémoire système sur les gros fichiers grib

		//$timestamp_min=$timestamp-3600; // Une heure dans le passé
        //$timestamp_max=$timestamp+6*3600; // 6 heures dans le futur (les gribs sont toutes les 3 heures

		// information d'entete
		$this->id = $header->id;

		// Longitude
        $this->lon_min = (float)$header->lon_min;
    	$this->lon_max = (float)$header->lon_max;

        if (($this->lon_min < 0.0) && ($this->lon_max < 0.0)){
            $this->lon_min360 = (float)($this->lon_min + 360.0);
            $this->lon_max360 = (float)($this->lon_max + 360.0);
            $this->applique360=true;
		}
		else if ($this->lon_min < 0.0){
            $this->lon_min360 = (float)($this->lon_min + 360.0);
            $this->lon_max360 = (float)($this->lon_max + 360.0);
            $this->applique360=true;
		}
		else{
            $this->lon_min360 = (float)($this->lon_min);
            $this->lon_max360 = (float)($this->lon_max);
            $this->applique360=false;
		}

        $this->lon_delta = (float)($this->lon_max360 - $this->lon_min360);

		// Latitude
		$this->lat_min = (float)$header->lat_min;
    	$this->lat_max = (float)$header->lat_max;
        $this->lat_delta = (float)($this->lat_max - $this->lat_min);

		$this->lon_n = $header->lon_n;
		$this->lon_inc = (float)$header->lon_inc;
		$this->lat_n = $header->lat_n;
		$this->lat_inc = (float)$header->lat_inc;

		// données
		$frames = array();
		$nf=0;
		if (!empty($data)){
    		foreach ($data as $dta){
				if (!empty($dta)){
					if ( ($dta->timestamp >= $timestamp_min) && ($dta->timestamp <= $timestamp_max)){
						$frame = new stdClass();
						$frame->timestamp=$dta->timestamp;
						// expanser les lignes
						// chaque ligne <U> </U> est composées de long_n * lat_n valeurs avec un séparateur ';' toutes les lat_n valeurs...
						$dta->u = str_replace(';','',$dta->u);  // dta->u est un tableau de lon_n lignes de lat_n colonnes
						$frame->u = explode(' ', $dta->u);      // expanser les enregistrements U et V
    	                $dta->v = str_replace(';','',$dta->v);  // dta->v est un tableau de lon_n lignes de lat_n colonnes
						$frame->v = explode(' ', $dta->v);
						$this->t_grib[]=$frame;
					}
					$nf++;
				}
			}
		}
	}

	//---------------------------------------
	public function setGrib_complete($header, $data){
	// conserve toutes les couches du grib
		// information d'entete
		$this->id = $header->id;

		// Longitude
        $this->lon_min = (float)$header->lon_min;
    	$this->lon_max = (float)$header->lon_max;

        if (($this->lon_min < 0.0) && ($this->lon_max < 0.0)){
            $this->lon_min360 = (float)($this->lon_min + 360.0);
            $this->lon_max360 = (float)($this->lon_max + 360.0);
            $this->applique360=true;
		}
		else if ($this->lon_min < 0.0){
            $this->lon_min360 = (float)($this->lon_min + 360.0);
            $this->lon_max360 = (float)($this->lon_max + 360.0);
            $this->applique360=true;
		}
		else{
            $this->lon_min360 = (float)($this->lon_min);
            $this->lon_max360 = (float)($this->lon_max);
            $this->applique360=false;
		}

        $this->lon_delta = (float)($this->lon_max360 - $this->lon_min360);

		// Latitude
		$this->lat_min = (float)$header->lat_min;
    	$this->lat_max = (float)$header->lat_max;
        $this->lat_delta = (float)($this->lat_max - $this->lat_min);

		$this->lon_n = $header->lon_n;
		$this->lon_inc = (float)$header->lon_inc;
		$this->lat_n = $header->lat_n;
		$this->lat_inc = (float)$header->lat_inc;

		// données
		$frames = array();
		$nf=0;
		if (!empty($data)){
    		foreach ($data as $dta){
				if (!empty($dta)){
					$frame = new stdClass();
					$frame->timestamp=$dta->timestamp;
					// expanser les lignes
					// chaque ligne <U> </U> est composées de long_n * lat_n valeurs avec un séparateur ';' toutes les lat_n valeurs...
					$dta->u = str_replace(';','',$dta->u);  // dta->u est un tableau de lon_n lignes de lat_n colonnes
					$frame->u = explode(' ', $dta->u);      // expanser les enregistrements U et V
    	            $dta->v = str_replace(';','',$dta->v);  // dta->v est un tableau de lon_n lignes de lat_n colonnes
					$frame->v = explode(' ', $dta->v);
					$this->t_grib[]=$frame;
					$nf++;
				}
			}
		}
	}


	//----------------------------------------------
	public function getGribInfo($timestamp){
		$grib_info = new stdClass();
        $grib_info->id=$this->id;
        $grib_info->longitude_min=$this->lon_min;
        $grib_info->longitude_max=$this->lon_max;
        $grib_info->lon_min360=$this->lon_min360;
        $grib_info->lon_max360=$this->lon_max360;

        $grib_info->latitude_min=$this->lat_min;
        $grib_info->latitude_max=$this->lat_max;

        $grib_info->lon_inc=$this->lon_inc;
        $grib_info->lat_inc=$this->lat_inc;

		$grib_info->lon_n=$this->lon_n;
        $grib_info->lat_n=$this->lat_n;

		$idframe=0;
		$nbframes = count($this->t_grib);
        $grib_info->framecourante = $idframe;
		$grib_info->timestamp_deb = $this->t_grib[0]->timestamp;
		$grib_info->timestamp_fin = $this->t_grib[$nbframes-1]->timestamp;
		$grib_info->longitude_centre = ($this->lon_min + $this->lon_max) / 2.0;
		$grib_info->latitude_centre = ($this->lat_min + $this->lat_max) / 2.0;

		return $grib_info;

	}

	//----------------------------------------------
	function getDalle($longitude, $latitude, $localdebug){
    //$localdebug=true;
 	   		// ATTENTION au passage de la ligne de changement de jour
		if ( $localdebug){
				echo "<br /><br><b>DEBUG :: GribClass.php :: 98</b> \n";
            	echo '<br />Longitude: '.$longitude.'<br/>Latitude: '.$latitude."\n";
				echo "<br /><b>Lon_min</b>: ".$this->lon_min." Lat_min: ".$this->lon_max."\n";
                echo "<br /><b>Lon_min360</b>: ".$this->lon_min360." Lon_max360: ".$this->lon_max360."\n";
				echo "<br /><br /><b>Lat_min</b>: ".$this->lat_min." Lat_max: ".$this->lat_max."\n";
                echo "<br /><b>Lon_delta</b>: ".$this->lon_delta." Lat_delta: ".$this->lat_delta."\n";

		}

		// attention aux longitudes negatives car il n'y en a pas dans les grib
        if (!$this->applique360){
			if ($longitude < 0.0 ){
				$longitude360 = ((float)$longitude + 360.0) ;
			}
			else{
            	$longitude360 = (float)$longitude;
			}
		}
		else{
			$longitude360 = ((float)$longitude + 360.0) ;
		}

		if (($this->lon_inc==0.0) || ($this->lat_inc==0.0)){
			echo "<br />DEBUG :: GribClass.php :: 114 :: <br />ERREUR \n";
			echo "Lon_inc: ".$this->lon_inc." Lat_inc: ".$this->lat_inc."<br />\n";
            die("DIVISION PAR ZERO\n");

		}

		$dalle = new stdClass();
		$dalle->index_lon = floor(($longitude360 - $this->lon_min360) / (float)$this->lon_inc );       // indice dans la dalle des données
	    $dalle->index_lat = floor(($latitude - $this->lat_min) / (float) $this->lat_inc );
		$dalle->delta_lon = (float)$longitude360 - ( (float)$this->lon_min360 + (float) $this->lon_inc * $dalle->index_lon);   // delta entre le point inferieur et la position recherchee
    	$dalle->delta_lat = (float)$latitude - ( (float)$this->lat_min +  (float) $this->lat_inc * $dalle->index_lat);
        $dalle->lon0 = (float)$this->lon_min +  (float)$this->lon_inc * $dalle->index_lon;
        $dalle->lon0_360 = (float)$this->lon_min360 +  (float)$this->lon_inc * $dalle->index_lon;
		$dalle->lat0 = (float)$this->lat_min +  (float)$this->lat_inc * $dalle->index_lat;
        $dalle->lon_360=(float)$longitude360;
        $dalle->lon=(float)$longitude;
        $dalle->lat=(float)$latitude;

		if ($localdebug){
			echo '<br />Dalle Grib<pre>'."\n";
            echo 'Lon: '.$dalle->lon."\n";
            echo 'Lon_360: '.$dalle->lon_360."\n";
            echo 'Lat: '.$dalle->lat."\n";
            echo 'Lon0: '.$dalle->lon0."\n";
            echo 'Lon0_360: '.$dalle->lon0_360."\n";
			echo 'Lat0: '.$dalle->lat0."\n";

			echo 'Index Lon: '.$dalle->index_lon."\n";
            echo 'Index Lat: '.$dalle->index_lat."\n";
            echo 'Delta Lon: '.$dalle->delta_lon."\n";
            echo 'Delta Lat: '.$dalle->delta_lat."\n";
    		echo '</pre>'."\n";
            // die("Données de position hors grille météo\n");
		}

		return $dalle;
	}

	//---------------------------
   	function getIndexesOfData($index_lon, $index_lat){
	// $index_lon, $index_lat : indice de la dalle où se situe le point à interpoler
	// je fais l'hypothèse que les données sont disposées en rangées de lat_n valeurs consécutives
	// la fonction retourne la liste des positions à récupérer
	// dans la table des composantes $u et $v
	// pos(index_lon, index_lat) = $index_lat + index_lon * lat_n
	// pos(index_lon+1, index_lat) = $index_lat +  (index_lon+1) * lat_n
	// pos(index_lon, index_lat+1) = $index_lat + 1 + index_lon * lat_n
    // pos(index_lon+1, index_lat+1) = $index_lat + 1 + (index_lon+1) * lat_n
		$posrec = new stdClass();
		if (($index_lon < $this->lon_n - 1) && ($index_lat < $this->lat_n -1 )){
	      	$posrec->index_00=$index_lat + $index_lon * $this->lat_n;
    	  	$posrec->index_10=$index_lat + ($index_lon+1) * $this->lat_n;
      		$posrec->index_01=$index_lat + 1 + $index_lon * $this->lat_n;
      		$posrec->index_11=$index_lat + 1 + ($index_lon+1) * $this->lat_n;
		}
		else{
			if ($index_lon == $this->lon_n - 1){
		      	$posrec->index_00=$index_lat + $index_lon * $this->lat_n;
    			// dupliquer
                $posrec->index_10=$posrec->index_00;
	      		$posrec->index_01=$index_lat + 1 + $index_lon * $this->lat_n;
                // dupliquer
      			$posrec->index_11=$posrec->index_01;
			}
			else if ($index_lat == $this->lat_n - 1){
		      	$posrec->index_00=$index_lat + $index_lon * $this->lat_n;
    		  	$posrec->index_10=$index_lat + ($index_lon+1) * $this->lat_n;
                // dupliquer
      			$posrec->index_01=$posrec->index_00;
      			$posrec->index_11=$posrec->index_10;
			}
		}

	  	return ($posrec);
	}

	//----------------------------------------------
	public function getTwsTwd($timestamp, $longitude, $latitude){
		$localdebug=false;
		if ($localdebug){
			echo "<br /><pre>TIMESTAMP : $timestamp\n";
            date_default_timezone_set('UTC');
        	echo "\n getTwsTwa: Date ".date("Y/m/d H:i:s T", $timestamp)."\n";
			echo "Lon: $longitude, Lat: $latitude\n";
			echo "<b>Données de la grille météo</b>\n";
        	echo "LON Min: ".$this->lon_min." LON Max: ".$this->lon_max."\n";
        	echo "LON Min_360: ".$this->lon_min360." LON_Max 360: ".$this->lon_max360."\n";
            echo "LON INC: ".$this->lon_inc."\n";
        	echo "LAT Min: ".$this->lat_min." LAT Max: ".$this->lat_max."\n";
			echo "LAT INC: ".$this->lat_inc."\n";
        	echo "LON Delta: ".$this->lon_delta." LAT Delta: ".$this->lat_delta."\n";
		}

		$cette_dalle = $this->getDalle($longitude, $latitude, $localdebug);

        if ($localdebug){
			echo "<b>Dalle Grib</b>\n";
            echo "Lon: ".$cette_dalle->lon."\n";
            echo "Lon_360: ".$cette_dalle->lon_360."\n";
            echo "Lat: ".$cette_dalle->lat."\n";
            echo "Lon0: ".$cette_dalle->lon0."\n";
            echo "Lon0_360: ".$cette_dalle->lon0_360."\n";
            echo "Lat0: ".$cette_dalle->lat0."\n";
			echo "Index Lon: ".$cette_dalle->index_lon."\n";
            echo "Index Lat: ".$cette_dalle->index_lat."\n";
            echo "Delta Lon: ".$cette_dalle->delta_lon."\n";
            echo "Delta Lat: ".$cette_dalle->delta_lat."\n";
      		echo "</pre>\n";
		}

		// rechercher la frame temporelle

		$idframe=0;

 // Ce code n'a plus lieu d'être car la frame enregistrée en position
 // 0 est celle qu'on recherche !
		$poursuivre=true;
		$nbframes = count($this->t_grib);
		while (($idframe < $nbframes) && $poursuivre){
        	if ($localdebug){
				echo "<br />Frame: ".$idframe." GRIB TIMESTAMP : ".$this->t_grib[$idframe]->timestamp."\n";
		       	echo '<br />Date '.date("Y/m/d H:i:s T", $this->t_grib[$idframe]->timestamp)."\n";
			}
			if ($this->t_grib[$idframe]->timestamp > $timestamp){ // fenetre temporelle depassee
				$poursuivre=false;
			}
			else{
				$idframe++;
			}
		}
    	if ($poursuivre){
    		return false; // echec
		}
		// fenetre trouvee
		$idframe--; // reculer
		$idframe1=$idframe+1; // fenêtre temporelle suivante
		// echo "<br />DEBUG :: GribClass :: 360:: NBFRAMES : $nbframes<br />IDFRAME: $idframe IDFRAME1: $idframe1\n";


        if (false){
			echo "<br />Frame sélectionnée: ".$idframe." GRIB TIMESTAMP : ".$this->t_grib[$idframe]->timestamp."\n";
	       	echo '<br />Date '.date("Y/m/d H:i:s T", $this->t_grib[$idframe]->timestamp)."\n";
			echo "<pre>\n";
			print_r($this->t_grib[$idframe] );
			echo "</pre>\n";
		}

		$pos_rec = $this->getIndexesOfData($cette_dalle->index_lon, $cette_dalle->index_lat);
        if ($localdebug){
			echo "<br />Index calculés pour Lon: ".$cette_dalle->index_lon." Lat: ".$cette_dalle->index_lat."\n";
	       	echo "<pre>\n";
			print_r($pos_rec);
			echo "</pre>\n";
			if ($pos_rec->index_00 < 0){
				echo "<br />ERREUR : INDEX_00 Négatif : $pos_rec->index_00\n";
				exit;
			}
			if ($pos_rec->index_10 < 0){
				echo "<br />ERREUR : INDEX_10 Négatif : $pos_rec->index_10\n";
				exit;
			}
			if ($pos_rec->index_01 < 0){
				echo "<br />ERREUR : INDEX_01 Négatif : $pos_rec->index_01\n";
				exit;
			}
			if ($pos_rec->index_11 < 0){
				echo "<br />ERREUR : INDEX_11 Négatif : $pos_rec->index_11\n";
				exit;
			}

		}

		// Algorithme de SOL : interpolation sur chaque composante séparément
		// Première composante temporelle
	    if (isset($this->t_grib[$idframe]->u)){       // composante  Est / Ouest  de TWS
			$composante_u_00 =  $this->t_grib[$idframe]->u[$pos_rec->index_00];
			$composante_u_01 =  $this->t_grib[$idframe]->u[$pos_rec->index_01];  // une case plus loin
        	$composante_u_10 =  $this->t_grib[$idframe]->u[$pos_rec->index_10];  // un rangee plus loin
         	$composante_u_11 =  $this->t_grib[$idframe]->u[$pos_rec->index_11];  // un rangee plus loin
        	// Interpolation bilinéaire en distance
        	$composante_u_0 = $this->ipolBilinDistance($cette_dalle->lon_360, $cette_dalle->lat,
				$cette_dalle->lon0_360 , $cette_dalle->lat0,
				$composante_u_00, $composante_u_01, $composante_u_10, $composante_u_11);
		}

		if (isset($this->t_grib[$idframe]->v)){       // composante  Nord / Sud  de TWS
			$composante_v_00 =  $this->t_grib[$idframe]->v[$pos_rec->index_00];
			$composante_v_01 =  $this->t_grib[$idframe]->v[$pos_rec->index_01];  // une case plus loin
        	$composante_v_10 =  $this->t_grib[$idframe]->v[$pos_rec->index_10];  // un rangee plus loin
         	$composante_v_11 =  $this->t_grib[$idframe]->v[$pos_rec->index_11];  // un rangee plus loin
			// Interpolation bilinéaire en distance
	        $composante_v_0 = $this->ipolBilinDistance($cette_dalle->lon_360, $cette_dalle->lat,
				$cette_dalle->lon0_360 , $cette_dalle->lat0,
				$composante_v_00, $composante_v_01, $composante_v_10, $composante_v_11);
		}

		//Seconde composante temporelle
	    if (isset($this->t_grib[$idframe1]->u)){       // composante  Est / Ouest  de TWS
			$composante_u_00 =  $this->t_grib[$idframe1]->u[$pos_rec->index_00];
			$composante_u_01 =  $this->t_grib[$idframe1]->u[$pos_rec->index_01];  // une case plus loin
        	$composante_u_10 =  $this->t_grib[$idframe1]->u[$pos_rec->index_10];  // un rangee plus loin
         	$composante_u_11 =  $this->t_grib[$idframe1]->u[$pos_rec->index_11];  // un rangee plus loin
        	// Interpolation bilinéaire en distance
        	$composante_u_1 = $this->ipolBilinDistance($cette_dalle->lon_360, $cette_dalle->lat,
				$cette_dalle->lon0_360 , $cette_dalle->lat0,
				$composante_u_00, $composante_u_01, $composante_u_10, $composante_u_11);
		}

		if (isset($this->t_grib[$idframe1]->v)){       // composante  Nord / Sud  de TWS
			$composante_v_00 =  $this->t_grib[$idframe1]->v[$pos_rec->index_00];
			$composante_v_01 =  $this->t_grib[$idframe1]->v[$pos_rec->index_01];  // un case plus loin
        	$composante_v_10 =  $this->t_grib[$idframe1]->v[$pos_rec->index_10];  // un rangee plus loin
         	$composante_v_11 =  $this->t_grib[$idframe1]->v[$pos_rec->index_11];  // un rangee plus loin
			// Interpolation bilinéaire en distance
	        $composante_v_1 = $this->ipolBilinDistance($cette_dalle->lon_360, $cette_dalle->lat,
				$cette_dalle->lon0_360 , $cette_dalle->lat0,
				$composante_v_00, $composante_v_01, $composante_v_10, $composante_v_11);
		}

         if ($localdebug){
   			echo "<br /><pre>".date("Y/m/d H:i:s T",$timestamp).", Long:".$longitude." Lat: ".$latitude."\n";
			echo "\nValeurs du calcul du point recherché : Long: ".$cette_dalle->lon_360.", Lat:".$cette_dalle->lat;
            echo "\nValeurs du calcul du point 0 de la dalle: Long: ".$cette_dalle->lon0_360.", Lat:".$cette_dalle->lat0;
			echo "\nComposantes espace\n";
			printf("Composantes (U0: %2.3f, V0:%2.3f) --> ", $composante_u_0, $composante_v_0);
			printf("Composantes (U1: %2.3f, V1:%2.3f) --> ", $composante_u_1, $composante_v_1);
            echo "\n</pre>\n";
		}

		// Interpollation linéaire en temps
		// On utilise une fonction de forme (shape function)
        $composante_u=$this->ipolLinTemps($timestamp, $this->t_grib[$idframe]->timestamp, $this->t_grib[$idframe1]->timestamp,
			$composante_u_0, $composante_u_1);

        $composante_v=$this->ipolLinTemps($timestamp, $this->t_grib[$idframe]->timestamp, $this->t_grib[$idframe1]->timestamp,
			$composante_v_0, $composante_v_1);

		// On calcule les vecteurs TWS et TWD
		$TwsTwd = $this->computeWindsComponents($composante_u, $composante_v);

        if ($localdebug){
            echo "\nComposantes temps\n";
			printf("(%2.3f, %2.3f) --> ", $composante_u, $composante_v);
            printf("TWS: %3.5f, TWD: %3.5f\t", $TwsTwd->tws, $TwsTwd->twd);
            echo "\n</pre><br />---------------------------------------<br /><br />\n";
//echo "EEEEE XXXXXXXXXXX IIII TTTTTT. ExitGribClass.php ::  ligne 362\n";
//			exit;
		}

		return $TwsTwd;
	}

	// --------------------------------------
	public function computeWindsComponents($tws_u, $tws_v){
	// $tws_v : composante horizontale (Vers l'Est)   en m/s ?
	// $tws_u : composante verticale (Vers le Nord )   en m/s ?
	// http://stackoverflow.com/questions/21484558/how-to-calculate-wind-direction-from-u-and-v-wind-components-in-r
    // https://blogs.esri.com/esri/arcgis/2013/07/17/displaying-speed-and-direction-symbology-from-u-and-v-vectors/
    // https://daysailer.wordpress.com/2011/02/20/how-to-calculate-tws-and-twd-from-grib-components/
		$tws_ms = sqrt($tws_u * $tws_u + $tws_v * $tws_v);

/*
   If (V > 0) then A = 180
If (U < 0) and (V < 0) then A = 0
If (U > 0) and (V < 0) then A = 360

TWD = (180 / pi) * arctan(U / V) + A

*/
  		if (($tws_v > -0.000001) && ($tws_v < 0.000001))  {  // pas de composante sur l'axe des x Risque de division par zero
			if ($tws_u > 0.0){
                $twd = 0.0;
			}
			else{
                $twd = 180.0;
			}
		}
		else {
        	// ne pas normaliser car cela introduit une erreur si u==v==0.0
			$twd_to_rad = atan2($tws_u, $tws_v); // direction où va le vent :: en radian :: coordonnées du cercle trigonométrique 0 rad = Est
			// atan2() retourne l'arc tangent de deux variables x et y.
			// La formule est : " arc tangent (y / x) ", et les signes des arguments
			// sont utilisés pour déterminer le quadrant du résultat.
			// Cette fonction retourne un résultat en radians, entre -PI et PI (inclus).

       	 	$twd_to_degrees = rad2deg($twd_to_rad);      // direction où va le vent :: en degré :: coordonnées du cercle trigonométrique 000 - 360
            $twd = $twd_to_degrees + 180.0;              // direction d'ou vient le vent
 		}

    	$tws =  $tws_ms * 1.94384449244059;  // conversion en noeuds
		$TwsTwd = new stdClass();
    	$TwsTwd->tws=$tws;
	    $TwsTwd->twd=$twd;
		return $TwsTwd;
	}

	//---------------------------------------
	public function affGrib($all=false){
	// les données sont rangées en serie de lat_n valeurs successives sur une "hauteur" de long_
       	echo "<br />Grib  N° ".$this->id."<br />\n<ul><li>Zone : Lon: ".$this->lon_min.", Lat: ".$this->lat_min." X Lon: ".$this->lon_max.", Lat: ".$this->lat_max."</li>
<li>Cases en longitudes: ".$this->lon_n." pas de ".$this->lon_inc."°;</li>
<li>Cases en latitudes: ".$this->lat_n." pas de ".$this->lat_inc."°</li></ul>\n";
		if ($all){
			echo "<br /><b>Données</b><br />\n";
			$n=count($this->t_grib);
			for($i=0; $i< $n; $i++){
				$this->affComposantesGrib($i);
			}
		}
	}

	//---------------------------------------
	public function affComposantesGrib($idframe){
	// les données sont rangées en serie de lat_n valeurs successives sur une "hauteur" de long_
   		echo "<br /><b>Données de la fenêtre $idframe</b><br />\n";
        $nbframes = count($this->t_grib);
		if ($idframe < $nbframes){
			$frame = $this->t_grib[$idframe];
			echo "<br /><br /><b>Date: ".date("Y/m/d H:i:s T", $frame->timestamp)."</b><br /><pre>\n";
			for ($i=0; $i<$this->lon_n; $i++){
				echo "LONGITUDE : $i / LATITUDE : \t";
				for ($j=0; $j<$this->lat_n; $j++){
                   	printf("(%2.3f, %2.3f) --> ", $frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
                    $TwsTwd=$this->computeWindsComponents($frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
					printf("TWS: %3.5f, TWD: %3.5f\t", $TwsTwd->tws,$TwsTwd->twd);
				}
                echo "\n";
			}
			echo "</pre>\n";

		}
	}


    //---------------------------
	function ipolBilinDistance($lon, $lat, $lon0, $lat0, $composante00, $composante01, $composante10, $composante11){
	//https://fr.wikipedia.org/wiki/Interpolation_bilin%C3%A9aire
	// retourne l'interpolation bilinéaire d'une composante scalaire
	// située en un point de coordonnées géographiques lon, lat
	// situé dans une dalle dont on fournit les valeurs scalaires
	// composante00, composante01, composante10, composante11
	// aux quatre sommets P0(lon0,lat0) ; P1(lon0,lat1) ; P2(lon1,lat0) ; P3(lon1,lat1)
 	// a priori la division par zéro est exclue si (lon0,lat0) != (lon1, lat1)...
		if (empty($this->lon_inc) || ($this->lon_inc==0.0) || empty($this->lat_inc) || ($this->lat_inc==0.0)) {
			return false;
		}

		// variables auxiliaires de position
		$d_lon = (float) ($lon - $lon0);
		$d_lat = (float) ($lat - $lat0);
		// $this->lon_inc = $delta_lon = $lon1 - $lon0;
    	// $this->lat_inc == $delta_lat = $lat1 - $lat0;

		// variables auxiliaires de composantes
		$delta_c1 = (float) ($composante10 - $composante00);
		$delta_c2 = (float) ($composante01 - $composante00);
		$delta_c3 = (float) ($composante00 + $composante11 - $composante10 - $composante01);
		// valeur scalaire résultante

		$bili = $composante00 + $delta_c1 * $d_lon / (float) $this->lon_inc;
		$bili += $delta_c2 * $d_lat / (float) $this->lat_inc;
		$bili += $delta_c3 * ($d_lon / (float) $this->lon_inc) * ($d_lat / (float) $this->lat_inc);
        return ($bili);
	}

/*
a) une normalisation de la durée
DUREE_SECONDES = t = T-T0
DUREE_SECONDES_NORMALISEE = tn= (T-T0)/(T1-T0)

b) une interpolation linéaire avec une fonction de forme (shape function) en temps
UXTY = UXT0 + ( UXT1 - UXT0 ) x ( 1/2 + cos ( DUREE_SECONDES_NORMALISEE / 2PI )/2 ).
VXTY = VXT0 + ( VXT1 - VXT0 )  x ( 1/2 + cos ( DUREE_SECONDES_NORMALISEE / 2PI )/2 ).
*/

	//---------------------------
	function ipolLinTemps($t, $t0, $t1, $composante0, $composante1){
	// shape function
		// normalisation en temps
		$tn= (float)($t - $t0) / (float)($t1 - $t0);  // pour avoir t dans l'intervalle [0..1]
        // $tn= (float) $t / (float)($t1 - $t0) - $t0;
		return ($composante0 + 0.5 * ($composante1 - $composante0) * (1.0 + cos($tn / (2.0 * M_PI))));
	}



	//---------------------------------------
	public function gribToTable($composante=false){
	// les données sont rangées en serie de lat_n valeurs successives sur une "hauteur" de long_
       	echo "<br /><pre>Grib  N° ".$this->id."\nZone : Lon: ".$this->lon_min.", Lat: ".$this->lat_min." X Lon: ".$this->lon_max.", Lat: ".$this->lat_max."
\nCases en longitudes: ".$this->lon_n." pas de ".$this->lon_inc."°;
\nCases en latitudes: ".$this->lat_n." pas de ".$this->lat_inc."°\n";
		$n=count($this->t_grib);
		for($i=0; $i< $n; $i++){
			$this->gribComposantesToTable($i, $composante);
		}
		echo "\n</pre>\n";
	}

	//---------------------------------------
	public function gribComposantesToTable($idframe, $affcomp=false, $sep='\t'){
	// les données sont rangées en serie de lat_n valeurs successives sur une "hauteur" de long_
   		if ($affcomp){
		   echo "\n<b>Données composante (u,v) de la fenêtre $idframe</b>";
		}
		else{
		   echo "\n<b>Données TWS, TWD de la fenêtre $idframe</b>";
		}
        $nbframes = count($this->t_grib);
		if ($idframe < $nbframes){
			$frame = $this->t_grib[$idframe];
			echo "\n<b>Date: ".date("Y/m/d H:i:s T", $frame->timestamp)."</b>";
            echo "\nLATITUDE\LONGITUDE\t";
            for ($i=0; $i<$this->lon_n; $i++){
				printf("%3.5f\t", $i*(float)$this->lon_inc+(float)$this->lon_min);
			}
			$j=$this->lat_n-1;
			while ($j>-1){
                printf("\n%3.5f\t", $j*(float)$this->lat_inc+(float)$this->lat_min);
				for ($i=0; $i<$this->lon_n; $i++){
                   	if ($affcomp){
					   printf("(%2.3f, %2.3f)\t", $frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
                    }
					else{
						$TwsTwd=$this->computeWindsComponents($frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
						printf("%3.5f, %3.5f\t", $TwsTwd->tws,$TwsTwd->twd);
					}
				}
				$j--;
			}
			echo "\n\n";
		}
	}

	//---------------------------------------
	public function exportGrib($grib2export){
	// les données sont rangées en serie de lat_n valeurs successives sur une "hauteur" de long_
        if ($gf = fopen($grib2export, "w")){
            if (fwrite($gf, "Grib  N° ".$this->id."\nZone : Lon: ".$this->lon_min.", Lat: ".$this->lat_min." X Lon: ".$this->lon_max.", Lat: ".$this->lat_max."\nCases en longitudes: ".$this->lon_n." pas de ".$this->lon_inc."°;\nCases en latitudes: ".$this->lat_n." pas de ".$this->lat_inc."°\n") !== FALSE){
				$n=count($this->t_grib);
				for($i=0; $i< $n; $i++){
					fwrite($gf, $this->exportTwsTwd($i));
				}
				fwrite($gf, "\n\n");
            	fclose($gf);
			}
			else{
				return false;
			}
			return true;
		}
		return false;
	}


	//---------------------------------------
	public function exportTwsTwd($idframe){
	// les données sont rangées en lat_n rangées de lon_n valeurs
		$s="\nDonnées TWS, TWD de la fenêtre $idframe\n";

        $nbframes = count($this->t_grib);
		if ($idframe < $nbframes){
			$frame = $this->t_grib[$idframe];
			$s.="\nDate: ".date("Y/m/d H:i:s T", $frame->timestamp)."";
            $s.="\nLATITUDE\LONGITUDE\t";
            for ($i=0; $i<$this->lon_n; $i++){
				$s.=sprintf("%3.5f\t", $i*(float)$this->lon_inc+(float)$this->lon_min);
			}
			$j=$this->lat_n-1;
			while ($j>-1){
                $s.=sprintf("\n%3.5f\t", $j*(float)$this->lat_inc+(float)$this->lat_min);
				for ($i=0; $i<$this->lon_n; $i++){
					$TwsTwd=$this->computeWindsComponents($frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
					$s.=sprintf("%3.5f;%3.5f\t", $TwsTwd->tws,$TwsTwd->twd);
				}
				$j--;
			}
			$s.="\n\n";
		}
		return $s;
	}


	//----------------------------------------------
    public function exportGrib2Barbules($idframe, $barb){
    	$t_barbules=array();
		if ($idframe<count($this->t_grib)){
			$frame = $this->t_grib[$idframe];
			//$s.="\nDate: ".date("Y/m/d H:i:s T", $frame->timestamp)."";
            //$s.="\nLATITUDE\LONGITUDE\t";
			// Longitude
            //for ($i=0; $i<$this->lon_n; $i++){
			//	$s.=sprintf("%3.5f\t", $i*(float)$this->lon_inc+(float)$this->lon_min);
			//}
			$j=$this->lat_n-1;
			while ($j>-1){
				// Latitudes
				//$s.=sprintf("\n%3.5f\t", $j*(float)$this->lat_inc+(float)$this->lat_min);
				for ($i=0; $i<$this->lon_n; $i++){
					$TwsTwd=$this->computeWindsComponents($frame->u[$i*$this->lat_n + $j], $frame->v[$i*$this->lat_n + $j]);
					//$s.=sprintf("%3.5f, %3.5f\t", $TwsTwd->tws,$TwsTwd->twd);

					$unebarbule = new Barbule();
                    $unebarbule->SetBarbule($i+$j*$this->lon_n, $j*(float)$this->lat_inc+(float)$this->lat_min, $i*(float)$this->lon_inc+(float)$this->lon_min,	$barb, $TwsTwd->twd, $TwsTwd->tws);
					$t_barbules[]=$unebarbule;
				}
				$j--;
			}
		}
		return $t_barbules;
	}

} // fin de la classe

?>