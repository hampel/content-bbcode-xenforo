<?php namespace Tests\Unit;

use Hampel\ContentBbCode\BbCode\Link;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LinkTest extends TestCase
{
	protected function render($url, array $options = [], $type = 'html')
	{
		return Link::render($url, 'text', $options, $this->app()->bbCode()->renderer($type));
	}

	public function test_internal_link_is_classed_internal_with_no_target()
	{
		$url = $this->app()->options()->boardUrl . '/threads/1/';

		$this->assertSame('<a href="' . $url . '" class="link link--internal">text</a>', $this->render($url));
	}

	public function test_external_link_opens_in_a_new_window()
	{
		$this->assertSame(
			'<a href="https://example.com/" target="_blank" class="link link--external" rel="noopener">text</a>',
			$this->render('https://example.com/')
		);
	}

	public function test_external_link_is_nofollow_when_the_context_asks_for_it()
	{
		$this->assertSame(
			'<a href="https://example.com/" target="_blank" class="link link--external" rel="nofollow noopener">text</a>',
			$this->render('https://example.com/', ['noFollowUrl' => true])
		);
	}

	public function test_internal_link_is_never_nofollow()
	{
		$url = $this->app()->options()->boardUrl . '/threads/1/';

		$this->assertSame('<a href="' . $url . '" class="link link--internal">text</a>', $this->render($url, ['noFollowUrl' => true]));
	}

	public function test_url_is_escaped_and_text_is_not()
	{
		$html = Link::render('https://example.com/?a=1&b="2"', '<b>x</b>', [], $this->app()->bbCode()->renderer('simpleHtml'));

		$this->assertSame('<a href="https://example.com/?a=1&amp;b=&quot;2&quot;"><b>x</b></a>', $html);
	}

	public function test_external_link_goes_through_the_link_proxy_when_it_is_enabled()
	{
		$this->setOption('imageLinkProxy', ['images' => false, 'links' => true]);

		$html = $this->render('https://example.com/');

		$this->assertStringContainsString(' data-proxy-href="', $html);
		$this->assertStringContainsString('proxy.php?link=https%3A%2F%2Fexample.com%2F&amp;hash=', $html);
	}

	public function test_link_proxy_is_skipped_when_the_context_asks_for_it()
	{
		$this->setOption('imageLinkProxy', ['images' => false, 'links' => true]);

		$this->assertSame(
			'<a href="https://example.com/" target="_blank" class="link link--external" rel="noopener">text</a>',
			$this->render('https://example.com/', ['noProxy' => true])
		);
	}

	public static function bareRenderers(): array
	{
		return [
			'simpleHtml' => ['simpleHtml'],
			'emailHtml'  => ['emailHtml'],
		];
	}

	#[DataProvider('bareRenderers')]
	public function test_simple_and_email_renderers_get_a_bare_link($type)
	{
		$this->setOption('imageLinkProxy', ['images' => false, 'links' => true]);

		$this->assertSame(
			'<a href="https://example.com/">text</a>',
			$this->render('https://example.com/', ['noFollowUrl' => true], $type)
		);
	}
}
