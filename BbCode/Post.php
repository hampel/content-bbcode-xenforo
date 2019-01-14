<?php
/**
 *
 */

class ContentBbCode_BbCode_Post
{
	public static function postTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		if (!empty($tag['option']))
		{
			$post_id = intval($tag['option']);
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
			$post_id = intval($tag['children'][0]);
			$text = '';
		}

		if ($post_id > 0)
		{
			$url = XenForo_Link::buildPublicLink('canonical:posts', ['post_id' => $post_id]);

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