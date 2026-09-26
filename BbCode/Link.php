<?php namespace Hampel\ContentBbCode\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;
use XF\BbCode\Renderer\EmailHtml;
use XF\BbCode\Renderer\SimpleHtml;

/**
 * Builds the anchor every tag renders, so the rules for each renderer live in one place.
 * Not a BB code callback itself.
 */
class Link
{
	/**
	 * @param string $url unescaped
	 * @param string $text already-rendered HTML
	 */
	public static function render($url, $text, array $options, AbstractRenderer $renderer)
	{
		// simple and email output get a bare link - classes, targets and the link proxy mean nothing there
		if ($renderer instanceof SimpleHtml || $renderer instanceof EmailHtml)
		{
			return '<a href="' . htmlspecialchars($url) . '">' . $text . '</a>';
		}

		$formatter = \XF::app()->stringFormatter();
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

		$relAttr = $rels ? ' rel="' . implode(' ', $rels) . '"' : '';

		return '<a href="' . htmlspecialchars($url) . '"' . $targetAttr . $classAttr . $proxyAttr . $relAttr . '>' . $text . '</a>';
	}
}
