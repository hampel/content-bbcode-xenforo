<?php
/**
 *
 */

class ContentBbCode_BbCode_Prefix
{
	public static function prefixTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		$parts = explode(',', $tag['option']);
		foreach ($parts AS &$part)
		{
			$part = trim($part);
			$part = str_replace(' ', '', $part);
		}

		$text = $formatter->renderSubTree($tag['children'], $rendererStates);

		$type = preg_replace("/[^a-z]/", '', strtolower(array_shift($parts)));
		$content_id = intval(array_shift($parts));
		$prefix_id = intval(array_shift($parts));

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
				return $formatter->renderTagUnparsed($tag, $rendererStates);
			}
		}

		$url = XenForo_Link::buildPublicLink("canonical:{$route}", [$key => $content_id], ['prefix_id' => $prefix_id]);

		if (empty($text))
		{
			$text = $url;
		}

		return '<a href="' . htmlspecialchars($url) . '" class="internalLink">' . $text . '</a>';
	}
}