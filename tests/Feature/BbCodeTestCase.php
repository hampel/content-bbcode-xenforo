<?php namespace Tests\Feature;

use Tests\TestCase;

/**
 * Renders BB code through all three HTML renderers the add-on distinguishes between.
 *
 * Named without the Test suffix so PHPUnit does not collect it.
 */
abstract class BbCodeTestCase extends TestCase
{
	/** @var string */
	protected $boardUrl;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setOption('useFriendlyUrls', true);
		$this->boardUrl = $this->app()->options()->boardUrl;
	}

	protected function url($path)
	{
		return $this->boardUrl . '/' . $path;
	}

	/**
	 * The html and emailHtml renderers wrap their output in a bbWrapper div; simpleHtml does not.
	 */
	protected function assertRendersAs($bbCode, $html, $simpleHtml, $emailHtml = null)
	{
		$this->assertBbCode('<div class="bbWrapper">' . $html . '</div>', $bbCode, 'html');
		$this->assertBbCode($simpleHtml, $bbCode, 'simpleHtml');
		$this->assertBbCode('<div class="bbWrapper">' . ($emailHtml ?? $simpleHtml) . '</div>', $bbCode, 'emailHtml');
	}

	/**
	 * A link to this board: classed as internal for html, bare for simpleHtml and emailHtml.
	 *
	 * @param string $url unescaped
	 * @param string $text the expected link text, already escaped
	 */
	protected function assertRendersInternalLink($bbCode, $url, $text)
	{
		$href = htmlspecialchars($url);

		$this->assertRendersAs(
			$bbCode,
			'<a href="' . $href . '" class="link link--internal">' . $text . '</a>',
			'<a href="' . $href . '">' . $text . '</a>'
		);
	}

	/**
	 * The tag is left as typed, because its option or body could not be used.
	 */
	protected function assertRendersUnparsed($bbCode)
	{
		$this->assertRendersAs($bbCode, $bbCode, $bbCode);
	}
}
