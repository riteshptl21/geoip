<?php namespace Riteshptl21\Geoip;

use Illuminate\Support\ServiceProvider;

class GeoipServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('riteshptl21/geoip');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
		$this->app['geoip'] = $this->app->share(function($app)
		{
			return new Geoip($app['config'], $app["session.store"]);
		});

		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Geoip', 'Riteshptl21\Geoip\Facades\Geoip');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('geoip');
	}

}
