<?php namespace App\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class VnnScraperCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vnn:scrape';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scrape VNN sites for address.';

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
     * Client for crawler
     *
     * @var Client
     */
    private $client;

    /**
     * URL for page to crawl.
     *
     * @var string
     */
    private $url;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		echo "Clearing schools.csv file.\n";
		File::put(storage_path() . '/temp/schools.csv', '');

		$this->url = 'http://varsitynewsnetwork.com/vnn-partner-schools/';

		echo "Crawling site.\n";
		$this->client = new Goutte\Client();
		$crawler = $this->client->request('GET', $this->url);

		$crawler->filter('.member_state')->each(function($node) {
    		$stateName = $node->filter('h3')->text();

    		$list = $node->filter('ul > li a');
    		foreach ($list as $node) {
        		$schoolName = $node->nodeValue;
        		$schoolUrl = $node->getAttribute('href');

        		// write to the csv file
        		$line = $schoolName . "," . $stateName . "," . $schoolUrl . "\n";
        		File::append(app_path() . '/temp/schools.csv', $line);
    		}
		});

		echo "File written \n";
	}

}
