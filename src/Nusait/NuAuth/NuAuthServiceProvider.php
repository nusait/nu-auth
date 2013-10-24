<?php namespace Nusait\NuAuth;

use Illuminate\Support\ServiceProvider;

class NuAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	public function boot() {
		$this->package('nusait/nu-auth');
		$this->app['auth']->extend('nuauth', function($app) {
			$autoCreate = $app['config']->get('nu-auth::config');
		    return new NuAuth($app['hash'], $app['config']['auth.model'], $autoCreate);
		});
	}
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}