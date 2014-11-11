<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$this->info("Clearing schools.csv file.\n");
\File::put(storage_path() . '/temp/schools.csv', '');

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
		\File::append(storage_path() . '/temp/schools.csv', $line);
	}
});

echo "File written\n";
