<?php
// JF 2009

// DEFINITION D'UN CLASSE VOILIER d'après MP
// V 1.3 : intégration de la trajectoire au modèle
// couleurs des voiles par methode autonome.
// A adapter au type de données enregistrées sur les serveurs de courses virtuelles

// -----------------------------------
// la classe Coordonnees 
class Coordonnees{
	// coordonnees décimales d'un point dans l'espace géodésique 
	var $lon;
	var $lat;
	var $alt;
	
	function Coordonnees($lon, $lat, $alt=0){
		$this->lon=$lon;
		$this->lat=$lat;
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
}

// -----------------------------------
// la classe voilier 
class Voilier{
    var $type; // monocoque, multicoque, motorboat
	var $mmsi; // identifiant AIS
	var $nom;
	var $syc; // yacht club
    var $idsol; // numero identifiant sur SolOnLine
	var $date_enregistrement;
	var $longitude;
	var $latitude;
	var $cog;
	var $sog;
	var $navstatus;
	var $voile;
	var $twa;
	var $twd;
    var $tws;

	var $couleur_coque; // coque RRR;VVV;BBB decimal 0-255
    var $couleur_pont; //
	var $couleur_gv; // Grand'voile

    var $couleur_vav; // VoileAvant
	var $couleur_spi; // Spi
	var $couleur_genak; // Spi 2

	var $couleur1; // pour la 2D
    var $couleur2;
    var $couleur3;
	var $couleur4;

    var $classement;
    var $dtg; // distance à courir
    var $dbl;   // distance au premier
	var $log;  // distance parcourue
	var $current_leg;

	var $trajectoire=array(); // tableau de Coordonnees(lon, lat, alt);

	function GetType(){
		return $this->type;
	}
	function GetMmsi(){
		return $this->mmsi;
	}
	function GetNom(){
		return $this->nom;
	}
	function GetSyc(){
		return $this->syc;
	}
	function GetIdSol(){
		return $this->idsol;
	}

	function GetDateEnregistrement(){
		return $this->date_enregistrement;
	}

	function GetCog(){
		return $this->cog;
	}
	function GetSog(){
		return $this->sog;
	}

	function GetLongitude(){
		return $this->longitude;
	}
	function GetLatitude(){
		return $this->latitude;
	}
	function GetCouleurCoque(){
		return $this->couleur_coque;
	}
	function GetCouleurPont(){
		return $this->couleur_pont;
	}
	function GetCouleurGV(){
		return $this->couleur_gv;
	}
	function GetCouleurVav(){
		return $this->couleur_vav;
	}
	function GetCouleurSpi(){
		return $this->couleur_spi;
	}
	function GetCouleurGennaker(){
		return $this->couleur_genak;
	}


		
	// -----------------------------------------------
	function SetColors3RGB($color_R, $color_G, $color_B){
	// SailOnLine version
		$this->couleur_coque = $color_R.';'.$color_G.';'.$color_B; // hull RRR;VVV;BBB decimal 0-255
		$this->couleur_pont= $color_G.';'.$color_B.';'.$color_R; // deck
		$this->couleur_gv = $color_B.';'.$color_R.';'.$color_G; // Main sail Grand'voile
		$this->couleur_vav = $color_R.';'.$color_B.';'.$color_G; // Jub or Genois VoileAvant
		$this->couleur_spi = $color_G.';'.$color_R.';'.$color_B;// Spi
		$this->couleur_genak= $color_B.';'.$color_G.';'.$color_R; // Gennaker ; Spi 2
	
	// Pour les bateaux en 2D
		$this->couleur1=$this->UneCouleur($this->couleur_coque);
		$this->couleur2=$this->UneCouleur($this->couleur_pont);
		$this->couleur3=$this->UneCouleur($this->couleur_gv);
		$this->couleur4=$this->UneCouleur($this->couleur_vav);		
	}

