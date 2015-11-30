<?php namespace Tacone\RapydDataTree;

use Illuminate\Support\ServiceProvider;

class RapydDataTreeServiceProvider extends ServiceProvider {

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
		$this->package('tacone/rapyd-datatree', 'datatree');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->booting(function () {
			$loader  =  \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('DataTree', 'Tacone\RapydDataTree\DataTree');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('datatree');
	}

}
