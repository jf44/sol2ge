<?
# A Travelling Salesman Problem Solver for Google Earth
# By Stefan Geens, Ogle Earth
# http://www.ogleearth.com

# Form and KML parsing routines pilfered from Brammeleman's Center of Gravity Calculator for Google Earth:
# http://oase.uci.ru.nl/~bradaa/nerdnotes.org/center_of_gravity/

# This script depends on the following PEAR (pear.php.net) modules:
# - HTML_Quickform
# - XML_Serializer

// set error reporting to ignore notices
error_reporting(E_ALL ^ E_NOTICE);

// load the main class
require_once 'HTML/QuickForm.php';
require_once 'XML/Unserializer.php';

// instantiate the HTML_QuickForm object
$form = new HTML_QuickForm('tspform');

// add some elements to the form
$form->addElement('header', null, 'Travelling Salesman Problem Solver');
$form->addElement('file', 'kmfile', 'KML file:');
$form->addElement('submit', null, 'Upload');

// try to validate a form
if ($form->validate()) {
		// determine the filename of the uploaded file
		$uploadInfo = $form->getSubmitValue('kmfile');
		$kmlfile = $uploadInfo['tmp_name'];
	
		// read the uploaded file into an associative array
		$doc = implode('', file($kmlfile));
		$unserializer = &new XML_Unserializer();
	
		// Check whether serialization worked
		if (PEAR::isError($unserializer->unserialize($doc))) {
				die($status->getMessage());
		}
	
		$kmldata = $unserializer->getUnserializedData();
	
		$placemarks = array();
		$placemark_count = 0;
		$oelocations = array();
		
		// extract the placemarks
		searchplacemarks($kmldata);
	
		foreach ($placemarks as $placemark) {
				// get lat and lon from the array in radians
				$coordinates = $placemark['Point']['coordinates'];
				// coordinates are separated by commas
				list($longitude, $latitude, $altitude) = explode(',', $coordinates);
	
				// convert to radians
				$lat = deg2rad($latitude);
				$lon = deg2rad($longitude);
	
				// stick into an associative array
				$oelocations[$placemark_count]['lat'] = $lat;
				$oelocations[$placemark_count]['lon'] = $lon;
	
				$placemark_count++;
		}
	
		// keep the size manageable -- take the first x placemarks
		if ($placemark_count > 9) {$placemark_count = 9;}
		
		// build an array of distances between each placemark
		$oedistances = array();
		for ($i = 0; $i < $placemark_count; $i++) :
				for ($j = $i + 1; $j < $placemark_count; $j++) :
				//You can use direct distance rather than great circle distances
				$oedistances[$i][$j] = distance($oelocations[$i]['lat'],$oelocations[$i]['lon'],$oelocations[$j]['lat'],$oelocations[$j]['lon']);
				// Don't believe me? Here's how you'd do it with great circle distances: 
				// $oedistances[$i][$j] = haversine($oelocations[$i]['lat'],$oelocations[$i]['lon'],$oelocations[$j]['lat'],$oelocations[$j]['lon']);
				// The following is inefficient, I know
				$oedistances[$j][$i] = $oedistances[$i][$j]; 
				endfor;		
		endfor;
	
		$size = $placemark_count - 1;
		$perm = range(0, $size); //create an array to hold permutations of all routes
		$pnum = factorial($size); 
		$minimum = 1000000000; // keep track of the shortest route until now
		
		// fill the array with the needed route permutations.
		for ($m = 0; $m < $pnum; $m++):
				foreach ($perm as $i) { 
						$perms[$m][] = $i;
				}
		$perm = pc_next_permutation($perm, $size);
		endfor;
		
		// for every route permutation, add up the total distance
		for ($i = 0; $i < $m; $i++):
				for ($k = 0; $k < $size; $k++):
						$temp_sum[$i] += $oedistances[$perms[$i][$k]][$perms[$i][$k+1]];
				endfor;
				$temp_sum[$i] += $oedistances[$perms[$i][$size]][$perms[$i][0]];
				// is it the shortest thus far?
				if ($temp_sum[$i] < $minimum) { 
						$minimum = $temp_sum [$i] ;
						$index = $i; // if so, remember which permutation it was
				}
		 endfor;
		
		// build the KML file
		$response = '<?xml version="1.0" encoding="UTF-8"?>';
		$response .= '<kml xmlns="http://earth.google.com/kml/2.0">';
		$response .= "  <Placemark>\n";
		$response .= "    <name>Traveling Salesman Problem: Shortest route</name>";
		$response .= "    <visibility>1</visibility>\n";
		$response .= "    <open>0</open>\n";
		$response .= "    <LineString>\n"; 
		$response .= "    <tessellate>1</tessellate>\n"; 
		$response .= "    <coordinates>\n";
		// add the LineString coordinates of the shortesr route
		for ($k = 0; $k <= $size; $k++):
				$response .=  rad2deg($oelocations[$perms[$index][$k]]['lon']) . ", " . rad2deg($oelocations[$perms[$index][$k]]['lat']) . ",0\n";
		endfor;
		$response .=  rad2deg($oelocations[$perms[$index][0]]['lon']) . ", " . rad2deg($oelocations[$perms[$index][0]]['lat']) . ",0\n";
		$response .= "    </coordinates>\n";
		$response .= "     </LineString>\n";
		$response .= "    </Placemark>\n";
		$response .= '</kml>' . "\n";
	
		// send the response file
		header('Content-Type: application/vnd.google-earth.kml+xml');    
		header('Content-Disposition: attachment; filename="tsp.kml"');
		echo $response;
		exit();
}

