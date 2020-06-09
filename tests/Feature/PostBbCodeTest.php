<?php namespace Tests\Feature;

use Tests\TestCase;

class PostBbCodeTest extends TestCase
{
	protected $boardUrl;

	protected function setUp() : void
	{
		parent::setUp();

		$options = $this->app()->options();

		$this->boardUrl = $options['boardUrl'];
	}

	// ------------------------------------------------

	public function test_bbcode_post_bad_id()
	{
		$bbCode = '[post=foo]view this post[/post]';

		$expectedHtml = '<div class="bbWrapper">[post=foo]view this post[/post]</div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'html');

		$expectedHtml = '[post=foo]view this post[/post]';
		$this->assertBbCode($expectedHtml, $bbCode, 'simpleHtml');

		$expectedHtml = '<div class="bbWrapper">[post=foo]view this post[/post]</div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'emailHtml');
	}

	public function test_bbcode_post_id_in_tag()
	{
		$bbCode = '[post=2]view this post[/post]';

		$expectedHtml = '<div class="bbWrapper"><a href="' . $this->boardUrl . '/posts/2/" target="_blank" class="link link--external">view this post</a></div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'html');

		$expectedHtml = '<a href="' . $this->boardUrl . '/posts/2/">view this post</a>';
		$this->assertBbCode($expectedHtml, $bbCode, 'simpleHtml');

		$expectedHtml = '<div class="bbWrapper"><a href="' . $this->boardUrl . '/posts/2/">view this post</a></div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'emailHtml');
	}

	public function test_bbcode_post_id_in_body()
	{
		$bbCode = '[post]2[/post]';

		$expectedHtml = '<div class="bbWrapper"><a href="' . $this->boardUrl . '/posts/2/" target="_blank" class="link link--external">' . $this->boardUrl . '/posts/2/</a></div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'html');

		$expectedHtml = '<a href="' . $this->boardUrl . '/posts/2/">' . $this->boardUrl . '/posts/2/</a>';
		$this->assertBbCode($expectedHtml, $bbCode, 'simpleHtml');

		$expectedHtml = '<div class="bbWrapper"><a href="' . $this->boardUrl . '/posts/2/">' . $this->boardUrl . '/posts/2/</a></div>';
		$this->assertBbCode($expectedHtml, $bbCode, 'emailHtml');
	}
}
