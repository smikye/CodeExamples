<?php
require_once 'Mysqli.php';
require_once 'LocationTypes.php';
require_once 'LocationRegions.php';

/** @var $mysqli Sql */
$mysqli = new Sql;
$conn = $mysqli->connection;

/** @var $types LocationTypes */
$types = new LocationTypes($conn);
$api_query = $_POST['api'];

/*
 * Check api query for right function
 */
switch($api_query){
	case 0:
		saveLocations($conn);
	break;

	case 1:
		loadLocations($conn);
	break;

	case 2:
		deleteRegion($conn);
	break;

	case 3:
		addRegion($conn);
	break;

	case 4:
		deleteType($conn);
	break;

	case 5:
		addType($conn);
	break;

	case 6:
		saveLocationOne($conn);
	break;

	case 7:
		editType($conn);
	break;

	case 8:
		getTypes($conn);
	break;

	case 9:
		getRegions($conn);
	break;

	case 10:
		getTypesCheckbox($conn);
	break;
}

$conn->close();

/**
 * Save old or new locations
 *
 * @param $conn mysqli
 */
function saveLocations($conn){
	$data = $_POST['data'];
	$data = json_decode($data);
	$who = $_POST['who'];

	$query_locations = '';

	foreach($data as $loc){
		$type = $loc->type;
		$lng = $loc->long;
		$lat = $loc->lat;
		$r = $loc->r;
		$id = $loc->id;

		$regionLat = (int)(floor($lat/5) * 5);
        $regionLong = (int)(floor($lng/5) * 5);

        $region = $regionLat . "," . $regionLong;

		$query_locations .= "('$type', $lat, $lng, $r, '$region', '$id', '$who'),";
	}

	$query_locations = substr($query_locations, 0, -1);

	$sql = "INSERT IGNORE INTO `locations`(`Type`, `Latitude`, `Longitude`, `Radius`, `Region`, `PlaceId`, `CreatedBy`) VALUES $query_locations";

	if (!$query = $conn->query($sql)) {

		echo -1;
		echo "Error: " . $conn->error . "\n";
		exit;
	}

	echo mysqli_affected_rows($conn);
}

/**
 * Save old or new location (One location)
 *
 * @param $conn mysqli
 */
function saveLocationOne($conn){
	$data = $_POST['data'];
	$data = json_decode($data);

	$query_locations = '';

	$type = $data->type;
	$lng = $data->long;
	$lat = $data->lat;
	$r = $data->r;
	$id = $data->id;
	$density = $data->density;
	$named = $data->namedzs;
	$time_beg = $data->time_beg;
	$time_end = $data->time_end;

	$regionLat = (int)(floor($lat/5) * 5);
	$regionLong = (int)(floor($lng/5) * 5);
	$region = $regionLat . "," . $regionLong;

	$sql = "UPDATE `locations` SET `Type`='$type', `Latitude`='$lat', `Longitude`='$lng', `Radius`='$r', `Region`='$region', `DensityAdjust`='$density', `TimeBeg`='$time_beg', `TimeEnd`='$time_end' WHERE `PlaceId`='$id'";

	if (!$query = $conn->query($sql)) {

		echo -1;
		exit;
	}

	echo mysqli_affected_rows($conn);
}

/**
 * Load locations placed in the bounds
 *
 * @param $conn mysqli
 */
function loadLocations($conn){
	header('Content-Type: application/json');

	$data = $_POST['data'];
	$data = json_decode($data);
	

	$query_locations = [];
	foreach($data as $key=>$val){
		$data[$key] = "'$val'";
	}

	$bounds = json_decode($_POST['bounds']);
	if ($bounds) {

	    $east = $bounds->east;
	    $west = $bounds->west;
	    $south = $bounds->south;
	    $north = $bounds->north;

	    $sql = "SELECT * FROM `locations` WHERE `Type` IN (" . implode(',', $data) . ") AND (Longitude>$west AND Longitude<$east) AND (Latitude>$south AND Latitude<$north)";
	}

	if (!$query = $conn->query($sql)) {
		echo -1;
		exit;
	}

	while( $item = $query->fetch_assoc()){
	     array_push($query_locations, $item);
	}

	echo json_encode($query_locations);
}

/**
 * Delete region
 *
 * @param $conn mysqli
 */
function deleteRegion($conn){
	header('Content-Type: application/json');

	$region = $_POST['region'];

	$sql = "DELETE FROM `location_regions` WHERE `Name`='$region'";

	if (!$query = $conn->query($sql)) {
		echo 0;
		exit;
	}
	
	echo 1;
}