// Show the form
echo '<html>';
echo '<body>';
$form->display();
echo '<p>Upload a KML file (NOT KMZ) containing placemarks. Only the first nine placemarks encountered will be used in the calculation of the shortest route connecting all of them. More info <a href="http://www.ogleearth.com/2006/08/traveling_sales.html" title="here">here</a>.</p>';
echo '<p>Click <a href="index.phps">here</a> for the source code.</p>';
echo '</body></html>';
exit();

// declaration of functions

// permutation generator
// adapted from O'Reilly's Perl Cookbook, I think:
// http://tinyurl.com/krw8k
// Not the most efficient routine, as mirror images are also returned. Since it doesn't matter to us in which direction we travel a route, I need to find a permutation generator that does not return mirror images. 
function pc_next_permutation($p, $size) {
		for ($i = $size - 1; $p[$i] >= $p[$i+1]; --$i) { }
		for ($j = $size; $p[$j] <= $p[$i]; --$j) { }
		$tmp = $p[$i]; $p[$i] = $p[$j]; $p[$j] = $tmp;
		for (++$i, $j = $size; $i < $j; ++$i, --$j) {
				 $tmp = $p[$i]; $p[$i] = $p[$j]; $p[$j] = $tmp;
		}
		return $p;
}

// look for array elements that are placemarks
function searchplacemarks($data) {
		global $placemarks;
		foreach($data as $key => $value) {
				if ($key === "Point") {
						$placemarks[] = $data;
				} else {
				if (count($value) > 1) {
						searchplacemarks($value);
						}
				}
		}
}

//Find the direct Euclidian distance between 2 points in 3D space
function distance($lat1,$long1,$lat2,$long2) {
	
		// convert spherical coordinate into cartesian
		$x1 = cos($lat1) * sin($long1);
		$y1 = cos($lat1) * cos($long1);
		$z1 = sin($lat1);
		$x2 = cos($lat2) * sin($long2);
		$y2 = cos($lat2) * cos($long2);
		$z2 = sin($lat2);
		
		// Find the distances
		$distx = abs($x1-$x2);
		$disty = abs($y1-$y2);
		$distz = abs($z1-$z2);
		
		$d = sqrt(pow($distx,2) + pow($disty,2) + pow($distz,2));
		return $d;
}

// find the great circle distance between two points
// adapted from Wanting Seed blog:
// http://wantingseed.com/sprout/2003/06/10/distance-between-two-points-on-earth/
function haversine($lat1,$long1,$lat2,$long2) {
		$dlat = abs($lat2 - $lat1);
		$dlong = abs($long2 - $long1);
		$l = ($lat1 + $lat2) / 2;
		$a = 6378;
		$b = 6357;
		$e = sqrt(1 - ($b * $b)/($a * $a));
		$r1 = ($a * (1 - ($e * $e))) / pow((1 - ($e * $e) * (sin($l) * sin($l))), 3/2);
		$r2 = $a / sqrt(1 - ($e * $e) * (sin($l) * sin($l)));
		$ravg = ($r1 * ($dlat / ($dlat + $dlong))) + ($r2 * ($dlong / ($dlat + $dlong)));
		$sinlat = sin($dlat / 2);
		$sinlon = sin($dlong / 2);
		$a = pow($sinlat, 2) + cos($lat1) * cos($lat2) * pow($sinlon, 2);
		$c = 2 * asin(min(1, sqrt($a)));
		$d = $ravg * $c;
		return $d;
}

// return the factorial of a number
function factorial($number) {
		if ($number == 0) return 1;
		return $number * factorial($number - 1);
}

?> 
