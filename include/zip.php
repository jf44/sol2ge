<?php

require_once('include/pclzip/pclzip.lib.php');


// ----------------
function ajouter_fichier_zip($dest_path, $fichier_in, $zip_name ){
// ajoute le fichier $fichier_in à l'archive $zip_name en retirant le chemin $dest_path
        // DEBUG
        //echo "<br />DEBUG :: include/zip.php :: 10 ::<br />DEST_PATH: '$dest_path', FICHIER_IN: '$fichier_in', ZIP_NAME: '$zip_name' \n";
        $archive = new PclZip($zip_name);
		if ( $archive->add($dest_path.'/'.$fichier_in,
                          PCLZIP_OPT_REMOVE_PATH, $dest_path) == 0) {
        	die("Error : ".$archive->errorInfo(true));
        }
        return true;
}

// ----------------
function creer_fichier_zip($dest_path, $t_fichier_in, $zip_name ){
// cree une archive $zip_name avec le contenu du $t_fichier_in (qui peut être un dossier)
// en supprimant le chemin $dest_path dans le nom du fichier créé
// Le fichier zip est céé dans le dossier appelant.
// Il faudra ensuite le déplacer dans le dossier cible
        // DEBUG
        //echo "<br />DEBUG :: include/zip.php :: 24 ::<br />DEST_PATH: '$dest_path'<br />FICHIER_IN: <br />\n";
		//print_r($t_fichier_in);
		//echo "<br />, ZIP_NAME: '$zip_name' \n";
        $archive = new PclZip($zip_name);
		if ($dest_path!=''){
			if ($archive->create($t_fichier_in,
                          PCLZIP_OPT_REMOVE_PATH, $dest_path) == 0) {
    			die('Error : '.$archive->errorInfo(true));
  			}
        }
		else{
			if ($archive->create($t_fichier_in) == 0) {
    			die('Error : '.$archive->errorInfo(true));
  			}
		}
		return true;
}


// ----------------
function unzip_fichier($dest_path, $zip_name, $ext_recherchee='ZIP'){
	if (is_file($zip_name)){
		$ext=strtoupper(substr( strrchr($zip_name, "." ), 1));
        if ($ext==$ext_recherchee){
            $archive = new PclZip($zip_name);
            /*
            if (($list = $archive->listContent()) == 0) {
                die("Error : ".$archive->errorInfo(true));
            }
            for ($i=0; $i<sizeof($list); $i++) {
                for(reset($list[$i]); $key = key($list[$i]); next($list[$i])) {
                    echo "File $i / [$key] = ".$list[$i][$key]."<br>";
                }
            echo "<br>";
            }
            */
            // In this sample all the files of the archive are extracted in the $dest_path directory.
            if ($archive->extract(PCLZIP_OPT_PATH, $dest_path) == 0) {
            	die("Error : ".$archive->errorInfo(true));
            }
            return true;
        }
    }
    return false;
}


// ----------------
function renommer_fichier($zip_name, $ext_new){
	if (is_file($zip_name)){
		$path=dirname($zip_name);
		$nf=basename($zip_name);
		$nom_strict=nom_fichier($nf);
		// DEBUG
		// echo "<br>DEBUG :: zip.php :: 69 :: Path : $path ;  Nomfichier : $nf ; Nom strict : $nom_strict\n";
		
		if ($nom_strict!=""){
			if (file_exists($path."/".$nom_strict.$ext_new)){
				unlink($path."/".$nom_strict.$ext_new);
			}
			if (rename($zip_name, $path."/".$nom_strict.$ext_new)){
				return $nom_strict.$ext_new;
			}
		}
	}
	return '';
}

// ---------------------------------
function extension_fichier($nomf){
// retourne l'extension du fichier
	return (substr( strrchr($nomf, "." ), 1));
}

// ---------------------------------
function nom_fichier($nf){
// Retourne le nom du fichier sans chemin ni extension
	$nomf = substr( strrchr($nf, "/" ), 1);
	if ($nomf==""){
		$nomf=$nf;
	}
	$ext = substr(strrchr($nomf, "."),1);
	if ($ext){
		$pos = strlen($nomf) - strlen($ext) - 1;
		$nom = substr($nomf,0,$pos);
	}
	else
		$nom=$nomf;
	
	return $nom;
}


