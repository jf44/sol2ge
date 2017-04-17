<?php

// JF + Xolub + Kobis

// Génération KML des  VOILIERS en 3D
// 2017/04/17

// --------------------
function GetNomVoile($voile){
// retourne une  voile
	switch ($voile){
		case '2' :
		case '32' :
        	$nom_voile='spi';
            break;
		case '64' :
			$nom_voile='genak';
			break;
		case '4' :
		case '8' : 
			$nom_voile='genois';
			break;
		case '1' : $nom_voile='foc';
		default :
			$nom_voile='foc';
			break;
	}
	return $nom_voile;
}

// --------------------
function GetFichierModeleVoile($nom_voile, $bord, $modele='monocoque'){
// retourne un modele de fichier dae correspondant a la voile portee
	if ($modele=='motorboat'){
        return 'coque_'.$bord.'_motorboat.dae';
	}
	else{
	if ($nom_voile=='spi'){
		return 'spi_'.$bord.'_'.$modele.'.dae';
	}
    else if ($nom_voile=='genak'){
		 return 'genak_'.$bord.'_'.$modele.'.dae';
	}
	else if ($nom_voile=='genois'){
		 return 'genois_'.$bord.'_'.$modele.'.dae';
	}
	else if ($nom_voile=='codezero' || $nom_voile=='foc'){
		return 'foc_'.$bord.'_'.$modele.'.dae';
	}
	else if ($nom_voile=='vav'){
		return 'vav_'.$bord.'_'.$modele.'.dae';
	}
	else {
		return 'gv_'.$bord.'_'.$modele.'.dae';
	}
	}
}

// --------------------
function GenereTextureCoqueBateau_3D($dossier_cible, $url_serveur, $couleur_coque, $couleur_pont, $couleur_cockpit='255;255;255'){
global $dir_serveur;
global $dossier_modeles;
global $dossier_textures;
	$chemin=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	if ($url_serveur!=''){ // liens absolus
		$url=$url_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	}
	else{ // liens relatifs
		$url='../'.$dossier_modeles.'/'.$dossier_textures;
	}
	// DEBUG
	//echo "<br /> DEBUG : $chemin   \n";
	//exit;
	$s='';
	if ($couleur_coque){
		list($rouge, $vert, $bleu) = explode(';', $couleur_coque);
		$texture=genere_texture($chemin, $rouge, $vert, $bleu);
		if ($texture!=""){
			$s.='	<Alias>
		<sourceHref>'.$dossier_textures.'/coque.png</sourceHref>
    	<targetHref>'.$url.'/'.$texture.'</targetHref>
	</Alias>
';
		}
	}
	if ($couleur_pont){
		list($rouge, $vert, $bleu) = explode(';', $couleur_pont);
		$texture=genere_texture($chemin, $rouge, $vert, $bleu);
		if ($texture!=""){
			$s.='	<Alias>
		<sourceHref>'.$dossier_textures.'/pont.png</sourceHref>
    	<targetHref>'.$url.'/'.$texture.'</targetHref>
	</Alias>
';
		}
	}
	if ($couleur_cockpit){
		list($rouge, $vert, $bleu) = explode(';', $couleur_cockpit);   // idem à GV
		$texture=genere_texture($chemin, $rouge, $vert, $bleu);
		if ($texture!=""){
			$s.='	<Alias>
		<sourceHref>'.$dossier_textures.'/glass.png</sourceHref>
    	<targetHref>'.$url.'/'.$texture.'</targetHref>
	</Alias>
';
		}
	}
	return $s;
}


// --------------------
function GenereTextureGrandVoileBateau_3D($dossier_cible, $url_serveur, $couleur){
global $dir_serveur;
global $dossier_modeles;
global $dossier_textures;
	$chemin=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	if ($url_serveur!=''){ // liens absolus
		$url=$url_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	}
	else{ // liens relatifs
		$url='../'.$dossier_modeles.'/'.$dossier_textures;
	}

	$s='';
	if ($couleur){
		list($rouge, $vert, $bleu) = explode(';', $couleur);
		// DEBUG
		// echo '<br>Couleur ('.$rouge.', '.$vert.', '.$bleu.')'."\n";
		$texture=genere_texture($chemin, $rouge, $vert, $bleu);

		if ($texture!=""){
			$s.='	<Alias>
		<sourceHref>'.$dossier_textures.'/gv.png</sourceHref>
    	<targetHref>'.$url.'/'.$texture.'</targetHref>
	</Alias>
';
		}
	}
 	return $s;
}

