<?php

namespace Digitalshopfront\PackageBlueprint;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton( 'package', function ( $app ) {
            return new Package();
        } );
    }
}
