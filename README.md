Content BBCode for XenForo 2.x
==============================

Note: this XenForo 2.0 addon replaces the `ThreadPostBbCode` addon released for XF 1.5 (and unreleased for XF 2.0).

By [Simon Hampel](https://twitter.com/SimonHampel).

This addon creates additional BBCodes for linking to various content found on XenForo forums:
 
 * thread
 * post
 * search (see below for details)
 * tag
 * XFMG
 	* media
 	* category
 	* album
 	* image
 	* thumbnail
 * forum (node)
 * prefix
 	* thread
 	* resource
 * resource
 
The search tag has options allowing you to link to searches across multiple content types:
 
 * forums
 * threads
 * posts
 * resources
 * media
 * media comments
 * user profiles
 * tags
 * Google
 	* site search
 	* web search
 	* image search
 	* map search
 	* video search
 	* news search

This addon was originally created to replicate the `[THREAD]` and `[POST]` BBCodes which were used in vBulletin and may
be present in post content after migration.

The additional BBCode tags were added primarily to support some forum statistics scripts I had written for my sites as 
I needed a way of programmatically generating links which were also independent of the site URL.

If you just want to use the thread and post tags, you can always disable the other custom bbcode tags in the admin UI.

Requirements
------------

This addon requires PHP 5.4 or higher and has been tested with XF v2.0 and v2.1 

Usage
-----

In post content (or anywhere that BBCode is allowed), the following substitutions will occur:

**THREAD**

```bbcode
[thread=1]see this thread[/thread]
```

... will be rendered as:

```html
<a href="http://www.example.com/threads/1/">see this thread</a>
```

Alternative syntax:

```bbcode
[thread]1[/thread]
```

... will be rendered as:

```html
<a href="http://www.example.com/threads/1/">http://www.example.com/threads/1/</a>
```

**POST**

```bbcode
[post=2]see this post[/post]
```

... will be rendered as:

```html
<a href="http://www.example.com/posts/2/">see this post</a>
```

**SEARCH**

Examples:

```bbcode
[search]foo[/search] (default is to search entire forum - search term between tags)
[search=forum]foo[/search] (general forum search - search term between tags)
[search=forum,foo]search the forums for 'foo'[/search] (general forum search - search term as option)

[search=thread,foo]search threads for 'foo'[/search]
[search=post,foo]search posts for 'foo'[/search]
[search=resource,foo]search resources for 'foo'[/search]
[search=media,foo]search media uploads for 'foo'[/search]
[search=comments,foo]search media comments for 'foo'[/search]
[search=tag,foo]search tags for 'foo'[/search] 

[search=site,foo]Google site search for 'foo'[/search] (search google with 'site:example.com' tag to perform a Google search of your site
[search=web,foo]Google web search for 'foo'[/search]
[search=image,foo]Google image search for 'foo'[/search]
[search=map,foo]Google map search for 'foo'[/search]
[search=video,foo]Google video search for 'foo'[/search]
[search=news,foo]Google new search for 'foo'[/search]
```

**TAG**

Examples:

```bbcode
[tag=foo bar]link to the tag 'foo bar'[/tag]
```

... will be rendered as:

```html
<a href="http://www.example.com/tags/foo-bar/">link to the tag 'foo bar'</a>
```

**XFMG**

Examples:

```bbcode
[xfmg=media,1]link to media item with id = 1[/xfmg]
[xfmg=album,1]link to album with id = 1[/xfmg]
[xfmg=category,1]link to category with id = 1[/xfmg]
```

... will be rendered as:

```html
<a href="http://www.example.com/media/1/">link to media item with id = 1</a>
<a href="http://www.example.com/media/albums/1/">link to album with id = 1</a>
<a href="http://www.example.com/media/categories/1/">link to category with id = 1</a>
```

You can also display full sized images:

```bbcode
[xfmg=img,1][/xfmg]
```

... will be rendered as:

```html
<img src="http://example.com/media/1/full" data-url="http://example.com/media/1/full" class="bbImage" data-zoom-target="1" alt="">
```

Note that if lightbox is enabled for images - the image will be rendered using the standard lightbox template in the
same way that the `IMG` bbcode tag works.

Similarly, you may display a thumbnail as follows:

```bbcode
[xfmg=thumb,1][/xfmg]
```

**FORUM**

Examples:

```bbcode
[forum=1]link to this forum node[/forum]
```

... will be rendered as:

```html
<a href="http://www.example.com/forums/1">link to this forum node</a>
```

**PREFIX**

Examples:

```bbcode
[prefix=forum,1,2]link to this prefix for a node[/prefix] (ie show all threads from node_id = 1 using prefix_id = 2)
[prefix=resource,0,1]link to this prefix for a resource[/prefix] (ie show all resources using prefix_id = 1)
[prefix=resource,2,3]link to this prefix for a resource category[/prefix] (ie show all resources from category_id = 2 using prefix_id = 3)
```

... will be rendered as:

```html
<a href="http://www.example.com/forums/1/?prefix_id=2">link to this prefix for a node</a>
<a href="http://www.example.com/resources/?prefix_id=1">link to this prefix for a resource</a>
<a href="http://www.example.com/resources/categories/2/?prefix_id=3">link to this prefix for a resource category</a>
```

**RESOURCE**

```bbcode
[resource=1]link to this resource[/resource]
```

... will be rendered as:

```html
<a href="http://www.example.com/resources/1">link to this resource</a>
```
