<?php namespace App\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Ivory\GoogleMap\Services\Geocoding\Geocoder;
use Ivory\GoogleMap\Services\Geocoding\GeocoderProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Goutte\Client;

use App\School;

class VnnGeocoderCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vnn:geocode';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Geocode Schools and save data.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		echo "Opening file.\n";
		$lines = file(storage_path() . '/temp/schools.csv', FILE_IGNORE_NEW_LINES);
		$this->emptySchoolTable();
		$schools = [];
		
		foreach($lines as $line)
        {
			if($line != '')
            {
				list($schoolName, $schoolState, $schoolUrl) = explode(',', $line);

				$this->info("Proccesing School: ". $schoolName);

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

				$response = $this->geocodeAddress($geocoder, $address);

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
						$school->save();

						array_push($schools, $school);
					}
				}
			}
		}

		$this->info('Geocoding Complete!');
	}

	private function geocodeAddress($geocoder, $address)
	{
		try {
			$response = $geocoder->geocode($address);
		} catch (Guzzle\Http\Exception\ConnectException $e) {
			$this->info("Error geocoding address: ". $address);
			$this->info($e->getMessage());
			return false;
		}

		return $response;
	}

	private function emptySchoolTable()
	{
		$schools = School::all();
		foreach ($schools as $school) {
			$school->delete();
		}
	}

}
