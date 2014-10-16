<?php namespace App\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Ivory\GoogleMap\Services\Geocoding\Geocoder;
use Ivory\GoogleMap\Services\Geocoding\GeocoderProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Goutte\Client;

class VnnGeocoderCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:name';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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
		$lines = file(app_path() . '/temp/schools.csv', FILE_IGNORE_NEW_LINES);

		$schools = [];
		foreach($lines as $line)
        {
			if($line != '')
            {
				list($schoolName, $schoolState, $schoolUrl) = explode(',', $line);

				echo "Proccesing School: ". $schoolName . "\n";

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

				if(strpos($street, 'Principal') || strpos($street, 'Director'))
                {
					var_dump('checking street 1');
					$street = str_replace(array("\r", "\n"), '', next($addressData));
				}

				if(strpos($street, 'Principal') || strpos($street, 'Director'))
                {
					var_dump('checking street 2');
					$street = str_replace(array("\r", "\n"), '', next($addressData));
				}

				$cityStateZip = str_replace(array("\r", "\n"), '', next($addressData));

				$address = $schoolName . ' ' . $street . ' ' . $cityStateZip;

				var_dump($address);

				// Geocode an address
				$response = $geocoder->geocode($address);
				$results = $response->getResults();

				foreach ($results as $result)
                {
					list($name, $street, $city, $statePostalCode) = explode(',', $result->getFormattedAddress());
					$statePostalCode = explode(' ', $statePostalCode);

					if(sizeof($statePostalCode) < 3)
                    {
						dd($result->getFormattedAddress());
					}

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

		var_dump($schools);
		die();
	}

}
