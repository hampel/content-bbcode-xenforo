<?php namespace Hampel\ContentBbCode\BbCode;

class Tag
{
	public static function renderTagTag($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		if (!empty($tagOption))
		{
			$term = $tagOption;
			$text = $renderer->renderSubTree($tagChildren, $options);
		}
		else
		{
			$term = $renderer->renderSubTreePlain($tagChildren);
			$text = $text = $renderer->filterString($term, ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
			$text = htmlspecialchars($text);
		}

		/** @var \XF\Repository\Tag $tagRepo */
		$tagRepo = \XF::repository('XF:Tag');
		$tag = $tagRepo->normalizeTag($term);
		$tag = preg_replace('/[^a-zA-Z0-9_ -]/', '', utf8_romanize(utf8_deaccent($tag)));
		$tag = preg_replace('/[ -]+/', '-', $tag);

		$router = \XF::app()->router('public');
		$url = $router->buildLink('canonical:tags', ['tag_url' => $tag]);

		$formatter = \XF::app()->stringFormatter();
		$linkInfo = $formatter->getLinkClassTarget($url);

		$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';

		return '<a href="' . htmlspecialchars($url) . '"' . $classAttr . '>' . $text . '</a>';
	}
}
