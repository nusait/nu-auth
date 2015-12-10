<?php namespace Nusait\NuAuth;

use Illuminate\Support\ServiceProvider;

class NuAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot() {

		$this->publishes([
			__DIR__ . '/../../config/config.php' => config_path('nuauth.php'),
			__DIR__ . '/../../config/ldap.php' => config_path('ldap.php'),
		]);

		$this->app['auth']->extend('nuauth', function($app) {
			$config = $app['config']->get('nuauth');
			$ldapConfig = $app['config']->get('ldap');
		    return new NuAuth($app['hash'], $app['config']['auth.model'], $config, $ldapConfig);
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
