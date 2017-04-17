<?php
/**
 * SailOnLine connection library
 * Utilitaires de connexion pour SailOnLine
 * JF march 017
 */


define ('DEBUGCONNECT', 0);      // home made debug maison !:))



//------------------------
 function get_login_token($racenumber, $login, $passwd){
 // Retourne le token pour la race active
 global $al; // localisation strings
 global $solhost;
 global $webclient;
 global $serviceauth;

	$token = '';
	if (!empty($racenumber)){
		$url=$solhost.$webclient.$serviceauth.'?username='.$login."&password=".$passwd."&race_id=".$racenumber;
		$fp = fopen($url, "rb");
		if (FALSE !== $fp) {
			if (DEBUGCONNECT){
				echo '<br /> '.$al->get_string('openaccess').$url."\n";
			}
			try {
    			if (($contents = file_get_contents($url)) === false) {
	        		// Handle the error
					echo '<br /><br />'.$al->get_string('filecontenerror')."\n";
					return false;
    			}
			} catch (Exception $e) {
    			// Handle exception
				print_r($e);
				return false;
			}

			if (!empty($contents)){

				if (DEBUGCONNECT){
					echo '<br /><pre>'."\n";
					echo htmlentities($contents);
	    	    	echo '</pre>'."\n";
				}

				// On va utiliser SimpleXML
				$token_xml = new SimpleXMLElement($contents);
				if ($token_xml){
					if (DEBUGCONNECT){
						echo '<pre>'."\n";
    	 				print_r($token_xml);
						echo '</pre>'."\n";
					}
					$token=$token_xml->token;
				}

			}
    	    fclose($fp);
			if (!empty($token)){
                $user=new stdClass();
    			$user->boatname=$login;
                $user->token = $token;
                return $user;
			}
		}
	}
	return false;
}


//------------------------
 function get_sol_token($racenumber){
 // Retourne un token generique à partir du compet generique "sol", "sol"
 global $al; // localisation strings
 global $solhost;
 global $webclient;
 global $serviceauth;

	$token='';
	if (!empty($racenumber)){
		$url=$solhost.$webclient.$serviceauth.'?username=sol&password=sol&race_id='.$racenumber;
		$fp = fopen($url, "rb");
		if (FALSE === $fp) {
    		echo ("Failed to open stream to URL: $url\n");
		}
		else if (DEBUGCONNECT){
			echo '<br /> '.$al->get_string('openaccess').$url."\n";
		}
		try {
    		if (($contents = file_get_contents($url)) === false) {
	       		// Handle the error
				echo '<br /><br />'.$al->get_string('filecontenerror')."\n";
				return false;
    		}
		} catch (Exception $e) {
    		// Handle exception
			print_r($e);
			return false;
		}

		if (!empty($contents)){
      		if (DEBUGCONNECT){
				echo '<br /><pre>'."\n";
				echo htmlentities($contents);
	   	    	echo '</pre>'."\n";
			}

			// SimpleXML object
			$token_xml = new SimpleXMLElement($contents);
			if ($token_xml){
    			if (DEBUGCONNECT){
					echo '<pre>'."\n";
     				print_r($token_xml);
					echo '</pre>'."\n";
				}
				$token=$token_xml->token;
			}
		}
        fclose($fp);
	}
	return $token;
}