	//-------------------------
	function hexa2_3dec($hexa){
		// rrvvbb -> hexdec(rr);hexdec(vv);hexdec(bb)
		if (list($rr, $vv, $bb) = explode(';', chunk_split ($hexa,2,';'))){
			return (hexdec($rr).';'.hexdec($vv).';'.hexdec($bb));
		}
		return false;
	}

	//-----------------------------------
	function SetColors4HexaToDec($hcolor){
	// Virtual Regatta version
	// Input: $hcolor  	= "coque,voile,foc,spi" coque:ffffff,voile:ffff33,foc:ffffff,spi:ee33ef
	// Output: $voilier->une_couleur_par_element au format RRR;VVV;BBB entre 0 et 255
		if ($hcouleur){
			list($ccoque, $cvoile, $cfoc, $cspi) = explode(',', $hcolor);
			$this->couleur_coque = $this->hexa2_3dec($ccoque);
			$this->couleur_pont = $this->hexa2_3dec($cpont);
			$this->couleur_vav = $this->hexa2_3dec($cfoc);
			$this->couleur_gv = $this->hexa2_3dec($cvoile);
			$this->couleur_spi = $this->hexa2_3dec($cspi);
			$this->couleur_genak = $this->hexa2_3dec($cgenak);	

			// Pour les bateaux en 2D
			$this->couleur1=$this->UneCouleur($this->couleur_coque);
			$this->couleur2=$this->UneCouleur($this->couleur_pont);
			$this->couleur3=$this->UneCouleur($this->couleur_gv);
			$this->couleur4=$this->UneCouleur($this->couleur_vav);					
		}
	}
	
	
	// -----------------------------------------------
	function SetVoile(){
		if (!isset($this->twa) || !isset($this->tws)){
            $this->voile = 1; // foc
		}
		else {
			$atwa= abs($this->twa);
         	if ( $atwa > 140.0)  {
					$this->voile = 2; // spi
			}
			else if (abs($atwa) > 110.0){
            	    $this->voile = 64; // genak
			}
			else if ($atwa> 40.0){
            	if ($this->tws > 15.0){
	                $this->voile = 8; // genois
				}
				else{
					$this->voile = 16; // code zero
				}
            }
            else {
				if ($this->tws < 15.0){
					// genois
					$this->voile = 8; // genois
				}
				else {
                	$this->voile = 1; // foc
				}
			}

		}
	}


	// -------------------------------------------
	function GetVoile(){
		if (empty($this->voile)){
            return 'spi';
		}
		else{
			switch ($this->voile){
				case 2 : return 'spi'; break;
				case 4 : return 'foc2'; break;
				case 8 : return 'genois'; break;
				case 16 : return 'code zero'; break;
				case 32 : return 'spi leger'; break;
				case 64 : return 'gennaker'; break;
				default : return 'foc'; break;
			}
		}
	}

	public function GiteVoilier($debug=false){
		$gite = new stdClass();
		$gite->x=0.0;
        $gite->y=0.0;
		if ($this->type!='motorboat'){
			$sign=1;
			if ($this->twa<0){
				$sign=-1;
			}
			switch ($this->voile){
				case 2 : 		// spi
				case 32 :
				case 64 :
	                $gite->x = abs(round((180.0 - abs($this->twa)) * $this->tws *.001)) % 180;
					$gite->y = (round((180.0 - abs($this->twa)) * $this->tws *.006)) % 180; // (360 - (int) (abs(180 - $this->wind_angle) * $this->wind_speed * 0.005));
					break;
				default :
    	            $gite->x = 0.0;
					$gite->y = $sign * (round((180.0 - abs($this->twa)) * $this->tws *.005)) % 180; // (360 - (int) (abs(180 - $this->wind_angle) * $this->wind_speed * 0.005));
					break;
			}
		}
		// DEBUG
		if ($debug){
			echo "<br>Voile:$this->voile, cog:$this->cog, TWA:$this->twa,  TWS:$this->tws , GiteX:$gite->x , GiteY:$gite->y\n";
		}
		return $gite;
	}
	
