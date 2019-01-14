<?php

class ContentBbCode_BbCode_Tag
{
	public static function tagTag(array $tag, array $rendererStates, XenForo_BbCode_Formatter_Base $formatter)
	{
		if (!empty($tag['option']))
		{
			$tagId = $tag['option'];
			$text = $formatter->renderSubTree($tag['children'], $rendererStates);
		}
		else
		{
			$tagId = $formatter->stringifyTree($tag['children']);

			$text = XenForo_Helper_String::censorString($tagId);
			$text = htmlspecialchars($text);
		}

		/** @var XenForo_Model_Tag $tagModel */
		$tagModel = XenForo_Model::create('XenForo_Model_Tag');
		$tagId = $tagModel->normalizeTag($tagId);
		$tagId = preg_replace('/[^a-zA-Z0-9_ -]/', '', utf8_romanize(utf8_deaccent($tagId)));
		$tagId = preg_replace('/[ -]+/', '-', $tagId);

		$link = XenForo_Link::buildPublicLink('canonical:tags', array('tag_url' => $tagId));

		return '<a href="' . htmlspecialchars($link) . '" class="internalLink">' . $text . '</a>';
	}
}
