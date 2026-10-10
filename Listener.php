<?php namespace Hampel\ContentBbCode;

class Listener
{
	/**
	 * Tells the Admin API add-on, where one is installed, which of this add-on's settings are
	 * secrets. It has no options and reads no config.php keys of its own, so the list is empty.
	 *
	 * @param array $declarations
	 */
	public static function adminApiRedaction(array &$declarations)
	{
		$declarations['Hampel/ContentBbCode'] = [];
	}
}