/**
 * Add new region
 *
 * @param $conn mysqli
 */
function addRegion($conn){
	header('Content-Type: application/json');

	$region = $_POST['region'];

	$sql = "INSERT IGNORE INTO `location_regions`(`Name`) VALUES ('$region')";

	if (!$query = $conn->query($sql)) {
		echo -1;
		exit;
	}
	
	echo mysqli_affected_rows($conn);
}

/**
 * Delete location type
 *
 * @param $conn mysqli
 */
function deleteType($conn){
	header('Content-Type: application/json');

	$type = $_POST['type'];

	$sql = "DELETE FROM `location_types` WHERE `Type`='$type'";

	if (!$query = $conn->query($sql)) {
		echo 0;
		echo $conn->error;
		exit;
	}
	
	echo mysqli_affected_rows($conn);
}

/**
 * Add new location type
 *
 * @param $conn mysqli
 */
function addType($conn){
	header('Content-Type: application/json');

	$cat = $_POST['loc_category'];
	$type = $_POST['loc_type'];
	$search = $_POST['loc_search'];
	$notes = $_POST['loc_notes'];
	$color = $_POST['loc_color'];
	$style = $_POST['loc_style'];
	$radius = $_POST['loc_radius'];
	$time_beg = $_POST['loc_time_beg'];
	$time_end = $_POST['loc_time_end'];
	$density = $_POST['loc_density'];
	$center_density = $_POST['loc_center_density'];
	$rare_chance = $_POST['loc_rare_chance'];
	$named_zs = $_POST['loc_namedzs'];
	$nodupsdistance = $_POST['loc_dupsdistance'];
	$quadsize = $_POST['loc_quadrsize'];

	$sql = "INSERT INTO `location_types`(`Type`,`Category`,`SearchExamples`,`Notes`,`PinColor`,`PinStyle`,`DefaultRadius`,`TimeBeg`,`TimeEnd`,`Density`,`CenterDensity`,`RareChance`,`NamedZs`,`DeleteDupsWithin`, `DivideQuadrantSize`) VALUES ('$type','$cat','$search','$notes','$color','$style','$radius','$time_beg','$time_end','$density','$center_density','$rare_chance','$named_zs', '$nodupsdistance', '$quadsize')";

	if (!$query = $conn->query($sql)) {
		echo -1;
		echo $conn->error;
		exit;
	}
	
	echo mysqli_affected_rows($conn);
}

/**
 * Save edited location type
 *
 * @param $conn mysqli
 */
function editType($conn){
	header('Content-Type: application/json');

	$cat = $_POST['loc_category'];
	$type = $_POST['loc_type'];
	$search = addslashes($_POST['loc_search']);
	$notes = addslashes ($_POST['loc_notes']);
	$color = $_POST['loc_color'];
	$style = $_POST['loc_style'];
	$radius = $_POST['loc_radius'];

	$time_beg = $_POST['loc_time_beg'];
	$time_end = $_POST['loc_time_end'];
	$density = $_POST['loc_density'];
	$center_density = $_POST['loc_center_density'];
	$rare_chance = $_POST['loc_rare_chance'];
	$named_zs = $_POST['loc_namedzs'];
	$nodupsdistance = $_POST['loc_dupsdistance'];
	$quadsize = $_POST['loc_quadrsize'];

	$sql = "UPDATE `location_types` SET `Category`='$cat',`SearchExamples`='$search',`Notes`='$notes',`PinColor`='$color',`PinStyle`='$style',`DefaultRadius`='$radius',`TimeBeg`='$time_beg',`TimeEnd`='$time_end',`Density`='$density',`CenterDensity`='$center_density',`RareChance`='$rare_chance',`NamedZs`='$named_zs', `DeleteDupsWithin`='$nodupsdistance', `DivideQuadrantSize`='$quadsize' WHERE `Type`='$type'";

	if (!$query = $conn->query($sql)) {
		echo -1;
		echo $conn->error;
		exit;
	}
	
	echo mysqli_affected_rows($conn);
}

/**
 * Get all location types
 *
 * @param $conn mysqli
 */
function getTypes($conn){
	$types = new LocationTypes($conn);
	$types->genTypesOptions();
}

/**
 * Get all location types checkboxes
 *
 * @param $conn mysqli
 */
function getTypesCheckbox($conn){
	$types = new LocationTypes($conn);
	$types->genTypesCheckboxes();
}

/**
 * Get all regions
 *
 * @param $conn mysqli
 */
function getRegions($conn){
	$regions = new LocationRegions($conn);
	$regions->genRegionOptions();
}



