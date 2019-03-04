<?php namespace Hampel\ContentBbCode\BbCode;

use XF\Repository\Tag;

class Search
{
	public static function renderTagSearch($tagChildren, $tagOption, $tag, array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		$options['noFollowUrl'] = true;

		if (empty($tagOption))
		{
			$term = $renderer->renderSubTreePlain($tagChildren);
			$text = $renderer->filterString($term, ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
			$text = htmlspecialchars($text);
			$type = 'forum';
		}
		else
		{
			$parts = explode(',', $tagOption);
			foreach ($parts AS &$part)
			{
				$part = trim($part);
				$part = str_replace(' ', '', $part);
			}

			if (count($parts) == 1)
			{
				$term = $renderer->renderSubTreePlain($tagChildren);

				$text = $renderer->filterString($term, ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
				$text = htmlspecialchars($text);

				$type = $renderer->filterString(array_shift($parts), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
			}
			else
			{
				$text = $renderer->renderSubTree($tagChildren, $options);
				$type = $renderer->filterString(array_shift($parts), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);

				$term = $renderer->filterString(array_shift($parts), ['stopSmilies' => 1, 'stopBreakConversion' => 1] + $options);
			}
		}

		$term = urlencode($term);
		$router = \XF::app()->router('public');
		$formatter = \XF::app()->stringFormatter();

		// TODO: add more descriptive text to tags without body text, eg "Search Results for Query: <keyword>"
		// TODO: custom search options

		switch ($type)
		{
			case 'forum':
			case 'forums':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term]);
				break;
			case 'thread':
			case 'threads':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'post', 'grouped' => 1]);
				break;
			case 'post':
			case 'posts':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'post']);
				break;
			case 'resource':
			case 'resources':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'resource']);
				break;
			case 'media':
			case 'gallery':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'xfmg_media']);
				break;
			case 'comments':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'xfmg_comment']);
				break;
			case 'profiles':
				$url = $router->buildLink('canonical:search/search', [], ['keywords' => $term, 'search_type' => 'profile_post']);
				break;
			case 'tag':
			case 'tags':

				/** @var Tag $tagRepo */
				$tagRepo = \XF::repository('XF:Tag');
				$term = $tagRepo->normalizeTag($term);
				$term = preg_replace('/[^a-zA-Z0-9_ -]/', '', utf8_romanize(utf8_deaccent($term)));
				$term = preg_replace('/[ -]+/', '-', $term);

				$url = $router->buildLink('canonical:tags', ['tag_url' => $term]);
				break;
			case 'site':
				$boardUrl = parse_url(\XF::options()->boardUrl);
				$term .= " site:" . preg_replace('#^www\.(.+\.)#i', '$1', $boardUrl['host']);
				$url = 'https://www.google.com/search?' . http_build_query(['q' => $term]);
				break;
			case 'web':
				$url = 'https://www.google.com/search?' . http_build_query(['q' => $term]);
				break;
			case 'image':
			case 'images':
				$url = 'https://www.google.com/search?' . http_build_query(['q' => $term, 'tbm' => 'isch']);
				break;
			case 'map':
			case 'maps':
				$url = 'https://www.google.com/maps?' . http_build_query(['q' => $term]);
				break;
			case 'video':
			case 'videos':
				$url = 'https://www.google.com/search?' . http_build_query(['q' => $term, 'tbm' => 'vid']);
				break;
			case 'news':
				$url = 'https://www.google.com/search?' . http_build_query(['q' => $term, 'tbm' => 'nws']);
				break;
			default:
				return $text;
		}

		$linkInfo = $formatter->getLinkClassTarget($url);
		$rels = [];

		$classAttr = $linkInfo['class'] ? " class=\"$linkInfo[class]\"" : '';
		$targetAttr = $linkInfo['target'] ? " target=\"$linkInfo[target]\"" : '';

		if (!$linkInfo['trusted'] && !empty($options['noFollowUrl']))
		{
			$rels[] = 'nofollow';
		}

		if ($linkInfo['target'])
		{
			$rels[] = 'noopener';
		}

		$proxyAttr = '';
		if (empty($options['noProxy']))
		{
			$proxyUrl = $formatter->getProxiedUrlIfActive('link', $url);
			if ($proxyUrl)
			{
				$proxyAttr = ' data-proxy-href="' . htmlspecialchars($proxyUrl) . '"';
			}
		}

		if ($rels)
		{
			$relAttr = ' rel="' . implode(' ', $rels) . '"';
		}
		else
		{
			$relAttr = '';
		}

		return '<a href="' . htmlspecialchars($url) . '"' . $targetAttr . $classAttr . $proxyAttr . $relAttr . '>' . $text . '</a>';
	}
}
