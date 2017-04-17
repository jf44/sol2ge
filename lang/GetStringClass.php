<?php
// JF 2017
// fichier de langue

class GetString{

	var $lang;
	var $module;

	// initialise les chaines
	//----------------------
	public function setLang($path, $lang='en', $module='solstrings'){
        $this->lang = $lang;
		$this->module = $module;

        $filetoload='';
		if (!empty($this->lang) && !empty($this->module)) {
			$filetoload = $path.'/'.$this->module.'_'.$this->lang.'_utf8.php';
			if (file_exists($filetoload)){
  				return $filetoload;
			}
		}
		return false;
	}

	//----------------------
	public function getAllLang($path, $module='solstrings'){
	$extension='php';
	$tinput = array();
    $toutput = array();
	$sep = '/';

	$h1=opendir($path);
    $nobj = 0;
	$s='';

    while ($f = readdir($h1)) {
		if (($f != ".") && ($f != "..")) {
	        if (!is_dir($path.$sep.$f)){
    	       	$g= eregi_replace($extension,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(substr($g,0,strlen($module)) == $module) // Les fichiers ne commencant par $module ne sont pas affichés
					&&
					(strtoupper($g.$extension) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
	               	$tinput[$f] = $f ;
				}
			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

	if ($nobj != 0) {
		asort($tinput);
		while (list($key) = each($tinput)) {
			$alang=substr(substr($key, strlen($module)+1), 0, 2);
            // DEBUG
			// echo '<br />'.$key.' --&gt; '.$alang."\n";
			$toutput[]=$alang;
		}
	}
	return $toutput;
	}

/**
 * Localisation function
 * return the string[$key]  for the current $module
 * @param string $key
 * @param string $a : an optional string included under the form {$a}
 * @return string|false ?
 */
	//----------------------
	public function get_string($key, $a=''){
		global $t_string;
        /// if $a happens to have % in it, double it so sprintf() doesn't break
    	if ($a) {
			$s = str_replace('{$a}', $a, $t_string[$key]);
		}
		else{
            $s = $t_string[$key];
		}
		return $s;
	}

    //----------------------
	public function print_string($key, $a=''){
		echo get_string($key, $a);
	}

} // Class


?>