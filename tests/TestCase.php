<?php

namespace Tests;

use Hampel\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * @var string $rootDir path to your XenForo root directory, relative to the addon path
	 *
	 * Set $rootDir to '../../../..' if you use a vendor in your addon id (ie <Vendor/AddonId>)
	 * Otherwise, set this to '../../..' for no vendor
	 *
	 * No trailing slash!
	 */
	protected $rootDir = '../../../..';

	/**
	 * @var array $addonsToLoad an array of XenForo addon ids to load
	 *
	 * Only the add-ons named here have their Composer autoloaders, class extensions and code event
	 * listeners loaded. Leaving it empty loads every installed add-on's - and on a forum where another
	 * add-on vendors a different major version of PHPUnit, the run dies before the first test.
	 */
	protected $addonsToLoad = ['Hampel/ContentBbCode'];

	/**
	 * Helper function to load mock data from a file (eg json)
	 * To use, create a "mock" folder relative to the tests folder, eg:
	 * 'src/addons/MyVendor/MyAddon/tests/mock'
	 *
	 * @param $file
	 *
	 * @return false|string
	 */
	protected function getMockData($file)
	{
		return file_get_contents(__DIR__ . '/mock/' . $file);
	}
}
