<?php

namespace ArtisanPackUI\Laravel;

use Illuminate\Support\ServiceProvider;

class CodeStyleServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( $this->app->runningInConsole() ) {
			$this->publishes( [
				__DIR__ . '/../../../resources/boost/guidelines/core.blade.php' => resource_path( 'boost/guidelines/artisanpack-ui-code-style.blade.php' ),
			], 'boost-guidelines' );
		}
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register(): void {
		//
	}

}
