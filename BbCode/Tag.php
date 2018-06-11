<?php namespace ThreadPostBbCode\BbCode;

class Tag
{
	public static function renderTag($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		$tags = ['thread', 'post'];

		if (!in_array($tag['tag'], $tags))
		{
			return $renderer->renderUnparsedTag($tag, $options);
		}

		$id = intval($tagOption);

		if (!$id)
		{
			// no option specified - try looking for an integer value in the body of the tag
			if (!isset($tag['children'][0]) || !is_numeric($tag['children'][0]))
			{
				// no integer value specified as tag body either - we can't do anything
				return $renderer->renderUnparsedTag($tag, $options);
			}

			// we got an integer - use that as the id
			$id = intval($tag['children'][0]);

			if ($id < 1 || strval($id) != $tag['children'][0])
			{
				// no positive integer value specified as tag body either - we can't do anything
				return $renderer->renderUnparsedTag($tag, $options);
			}

			$children = ''; // no children - we used the body as the id
		}
		else
		{
			$children = $renderer->renderSubTree($tagChildren, $options);
		}

		$router = \XF::app()->router('public');
		$formatter = \XF::app()->stringFormatter();

		if ($tag['tag'] == 'thread')
		{
			$link = $router->buildLink('canonical:threads', ['thread_id' => $id]);
		}
		else // post
		{
			$link = $router->buildLink('canonical:posts', ['post_id' => $id]);
		}

		if (empty($children)) $children = $link; // using the body as id, so render a full URL to display in the thread instead

		$linkInfo = $formatter->getLinkClassTarget($link);

		$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';
		$targetAttr = $linkInfo['target'] ? " target=\"$linkInfo[target]\"" : '';

		return '<a href="' . htmlspecialchars($link) . '"' . $targetAttr . $classAttr . '>' . $children . '</a>';
	}
}
