<?php
// JF 2009

// Boat class for SailOnLine
// DEFINITION D'UN CLASSE VOILIER d'après SailOnLine
// V 0.1


// -----------------------------------
// data from boat.xml
/*
                      <data>
<lmi>912</lmi>
<boat>
	<id>303501</id>
	<name>jf44</name>
	<start_time></start_time>
	<finish_time></finish_time>
	<twa>-1.84023950558</twa>
	<twd>4.30656577385</twd>
	<tws>1.9808777227</tws>
	<sog>16.0572497068</sog>
	<efficiency>1.0</efficiency>
	<dtg>57.6058202107</dtg>
	<dbl>35.2595120395</dbl>
	<lon>-88.5055976416</lon>
	<lat>43.989248996</lat>
	<cog>6.14680527943</cog>
	<ranking>4</ranking>
	<current_leg>7</current_leg>
	<last_cmd_type>cc</last_cmd_type>
</boat>



<chats>
	<timestamp></timestamp>

</chats>


</data>

*/
class SolBoat{
	var $id;
	var $name;
	var $start_time;
    var $finish_time;
	var $twa;
	var $twd;
	var $tws;
	var $sog;
	var $efficiency;
	var $dtg;
	var $dbl;
	var $lon;
	var $lat;
	var $cog;
	var $ranking;
	var $current_leg;
	var $last_cmd_type;


	function getId(){
		return $this->id;
	}
	function getName(){
		return $this->name;
	}
	function getStartTime(){
		return $this->start_time;
	}

	function getFinishTime(){
		return $this->finish_time;
	}
	function getRanking(){
		return $this->ranking;
	}

	function getLongitude(){
		return $this->lon;
	}
	function getLatitude(){
		return $this->lat;
	}

	function getWindSpeed(){
		return $this->tws;
	}
	function getWindAngle(){
		return $this->twa;
	}
	function getWindDirection(){
		return $this->twd;
	}
	function getBoatCap(){
		return $this->cog;
	}
	function getBoatSpeed(){
		return $this->sog;
	}

	function getEfficiency(){
		return $this->efficiency;
	}


	public function setBoat($id, $name, $start_time, $finish_time, $twa, $twd, $tws, $sog, $efficiency,
		$dtg, $dbl, $lon, $lat, $cog, $ranking, $current_leg, $last_cmd_type){
		$this->id=$id;
		$this->name=$name;
		$this->start_time=$start_time;
        $this->finish_time=$finish_time;
        $this->twa=$twa;
        $this->twd=$twd;
        $this->tws=$tws;
		$this->sog=$sog;
        $this->efficiency=$efficiency;
        $this->dtg=$dtg;
        $this->dbl=$dbl;
		$this->lon=$lon;
		$this->lat=$lat;
		$this->cog=$cog;
		$this->ranking=$ranking;
        $this->current_leg=$current_leg;
        $this->last_cmd_type=$last_cmd_type;
	}

	function setRang($rang){
		$this->rang=$rang;
	}


	function displayRanking(){
		echo ('Id:'.$this->id.', Name:'.$this->name.', COG:'.$this->cog.', SOG:'.$this->sog.', Ranking:'.$this->ranking);
	}

	function displayBoatWind(){
		echo ('TWS:'.$this->tws.', TWA:'.$this->twa.', TWD:'.$this->twd);
	}

	function displayBoatPosition(){
		echo ('Lat:'.$this->lat.', Lon:'.$this->lon);
	}

	function displayBoatPerformance(){
		echo $this->efficiency;
	}


	public function Dump() {
        var_dump(get_object_vars($this));
    }

	public function displayBoat(){
		echo "<ul><li>Boat: <b>$this->name</b></li>
		<li>BoatId: <i>$this->id</i>
		<li>COG: $this->cog, SOG: $this->sog</li>
		<li>TWD: $this->twd, TWS: $this->tws</li>
		<li>TWA: $this->twa</li>
		<li>Long: $this->lon, Lat: $this->lat</li>
		<li>Ranking: $this->ranking, Leg: $this->current_leg</li>
        <li>Efficiency:  $this->efficiency</li>
		<li>DTG: $this->dtg, DBG: $this->dbl</li>
		<li>Start: $this->start_time</li>
		<li>Finish: $this->finish_time</li>
        <li>Last command type : $this->last_cmd_type</li>
</ul>
";
	}

}

?>