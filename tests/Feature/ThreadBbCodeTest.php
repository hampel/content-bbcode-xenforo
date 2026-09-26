<?php namespace Tests\Feature;

class ThreadBbCodeTest extends BbCodeTestCase
{
	public function test_id_in_option_links_the_body()
	{
		$this->assertRendersInternalLink('[thread=2]view this thread[/thread]', $this->url('threads/2/'), 'view this thread');
	}

	public function test_id_in_body_links_the_url()
	{
		$url = $this->url('threads/2/');

		$this->assertRendersInternalLink('[thread]2[/thread]', $url, htmlspecialchars($url));
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		$this->assertRendersInternalLink('[thread=2]a & [b]b[/b][/thread]', $this->url('threads/2/'), 'a &amp; <b>b</b>');
	}

	public function test_non_numeric_option_is_left_unparsed()
	{
		// option_regex rejects it, so the callback never runs
		$this->assertRendersUnparsed('[thread=foo]view this thread[/thread]');
	}

	public function test_non_numeric_body_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[thread]foo[/thread]');
	}

	public function test_body_that_is_not_a_positive_integer_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[thread]0[/thread]');
		$this->assertRendersUnparsed('[thread]-3[/thread]');
		$this->assertRendersUnparsed('[thread]2.5[/thread]');
	}
}
