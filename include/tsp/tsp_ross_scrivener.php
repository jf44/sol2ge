<?php

/* PHP Class TSP

Author: 	Ross Scrivener
WWW: 		http://scrivna.com
Description:
	This class is a calculator for the common "Travelling salesman" maths problem.
	
	It takes any number of coordinates and brute force calculates the shortest distance to travel to all those points.
	It doesn't do anything clever like forcing a starting / ending point, however this could easily be implemented.

Date: 		2008-03-15

Feel free to modify this script and use as you please, a little note saying original author would 
be nice and if you do use it, drop me a line, i'd like to see what you've done with it.

Please mail me any bug fixes etc. so i can update this script for others

*/

class TSP {

	private $locations 	= array();		// all locations to visit
	private $longitudes = array();
	private $latitudes 	= array();
	private $shortest_route = array();	// holds the shortest route
	private $shortest_routes = array();	// any matching shortest routes
	private $shortest_distance = 0;		// holds the shortest distance
	private $all_routes = array();		// array of all the possible combinations and there distances
	
	// add a location
	public function add($name,$longitude,$latitude){
		$this->locations[$name] = array('longitude'=>$longitude,'latitude'=>$latitude);
	}
	// the main function that des the calculations
	public function compute(){
		$locations = $this->locations;
		
		foreach ($locations as $location=>$coords){
			$this->longitudes[$location] = $coords['longitude'];
			$this->latitudes[$location] = $coords['latitude'];
		}
		$locations = array_keys($locations);
		
		$this->all_routes = $this->array_permutations($locations);
		
		foreach ($this->all_routes as $key=>$perms){
			$i=0;
			$total = 0;
			foreach ($perms as $value){
				if ($i<count($this->locations)-1){
					$total+=$this->distance($this->latitudes[$perms[$i]],$this->longitudes[$perms[$i]],$this->latitudes[$perms[$i+1]],$this->longitudes[$perms[$i+1]]);
				}
				$i++;
			}
			$this->all_routes[$key]['distance'] = $total;
			if ($total<$this->shortest_distance || $this->shortest_distance ==0){
				$this->shortest_distance = $total;
				$this->shortest_route = $perms;
				$this->shortest_routes = array();
			}
			if ($total == $this->shortest_distance){
				$this->shortest_routes[] = $perms;
			}
		}
	}
	// work out the distance between 2 longitude and latitude pairs
	function distance($lat1, $lon1, $lat2, $lon2) { 
		if ($lat1 == $lat2 && $lon1 == $lon2) return 0;
		$unit = 'M';	// miles please!
		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		
		if ($unit == "K") {
			return ($miles * 1.609344); 
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
	}
	// work out all the possible different permutations of an array of data
	private function array_permutations($items, $perms = array( )) {
		static $all_permutations;
		if (empty($items)) {
			$all_permutations[] = $perms;
		}  else {
			for ($i = count($items) - 1; $i >= 0; --$i) {
				$newitems = $items;
				$newperms = $perms;
				list($foo) = array_splice($newitems, $i, 1);
				array_unshift($newperms, $foo);
				$this->array_permutations($newitems, $newperms);
			}
		}
		return $all_permutations;
	}
	// return an array of the shortest possible route
	public function shortest_route(){
		return $this->shortest_route;
	}
	// returns an array of any routes that are exactly the same distance as the shortest (ie the shortest backwards normally)
	public function matching_shortest_routes(){
		return $this->shortest_routes;
	}
	// the shortest possible distance to travel
	public function shortest_distance(){
		return $this->shortest_distance;
	}
	// returns an array of all the possible routes
	public function routes(){
		return $this->all_routes;
	}
}

?>