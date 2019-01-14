<?php

class ContentBbCode_BbCode_Search
{
	public static function searchTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		$rendererStates = array_merge($rendererStates, [
			'noFollowDefault' => true,
		]);

		if (empty($tag['option']))
		{
			$term = $formatter->stringifyTree($tag['children']);

			$text = XenForo_Helper_String::censorString($term);
			$text = htmlspecialchars($text);
			$type = 'forum';
		}
		else
		{
			$parts = explode(';', $tag['option']);
			foreach ($parts AS &$part)
			{
				$part = trim($part);
				$part = str_replace(' ', '', $part);
			}

			if (count($parts) == 1)
			{
				$term = $formatter->stringifyTree($tag['children']);

				$text = XenForo_Helper_String::censorString($term);
				$text = htmlspecialchars($text);

				$type = $formatter->filterString(array_shift($parts),
					array_merge($rendererStates, [
						'stopSmilies' => true,
						'stopLineBreakConversion' => true
					])
				);

			}
			else
			{
				$text = $formatter->renderSubTree($tag['children'], $rendererStates);

				$type = $formatter->filterString(array_shift($parts),
					array_merge($rendererStates, [
						'stopSmilies' => true,
						'stopLineBreakConversion' => true
					])
				);

				$term = $formatter->filterString(array_shift($parts),
					array_merge($rendererStates, [
						'stopSmilies' => true,
						'stopLineBreakConversion' => true
					])
				);
			}
		}

		$term = urlencode($term);

		// TODO: add more descriptive text to tags without body text, eg "Search Results for Query: <keyword>"
		// TODO: custom search options

		switch ($type)
		{
			case 'forum':
			case 'forums':
				$url = XenForo_Link::buildPublicLink('canonical:search/search', [], ['keywords' => $term]);
				$linktype = 'internal';
				break;
			case 'thread':
			case 'threads':
				$url = XenForo_Link::buildPublicLink('canonical:search/search', [], ['keywords' => $term, 'type' => 'thread']);
				$linktype = 'internal';
				break;
			case 'post':
			case 'posts':
				$url = XenForo_Link::buildPublicLink('canonical:search/search', [], ['keywords' => $term, 'type' => 'post']);
				$linktype = 'internal';
				break;
			case 'resource':
			case 'resources':
				$url = XenForo_Link::buildPublicLink('canonical:search/search', [], ['keywords' => $term, 'type' => 'resource_update']);
				$linktype = 'internal';
				break;
			case 'media':
				$url = XenForo_Link::buildPublicLink('canonical:search/search', [], ['keywords' => $term, 'type' => 'xengallery_media']);
				$linktype = 'internal';
				break;
			case 'tag':
			case 'tags':
				/** @var XenForo_Model_Tag $tagModel */
				$tagModel = XenForo_Model::create('XenForo_Model_Tag');
				$term = $tagModel->normalizeTag($term);
				$term = preg_replace('/[^a-zA-Z0-9_ -]/', '', utf8_romanize(utf8_deaccent($term)));
				$term = preg_replace('/[ -]+/', '-', $term);

				$url = XenForo_Link::buildPublicLink('canonical:tags', ['tag_url' => $term]);
				$linktype = 'internal';
				break;
			case 'site':
				$term .= " site:" . self::getBoardDomain();
				$url = 'https://www.google.com/search?' . XenForo_Link::buildQueryString(['q' => $term]);
				$linktype = 'external';
				break;
			case 'web':
				$url = 'https://www.google.com/search?' . XenForo_Link::buildQueryString(['q' => $term]);
				$linktype = 'external';
				break;
			case 'image':
			case 'images':
				$url = 'https://www.google.com/search?' . XenForo_Link::buildQueryString(['q' => $term, 'tbm' => 'isch']);
				$linktype = 'external';
				break;
			case 'map':
			case 'maps':
				$url = 'https://www.google.com/maps?' . XenForo_Link::buildQueryString(['q' => $term]);
				$linktype = 'external';
				break;
			case 'video':
			case 'videos':
				$url = 'https://www.google.com/search?' . XenForo_Link::buildQueryString(['q' => $term, 'tbm' => 'vid']);
				$linktype = 'external';
				break;
			case 'news':
				$url = 'https://www.google.com/search?' . XenForo_Link::buildQueryString(['q' => $term, 'tbm' => 'nws']);
				$linktype = 'external';
				break;
			default:
				return $text;
		}

		if ($linktype == 'internal')
		{
			$target = '';
			$class = 'internalLink';
			$noFollow = '';
		}
		else
		{
			$target = '_blank';
			$class = 'externalLink';
			$noFollow = (empty($rendererStates['noFollowDefault']) ? '' : ' rel="nofollow"');
		}

		// TODO proxy links

		$href = XenForo_Helper_String::censorString($url);
		if ($rendererStates['disableProxying'])
		{
			$proxyHref = false;
		}
		else
		{
			$proxyHref = self::handleLinkProxyOption($href, $linktype);
		}

		$proxyAttr = '';
		if ($proxyHref)
		{
			$proxyAttr = ' data-proxy-href="' . htmlspecialchars($proxyHref) . '"';
			$class .= ' ProxyLink';
		}

		$class = $class ? " class=\"$class\"" : '';
		$target = $target ? " target=\"$target\"" : '';

		return '<a href="' . htmlspecialchars($url) . '"' . $target . $class . $proxyAttr . $noFollow . '>' . $text . '</a>';
	}

	protected static function getBoardDomain()
	{
		$url = parse_url(XenForo_Application::get('options')->boardUrl);

		return preg_replace('#^www\.(.+\.)#i', '$1', $url['host']);
	}

	protected static function handleLinkProxyOption($url, $linkType)
	{
		if ($linkType == 'external')
		{
			$options = XenForo_Application::getOptions();

			if (!empty($options->imageLinkProxy['links']))
			{
				return self::generateProxyLink('link', $url);
			}
		}

		return false;
	}

	protected static function generateProxyLink($proxyType, $url)
	{
		$hash = hash_hmac('md5', $url,
			XenForo_Application::getConfig()->globalSalt . XenForo_Application::getOptions()->imageLinkProxyKey
		);
		return 'proxy.php?' . $proxyType . '=' . urlencode($url) . '&hash=' . $hash;
	}
}
