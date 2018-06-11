Thread & Post BBCode for XenForo 2.0
====================================

This XenForo 2.0 addon adds `[thread]` and `[post]` BBCode tags.

By [Simon Hampel](https://twitter.com/SimonHampel).

Requirements
------------

This addon requires PHP 5.4 or higher and only works on XenForo 2.0.x 

Usage
-----

In post content (or anywhere that BBCode is allowed), the following substitutions will occur:

```bbcode
[thread=1]see this thread[/thread]
```

... will be rendered as:

```html
<a href="http://www.example.com/community/index.php?threads/1/">see this thread</a>
```

Similarly with posts

```bbcode
[post=2]see this post[/post]
```

... will be rendered as:

```html
<a href="http://www.example.com/community/index.php?posts/2/">see this post</a>
```