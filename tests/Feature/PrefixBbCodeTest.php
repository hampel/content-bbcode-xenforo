<?php namespace Tests\Feature;

class PrefixBbCodeTest extends BbCodeTestCase
{
	public function test_forum_prefix_links_the_forum_filtered_by_prefix()
	{
		$this->assertRendersInternalLink('[prefix=forum,1,2]prefixed[/prefix]', $this->url('forums/1/?prefix_id=2'), 'prefixed');
		$this->assertRendersInternalLink('[prefix=forums,1,2]prefixed[/prefix]', $this->url('forums/1/?prefix_id=2'), 'prefixed');
	}

	public function test_resource_prefix_without_a_category_links_all_resources()
	{
		$this->assertRendersInternalLink('[prefix=resource,0,1]prefixed[/prefix]', $this->url('resources/?prefix_id=1'), 'prefixed');
	}

	public function test_resource_prefix_with_a_category_links_the_category()
	{
		$this->assertRendersInternalLink('[prefix=resources,2,3]prefixed[/prefix]', $this->url('resources/categories/2/?prefix_id=3'), 'prefixed');
	}

	public function test_type_is_case_insensitive_and_ignores_spaces()
	{
		$this->assertRendersInternalLink('[prefix=Forum, 1, 2]prefixed[/prefix]', $this->url('forums/1/?prefix_id=2'), 'prefixed');
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		// regression: renderSubTree() output was passed through htmlspecialchars() again
		$this->assertRendersInternalLink('[prefix=forum,1,2]a & [b]b[/b][/prefix]', $this->url('forums/1/?prefix_id=2'), 'a &amp; <b>b</b>');
	}

	public function test_unknown_type_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[prefix=thread,1,2]a & b[/prefix]', 'a &amp; b', 'a &amp; b');
	}

	public function test_missing_prefix_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[prefix=forum,1]a & b[/prefix]', 'a &amp; b', 'a &amp; b');
	}

	public function test_missing_prefix_with_no_text_is_left_unparsed()
	{
		$this->assertRendersUnparsed('[prefix=forum,1,0][/prefix]');
	}
}
