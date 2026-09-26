<?php namespace Tests\Feature;

class PostBbCodeTest extends BbCodeTestCase
{
	public function test_id_in_option_links_the_body()
	{
		$this->assertRendersInternalLink('[post=2]view this post[/post]', $this->url('posts/2/'), 'view this post');
	}

	public function test_id_in_body_links_the_url()
	{
		$url = $this->url('posts/2/');

		$this->assertRendersInternalLink('[post]2[/post]', $url, htmlspecialchars($url));
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		$this->assertRendersInternalLink('[post=2]a & [b]b[/b][/post]', $this->url('posts/2/'), 'a &amp; <b>b</b>');
	}

	public function test_non_numeric_option_is_left_unparsed()
	{
		// option_regex rejects it, so the callback never runs
		$this->assertRendersUnparsed('[post=foo]view this post[/post]');
	}

	public function test_non_numeric_body_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[post]foo[/post]');
	}

	public function test_body_that_is_not_a_positive_integer_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[post]0[/post]');
		$this->assertRendersUnparsed('[post]-3[/post]');
		$this->assertRendersUnparsed('[post]2.5[/post]');
	}
}