// --------------------
function GenereTextureVoileAvantBateau_3D($dossier_cible, $url_serveur, $voile, $couleur){
global $dir_serveur;
global $dossier_modeles;
global $dossier_textures;
	$chemin=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	if ($url_serveur!=''){ // liens absolus
		$url=$url_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$dossier_textures;
	}
	else{ // liens relatifs
		$url='../'.$dossier_modeles.'/'.$dossier_textures;
	}
	$s='';
	if ($couleur){
		list($rouge, $vert, $bleu) = explode(';', $couleur);
		// DEBUG
		// echo '<br>Couleur ('.$rouge.', '.$vert.', '.$bleu.')'."\n";
		$texture=genere_texture($chemin, $rouge, $vert, $bleu);

		if ($texture!=""){
			$s.='	<Alias>
		<sourceHref>'.$dossier_textures.'/vav.png</sourceHref>
    	<targetHref>'.$url.'/'.$texture.'</targetHref>
	</Alias>
';
		}
	}
	return $s;
}

// --------------------
function GenereRessourceMapBateauComplet($dossier_cible, $url_serveur, $bato){
// Coque + GV + Voiles avant
	$s='<ResourceMap>
';
	$s.=GenereTextureCoqueBateau_3D($dossier_cible, $url_serveur, $bato->couleur_coque, $bato->couleur_pont, $bato->couleur_gv);

	$s.=GenereTextureGrandVoileBateau_3D($dossier_cible, $url_serveur, $bato->couleur_gv);

	$unevoile = GetNomVoile($bato->voile);
	if (($unevoile=='spi') || ($unevoile=='genak')){
		$s.=GenereTextureVoileAvantBateau_3D($dossier_cible, $url_serveur, $bato->voile, $bato->couleur_spi);
	}
	else {
		$s.=GenereTextureVoileAvantBateau_3D($dossier_cible, $url_serveur, $bato->voile, $bato->couleur_vav);
	}
	$s.='</ResourceMap>
';
	// DEBUG
	//echo "<br /> DEBUG : Ressources : $s   \n";


	return $s;
}


// --------------------
function GenereRessourceMapCoque($dossier_cible, $url_serveur, $bato){
// Version par element
	$s='<ResourceMap>
';
	$s.=GenereTextureCoqueBateau_3D($dossier_cible, $url_serveur, $bato->couleur_coque, $bato->couleur_pont, $bato->couleur_gv);
	$s.='</ResourceMap>
';
	return $s;
}


// --------------------
function GenereRessourceMapGrandVoile($dossier_cible, $url_serveur, $bato){
// Version par element
	$s='<ResourceMap>
';
	$s.=GenereTextureGrandVoileBateau_3D($dossier_cible, $url_serveur, $bato->couleur_gv);
	$s.='</ResourceMap>
';
	return $s;
}



// --------------------
function GenereRessourceMapVoileAvant($dossier_cible, $url_serveur, $bato){
// Version par element
	$s='<ResourceMap>
';
	$unevoile = GetNomVoile($bato->voile);
	if (($unevoile=='spi') || ($unevoile=='genak')){
		$s.=GenereTextureVoileAvantBateau_3D($dossier_cible, $url_serveur, $bato->voile, $bato->couleur_spi);
	}
	else {
		$s.=GenereTextureVoileAvantBateau_3D($dossier_cible, $url_serveur, $bato->voile, $bato->couleur_vav);
	}
	$s.='</ResourceMap>
';
	return $s;
}

