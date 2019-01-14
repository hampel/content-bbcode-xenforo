<?php

class ContentBbCode_BbCode_Xfmg
{
	/**
	 * String used for outputting [IMG] tags. Will be passed the following params:
	 * 1	URL
	 * 2	Additional CSS classes
	 *
	 * @var string
	 */
	protected static $_imageTemplate = '<img src="%1$s" class="bbCodeImage%2$s" alt="[&#x200B;IMG]" data-url="%3$s" />';

	public static function xfmgTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		$parts = explode(',', $tag['option']);
		foreach ($parts AS &$part)
		{
			$part = trim($part);
			$part = str_replace(' ', '', $part);
		}

		$type = $formatter->filterString(
			array_shift($parts),
			array_merge(
				$rendererStates, [
					'stopSmilies' => true,
					'stopLineBreakConversion' => true
				]
			)
		);

		$type = strtolower($type);
		$id = array_shift($parts);
		$text = $formatter->stringifyTree($tag['children']);
		$format = "link";

		switch ($type)
		{
			case "media":
				$url = XenForo_Link::buildPublicLink('canonical:xengallery', ['media_id' => $id]);
				break;
			case "category":
				$url = XenForo_Link::buildPublicLink('canonical:xengallery/categories', ['category_id' => $id]);
				break;
			case "album":
				$url = XenForo_Link::buildPublicLink('canonical:xengallery/albums', ['album_id' => $id]);
				break;
			case "img":
				$url = XenForo_Link::buildPublicLink('canonical:xengallery/full', ['media_id' => $id]);
				$format = 'image';
				break;
			case "thumb":
				/** @var XenGallery_Model_Media $model */
				$model = XenForo_Model::create('XenGallery_Model_Media');
				$fetchOptions = [
					'join' => XenGallery_Model_Media::FETCH_ATTACHMENT
				];
				$media = $model->getMediaById($id, $fetchOptions);
				if (!$media)
				{
					return "[Media: {$text}]";
				}
				if (!$model->canViewMediaItem($media))
				{
					// just build a link instead
					$url = XenForo_Link::buildPublicLink('canonical:xengallery', ['media_id' => $id]);
				}
				else
				{
					$url = $model->getMediaThumbnailUrl($media);
					$format = 'image';
				}
				break;
			default:
				return $text;
				break;
		}

		if ($format == 'link')
		{
			return '<a href="' . htmlspecialchars($url) . '" class="internalLink">' . $text . '</a>';
		}
		else
		{
			if ($formatter instanceof XenForo_BbCode_Formatter_Text)
			{
				return "[Media: {$text}]";
			}

			if ($rendererStates['disableProxying'])
			{
				$imageUrl = $url;
			}
			else
			{
				$imageUrl = self::_handleImageProxyOption($url, $rendererStates);
			}

			return sprintf(self::$_imageTemplate,
				htmlspecialchars($imageUrl),
				$rendererStates['lightBox'] ? ' LbImage' : '',
				htmlspecialchars($url)
			);
		}
	}

	/**
	 * Pass an image URL to the image proxy system if appropriate
	 *
	 * @param $url
	 *
	 * @return string
	 */
	protected static function _handleImageProxyOption($url)
	{
		list($class, $target, $type, $schemeMatch) = XenForo_Helper_String::getLinkClassTarget($url);

		if (($type == 'external' || !$schemeMatch))
		{
			$options = XenForo_Application::getOptions();
			if (!empty($options->imageLinkProxy['images']))
			{
				$url = self::_generateProxyLink('image', $url);
			}
		}

		return $url;
	}

	protected static function _generateProxyLink($proxyType, $url)
	{
		$hash = hash_hmac('md5', $url,
			XenForo_Application::getConfig()->globalSalt . XenForo_Application::getOptions()->imageLinkProxyKey
		);
		return 'proxy.php?' . $proxyType . '=' . urlencode($url) . '&hash=' . $hash;
	}
}




