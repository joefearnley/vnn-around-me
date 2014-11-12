<?php

require 'vendor/autoload.php';
require 'setup.php';

$app = new \Slim\Slim(
	[
		'debug' => true,
		'templates.path' => 'views'
	]
);

$app->get('/', function () use ($app) {
	$app->render('home.php');
});

$app->get('/school/find', function () use ($app) {
	$latitude = $app->request->get('latitude');
	$longitude = $app->request->get('longitude');

	$startingPoint = [$latitude, $longitude];

	$schools =  School::all();

	$items = [];
	$i = 0;
	foreach($schools as $school) {
		$items[$i] = [
			$school->id,
			$school->latitude,
			$school->longitude
		];
		$i++;
	}

	$distances = array_map(function($item) use ($startingPoint) {
		$itemLatLon = array_slice($item, -2);
	    return distance($itemLatLon, $startingPoint);
	}, $items);

	asort($distances);

	$closestSchool = School::find($items[key($distances)][0]);

	header("Content-Type: application/json");
	echo $closestSchool->to_json();
	exit;
});

/**
 * Calculate the distance between town sets of latitude and longitude.
 * http://stackoverflow.com/questions/9589130/find-closest-longitude-and-latitude-in-array
 * 
 * @param Array $itemLatLon
 * @param Array $itemLatLon
 * @return 
 */
function distance($itemLatLon, $startingPoint)
{
    list($lat1, $lon1) = $itemLatLon;
    list($lat2, $lon2) = $startingPoint;

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  
    		cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return $miles;
}

$app->run();