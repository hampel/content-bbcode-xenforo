<?php namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Each tag is two halves - a definition in _output/bb_codes/ and a callback in BbCode/ - that
 * nothing else checks against each other.
 */
class BbCodeDefinitionsTest extends TestCase
{
	public static function definitions(): array
	{
		$definitions = [];

		foreach (glob(__DIR__ . '/../../_output/bb_codes/*.json') as $file)
		{
			$id = basename($file, '.json');
			if ($id !== '_metadata')
			{
				$definitions[$id] = [$id, json_decode(file_get_contents($file), true)];
			}
		}

		return $definitions;
	}

	public function test_every_tag_is_defined()
	{
		$this->assertSame(
			['forum', 'post', 'prefix', 'resource', 'search', 'tag', 'thread', 'xfmg'],
			array_keys(self::definitions())
		);
	}

	#[DataProvider('definitions')]
	public function test_callback_exists($id, array $definition)
	{
		$this->assertSame('callback', $definition['bb_code_mode']);
		$this->assertTrue(
			is_callable([$definition['callback_class'], $definition['callback_method']]),
			"[{$id}] names {$definition['callback_class']}::{$definition['callback_method']}, which cannot be called"
		);
	}

	#[DataProvider('definitions')]
	public function test_installed_definition_matches_the_file($id, array $definition)
	{
		$this->assertDatabaseHas('xf_bb_code', [
			'bb_code_id' => $id,
			'addon_id' => 'Hampel/ContentBbCode',
			'callback_class' => $definition['callback_class'],
			'callback_method' => $definition['callback_method'],
			'option_regex' => $definition['option_regex'],
			'has_option' => $definition['has_option'],
		]);
	}
}
