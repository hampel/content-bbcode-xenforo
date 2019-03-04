<?php namespace Hampel\ContentBbCode\BbCode;

class Forum
{
	public static function renderTagForum($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		if (!empty($tagOption))
		{
			$forum_id = intval($tagOption);
			$text = $renderer->renderSubTree($tagChildren, $options);
		}
		else
		{
			// no option specified - try looking for an integer value in the body of the tag
			if (!isset($tag['children'][0]) || !is_numeric($tag['children'][0]) || $tag['children'][0] < 1)
			{
				// no integer value specified as tag body either - we can't do anything
				return $renderer->renderUnparsedTag($tag, $options);
			}

			// we got an integer - use that as the id
			$forum_id = intval($tag['children'][0]);
			$text = '';
		}

		if ($forum_id > 0)
		{
			$router = \XF::app()->router('public');
			$formatter = \XF::app()->stringFormatter();

			$url = $router->buildLink('canonical:forums', ['node_id' => $forum_id]);

			$linkInfo = $formatter->getLinkClassTarget($url);
			$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';

			if (empty($text))
			{
				$text = $url;
			}

			return '<a href="' . htmlspecialchars($url) . '"' . $classAttr . '>' . $text . '</a>';
		}
		else
		{
			if (!empty($text))
			{
				return $text;
			}
			else
			{
				return $renderer->renderUnparsedTag($tag, $options);
			}
		}
	}
}