//------------------------
 function get_all_active_races(){
 // Retourne la liste des races actives
	global $al;
 	global $solhost;
 	global $webclient;
    global $serviceactiveraces;

	$t_races=array();
	$url=$solhost.$webclient.$serviceactiveraces.'?filter=active';
	$fp = fopen($url, "rb");
	if (FALSE === $fp) {
    	echo ("Failed to open stream to URL $url\n");
	}
	else{
  		if (DEBUGCONNECT){
			echo '<br />'.$al->get_string('openaccess').' '.$url."\n";
		}
		try {
    		if (($contents = file_get_contents($url)) === false) {
        		// Handle the error
				echo '<br /><br />'.$al->get_string('filecontenterror')."\n";
    		}
		} catch (Exception $e) {
    		// Handle exception
			print_r($e);
			return false;
		}

		if (!empty($contents)){
            /*
			if (DEBUGCONNECT){
				echo '<br /><pre>'."\n";
				echo htmlentities($contents);
	        	echo '</pre>'."\n";
			}
			*/
			// On va utiliser SimpleXML
			$active_races_xml = new SimpleXMLElement($contents);
			if ($active_races_xml){
    			if (DEBUGCONNECT){
					echo '<pre>'."\n";
     				print_r($active_races_xml);
					echo '</pre>'."\n";
				}

				$n=0;

				foreach ($active_races_xml->race as $race_xml){
					$race = new stdClass();
                    $race->id=$race_xml->id;
                    $race->name=$race_xml->name;
                    $race->url=$race_xml->url;
                    $race->start_time=$race_xml->start_time;

     				if (DEBUGCONNECT){
                        $s=$race_xml->id;           	// >1025</id>
						$s.=', '.$race_xml->name;	//>Gray Whale Spring Migration 2017</name>
						$s.=', '.$race_xml->description;	//>This race in 90ft monohulls replicates the 4000nm epic annual S-N journey of the gray whale - from breeding grounds off Mexico to the food-rich northern waters off Alaska, this is one of nature&#39;s greatest feats of navigation.&lt;br&gt; Race #1025 &lt;br&gt;&lt;b&gt;&lt;a href=&quot;http://sol.brainaid.de/notice/notice_1025.html&quot;&gt;INFO&lt;/a&gt;&lt;/b&gt; from brainaid.de&lt;br&gt;90ft Monohull &lt;b&gt;&lt;a href=&quot;http://sailonline.org/static/var/sphene/sphwiki/attachment/2016/08/01/90ft_Monohull_Particulars.pdf&quot;&gt;Particulars&lt;/a&gt;&lt;/b&gt;&lt;br&gt;  WX Updates: &lt;br&gt;0430 / 1030 / 1630 / 2230  &lt;br&gt;Ranking: SYC &lt;br&gt;ALT. CLIENT:&lt;b&gt;&lt;a href=&quot;http://www.sailonline.org/windy/run/1025/?version=classic&quot;&gt;Classic&lt;/a&gt;&lt;/b&gt;&lt;/b&gt;&lt;br&gt;PRIZE:&lt;a href=&quot;http://www.sailonline.org/board/thread/14780/2017-smpf-scheme/?page=1#post-14781&quot; &gt; SMPF&lt;/a&gt;</description>
						$s.=', '.$race_xml->message;	//>Practice sailing. Race starts 05/03-18.00UTC. Currently 45 boats registered.</message>
						$s.=', '.$race_xml->race_type;	//></race_type>
						$s.=', '.$race_xml->start_time;	//>March 5, 2017, 6 p.m.</start_time>
						$s.=', '.$race_xml->archived;	//>False</archived>
						$s.=', '.$race_xml->minlon;	//>-179.0</minlon>
						$s.=', '.$race_xml->maxlon;	//>-69.0</maxlon>
						$s.=', '.$race_xml->minlat;	//>0.0</minlat>
						$s.=', '.$race_xml->maxlat;	//>70.0</maxlat>
						$s.=', '.$race_xml->mapurl;	//>http://sailonline.org/site_media/maps/xmlmaps/i.xml</mapurl>
						$s.=', '.$race_xml->url;  //>/webclient/race_1025.xml</url>
                        echo '<br />'.$al->get_string('race'). $s."\n";
					}
			    	$t_races[] = $race;

					flush();
					$n++;
				}
			}
		}
        fclose($fp);
	}
	return $t_races;
}

/**
 * Display a select form
 *
 * @input optional $params
 *  For exemple
 * $params['key']  = $value;
 *
 * @output : nothing
 */

//------------------------
 function select_a_race($params=null){

 global $appli;
 global $racenumber;
 global $racename;
 global $lang;
 global $al;

    if ($t_races=get_all_active_races()){
  		if (DEBUGCONNECT){
			echo '<pre>'."\n";
     		print_r($t_races);
			echo '</pre>'."\n";
		}

		echo '
<form action="'.$appli.'" method="post" name="selectrace" id="selectrace">
<b>'.$al->get_string('selectrace').'</b>
<br />
<select name="racenamenumber" id="racenamenumber" size="3">
';
			foreach  ($t_races as $race){
				if ($racenumber==$race->id){
					echo '<option value="'.$race->id.'#§#'.$race->name.'" SELECTED>'.substr($race->name,0,45).' (<i>'.$race->id.'</i>)</option>';
				}
                else{
					echo '<option value="'.$race->id.'#§#'.$race->name.'">'.substr($race->name,0,45).' ('.$race->id.')</option>';
				}
			}
		echo '
</select>
<br />
<input type="reset" />
<input type="submit" name="action" id="action" value="'.$al->get_string('race').'"/>
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
<input type="hidden" name="racename" id="racename" value="'.$racename.'"/>
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'"/>
';
		if (!empty($params)){
			foreach($params as $key=>$value){
    			echo '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$value.'"/>'."\n";
			}
		}
echo '</form>
';
	}
 }

