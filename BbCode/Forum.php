<?php
/**
 *
 */

class ContentBbCode_BbCode_Forum
{
	public static function forumTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		if (!empty($tag['option']))
		{
			$forum_id = intval($tag['option']);
			$text = $formatter->renderSubTree($tag['children'], $rendererStates);
		}
		else
		{
			// no option specified - try looking for an integer value in the body of the tag
			if (!isset($tag['children'][0]) || !is_numeric($tag['children'][0]) || $tag['children'][0] < 1)
			{
				return $formatter->renderTagUnparsed($tag, $rendererStates);
			}

			// we got a number - use that as the id
			$forum_id = intval($tag['children'][0]);
			$text = '';
		}

		if ($forum_id > 0)
		{
			$url = XenForo_Link::buildPublicLink('canonical:forums', ['node_id' => $forum_id]);

			if (empty($text))
			{
				$text = $url;
			}

			return '<a href="' . htmlspecialchars($url) . '" class="internalLink">' . $text . '</a>';
		}
		else
		{
			if (!empty($text))
			{
				return $text;
			}
			else
			{
				return $formatter->renderTagUnparsed($tag, $rendererStates);
			}
		}

	}
}