// --------------------
function GenereBateauKML_3D($dossier_cible, $url_serveur, $bato, $echelle=6, $altitude=1000){
// Modele 3D
// Pas de generation de parcours dans cette fonction
// le parcours est transformé en Tour apppelé dan GenereEnQueue()
global $dossier_modeles;
global $afficher_trajectoire;
global $t_parcours;

	// $echelle_z=$echelle*1.5;
	$echelle_z=$echelle;
    if ($bato->twa < 0) {
		$bord = 'babord';
	}
	else{
		$bord = 'tribord';
	}
	// recopier le fichier modele
	if ($bato->type=='catamaran'){
		$modele='catamaran';
	}
	else if ($bato->type=='trimaran'){
		$modele='trimaran';
	}
	else  if ($bato->type=='motorboat'){
    	$modele='motorboat';
	}
	else  if ($bato->type=='greatboat'){
    	$modele='greatboat';
	}	
	else{
    	$modele='monocoque';
    }

	$range = 5000.0 * $echelle;

	$angulation = $bato->getAngulation();

	$s='';
	if ($url_serveur!=''){ // liens absolus
		$url_modeles=$url_serveur.'/'.$dossier_cible.'/'.$dossier_modeles;
	}
	else{ // liens relatifs
		$url_modeles=$dossier_modeles;
	}
	
	if (!empty($bato)){
		if (!empty($bato->symbole) ){
		// On place juste une balise
		// Pas de parcours
$s.='<Placemark>
<open>1</open>
<description>
<![CDATA[<p>
';
if (!empty($bato->mmsi)){
    $s.=' MMSI: '.$bato->mmsi;
}
else if (!empty($bato->idsol)){
    $s.=' IdSol: '.$bato->idsol;
}
$s.='<br>'.$bato->date_enregistrement.'
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>COG: '.$bato->cog;
if (!empty($bato->sog)){
    $s.='<br>SOG: '.$bato->sog;
}
if (!empty($bato->twa)){
    $s.='<br>TWA: '.$bato->twa;
}
if (!empty($bato->twd)){
    $s.='<br>TWD: '.$bato->twd;
}
if (!empty($bato->tws)){
    $s.='<br>TWS: '.$bato->tws;
}
if (!empty($bato->classement)){
	$s.='<br>Rank : '.$bato->classement;
}
if (!empty($bato->dtg)){
	$s.='<br>DTG : '.$bato->dtg;
}
if (!empty($bato->dbl)){
	$s.='<br>DBL : '.$bato->dbl;
}
if (!empty($bato->log)){
	$s.='<br>LOG : '.$bato->log;
}
$s.='</p>]]>
</description>
	<LookAt>
    	<longitude>'.$bato->longitude.'</longitude>
		<latitude>'.$bato->latitude.'</latitude>
        <altitude>'.$altitude.'</altitude>
		<range>'.$range.'</range>
        <heading>'.$bato->cog.'</heading>
        <tilt>85</tilt>
		<altitudeMode>relativeToGround</altitudeMode>
    </LookAt>
<styleUrl>#Balise_sailboat</styleUrl>
	<Point>
		<gx:drawOrder>1</gx:drawOrder>
		<coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
	</Point>
</Placemark>
';
		}
		else{
		$gite=$bato->GiteVoilier();
		
		// $t_parcours[]=new Coordonnees($bato->longitude, $bato->latitude);
		
		if ($bato->nom!=''){

				$s.='<Placemark>
<name>'.$bato->nom.'</name>
<description>
<![CDATA[<p>
';
if (!empty($bato->mmsi)){
    $s.=' MMSI: '.$bato->mmsi;
}
if (!empty($bato->idsol)){
    $s.=' IdSol: '.$bato->idsol;
}
$s.='<br>'.$bato->date_enregistrement.'
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>COG: '.$bato->cog;
if (!empty($bato->sog)){
    $s.='<br>SOG: '.$bato->sog;
}
if (!empty($bato->twa)){
    $s.='<br>TWA: '.$bato->twa;
}
if (!empty($bato->twd)){
    $s.='<br>TWD: '.$bato->twd;
}
if (!empty($bato->tws)){
    $s.='<br>TWS: '.$bato->tws;
}
if (!empty($bato->classement)){
	$s.='<br>Rank : '.$bato->classement;
}
if (!empty($bato->dtg)){
	$s.='<br>DTG : '.$bato->dtg;
}
if (!empty($bato->dbl)){
	$s.='<br>DBL : '.$bato->dbl;
}
if (!empty($bato->log)){
	$s.='<br>LOG : '.$bato->log;
}
$s.='</p>]]>
</description>
	<LookAt>
    	<longitude>'.$bato->longitude.'</longitude>
		<latitude>'.$bato->latitude.'</latitude>
        <altitude>'.$altitude.'</altitude>
		<range>'.$range.'</range>
        <heading>'.$bato->cog.'</heading>
        <tilt>85</tilt>
		<altitudeMode>relativeToGround</altitudeMode>
    </LookAt>
';
				$s.='
	<styleUrl>#Balise_sailboat</styleUrl>
	<MultiGeometry>
    	<Point>
            <extrude>1</extrude>
            <altitudeMode>relativeToGround</altitudeMode>
            <coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
		</Point>
';
   			$s.='		<Model>
			<altitudeMode>relativeToGround</altitudeMode>
	    	<Location>
    			<longitude>'.$bato->longitude.'</longitude>
    			<latitude>'.$bato->latitude.'</latitude>
    			<altitude>'.$altitude.'</altitude>
	    	</Location>
    		<Orientation>
    			<heading>'.$bato->cog.'</heading>
				<tilt>'.$gite->x.'</tilt>
                <roll>'.$gite->y.'</roll>
    		</Orientation>
	    	<Scale>
    			<x>'.$echelle.'</x>
        		<y>'.$echelle.'</y>
        		<z>'.$echelle_z.'</z>
	    	</Scale>
';
				// A VERIFIER CAR ICI J'IMPROVISE
				$fichier_dae=GetFichierModeleVoile(GetNomVoile($bato->voile), $bord, $modele);
				if ($fichier_dae!=''){
					// recopier le fichier modele
// version individualisée
//if (recopier_modele_complet_dae_old($dossier_cible, $fichier_dae, $bato->nom, GetNomVoile($bato->voile), $bato->type, $bord, $modele)){
// $s.='			<Link id="'.$bato->nom.'">
//    			<href>'.$url_modeles.'/'.$bato->nom.'_'.GetNomVoile($bato->voile).'_'.$bord.$modele.'.dae</href>
//    		</Link>
// ';

				if (recopier_modele_complet_dae($dossier_cible, $fichier_dae, GetNomVoile($bato->voile), $bato->type, $bord, $modele)){
						$s.='			<Link id="'.$bato->nom.'">
    			<href>'.$url_modeles.'/'.GetNomVoile($bato->voile).'_'.$bord.'_'.$modele.'.dae</href>
    		</Link>
';
					}
				}
                $s.= GenereRessourceMapBateauComplet($dossier_cible, $url_serveur, $bato);
				$s.='
		</Model>
';


/*
				// recopier le fichier modele
				// recopier le fichier modele
				if ($bato->type=='catamaran'){
					$modele='_catamaran';
				}
				else
				if ($bato->type=='trimaran'){
					$modele='_trimaran';
				}
				else  if ($bato->type=='motorboat'){
                    $modele='_motorboat';
				}
				else{
                    $modele='';
                }

				if (recopier_modele_voilier_dae($dossier_cible, $bato->nom, $bato->type, $modele)){
						$s.='			<Link id="'.$bato->nom.'_coque'.$modele.'">
    			<href>'.$url_modeles.'/'.$bato->nom.'_coque'.$modele.'.dae</href>
    		</Link>
';
				}

				$s.= GenereRessourceMapCoque($dossier_cible, $url_serveur, $bato);
				$s.='
		</Model>

';
		// GV

                $heading = $bato->cog+$angulation;

 				$s.='		<Model>
			<altitudeMode>relativeToGround</altitudeMode>
	    	<Location>
    			<longitude>'.$bato->longitude.'</longitude>
    			<latitude>'.$bato->latitude.'</latitude>
    			<altitude>'.$altitude.'</altitude>
	    	</Location>
    		<Orientation>
    			<heading>'.$heading.'</heading>
				<tilt>'.$gite->x.'</tilt>
                <roll>'.$gite->y.'</roll>
    		</Orientation>
	    	<Scale>
    			<x>'.$echelle.'</x>
        		<y>'.$echelle.'</y>
        		<z>'.$echelle_z.'</z>
	    	</Scale>
';
				// A VERIFIER CAR ICI J'IMPROVISE
				// recopier le fichier modele
				if (recopier_modele_gv_dae($dossier_cible, $bato->nom, $bato->type, $modele, $bord)){
						$s.='			<Link id="'.$bato->nom.'_gv">
    			<href>'.$url_modeles.'/'.$bato->nom.'_gv'.$modele.'.dae</href>
    		</Link>
';
				}
				$s.= GenereRessourceMapGrandVoile($dossier_cible, $url_serveur, $bato);
				$s.='
		</Model>

';
				// Voile d'avant
                $heading = $bato->cog+$angulation;
 				$s.='		<Model>
			<altitudeMode>relativeToGround</altitudeMode>
	    	<Location>
    			<longitude>'.$bato->longitude.'</longitude>
    			<latitude>'.$bato->latitude.'</latitude>
    			<altitude>'.$altitude.'</altitude>
	    	</Location>
    		<Orientation>
    			<heading>'.$heading.'</heading>
				<tilt>'.$gite->x.'</tilt>
                <roll>'.$gite->y.'</roll>
    		</Orientation>
	    	<Scale>
    			<x>'.$echelle.'</x>
        		<y>'.$echelle.'</y>
        		<z>'.$echelle_z.'</z>
	    	</Scale>
';
				// A VERIFIER CAR ICI J'IMPROVISE
				// recopier le fichier modele
				$fichier_dae=GetFichierModeleVoile(GetNomVoile($bato->voile), $bord, $modele);
				if ($fichier_dae!=''){
					// recopier le fichier modele

					if (recopier_modele_voile_dae($dossier_cible, $fichier_dae, $bato->nom, $bato->type, $modele, GetNomVoile($bato->voile))){
						$s.='			<Link id="'.$bato->nom.'_'.GetNomVoile($bato->voile).$modele.'">
    			<href>'.$url_modeles.'/'.$bato->nom.'_'.GetNomVoile($bato->voile).$modele.'.dae</href>
    		</Link>
';

					}
				}
				$s.= GenereRessourceMapVoileAvant($dossier_cible, $url_serveur, $bato);
				$s.='
		</Model>
*/
		$s.='
	</MultiGeometry>
</Placemark>
';

			// Trajectoire
			if (isset($afficher_trajectoire) && ($afficher_trajectoire==1)){
				$s.=GenereTrajectoireBateauKML($bato, $altitude);
			}
		}
	}
	}
	return $s;
}


// --------------------
function GenereMarquesParcoursEtDebutPositionsBateauxKML_3D($echelle, $okmarques=true){
global $url_serveur;
global $t_wp;
global $t_voilier; // Liste de bateaux
//global $url_fichier_marque; // ='/sources/vor_styles_marques.kml';

	$s='';
    $s.= GenereStylesBalisesBateauxKML();

	if ($okmarques){
	/*
	// Les marques sont dans un ficher préparé à l'avance
		$s='
	<NetworkLink id="MarquesStyles">
		<name>Marques de parcours</name>
		<refreshVisibility>1</refreshVisibility>
		<flyToView>0</flyToView>
		<Link id="SolTracks">
			<href>';
	$s.=$url_serveur.$url_fichier_marque; // $url_fichier_marque='/sources/vor_styles_marques.kml';
	$s.='</href>
			<refreshMode>onChange</refreshMode>   
		</Link>
	</NetworkLink>
';
	 */
	 // les marques sont générées à la volée
 		$s.= MarquesParcoursToKml($t_wp);
	}
	// Bateaux
	$s.='	<Folder>
		<name>Positions</name>
';
	return $s;
}

// -------------------------
function GenereEnQueueKML_3D(){
	$s='</Folder>
	</Folder>
</Document>
</kml>
';
	return $s;
}

//------------------------
function genere_texture($chemin, $rouge, $vert, $bleu, $size=144){
// cree une image RVB
	if (file_exists($chemin.'/c_'.$rouge.'_'.$vert.'_'.$bleu.'.png')){
		return 'c_'.$rouge.'_'.$vert.'_'.$bleu.'.png';
	}
	else{
		// Générer les textures à la volee
		$image=imagecreatetruecolor($size, $size);
		$back = imagecolorallocate($image, $rouge, $vert, $bleu);
		imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $back);
		// Enregistrer l'image
		$fichier=$chemin.'/c_'.$rouge.'_'.$vert.'_'.$bleu.'.png';
		$ok=imagepng($image, $fichier);
		imagedestroy($image);
		if ($ok){
			return 'c_'.$rouge.'_'.$vert.'_'.$bleu.'.png';
		}
		else{
			return '';
		}
	}
}


//------------------------
function recopier_modele_complet_dae_nombateau($dossier_cible, $fichier_dae, $nom_bato, $voile, $dossier_type, $bord, $modele){
// copie du modele sous le nom du bateau
global $dir_serveur;
global $extension_dae;
global $dossier_modeles;
	// DEBUG
	//echo '<br />DEBUG :: recopier_modele_complet_dae :: 630 :: '.$dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae."\n";
	//exit;

	if (file_exists($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae)){
		$contenu=file_get_contents($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae);
		$f_name=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$nom_bato.'_'.$voile.'_'.$bord.'_'.$modele.$extension_dae;
		$fp = fopen($f_name, 'w');
		if ($fp){
			fwrite($fp, $contenu);
			fclose($fp);
            return true;
		}
	}
 	return false;
}

//------------------------
function recopier_modele_complet_dae($dossier_cible, $fichier_dae, $voile, $dossier_type, $bord, $modele){
// copie du modele generique
// gain appareciable de place
global $dir_serveur;
global $extension_dae;
global $dossier_modeles;
	// DEBUG
	//echo '<br />DEBUG :: recopier_modele_complet_dae :: 630 :: '.$dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae."\n";
	//exit;

	if (file_exists($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae)){
		$contenu=file_get_contents($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae);
		$f_name=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$voile.'_'.$bord.'_'.$modele.$extension_dae;
		$fp = fopen($f_name, 'w');
		if ($fp){
			fwrite($fp, $contenu);
			fclose($fp);
            return true;
		}
	}
 	return false;
}



//------------------------
function recopier_modele_coque_dae($dossier_cible, $nom_bato, $dossier_type, $modele){
// copie du modele sous le nom du bateau
global $dir_serveur;
global $extension_dae;
global $dossier_modeles;
// DEBUG
//echo '<br />DEBUG kml_3d.php :: 447 :: recopier_modele_coque_dae <br />'.$dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/gv_'.$bord.'.dae'."\n";

	if (file_exists($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/coque'.$modele.'.dae')){
		$contenu=file_get_contents($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/coque'.$modele.'.dae');
		$f_name=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$nom_bato.'_coque'.$modele.$extension_dae;
		$fp = fopen($f_name, 'w');
		if ($fp){
			fwrite($fp, $contenu);
			fclose($fp);
			return true;
		}
	}
	return false;
}

//------------------------
function recopier_modele_gv_dae($dossier_cible, $nom_bato, $dossier_type, $modele, $bord){
// copie du modele sous le nom du bateau
global $dir_serveur;
global $extension_dae;
global $dossier_modeles;
// DEBUG
//echo '<br />DEBUG kml_3d.php :: 466 :: recopier_modele_gv_dae <br />'.$dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/gv_'.$bord.'.dae'."\n";

	if (file_exists($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/gv_'.$bord.$modele.'.dae')){
		$contenu=file_get_contents($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/gv_'.$bord.'_'.$modele.'.dae');
		$f_name=$dir_serveur.'/'.$dossier_cible.$dossier_modeles.'/'.$nom_bato.'_gv'.$modele.$extension_dae;
		$fp = fopen($f_name, 'w');
		if ($fp){
			fwrite($fp, $contenu);
			fclose($fp);
			return true;
		}
        return false;
	}
}


//------------------------
//     (recopier_modele_voile_dae($dossier_3d, $fichier_dae, $bato->nom, $bato->type, $modele, GetNomVoile($bato->voile))
function recopier_modele_voile_dae($dossier_cible, $fichier_dae, $nom_bato, $dossier_type, $modele, $voile){
// copie du modele sous le nom du bateau
global $dir_serveur;
global $extension_dae;
global $dossier_modeles;
// DEBUG
//echo '<br />DEBUG kml_3d.php :: 489 :: recopier_modele_voile_dae <br />'.$dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae."\n";

	if (file_exists($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae)){
		$contenu=file_get_contents($dir_serveur.'/sources_3d/'.$dossier_modeles.'/'.$dossier_type.'/'.$fichier_dae);
		$f_name=$dir_serveur.'/'.$dossier_cible.'/'.$dossier_modeles.'/'.$nom_bato.'_'.$voile.'_'.$modele.$extension_dae;
		$fp = fopen($f_name, 'w');
		if ($fp){
			fwrite($fp, $contenu);
			fclose($fp);
			return true;
		}
	}
	return false;
}



// -----------------------
function EnregistreKML_3D($dossier_3d,  $contenu,  $archive=false, $al=NULL){
// Deux fichiers sont crees : un fichier d'archive et un fichier courant (dit de cache) au contenu midentique.
// c'est ce fichier de cache (dont le nom est toujours identique) qui est appelé par le fichier kml lu par GoogleEarth
// Le dossier d'achive est zippé

global $dir_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;
global $dossier_kml;
global $dossier_kmz;
global $dossier_modeles;
global $dossier_textures;
global $al;
$fichier_kml_cache_3d=$fichier_kml_cache.'3D';

	
	// Commencer par enregister le fichier KML
	$f_cache_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d.'/'.$fichier_kml_cache_3d.$extension_kml;
	$fp_data = fopen($f_cache_name, 'w');
	if ($fp_data ){
		fwrite($fp_data, $contenu);
		fclose($fp_data);
	}

	if ($archive){
		// faire une copie zippee du  dossier $dossier_3d 
		// if (creer_fichier_zip($dir_serveur, $dossier_3d, $fichier_kml_cache_3d)){
			// if (creer_fichier_zip('', $dossier_3d, $fichier_kml_cache_3d)){
		$t_fichiers=array();
		$t_fichiers[0]=$dossier_kml.'/'.$dossier_3d.'/'.$fichier_kml_cache_3d.$extension_kml;
		$t_fichiers[1]=$dossier_kml.'/'.$dossier_3d.'/'.$dossier_modeles;
		$t_fichiers[2]=$dossier_kml.'/'.$dossier_3d.'/'.$dossier_modeles.'/'.$dossier_textures;

		if (creer_fichier_zip($dossier_kml.'/'.$dossier_3d, $t_fichiers, $fichier_kml_cache_3d)){
			// puis le renommer .kmz
			$nom_fichier_kmz=renommer_fichier($fichier_kml_cache_3d, $extension_kmz);
			// DEBUG
			// echo "<br>DEBUG :: kml_3d.php :: 390 :: $nom_fichier_kmz\n";
			if ($nom_fichier_kmz!=''){
				// creer un fichier d'archive
				// le nom du fichier d'archive recoit une date+heure qui sera utilisee 
				// pour verifier si le delai depuis la génération précédente est suffisant
				$f_name_cache=$dir_serveur.'/'.$nom_fichier_kmz;
				$f_archive=$dir_serveur.'/'.$dossier_kml.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz;
				copy($f_name_cache, $f_archive);
                rename($f_name_cache, $dir_serveur.'/'.$dossier_kmz.'/'.$nom_fichier_kmz);
                rename($f_archive, $dir_serveur.'/'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz);
				// Suppression
				// DEBUG
				//echo "<br />DEBUG : RRMDIR :".$dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d."\n";
				rrmdir($dir_serveur.'/'.$dossier_kml.'/'.$dossier_3d);

				if ($al){
					echo '<br />'.$al->get_string('file_updated').' <a href="'.$dossier_kmz.'/'.$fichier_kml_cache_3d.$extension_kmz.'"><b>'.$fichier_kml_cache_3d.$extension_kmz.'</b></a>'."\n";
        			echo '<br />'.$al->get_string('file_zip').' <a href="'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'"><b>'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'</b></a>'."\n";
				}
				else{
					echo '<br />Updated File: <a href="'.$dossier_kmz.'/'.$fichier_kml_cache_3d.$extension_kmz.'"><b>'.$fichier_kml_cache_grib.$extension_kmz.'</b></a>'."\n";
					echo '<br />Zipped File: <a href="'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'"><b>'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'</b></a>'."\n";
				}
			}
		}
	}
	else{
		if ($al){
			echo $al->get_string('file_updated').' <a href="'.$dossier_kml.'/'.$dossier_3d.'/'.$fichier_kml_cache_3d.$extension_kml.'"><b>'.$fichier_kml_cache_3d.$extension_kml.'</b></a>'."\n";
		}
		else{
			echo 'File updated: '.' <a href="'.$dossier_kml.'/'.$dossier_3d.'/'.$fichier_kml_cache_3d.$extension_kml.'"><b>'.$fichier_kml_cache_3d.$extension_kml.'</b></a>'."\n";
		}
	}
}

// -----------------------
function ExisteKML_3D(){
// verifie si une generation a ete faite durant l'heure courante
global $dir_serveur;
global $dossier_kml;
global $dossier_kmz;
global $dossier_3d;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;

	$fichier_kml_cache_3d=$fichier_kml_cache.'3D';

	$f_data_name=$dir_serveur.'/'.$dossier_kmz.'/'.$fichier_kml_cache_3d.date('YmdH').$extension_kmz;
	// DEBUG
	// echo "<br>Fichier courant: $f_data_name\n";
	
	if (file_exists($f_data_name)){
		return $f_data_name;
	}
	else{
		return '';
	}
}

// --------------------
function GenereStylesBalisesBateauxKML(){
global $url_serveur;
	$s='
<Style id="Balise_sailboat">
	<IconStyle>
		<scale>0.4</scale>
		<Icon>
			<href>http://maps.google.com/mapfiles/kml/shapes/sailing.png</href>
		</Icon>
	</IconStyle>
	<LabelStyle>
    	<scale>0.65</scale>
	</LabelStyle>
</Style>

';

	return $s;
}


// --------------------
function GenereEnteteKML_3D($longitude, $latitude, $cog ){
global $url_serveur;
global $dossier_kml;
global $dossier_kmz;
global $racename;
global $racenumber;
global $image_nom_course;

	if ($url_serveur!=''){ // liens absolus
		$url=$url_serveur.'/'.$image_nom_course;
	}
	else{ // liens relatifs
		$url='./'.$image_nom_course;
	}
// corrige un bug dans KML
$racename=str_replace('&',' ',$racename);
$cog_oppose=(($cog+180) % 360);

// <href>http://maps.google.com/mapfiles/kml/shapes/arrow.png</href>
	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
	<Style id="pmIcon">
    	<IconStyle id="">
        	<color>CC00FFFF</color>
        	<scale>0.5,0.5</scale>
        	<Icon>
				<href>http://maps.google.com/mapfiles/kml/pal4/icon21.png</href>
			</Icon>
		    <hotSpot x="0.5"  y="0.5" xunits="fraction" yunits="fraction"/>    <!-- kml:vec2 -->
      	</IconStyle>
		<LineStyle>
			<width>1</width>
		</LineStyle>
        <LabelStyle>
    		<scale>0.5</scale>
		</LabelStyle>
    </Style>
<Folder>
	<name>Sol_3D</name>
	<open>1</open>
	<LookAt>
		<longitude>'.$longitude.'</longitude>
		<latitude>'.$latitude.'</latitude>
        <altitude>5000000</altitude>
		<range>100000</range>
        <heading>0</heading>
        <tilt>'.$cog_oppose.'</tilt>
		<range>100000</range>
        <heading>270</heading>
        <tilt>20</tilt>
	</LookAt>
    <ScreenOverlay>
		<name>'.$racename.' ('.$racenumber.')</name>
		<Icon>
			<href>'.$url.'</href>
		</Icon>
		<overlayXY x="0" y="1" xunits="fraction" yunits="fraction"/>
		<screenXY x="0.005" y="0.98" xunits="fraction" yunits="fraction"/>
		<rotationXY x="0" y="0" xunits="fraction" yunits="fraction"/>
		<size x="0" y="0" xunits="fraction" yunits="fraction"/>
	</ScreenOverlay>

';
return $s;
}


// --------------------
function GenereKML_3D($dossier_3d){
// génère le fichier KML courant qui se connecte au serveur depuis Google Earth

global $dir_serveur;
global $dossier_kml;
global $url_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;

	$fichier_kml_cache_3d=$fichier_kml_cache.'3D';

	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
  <Folder>
	<name>Click to the map</name>
    <NetworkLink>
      <refreshVisibility>0</refreshVisibility>
      <flyToView>1</flyToView>
      <Link>
        <href>'.$url_serveur.'/'.$dossier_kml.'/'.$dossier_3d.'/'.$fichier_kml_cache_3d.$extension_kml.'</href>
        <refreshInterval>1800</refreshInterval>
        <viewRefreshMode>onRequest</viewRefreshMode>
      </Link>
    </NetworkLink>
  </Folder>
</kml>
';
	// enregistrer ce ficher
	$fp_data = fopen($dossier_kml.'/'.$fichier_kml_courant.$extension_kml, 'w');
	if ($fp_data ){
		fwrite($fp_data, $s);
		fclose($fp_data);
	}
}

?>