/**
 * @input : a server url
 * @output: data flow (text)
 *
 *
 */
function my_get_content($solserverurl){
// retourne un objet
	global $al;
	if (!empty($solserverurl)){
		$fp = fopen($solserverurl, "rb");
		if (FALSE === $fp) {
    		echo ("Failed to open stream to URL");
			return false;
		}
		else{
          	if (DEBUGCONNECT){
				echo '<br />'.$al->get_string('openaccess').' '.$solserverurl."\n";
			}

			try {
    			if (($contents = file_get_contents($solserverurl)) === false) {
        			// Handle the error
					echo '<br /><br /'.$al->get_string('filecontenterror')."\n";
    			}
			} catch (Exception $e) {
    			// Handle exception
				print_r($e);
				die("file_get_contents non supported...");
			}
            fclose($fp);
			if (!empty($contents)){
				return ($contents);
			}
			else{
				return false;
			}
		}
	}
}

/**
 * Data decompression
 * The header contains Accept-Encoding gzip, deflate
 * @input : ziped data
 * @output : dezipedtext
 */
function gzBody($gzData){
// data decompression
// the received header contains Accept-Encoding gzip, deflate
    if (substr($gzData,0,3)=="\x1f\x8b\x08"){    // Deflate file
        $i=10;
        $flg=ord(substr($gzData,3,1));
        if($flg>0){
            if($flg&4){
                list($xlen)=unpack('v',substr($gzData,$i,2));
                $i=$i+2+$xlen;
            }
            if($flg&8) $i=strpos($gzData,"\0",$i)+1;
            if($flg&16) $i=strpos($gzData,"\0",$i)+1;
            if($flg&2) $i=$i+2;
        }
        return gzinflate(substr($gzData,$i,-8));
    }
	else{
		// Try Uncompress
        return gzuncompress($gzData);
    }
	return false;
}


/**
 * Get boat tracks
 * The date are gziped
 * @input : racenumber and token
 * @output : deziped xml text
 */
function getTracks($racenumber, $token){
 global $al; // localisation strings
 global $solhost;
 global $webclient;
 global $servicetracks;
// 'http://www.sailonline.org/webclient/traces_'.$racenumber.'1018.xml?token='.$token
// return xml zip
//
	$inflate='';

    if (!empty($racenumber) && !empty($token)){
        // Fichiers de Traces
		$url=$solhost.$webclient.$servicetracks.$racenumber.'.xml?token='.$token;
 		if ($contents=my_get_content($url)){
			if (DEBUGCONNECT){
				// DEBUG
				// Display first bytes to know if it is the deflate or the gzip protocol
                $readable= bin2hex($contents);
				echo '<br /><pre>'."\n";
				echo substr($readable,0, 20).'...'."\n";
			    echo '</pre>'."\n";
			}
			$inflate= gzBody($contents);
		}
	 }
	 return ($inflate);
}


/**
 * Get boat position
 * The date are xml-ed
 * @input : user boat token
 * @output : text
 */
function getBoat($token){
 global $al; // localisation strings
 global $solhost;
 global $webclient;
 global $serviceboat;
//  http://node1.sailonline.org/webclient/boat.xml?token='.$token
// return xml

	$contents='';

    if (!empty($token)){
        // Fichiers de Traces
		$url=$solhost.$webclient.$serviceboat.'?token='.$token;
 		if ($contents=my_get_content($url)){
			if (DEBUGCONNECT){
				// DEBUG
				echo '<br /><pre>'."\n";
				echo htmlspecialchars($contents);
			    echo '</pre>'."\n";
			}
		}
	 }
	 return ($contents);
}

?>