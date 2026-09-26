<?php namespace Tests\Feature;

class ForumBbCodeTest extends BbCodeTestCase
{
	public function test_id_in_option_links_the_body()
	{
		$this->assertRendersInternalLink('[forum=1]this forum[/forum]', $this->url('forums/1/'), 'this forum');
	}

	public function test_id_in_body_links_the_url()
	{
		$url = $this->url('forums/1/');

		$this->assertRendersInternalLink('[forum]1[/forum]', $url, htmlspecialchars($url));
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		$this->assertRendersInternalLink('[forum=1]a & [b]b[/b][/forum]', $this->url('forums/1/'), 'a &amp; <b>b</b>');
	}

	public function test_unusable_option_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[forum=abc]a & b[/forum]', 'a &amp; b', 'a &amp; b');
	}

	public function test_body_that_is_not_a_positive_integer_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[forum]foo[/forum]');
		$this->assertRendersUnparsed('[forum]0[/forum]');
	}
}
