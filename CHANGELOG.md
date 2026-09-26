CHANGELOG
=========

2.1.0 (2026-09-26)
------------------

* requires XenForo 2.2.0 or later - XenForo 2.0 and 2.1 are no longer supported
* fix a `TypeError` which prevented a post being saved when its `[xfmg]` link text began with formatting, eg `[xfmg=media,1][b]text[/b][/xfmg]`
* fix `[search]` search terms: a term given in the option keeps its spaces, terms are no longer encoded twice, and Google searches no longer contain HTML entities
* fix `[search=tag]` links, which were built from the encoded search term
* fix the `[xfmg=thumb]` fallback link shown to visitors who cannot view the media item
* fix `[prefix]` and `[tag]` link text being escaped twice
* `[xfmg]` link text now renders nested BB code
* a search term given in the `[search]` option may now contain commas
* every tag now renders a plain link in signatures and emails (the simple and email renderers), as `[thread]` and `[post]` already did

2.0.3 (2025-12-11)
------------------

* run enqueuePostUpgradeCleanUp during upgrades if we're running XF2.3+

2.0.2 (2024-07-10)
------------------

* check whether XFMG installed before doing lookup on media item for BBCode rendering

2.0.1 (2020-06-09)
------------------

* change legacy_addon_id to ContentBbCode so we can upgrade from v1.5 version

2.0.0 (2019-03-05)
------------------

* migrated to XF v2.0
* added new SEARCH options for XFMG comments and user profiles

1.5.0 (2019-01-14)
-------------------

* changed addon id to ContentBbCode and added support for linking to additional content types
* added SEARCH bbcode
* added TAG bbcode
* added XFMG bbcode
* added FORUM bbcode
* added PREFIX bbcode
* added RESOURCE bbcode

1.0.1 (2018-09-05)
-------------------

* remove link class and target attributes when using SimpleHtml or EmailHtml based renderers

1.0.0a (2018-08-14)
-------------------

* first working version (re-release, new addon_id)

1.0.0 (2018-06-11)
------------------

* first working version
