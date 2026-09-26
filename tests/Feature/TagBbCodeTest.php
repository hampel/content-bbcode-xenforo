<?php namespace Tests\Feature;

class TagBbCodeTest extends BbCodeTestCase
{
	public function test_tag_in_option_links_the_body()
	{
		$this->assertRendersInternalLink('[tag=foo bar]the tag[/tag]', $this->url('tags/foo-bar/'), 'the tag');
	}

	public function test_tag_in_body_links_the_tag_name()
	{
		$this->assertRendersInternalLink('[tag]foo bar[/tag]', $this->url('tags/foo-bar/'), 'foo bar');
	}

	public function test_tag_name_in_body_is_escaped_once()
	{
		// regression: filterString() escaped it and htmlspecialchars() escaped it again
		$this->assertRendersInternalLink('[tag]a & b[/tag]', $this->url('tags/a-b/'), 'a &amp; b');
		$this->assertRendersInternalLink('[tag]"quoted"[/tag]', $this->url('tags/quoted/'), '&quot;quoted&quot;');
	}

	public function test_tag_url_is_normalised_the_way_xenforo_normalises_it()
	{
		$this->assertRendersInternalLink('[tag]Ça Va[/tag]', $this->url('tags/ca-va/'), 'Ça Va');
		$this->assertRendersInternalLink('[tag=a & b]x[/tag]', $this->url('tags/a-b/'), 'x');
	}
}