// ---------------------------------
function nom_fichier_reduit($nf){
// Retourne le nom du fichier sans chemin
	return substr( strrchr($nf, "/" ), 1);
}

// ---------------------------------
function nom_dossier($nf){
// Retourne le nom du fichier sans chemin
	$nomf = substr( strrchr($nf, "/" ), 1);
	if ($nomf==""){
		$nomf=$nf;
	}	
	return $nomf;
}


// ----------------
function cree_dossier($dossier){
	if (!is_dir($dossier)){
		umask(0000);
		$ok=mkdir($dossier, 0777);
		// chmod( $dossier, 0755 );
		return $ok;
	}
	else return true;
}

// ----------------
function supprime_dossier($dossier){
	if (is_dir($dossier)){
		return @rmdir($dossier);
	}
	else return true;
}


// ----------------
function supprime_fichier($fichier){
	if (is_file($fichier)){
		return(unlink($fichier));
	}
	else return true;
}



// -------------------
function repertoire_pere($dest_path, $dest_path_ressource){
// Dossiers
	if (strcmp($dest_path, $dest_path_ressource)!=0){ // blindage
		// decouper $dest_path
		$dp=explode("/",$dest_path);
		$dp2=$dp[0];
		for ($i=1; $i<count($dp)-1; $i++){
			$dp2.="/".$dp[$i];
		}
		// echo "<br> $dp2";
		return $dp2;
	}
	return "";
}


