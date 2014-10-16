<?php namespace App\Providers;

use InspireCommand;
use Illuminate\Support\ServiceProvider;

class ArtisanServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->commands('App\Console\InspireCommand');
		$this->commands('App\Console\VnnScraperCommand');
		$this->commands('App\Console\VnnGeocodeCommand');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'App\Console\InspireCommand',
			'App\Console\VnnScraperCommand',
			'App\Console\VnnGeocodeCommand'
		];
	}

}
