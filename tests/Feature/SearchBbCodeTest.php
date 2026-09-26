<?php namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;

class SearchBbCodeTest extends BbCodeTestCase
{
	public static function forumSearches(): array
	{
		return [
			'forum'     => ['forum', 'search/search?keywords=foo+bar'],
			'forums'    => ['forums', 'search/search?keywords=foo+bar'],
			'thread'    => ['thread', 'search/search?keywords=foo+bar&search_type=post&grouped=1'],
			'threads'   => ['threads', 'search/search?keywords=foo+bar&search_type=post&grouped=1'],
			'post'      => ['post', 'search/search?keywords=foo+bar&search_type=post'],
			'posts'     => ['posts', 'search/search?keywords=foo+bar&search_type=post'],
			'resource'  => ['resource', 'search/search?keywords=foo+bar&search_type=resource'],
			'resources' => ['resources', 'search/search?keywords=foo+bar&search_type=resource'],
			'media'     => ['media', 'search/search?keywords=foo+bar&search_type=xfmg_media'],
			'gallery'   => ['gallery', 'search/search?keywords=foo+bar&search_type=xfmg_media'],
			'comments'  => ['comments', 'search/search?keywords=foo+bar&search_type=xfmg_comment'],
			'profiles'  => ['profiles', 'search/search?keywords=foo+bar&search_type=profile_post'],
			'tag'       => ['tag', 'tags/foo-bar/'],
			'tags'      => ['tags', 'tags/foo-bar/'],
		];
	}

	#[DataProvider('forumSearches')]
	public function test_term_in_option_searches_the_forum($type, $path)
	{
		$this->assertRendersInternalLink("[search={$type},foo bar]search it[/search]", $this->url($path), 'search it');
	}

	#[DataProvider('forumSearches')]
	public function test_term_in_body_searches_the_forum($type, $path)
	{
		$this->assertRendersInternalLink("[search={$type}]foo bar[/search]", $this->url($path), 'foo bar');
	}

	public function test_no_option_searches_the_whole_forum()
	{
		$this->assertRendersInternalLink('[search]foo bar[/search]', $this->url('search/search?keywords=foo+bar'), 'foo bar');
	}

	public function test_term_is_encoded_once()
	{
		// regression: the term was urlencoded before buildLink() encoded it again, so this searched for "a+%26+b"
		$this->assertRendersInternalLink('[search]a & b[/search]', $this->url('search/search?keywords=a+%26+b'), 'a &amp; b');
	}

	public function test_term_in_option_keeps_its_spaces_and_commas()
	{
		// regression: spaces were stripped from the whole option, so this searched for "foobar"
		$this->assertRendersInternalLink('[search=forum, foo bar, baz ]x[/search]', $this->url('search/search?keywords=foo+bar%2C+baz'), 'x');
	}

	public function test_type_is_case_insensitive()
	{
		$this->assertRendersInternalLink('[search=Post,foo]x[/search]', $this->url('search/search?keywords=foo&search_type=post'), 'x');
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		$this->assertRendersInternalLink('[search=forum,foo]a & [b]b[/b][/search]', $this->url('search/search?keywords=foo'), 'a &amp; <b>b</b>');
	}

	public function test_empty_term_in_option_falls_back_to_the_body()
	{
		$this->assertRendersInternalLink('[search=post,]foo[/search]', $this->url('search/search?keywords=foo&search_type=post'), 'foo');
	}

	public function test_unknown_type_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[search=bogus,foo]a & b[/search]', 'a &amp; b', 'a &amp; b');
	}

	public static function googleSearches(): array
	{
		return [
			'web'    => ['web', 'https://www.google.com/search?q=a%26b+c'],
			'image'  => ['image', 'https://www.google.com/search?q=a%26b+c&tbm=isch'],
			'images' => ['images', 'https://www.google.com/search?q=a%26b+c&tbm=isch'],
			'map'    => ['map', 'https://www.google.com/maps?q=a%26b+c'],
			'maps'   => ['maps', 'https://www.google.com/maps?q=a%26b+c'],
			'video'  => ['video', 'https://www.google.com/search?q=a%26b+c&tbm=vid'],
			'videos' => ['videos', 'https://www.google.com/search?q=a%26b+c&tbm=vid'],
			'news'   => ['news', 'https://www.google.com/search?q=a%26b+c&tbm=nws'],
		];
	}

	#[DataProvider('googleSearches')]
	public function test_google_search_is_an_external_nofollow_link($type, $url)
	{
		// regression: the query carried "&amp;" and was encoded twice - q=a%2526amp%253Bbc
		$href = htmlspecialchars($url);

		$this->assertRendersAs(
			"[search={$type},a&b c]x[/search]",
			'<a href="' . $href . '" target="_blank" class="link link--external" rel="nofollow noopener">x</a>',
			'<a href="' . $href . '">x</a>'
		);
	}

	public function test_site_search_restricts_google_to_this_board()
	{
		$this->setOption('boardUrl', 'https://www.example.com/community');

		$href = htmlspecialchars('https://www.google.com/search?q=foo+bar+site%3Aexample.com');

		$this->assertRendersAs(
			'[search=site,foo bar]x[/search]',
			'<a href="' . $href . '" target="_blank" class="link link--external" rel="nofollow noopener">x</a>',
			'<a href="' . $href . '">x</a>'
		);
	}
}
