# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Content BBCode is a XenForo 2 add-on (`Hampel/ContentBbCode`) that adds custom BB codes linking
to forum content by ID or term: `[thread]`, `[post]`, `[forum]`, `[resource]`, `[tag]`, `[prefix]`,
`[search]` and `[xfmg]`. `README.md` documents the syntax of each and the HTML it renders; read it
before changing a tag's behaviour.

## Architecture

**There are no class extensions or templates, and one code event listener that renders nothing.**
Each tag is a XenForo custom BB code in callback mode, and the whole add-on is two halves that
must agree:

- `_output/bb_codes/<tag>.json` — the definition: `has_option`, `option_regex`, flags such as
  `disable_autolink`, and `callback_class`/`callback_method`.
- `BbCode/<Tag>.php` — one static `renderTag<Tag>()` per tag, with XF's callback signature
  `($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)`.

The title, description, example and output text for each tag shown in the admin UI and the BB
code help page are phrases: `_output/phrases/custom_bb_code_{title,desc,example,output}.<tag>.txt`.

Conventions every callback follows, and a new or changed one should too:

- **Links are built with `canonical:` routes** through the public router, so stored content never
  embeds the board URL. That independence from the site URL is the add-on's reason for existing.
- **An option that fails to parse renders the tag unparsed** (`$renderer->renderUnparsedTag()`)
  rather than throwing or emitting an empty link. `[thread]`, `[post]`, `[forum]` and `[resource]`
  also accept the ID as the tag body, and then display the full URL as the link text.
- **Link text is rendered with `renderSubTree()`, which escapes it once.** Escaping its result
  again, or passing a child node to `filterString()`, is the bug this add-on has shipped before.
- **Every anchor comes from `BbCode\Link::render()`**, which is a helper, not a tag. It gives the
  `SimpleHtml` and `EmailHtml` renderers a bare `<a href>`, and the `html` renderer the class,
  target, `rel` and link-proxy attributes XF's own `[url]` would get.
- **XFMG and XFRM are optional.** `Xfmg` checks `addon.cache` for `XFMG` before looking up a
  media item; any new lookup against an optional add-on's entities needs the same guard.

`Setup.php` does nothing except enqueue XF 2.3's post-upgrade cleanup.

`Listener::adminApiRedaction()` answers the `hampel_admin_api_redaction` code event, declaring
which of the add-on's settings are secrets to an Admin API add-on where one is installed. The
list is empty because the add-on has no options and reads no `config.php` keys of its own.
**Adding an option or a `config.php` key means updating that declaration in the same commit**;
`tests/Unit/SecretsDeclarationTest.php` fails on a credential-named option nobody has decided
about. The event is not a dependency: on a forum where nothing defines it, it never fires.

### No `config` or `validate` command, deliberately

**The add-on has nothing to report that the forum does not already show**, so it adds no CLI
commands (decided 2026-10-11). Each of the three questions that would earn them is a "no":

- **no setting is opaque** — the add-on has no options at all;
- **nothing runs unattended** — no cron entries, no jobs of its own and no outbound calls; the one
  job it queues is XenForo's own file clean-up, once after an upgrade;
- **the admin panel already has it on one screen** — the custom BB codes list shows all eight tags,
  each with its active toggle.

Reopen this if the add-on gains an option holding a credential, an endpoint or a path, or any
scheduled or queued work of its own.

### The floor is XenForo 2.2, and that constrains the code two ways

`addon.json` requires XF 2.2.0+ and declares no PHP version of its own, so XF 2.2's PHP 7.0
minimum applies. Runtime code in `BbCode/` and `Setup.php` must therefore:

- **run on PHP 7.0** — no nullable or `void` types, no typed properties, nothing later;
- **name core classes by their pre-2.3 names** (`XF\Repository\Tag`, not `TagRepository`). XF 2.3
  aliases the old names forward; XF 2.2 has no alias for the new ones and fatals.

The test suite needs PHP 8.3 (framework 5, PHPUnit 12); that constraint applies to `tests/` only.

## Commands

XF commands run through `cmd.php` at the root of the XenForo install, four levels up from this
directory:

```bash
php ../../../../cmd.php xf-dev:import --addon=Hampel/ContentBbCode   # after hand-editing _output/
php ../../../../cmd.php xf-addon:build-release Hampel/ContentBbCode  # release zip into _releases/
```

**Never run `xf-dev:export`** — it writes the database over `_output/`, and without `--addon` it
does so for every add-on in the install. `build-release` runs the scoped export itself.

### Tests

PHPUnit 12 via `hampel/xenforo-test-framework` (`^5.7`), which boots the XF app of the install this
add-on sits in (`tests/TestCase.php` sets `$rootDir` to the install root). `vendor/` is gitignored.

```bash
composer install                                          # first time
vendor/bin/phpunit                                        # whole suite
vendor/bin/phpunit --testsuite Feature                    # one suite
vendor/bin/phpunit --filter SearchBbCodeTest              # one class
vendor/bin/phpunit --filter test_term_is_encoded_once     # one test
```

- **`$addonsToLoad` is what makes the suite run at all.** Without it XF registers every installed
  add-on's Composer autoloader, and one that vendors a different PHPUnit major kills the run before
  the first test.
- **`tests/Feature/BbCodeTestCase.php` renders each tag through `html`, `simpleHtml` and
  `emailHtml`** — the three outputs that differ. One `<Tag>BbCodeTest` per tag; each bug fixed
  since 2.0.3 has a regression test that fails against the old code.
- **`BbCodeDefinitionsTest` checks the two halves agree**: every `_output/bb_codes/` definition
  names a callable callback, and the installed row matches the file.
- **`phpunit/phpunit` is pinned to `^12.0` in `require-dev`** (2026-09-26). The framework accepts
  several PHPUnit majors, so without the pin a `composer update` could change the major by itself.
  Widen it when the framework supports a newer major and the suite passes on it.
- **`phpunit.xml` keeps `failOnPhpunitDeprecation`**, which the framework's own copy no longer
  sets because PHPUnit 10.5 lacks it. It is valid on PHPUnit 12; leave it in when re-copying.
- **`[xfmg=thumb]` is tested with `mockFind('XFMG:MediaItem', …)`**, so it needs no gallery
  content. The unparsed-tag assertions pass even if a tag is not registered; they are evidence
  only beside the link assertions in the same class.

## Release packaging

`build.json` strips the dev-only files (`TESTING.md`, both Claude files, Composer files,
`phpunit.xml`, `tests/`, `vendor/`) from the release, then **moves every remaining root `*.md` to
the zip root**. Any new root markdown file that must not ship needs an `rm` line placed before
that `mv`.

`git archive` — GitHub's "Download ZIP" — is a separate surface: `.gitattributes` marks the same
dev-only files `export-ignore`. A new dev-only file needs adding in both places.

`CLAUDE.local.md` is gitignored and holds anything true only of one machine; this file carries
nothing that would not hold for any clone.
