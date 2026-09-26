<?php namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;

class XfmgBbCodeTest extends BbCodeTestCase
{
	public static function links(): array
	{
		return [
			'media'    => ['media', 'media/1/'],
			'category' => ['category', 'media/categories/1/'],
			'album'    => ['album', 'media/albums/1/'],
		];
	}

	#[DataProvider('links')]
	public function test_link_types_link_the_gallery_page($type, $path)
	{
		$this->assertRendersInternalLink("[xfmg={$type},1]gallery[/xfmg]", $this->url($path), 'gallery');
	}

	public function test_type_is_case_insensitive()
	{
		$this->assertRendersInternalLink('[xfmg=Album, 1]gallery[/xfmg]', $this->url('media/albums/1/'), 'gallery');
	}

	public function test_link_text_keeps_its_formatting_and_is_escaped_once()
	{
		// regression: text starting with a nested tag made the first child an array, and filterString() threw a TypeError
		$this->assertRendersInternalLink('[xfmg=media,1][b]bold[/b] text[/xfmg]', $this->url('media/1/'), '<b>bold</b> text');

		// regression: plain text was escaped by filterString() and again by htmlspecialchars()
		$this->assertRendersInternalLink('[xfmg=media,1]a & [b]b[/b][/xfmg]', $this->url('media/1/'), 'a &amp; <b>b</b>');
	}

	public function test_unknown_type_renders_the_text_without_a_link()
	{
		$this->assertRendersAs('[xfmg=video,1]a & b[/xfmg]', 'a &amp; b', 'a &amp; b');
	}

	public function test_img_renders_the_full_size_image()
	{
		$this->fakesErrors();

		$src = htmlspecialchars($this->url('media/1/full'));
		$img = '<img src="' . $src . '" class="bbImage" alt="" data-url="' . $src . '" />';

		$this->assertBbCode($img, '[xfmg=img,1][/xfmg]', 'simpleHtml');
		$this->assertBbCode('<div class="bbWrapper">' . $img . '</div>', '[xfmg=img,1][/xfmg]', 'emailHtml');

		// html renders through the lightbox template, whose markup belongs to XenForo
		$html = $this->app()->bbCode()->render('[xfmg=img,1][/xfmg]', 'html', 'unitTest', null);

		$this->assertSee($html, 'src="' . $src . '"', false);
		$this->assertSee($html, 'class="bbImage', false);
		$this->assertNoTemplateErrors();
	}

	public function test_thumb_renders_the_thumbnail_when_the_visitor_can_view_it()
	{
		$this->mockMediaItem(5, true, 'https://example.com/thumb.jpg');

		$img = '<img src="https://example.com/thumb.jpg" class="bbImage" alt="" data-url="https://example.com/thumb.jpg" />';

		$this->assertBbCode($img, '[xfmg=thumb,5]t[/xfmg]', 'simpleHtml');
	}

	public function test_thumb_falls_back_to_a_link_when_the_visitor_cannot_view_it()
	{
		// regression: the fallback used the XF1 route "xengallery", which rendered index.php?xengallery
		$this->mockMediaItem(5, false);

		$this->assertRendersInternalLink('[xfmg=thumb,5]t[/xfmg]', $this->url('media/5/'), 't');
	}

	public function test_thumb_of_a_missing_item_renders_a_placeholder()
	{
		$this->mockMediaItem(5, null);

		$this->assertRendersAs('[xfmg=thumb,5]a & b[/xfmg]', '[Media: a &amp; b]', '[Media: a &amp; b]');
	}

	public function test_thumb_without_the_media_gallery_renders_a_placeholder()
	{
		// a viewable item, so only the add-on check can produce the placeholder
		$this->mockMediaItem(5, true, 'https://example.com/thumb.jpg');

		$this->swap('addon.cache', function ()
		{
			return ['XF' => \XF::$versionId];
		});

		$this->assertRendersAs('[xfmg=thumb,5]t[/xfmg]', '[Media: t]', '[Media: t]');
	}

	/**
	 * @param bool|null $canView null for an item that does not exist
	 */
	protected function mockMediaItem($id, $canView, $thumbnailUrl = null)
	{
		$media = null;

		if ($canView !== null)
		{
			$media = \Mockery::mock(\XFMG\Entity\MediaItem::class);
			$media->shouldReceive('canView')->andReturn($canView);
			$media->shouldReceive('getThumbnailUrl')->andReturn($thumbnailUrl);
		}

		$this->mockFinder('XFMG:MediaItem', function ($finder) use ($id, $media)
		{
			$finder->shouldReceive('whereId')->with($id)->andReturnSelf();
			$finder->shouldReceive('with')->andReturnSelf();
			$finder->shouldReceive('fetchOne')->andReturn($media);
		});
	}
}