// -------------------
function get_contenu_dossier($dest_path, $dest_path_ressource, $masque){
	global $appli;
	global $afficher_dossier;
	global $selection_fichier;
	global $nb_fichier;
	
	$afficher_dossier="";
	reset($selection_fichier);
	$nb_fichier=0;
	$myDir = opendir($dest_path);
	$se="";
	while ($entryName = readdir($myDir)){
		// echo "<br>'$entryName'";
		$entryName = addslashes($entryName);
		$se .= "$entryName,";
	}
	closedir($myDir);
# DEBUG			
# echo "<BR>$se<BR>\n";
	
	if (strlen($se) > 0){
		// echo "<br> $se";
		$te = explode(",",$se);
		while (list($k, $v)=each($te)) {
			// echo "<br> $v";
			// Dossiers
			if ( 
			((strcmp($dest_path, $dest_path_ressource)!=0) || (strcmp($v, "..")!=0)) 
			&& (strcmp($v, ".")!=0) && (is_dir($dest_path.'/'.$v)) && ($v!="")){
				if ($v=="..") {
					// decouper $dest_path
					$dp=explode("/",$dest_path);
					$dp2=$dp[0];
					for ($i=1; $i<count($dp)-1; $i++){
						$dp2.="/".$dp[$i];
					}
					// echo "<br> $dp2";
					$afficher_dossier .= "\n<OPTION VALUE='".$dp2."'>..";
				}
				else {
					$nom_complet=$dest_path.'/'.$v;
					if ($masque){
						if (substr($v,0,1) != "_"){
							$afficher_dossier .= "\n<OPTION VALUE='".$dest_path."/".$v."'>".$v;
							$s='<tr valign="top" bgcolor="#ffffe0"><td bgcolor="#eeeeee"><input type="checkbox" name="dossier[]" value="'.$nom_complet.'"></td>';
							$s.='<td><a href="'.$appli.'?dest_path='.$nom_complet.'&amp;dest_path_ressource='.$dest_path_ressource.'"><img src="./images/folder.gif" border="0"> <b>'.$v.'</b></a></td><td>&nbsp;</td><td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
							$selection_fichier[] = $s;
							$nb_fichier++;
						}
					}
					else {
						$afficher_dossier .= "\n<OPTION VALUE='".$dest_path."/".$v."'>".$v;					
						if (substr($v,0,1) == "_"){
							$s='<tr valign="top" bgcolor="#ffffe0"><td bgcolor="#eeeeee"><input type="checkbox" name="dossier[]" value="'.$nom_complet.'"></td>';
							$s.='<td><a href="'.$appli.'?dest_path='.$nom_complet.'&amp;dest_path_ressource='.$dest_path_ressource.'"><img src="./images/folder.gif" border="0"> <b><i>'.$v.'</i></b></a></td><td>&nbsp;</td><td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
							$selection_fichier[] = $s;
							$nb_fichier++;
						}
						else {
							$s='<tr valign="top" bgcolor="#ffffe0"><td bgcolor="#eeeeee"><input type="checkbox" name="dossier[]" value="'.$nom_complet.'"></td>';
							$s.='<td><a href="'.$appli.'?dest_path='.$nom_complet.'&amp;dest_path_ressource='.$dest_path_ressource.'"><img src="./images/folder.gif" border="0"> <b>'.$v.'</b></a></td><td>&nbsp;</td><td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
							$selection_fichier[] = $s;
							$nb_fichier++;
						}
					}
				}
			}
			// Fichiers
			// Ne pas afficher les fichiers qui commencent par "_"
			// if  (is_file($dest_path.'/'.$v) && ($v!="") && (substr($v,0,1) != "_") && (substr($v,-3,3) != "php")){
			if  (is_file($dest_path.'/'.$v) && ($v!="") && (substr($v,-3,3) != "php")){
				$nom_complet=$dest_path.'/'.$v;
				$taille=filesize($nom_complet);
				$type=img_ext($v);
				
				if ($masque) {
					if (substr($v,0,1) != "_"){	
						$s= '<tr valign="top" bgcolor="#ffffff">
<td bgcolor="#eeeeee"><input type="checkbox" name="fichier[]" value="'.$nom_complet.'"></td>';
						$s.= '<td><a target="display" title="Popup window" href="'.$nom_complet.'"';
						$s.= " onclick=\"return openpopup('".$nom_complet."', 'display', 'menubar=0,location=0,scrollbars,resizable,width=640,height=480', 0);\">";
						$s.= '<img src="'.$type.'" border="0"> '.$v.'</a></td><td align="right">&nbsp; '.(int)($taille / 1024).','.(int)($taille % 1024).' KO</td>
<td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
						$selection_fichier[]=$s;
						$nb_fichier++;
					}
				}
				else {
					if (substr($v,0,1) == "_"){
						$s= '<tr valign="top" bgcolor="#ffffff">
<td bgcolor="#eeeeee"><input type="checkbox" name="fichier[]" value="'.$nom_complet.'"></td>';
						$s.= '<td><a target="display" title="Popup window" href="'.$nom_complet.'"';
						$s.= " onclick=\"return openpopup('".$nom_complet."', 'display', 'menubar=1,location=1,scrollbars,resizable,width=640,height=480', 0);\">";
						$s.= '<img src="'.$type.'" border="0"> <i>'.$v.'</i></a></td><td align="right">&nbsp; '.(int)($taille / 1024).','.(int)($taille % 1024).' KO</td>
<td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
						$selection_fichier[]=$s;
						$nb_fichier++;
					}
					else{
						$s= '<tr valign="top" bgcolor="#ffffff">
<td bgcolor="#eeeeee"><input type="checkbox" name="fichier[]" value="'.$nom_complet.'"></td>';
						$s.= '<td><a target="display" title="Popup window" href="'.$nom_complet.'"';
						$s.= " onclick=\"return openpopup('".$nom_complet."', 'display', 'menubar=0,location=0,scrollbars,resizable,width=640,height=480', 0);\">";
						$s.= '<img src="'.$type.'" border="0"> '.$v.'</a></td><td align="right">&nbsp; '.(int)($taille / 1024).','.(int)($taille % 1024).' KO</td>
<td>&nbsp; <i>'.date("d/m/Y H:i:s",filemtime($nom_complet)).'</i></td></tr>';
						$selection_fichier[]=$s;
						$nb_fichier++;
					}
				}
			}
		}
	}
}

?>