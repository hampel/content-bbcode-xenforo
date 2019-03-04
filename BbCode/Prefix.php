<?php namespace Hampel\ContentBbCode\BbCode;

class Prefix
{
	public static function renderTagPrefix($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		$parts = explode(',', $tagOption);
		foreach ($parts AS &$part)
		{
			$part = trim($part);
			$part = str_replace(' ', '', $part);
		}

		$type = strtolower($renderer->filterString(array_shift($parts), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options));
		$content_id = intval(array_shift($parts));
		$prefix_id = intval(array_shift($parts));

		$text = $renderer->renderSubTree($tagChildren, $options);
		$text = htmlspecialchars($text);

		$map = [
			'forums' => ['route' => 'forums', 'key' => 'node_id'],
			'forum' => ['route' => 'forums', 'key' => 'node_id'],
			'resource' => ['route' => 'resources' . ($content_id > 0 ? '/categories' : ''), 'key' => 'resource_category_id'],
			'resources' => ['route' => 'resources' . ($content_id > 0 ? '/categories' : ''), 'key' => 'resource_category_id'],
		];

		if (!array_key_exists($type, $map))
		{
			return $text;
		}

		$route = $map[$type]['route'];
		$key = $map[$type]['key'];

		if ($prefix_id < 1)
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

		$router = \XF::app()->router('public');
		$url = $router->buildLink("canonical:{$route}", [$key => $content_id], ['prefix_id' => $prefix_id]);
		$formatter = \XF::app()->stringFormatter();
		$linkInfo = $formatter->getLinkClassTarget($url);

		$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';

		if (empty($text))
		{
			$text = $url;
		}

		return '<a href="' . htmlspecialchars($url) . '"' . $classAttr . '>' . $text . '</a>';
	}
}
