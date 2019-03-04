<?php namespace Hampel\ContentBbCode\BbCode;

class Resource
{
	public static function renderTagResource($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		if (!empty($tagOption))
		{
			$resource_id = intval($tagOption);
			$text = $renderer->renderSubTree($tagChildren, $options);
		}
		else
		{
			// no option specified - try looking for an integer value in the body of the tag
			if (!isset($tagChildren[0]) || !is_numeric($tagChildren[0]) || $tagChildren[0] < 1)
			{
				return $renderer->renderUnparsedTag($tag, $options);
			}

			// we got a number - use that as the id
			$resource_id = intval($tagChildren[0]);
			$text = '';
		}

		if ($resource_id > 0)
		{
			$router = \XF::app()->router('public');
			$url = $router->buildLink('canonical:resources', ['resource_id' => $resource_id]);

			$formatter = \XF::app()->stringFormatter();
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
