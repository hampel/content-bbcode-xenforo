<?php namespace Hampel\ContentBbCode\BbCode;

use XFMG\Entity\MediaItem;

class Xfmg
{
	protected static $_imageTemplate = '<img src="%1$s" class="bbImage" alt="" data-url="%2$s" />';

	public static function renderTagXfmg($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		$parts = explode(',', $tagOption);
		foreach ($parts AS &$part)
		{
			$part = trim($part);
			$part = str_replace(' ', '', $part);
		}

		$type = $renderer->filterString(array_shift($parts), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);

		$type = strtolower($type);
		$id = intval(array_shift($parts));

		$text = $renderer->filterString(array_shift($tagChildren), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
		$text = htmlspecialchars($text);

		$format = "link";
		$router = \XF::app()->router('public');

		switch ($type)
		{
			case "media":
				$url = $router->buildLink('canonical:media', ['media_id' => $id]);
				break;
			case "category":
				$url = $router->buildLink('canonical:media/categories', ['category_id' => $id]);
				break;
			case "album":
				$url = $router->buildLink('canonical:media/albums', ['album_id' => $id]);
				break;
			case "img":
				$url = $router->buildLink('canonical:media/full', ['media_id' => $id]);
				$format = 'image';
				break;
			case "thumb":

				/** @var MediaItem $mediaItem */
				$media = \XF::em()->find('XFMG:MediaItem', $id, ['Attachment']);

				if (!$media)
				{
					return "[Media: {$text}]";
				}
				if (!$media->canView($error))
				{
					// just build a link instead
					$url = $router->buildLink('canonical:xengallery', ['media_id' => $id]);
				}
				else
				{
					$url = $media->getThumbnailUrl();
					$format = 'image';
				}
				break;
			default:
				return $text;
				break;
		}

		if ($format == 'link')
		{
			$formatter = \XF::app()->stringFormatter();
			$linkInfo = $formatter->getLinkClassTarget($url);

			$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';

			return '<a href="' . htmlspecialchars($url) . '"' . $classAttr . '>' . $text . '</a>';
		}
		else
		{
			if (!empty($options['lightbox']))
			{
				return \XF::app()->templater()->renderTemplate('public:bb_code_tag_img', [
					'imageUrl' => $url,
					'validUrl' => $url
				]);
			}
			else
			{
				return sprintf(self::$_imageTemplate,
				               htmlspecialchars($url),
				               htmlspecialchars($url)
				);
			}
		}
	}
}
