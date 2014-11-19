<?php

require '../vendor/autoload.php';

use Ivory\GoogleMap\Services\Geocoding\Geocoder;
use Ivory\GoogleMap\Services\Geocoding\GeocoderProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Goutte\Client;

date_default_timezone_set('America/Detroit');

$connections = [
	'production' => 'mysql://vnn:password@localhost/vnnaroundme'
];

ActiveRecord\Config::initialize(function($cfg) use ($connections)
{
	$cfg->set_model_directory('../models');
	$cfg->set_connections($connections);
	$cfg->set_default_connection('production');
});

echo "Opening file.\n";
$lines = file('../temp/schools.csv', FILE_IGNORE_NEW_LINES);
emptySchoolTable();
$schools = [];

// alright, lets read through this bitch.
foreach($lines as $line)
{
	// in some countries, lines are blank (there are no lines)
	if($line != '')
	{
		list($schoolName, $schoolState, $schoolUrl) = explode(',', $line);

		echo "Proccesing School: " . $schoolName . "\n";

		if($schoolName[0] === '#') {
			echo "Skipping School: " . $schoolName . " because it was commented out.\n";
			continue;
		}

		$geocoder = new Geocoder();
		$geocoder->registerProviders(array(
		new GeocoderProvider(new CurlHttpAdapter()),
		));

		$client = new Client();
		$crawler = $client->request('GET', $schoolUrl);
		$node = $crawler->filter('#footer_widget_area_1');
		$t = $node->filter('.textwidget p')->html();

		$addressData = explode("<br>", $t);
		$street = str_replace(array("\r", "\n"), '', $addressData[0]);

		// some addresses have the Priciple and/or Athletic Director listed in the area
		if(strpos($street, 'Principal') !== false || strpos($street, 'Director') !== false)
		{
			$street = str_replace(array("\r", "\n"), '', next($addressData));
		}

		// some have both
		if(strpos($street, 'Principal') !== false || strpos($street, 'Director') !== false)
		{
			$street = str_replace(array("\r", "\n"), '', next($addressData));
		}

		$cityStateZip = str_replace(array("\r", "\n"), '', next($addressData));
		$address = $street . ' ' . $cityStateZip;

		$response = geocodeAddress($geocoder, $address);

		if($response !== false)
		{
			$results = $response->getResults();

			foreach ($results as $result)
			{
				list($street, $city, $statePostalCode) = explode(',', $result->getFormattedAddress());
				$statePostalCode = explode(' ', $statePostalCode);

				$state = $statePostalCode[1];
				$zip = $statePostalCode[2];

				$latitude = $result->getGeometry()->getLocation()->getLatitude();
				$longitude = $result->getGeometry()->getLocation()->getLongitude();

				$school = new School();
				$school->name = $schoolName;
				$school->address = $street;
				$school->city = $city;
				$school->state = $statePostalCode[1];
				$school->zip = $statePostalCode[2];
				$school->latitude = $latitude;
				$school->longitude = $longitude;
				$school->url = $schoolUrl;
				$school->save();

				array_push($schools, $school);
			}
		}
	}
}

echo "Geocoding Complete!\n";

/**
 * Attempt to geocode an address and catch an exception if one is thrown.
 * 
 * @param Geocoder geocoder
 * @param string address
 *
 * return boolean  
 */
function geocodeAddress($geocoder, $address)
{
	try {
		$response = $geocoder->geocode($address);
	} catch (Exception $e) {
		echo "Error geocoding address: " . $address . "\n";
		echo $e->getMessage();
		return false;
	}
	return $response;
}

/**
 * Empty the school table (duh?)
 */
function emptySchoolTable()
{
	$schools = School::all();
	foreach ($schools as $school) {
		$school->delete();
	}

	School::query('ALTER TABLE schools AUTO_INCREMENT = 1;');
}