	public function getAngulation($debug=false){
		// angle de la voile avec la direction du bateau
		// dépend de la force du vent (TWS) et de l'angle avec le vent (TWA)
		return 0; // pas encore au point
		$angulation=0.0;
        $sign=1;
		if ($this->twa>=0){ // tribord amure
			$sign=-1;      // on diminue l'angle par rapport à la direction
		}
		switch ($this->voile){
			case 2 : 		// spi
			case 32 :
			case 64 :
                $angulation = (180.0 - abs($this->twa)) * $this->tws * 0.025 ; // (/ 40 force max du vent
				break;
			default :
                $angulation = (2 * abs($this->twa)) * $this->tws * 0.025 ;
				break;
		}
        $angulation = $sign * $angulation;
		// DEBUG
		if ($debug){
			echo "<br>Angulation: $this->voile, cog:$this->cog, TWA:$this->twa,  TWS:$this->tws  : $angulation\n";
		}
		return $angulation;
	}

	function SetPosition( $mmsi, $nom, $syc, $idsol,
							$date_enregistrement,
							$latitude, $longitude,
							$cog, $sog,
							$navstatus,
							$voile=0,
                            $twd, $tws, $twa,
							$classement=0, $dtg=0, $dbl=0, $log=0, $current_leg=0, $type='monocoque'){

        $this->type=$type;  // type ==  monocoque || catamaran || trimaran || motorboat
		$this->mmsi=$mmsi;
		$this->nom=$nom;
        $this->syc=$syc;
        $this->idsol=$idsol;
		$this->date_enregistrement=$date_enregistrement;
		$this->latitude=$latitude;
		$this->longitude=$longitude;
		$this->cog=$cog;
		$this->sog=$sog;
        $this->navstatus=$navstatus;

		$this->twd=$twd;
        $this->tws=$tws;
		$this->twa=$twa;
		$this->classement=$classement;
        $this->dtg=$dtg;
        $this->dtg=$dbl;
        $this->dtg=$log;
        $this->current_leg=$current_leg;

		$this->ok_trajectoire=false; // pas de trajectoire par defaut
	}
	/*
	function SetClassementCourse($rang){
		$this->classement_course=$rang;
	}

	function SetRang($rang){
		$this->rang=$rang;
	}
	*/

	function Affiche(){
		echo ($this->mmsi.', '.$this->nom.', '.$this->syc.', '.$this->idsol.', '.$this->date_enregistrement.', '.$this->latitude.', '.$this->longitude.', '.$this->cog.', '.$this->sog.', '.$this->couleur1);
		echo "<br>\n";
	}
	
	function Dump() {
        var_dump(get_object_vars($this));
    }


	function UneCouleur($couleur){
		// a réécrire pour plus de fidélité aux couleurs des bateaux
		if ($couleur){
			list($rouge, $vert, $bleu) = explode(';', $couleur);
			// DEBUG
			// echo '<br>Couleur ('.$rouge.', '.$vert.', '.$bleu.')'."\n";
			
			if (($rouge<128) && ($vert<128) && ($bleu>=128)) return 'bleu';
			if (($rouge<128) && ($vert>=128) && ($bleu<128)) return 'vert';
			if (($rouge>=128) && ($vert<128) && ($bleu<128)) return 'rouge';
			if (($rouge>=128) && ($vert<128) && ($bleu>=128)) return 'mauve';
			if (($rouge>=128) && ($vert>=128) && ($bleu<128)) return 'jaune';
			if (($rouge>=128) && ($vert>=128) && ($bleu>=128)) return 'blanc';
		}
		return 'noir';
	}
	
