# Testing Content BBCode

What this add-on touches, what breaks quietly, what the test suite settles on its own, and what
still needs a person with a browser. Read it before a release.

## Surfaces

**No class extensions, code event listeners, template modifications or options.** Everything the
add-on does happens while BB code is rendered.

| surface | what it is |
|---|---|
| 8 custom BB codes | `thread`, `post`, `forum`, `resource`, `tag`, `prefix`, `search`, `xfmg` — all callback mode, defined in `_output/bb_codes/`, one callback class each in `BbCode/` |
| 32 phrases | the title, description, example and output text for each tag, shown in the admin UI and on the BB code help page |
| `BbCode\Link` | builds the `<a>` for every tag; not a BB code itself |
| `Setup::postUpgrade()` | queues XenForo's post-upgrade file clean-up, on XenForo 2.3 and later only |

## Fragile points

- **Link text must be rendered once.** `renderSubTree()` returns escaped HTML. Escaping it again,
  or handing a child node to `filterString()`, shows markup as text or throws a `TypeError` when
  the text begins with a nested tag. Every tag has shipped one of these at some point.
- **A search term is encoded by whatever builds the URL, and by nothing else.** `buildLink()` and
  `http_build_query()` encode; encoding before them searches for the encoded string.
- **Each tag is two halves.** The definition in `_output/bb_codes/` names a callback class and
  method; renaming either without the other leaves the tag rendering as typed text.
- **The Media Gallery and Resource Manager are optional.** `[xfmg=thumb]` is the only tag that
  looks an entity up, and it checks the gallery is installed first. The other `[xfmg]` and
  `[resource]` forms only build links, so without those add-ons they link to pages that do not
  exist rather than failing.
- **The minimum is XenForo 2.2.** Code must run on PHP 7.0 and name core classes by their pre-2.3
  names: XenForo 2.3 still resolves the old names, and 2.2 has never heard of the new ones.

## Automated

The suite boots the XenForo install the add-on sits in, loading only this add-on.

```bash
composer install
vendor/bin/phpunit                          # whole suite
vendor/bin/phpunit --order-by=random        # catches a test that relies on an earlier one
vendor/bin/phpunit --testsuite Feature      # read the count, not only the exit code
```

It covers every tag through the `html`, `simpleHtml` and `emailHtml` renderers, the `Link` helper,
the post-upgrade job, and a check that each `_output/bb_codes/` definition names a real callback
and matches the installed definition. `[xfmg=thumb]` runs against a mocked media item lookup, so
it needs no gallery content.

**Each bug fixed since 2.0.3 has a regression test, and each was proven by putting the bug back.**
Reverting the fix, one at a time, must fail the test named for it. Repeat that for any new
regression test before trusting it; a test that still passes with its fix removed tests nothing.

## Needs a human

- **`[xfmg=thumb]` against a real gallery.** Post `[xfmg=thumb,<id>]x[/xfmg]` for a media item a
  guest cannot view; as a guest it should link to `media/<id>/`, and as a member who can view it,
  it should show the thumbnail. The suite mocks this lookup.
- **`[xfmg=img]` with the lightbox.** The `html` output is XenForo's image template; check in a
  browser that clicking it opens the lightbox.
- **The upgrade from the last published version.** Install that release's zip on a forum, post one
  of each tag, upgrade to the new build, and confirm the posts still render. After the upgrade,
  run the job queue so the XenForo 2.3 file clean-up actually executes, then check the forum still
  loads and the error log is empty.
- **The simple and email renderers in their real places** — a signature, and a notification email
  quoting a post — to see the bare links as a reader does.
