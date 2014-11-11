<?php

require '../vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

echo "Clearing schools.csv file.\n";
file_put_contents('../temp/schools.csv', '');

$url = 'http://varsitynewsnetwork.com/vnn-partner-schools/';

echo "Crawling site.\n";
$client = new \Goutte\Client();
$crawler = $client->request('GET', $url);

$crawler->filter('.member_state')->each(function($node) {
	$stateName = $node->filter('h3')->text();

	$list = $node->filter('ul > li a');
	foreach ($list as $node) {
		$schoolName = $node->nodeValue;
		$schoolUrl = $node->getAttribute('href');

		// write to the csv file
		$line = $schoolName . "," . $stateName . "," . $schoolUrl . "\n";
		 file_put_contents('../temp/schools.csv', $line, FILE_APPEND);
	}
});

echo "File written\n";