	function SetTrajectoire($une_liste_trajectoire){
		// 	120.45!35.7402;121.513!35.6521;121.723!35.7717;122.005!35.9095;122.929!35.4879;123.407!34.2182;123.777!33.2043;124.302!32.0607;125.059!31.7046;126.559!30.3949;128.73!28.5023;129.363!27.6401;131.83!27.5401;133.809!27.5401;134.338!27.0157;136.038!25.989;137.363!25.2493;138.922!23.7126;139.214!23.2367;139.298!22.5751;141.3!19.3556;141.773!18.7157;142.985!18.4076;143.268!17.7516;144.166!16.7804;145.742!15.6018;146.808!14.453;148.112!12.8392;148.916!11.8915;149.232!11.5815;150.275!10.9631;152.009!9.52024;153.004!8.7869;153.716!8.00948;155.069!6.19258;155.349!5.89654;156.229!4.73727;156.62!4.18257;156.921!3.71216;157.6!1.69251;157.768!1.41695;158.285!0.535084;158.599!-0.228893;158.902!-0.567375;159.482!-2.7696;160.396!-3.9546;160.644!-4.76668;160.902!-5.62809;161.925!-6.87726;162.185!-7.27779;162.358!-7.7477;163.825!-9.02144;164.478!-11.064;164.767!-11.5391;165.559!-12.4896;166.208!-13.6263;166.507!-15.251;166.422!-16.4251;166.498!-16.9328;166.729!-17.1236;167.617!-17.7865;167.909!-18.6209;168.687!-18.1922;168.793!-18.6472;168.909!-19.5911;169.259!-19.5687;170.199!-19.6453;
		// lon!lat
		if (($une_liste_trajectoire!='') && ereg(';',$une_liste_trajectoire) && ereg('!',$une_liste_trajectoire)){
			$t_traj=explode(';',$une_liste_trajectoire);
			for ($i=0; $i<count($t_traj); $i++){
				if (!empty($t_traj[$i])){
					list($lon, $lat) = explode('!', $t_traj[$i]);
					// 120.45!35.7402
					$lon=trim($lon);
					$lat=trim($lat);
					$this->trajectoire[]=new Coordonnees($lon, $lat);
				}
			}
			$this->ok_trajectoire=true;
		}
	}

	function GetTrajectoire(){
		if ($this->ok_trajectoire==true){
			return $this->trajectoire; 
		}
		else{
			return false;
		}
	}
	
	function ListeTrajectoire($altitude=0){
		$s='';
		if ($this->ok_trajectoire){
			for ($i=0; $i<count($this->trajectoire); $i++){
				if (($this->trajectoire[$i]->GetLon()!='') && ($this->trajectoire[$i]->GetLat()!='')){
					$s.= $this->trajectoire[$i]->GetLon().",".$this->trajectoire[$i]->GetLat().','.$this->trajectoire[$i]->GetAlt()." ";
				}
			}
			$s.= $this->longitude.",".$this->latitude.','.$altitude."\n";
		}
		return $s;
	}
	
	
	function meme_quadran($lon1, $lat1, $lon2, $lat2, $lon, $lat){
		$ouest1=($lon1<=$lon);
		$nord1=($lat1>=$lat);
		$ouest2=($lon2<=$lon);
		$nord2=($lat2>=$lat);
		if ( 
			($ouest1 && $nord1 && $ouest2  && $nord2)
			||
			($ouest1 && !$nord1 && $ouest2  && !$nord2)
			||
			(!$ouest1 && $nord1 && !$ouest2  && $nord2)
			||
			(!$ouest1 && !$nord1 && !$ouest2  && !$nord2)
			){
			// même quadran
			return true; // passer au point suivant
		}
		return false;
	}

	function oppose_quadran($lon1, $lat1, $lon2, $lat2, $lon, $lat){
		$ouest1=($lon1<=$lon);
		$nord1=($lat1>=$lat);
		$ouest2=($lon2<=$lon);
		$nord2=($lat2>=$lat);
		if (($ouest1 && !$nord1 && !$ouest2 && $nord2)
				||
				(!$ouest1 && $nord1 && $ouest2 && !$nord2)
				||
				($ouest1 && $nord1 && !$ouest2 && !$nord2)
				||
				(!$ouest1 && !$nord1 && $ouest2 && $nord2)
			){
			// quadrans opposes
			return true; 
		}
		return false;
	}

