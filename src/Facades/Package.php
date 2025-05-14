<?php

namespace ArtisanPackUI\PackageBlueprint\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArtisanPackUI\PackageBlueprint\A11y
 */
class Package extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'package';
	}
}
