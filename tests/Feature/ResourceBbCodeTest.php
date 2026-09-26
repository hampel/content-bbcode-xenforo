<?php namespace Tests\Feature;

class ResourceBbCodeTest extends BbCodeTestCase
{
	public function test_id_in_option_links_the_body()
	{
		$this->assertRendersInternalLink('[resource=1]this resource[/resource]', $this->url('resources/1/'), 'this resource');
	}

	public function test_id_in_body_links_the_url()
	{
		$url = $this->url('resources/1/');

		$this->assertRendersInternalLink('[resource]1[/resource]', $url, htmlspecialchars($url));
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		$this->assertRendersInternalLink('[resource=1]a & [b]b[/b][/resource]', $this->url('resources/1/'), 'a &amp; <b>b</b>');
	}

	public function test_unusable_option_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[resource=abc]a & b[/resource]', 'a &amp; b', 'a &amp; b');
	}

	public function test_body_that_is_not_a_positive_integer_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[resource]foo[/resource]');
		$this->assertRendersUnparsed('[resource]0[/resource]');
	}
}