	function SetMarqueValide($lon, $lat, $mode){
	// verifie si la trajectoire remplit la condition de passage de marque de parcours
	// $lon : longitude, $lat : latitude marque de parcours
	/* $mode :
		'NWE' : // laisser balise au Sud en allant de W vers E
		'SWE' : // laisser balise au Nord en allant de W vers E
		'NEW' : // laisser balise au Sud en allant de E vers W
		'SEW' : // laisser balise au Nord en allant de E vers W
		
		'ENS' : // laisser balise à l'Ouest en allant de N vers S
		'WNS' : // laisser balise Est en allant de N vers S 
		'ESN' : // laisser balise à l'Ouest en allant de S vers N
		'WSN' : // laisser balise Est en allant de S vers N
	*/
		$valide=false;
		if ($this->ok_trajectoire){
			$i=0; 
			while (($i<count($this->trajectoire)-1) && (!$valide)){
				if (
					($this->trajectoire[$i]->GetLon()!='') 
					&& ($this->trajectoire[$i]->GetLat()!='') 
					&& ($this->trajectoire[$i+1]->GetLon()!='')
					&& ($this->trajectoire[$i+1]->GetLat()!='')
					){
					$lon1=$this->trajectoire[$i]->GetLon();
					$lon2=$this->trajectoire[$i+1]->GetLon();
					$lat1=$this->trajectoire[$i]->GetLat();
					$lat2=$this->trajectoire[$i+1]->GetLat();
					
					if (!$this->meme_quadran($lon1, $lat1, $lon2, $lat2, $lon, $lat)){
						while ($encore && $this->oppose_quadran($lon1, $lat1, $lon2, $lat2, $lon, $lat)){
							// partager segment et recommencer avec point milieu
							$lon_milieu=($lon1+$lon2) / 2.0;
							$lat_milieu=($lat1+$lat2) / 2.0;
							if ($this->meme_quadran($lon1, $lat1, $lon_milieu, $lat_milieu, $lon, $lat)){
									$lon1=$lon_milieu;
									$lat1=$lat_milieu;
									$encore=false;
							}
							else if ($this->meme_quadran($lon2, $lat2, $lon_milieu, $lat_milieu, $lon, $lat)){
								$lon2=$lon_milieu;
								$lat2=$lat_milieu;
								$encore=false;
							}
							else{
								// nouvelle division
								$lon2=$lon_milieu;
								$lat2=$lat_milieu;
							}
						}
						
						// comparer 
						$ouest1=($lon1<=$lon);
						$nord1=($lat1>=$lat);
						$ouest2=($lon2<=$lon);
						$nord2=($lat2>=$lat);
						
						switch ($mode){
						case 'NWE' : // laisser balise au sud
								$valide=$ouest1 && !$ouest2 && $nord1 && $nord2;
							break;
						case 'SWE' : // laisser balise au Nord
								$valide=$ouest1 && !$ouest2 && !$nord1 && !$nord2;
							break;
						case 'NEW' : // laisser balise au sud
								$valide=!$ouest1 && $ouest2 && $nord1 && $nord2;
							break;
						case 'SEW' : // laisser balise au Nord
								$valide=!$ouest1 && $ouest2 && !$nord1 && !$nord2;
							break;
						case 'ENS' : // laisser balise à l'ouest
								$valide=!$ouest1 && !$ouest2 && $nord1 && !$nord2;
							break;
						case 'WNS' : // laisser balise est
								$valide=$ouest1 && $ouest2 && $nord1 && !$nord2;
							break;
						case 'ESN' : // laisser balise à l'ouest
								$valide=!$ouest1 && !$ouest2 && $nord2 && !$nord1;
							break;
						case 'WSN' : // laisser balise est
								$valide=$ouest1 && $ouest2 && $nord2 && !$nord1;
							break;
						default :
								$valide=false;
							break;
						}
					}
				}
				$i++;
			}
		}
		$this->marque_valide=$valide;
	}

}

?>