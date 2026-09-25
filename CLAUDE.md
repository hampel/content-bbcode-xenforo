# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Content BBCode is a XenForo 2 add-on (`Hampel/ContentBbCode`) that adds custom BB codes linking
to forum content by ID or term: `[thread]`, `[post]`, `[forum]`, `[resource]`, `[tag]`, `[prefix]`,
`[search]` and `[xfmg]`. `README.md` documents the syntax of each and the HTML it renders; read it
before changing a tag's behaviour.

## Architecture

**There are no class extensions, code event listeners or templates.** Each tag is a XenForo
custom BB code in callback mode, and the whole add-on is two halves that must agree:

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
  rather than throwing or emitting an empty link. `[thread]` and `[post]` also accept the ID as the
  tag body, and then display the full URL as the link text.
- **`SimpleHtml` and `EmailHtml` renderers get a bare `<a href>`**, with no `class` or `target`
  from `getLinkClassTarget()`. The `html` renderer gets both.
- **XFMG and XFRM are optional.** `Xfmg` checks `addon.cache` for `XFMG` before looking up a
  media item; any new lookup against an optional add-on's entities needs the same guard.

`Setup.php` does nothing except enqueue XF 2.3's post-upgrade cleanup.

### Declared compatibility is wider than the dev tooling

`addon.json` declares PHP 5.4+ and XF 2.0+, and the runtime code in `BbCode/` and `Setup.php` is
written to that floor — no scalar or return type declarations, no `??`. Keep it that way unless
the requirement in `addon.json` is raised deliberately. The test suite needs a much newer PHP
(PHPUnit 10); that constraint applies to `tests/` only.

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

PHPUnit via `hampel/xenforo-test-framework`, which boots the XF app of the install this add-on
sits in (`tests/TestCase.php` sets `$rootDir` to the install root). `vendor/` is gitignored.

```bash
composer install                                        # first time
vendor/bin/phpunit                                      # whole suite
vendor/bin/phpunit --filter test_bbcode_post_id_in_body # one test
```

Only `[post]` has tests so far (`tests/Feature/PostBbCodeTest.php`). They use `assertBbCode()`
against the `html`, `simpleHtml` and `emailHtml` renderers — the three outputs that differ.

## Release packaging

`build.json` strips the dev-only files (`TESTING.md`, both Claude files, Composer files,
`phpunit.xml`, `tests/`, `vendor/`) from the release, then **moves every remaining root `*.md` to
the zip root**. Any new root markdown file that must not ship needs an `rm` line placed before
that `mv`.

`git archive` — GitHub's "Download ZIP" — is a separate surface: `.gitattributes` marks the same
dev-only files `export-ignore`. A new dev-only file needs adding in both places.

`CLAUDE.local.md` is gitignored and holds anything true only of one machine; this file carries
nothing that would not hold for any clone.
