<?php


// gestion des donnees voiliers dans un cache

require_once('include/Voilier.php'); // Définition de la classe Voilier

// ------------------
function recupere_cache_data_voilier($pseudo_user){
// Lire la page dans le cache et supprime ancienne version
global $course;
global $t_course;
global $datacache;
global $ext_data;
global $MAXTAILLECACHE;
global $date_cache;
global $dir_serveur;
	if ($pseudo_user!=""){
		$f_data_name=$dir_serveur.'/'.$datacache."/".$pseudo_user."_".$t_course[$course].$ext_data;
		if (file_exists($f_data_name)){
		 	$contenu = implode("", @file($f_data_name));
			if ($contenu!=""){
				// DEBUG 
				// echo "<br>DEBUG :: cache_voiliers.php :: Ligne 23 :: <br>\n";
				// echo $contenu."<br>\n";
				$ce_voilier=unserialize($contenu);
				// $ce_voilier->Dump();
				// echo $ce_voilier->Affiche();
				// 2009-04-19 10:59:05
				$date_enregistrement=$ce_voilier->GetDateEnregistrement();
				// echo "<br>Date enregistrement : $date_enregistrement ; Date cache :$date_cache\n";
				if ($date_enregistrement >= $date_cache){
					echo "<br>$pseudo_user : cache récent retourné.\n";
					return $ce_voilier;
				}
				else{
					// echo "<br>$pseudo_user : cache ancien supprimé\n";
					// supprimer_cache_voilier($pseudo_user);
					// @unlink($f_data_name);
				}
			}
		}
	}
	return false;
}

// ------------------
function recupere_archive_data_voilier($pseudo_user){
// Lire la page dans le cache 
global $course;
global $t_course;
global $datacache;
global $ext_data;
global $MAXTAILLECACHE;
global $date_cache;
global $dir_serveur;

	if ($pseudo_user!=""){
		$stamp='_old';
		$f_data_name=$dir_serveur.'/'.$datacache."/".$un_voilier->pseudo."_".$t_course[$course].$stamp.$ext_data;
		if (file_exists($f_data_name)){
		 	$contenu = implode("", @file($f_data_name));
			if ($contenu!=""){
				// DEBUG 
				echo "<br>DEBUG :: cache_voiliers.php :: Ligne 65 :: <br>\n";
				// echo $contenu."<br>\n";
				$ce_voilier=unserialize($contenu);
				// $ce_voilier->Dump();
				echo $ce_voilier->Affiche();
				// 2009-04-19 10:59:05
				return $ce_voilier;
			}
		}
	}
	return false;
}


// ------------------
function sauve_cache_data_voilier($un_voilier){
// Enregistrer le volier serialise 
global $course;
global $t_course;
global $datacache;
global $ext_data;
global $dir_serveur;
global $date_cache;
	if ($un_voilier){
		$f_data_name=$dir_serveur.'/'.$datacache."/".$un_voilier->pseudo."_".$t_course[$course].$ext_data;
		
		// Archive a remplacer ?
		if (file_exists($f_data_name)){
		 	$contenu_old = implode("", @file($f_data_name));
			if ($contenu_old!=""){
				// DEBUG 
				// echo "<br>DEBUG :: cache_voiliers.php :: Ligne 23 :: <br>\n";
				// echo $contenu."<br>\n";
				$old_voilier=unserialize($contenu_old);
				// $old_voilier->Dump();
				// echo $old_voilier->Affiche();
				// 2009-04-19 10:59:05
				$date_enregistrement_old=$old_voilier->GetDateEnregistrement();
				// echo "<br>Date enregistrement : $date_enregistrement ; Date cache :$date_cache\n";
				if ($date_enregistrement_old >= $date_cache){
					// archiver 
					$stamp='_old';
					$f_data_name_old=$dir_serveur.'/'.$datacache."/".$un_voilier->pseudo."_".$t_course[$course].$stamp.$ext_data;
					// supprimer
					unlink($f_data_name_old);
					// archiver
					rename($f_data_name, $f_data_name_old);
				}
			}
		}
		// sauvegarde
		$contenu=serialize($un_voilier);
		$fp_data = fopen($f_data_name, 'w');
		if ($fp_data){
			fwrite($fp_data, $contenu);
			fclose($fp_data);
			return true;
		}
	}
	return false;
}

// ------------------
function supprimer_cache_voilier($pseudo_user){
// Supprime le fichier de cache serialise du voilier de nom $pseudo_user
global $course;
global $t_course;
global $datacache;
global $ext_data;
global $dir_serveur;
	if ($pseudo_user!=""){
		$f_data_name=$dir_serveur.'/'.$datacache."/".$pseudo_user."_".$t_course[$course].$ext_data;
		if (file_exists($f_data_name)){
			@unlink($f_data_name);
		}
	}
}



?>
