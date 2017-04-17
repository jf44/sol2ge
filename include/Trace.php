<?php
// la classe Coordonnees
class Trace{
	// coordonnees décimales d'un point dans l'espace géodésique
	var $boatid;
	var $lonlat;


	function Trace($id, $data){
		$this->boatid=$id;
		$this->lonlat=$data;
	}

	function GetId(){
		return $this->boatid;
	}
	function GetData(){
		return $this->data;
	}
	function SetId(){
		return $this->boatid=$id;
	}
	function SetLonLat($data){
		return $this->lonlat=$data;
	}

    public function TrajectoireGE($altitude=0){
	// Trajectoire pour Google Earth
	$s='';
		if (!empty($this->lonlat)){
			foreach(explode(' ',$this->lonlat) as $lonlat){
       			if (list($lon, $lat) = explode(',',$lonlat)){
					$s.= $lon.",".$lat.','.$altitude." ";   // attention de laisser l'espace en fin de chaine
				}
			}
		}
		return $s;
	}

}


?>