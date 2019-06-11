# The search for a Regex to match BEM CSS class-names

## TL;DR

Use this regular expression to match BEM class-names:

```
^\.[a-z]([a-z0-9-]+-?)?(__([a-z0-9]+-?)+)?(--([a-z0-9]+-?)+){0,2}$
```

## Using Hyphenated BEM

As part of an initiative to use a more standardised approach to writing CSS, at
my current employers, we are using [BEM to define class names][bem-methodology]
for one of the projects I am involved in.

BEM stands for *B*lock *E*lement *M*odifier. It is a methodology invented at
Yandex to create extendable and reusable interface components.

There are two [naming conventions in BEM][bem-naming-convention] that are most
popular:

- Classic style (also known as "Strict BEM").
- Two Dashes style (also known as "Hyphenated BEM").

Both have similarities:

- Names are written in lowercase Latin letters.
- Words within the names of BEM entities are separated by a hyphen (`-`).
- The element name is separated from the block name by a double underscore (`__`).
- For boolean modifiers, the value is not included in the name.

The major difference between the two is the fact that a modifier name (or value)
is separated from its sibling by a single underscore `_` in Strict BEM and by a
_double_ hyphen `--` in Hyphenated BEM (hence the name).

Because we find it more readable, we use Hyphenated BEM.

## Custom Sass Lint class name format

In order to make sure everybody adheres to this convention, we use [sass-lint][sass-lint].
Sass-lint comes with [support for both Strict BEM and Hyphenated BEM for class-names][sass-lint-class-name-format],
straight out of the box.

But as we are in the process of moving from one convention to another, we want
to be able to support both, side-by-side.

For sass-lint, this means setting up a custom regular expression (or "regex") for
the `class-name-format` rule.

And so the search began to create a (re)usable regex.

## Creating use-cases

Before starting the search, we created a test-case that outlined what _should_
and _should not_ be matched.

The BEM entities that are available are:

`.block-name__element-name--modifier-name--modifier-value`

- Block (`.block-name`)
- Element (`__element-name`)
- Modifier Name (`--modifier-name`)
- Modifier Value (`--modifier-value`)

As we are using Hyphenated BEM style, we want a regex that matches that.
We  don't really care for (also) being able to match the Classic style.

### Valid combinations

This gives us the following valid combinations:

- Block Only
- Block + Element
- Block + Modifier
- Block + Modifier Name + Modifier Value
- Block + Element + Modifier
- Block + Element + Modifier Name + Modifier Value

With multiple words within each entity separated by a hyphen `-`.

This gives us roughly 50 variations that are valid Hyphenated BEM class names.

(For full result see appendix A).

### Invalid combinations

Combinations that are **not** valid are:

- Classes with underscores
- Mixing dashes and underscores
- Multiple similar BEM item (meaning more than one Element or more than two Modifiers)
- Trailing dashes
- Trailing underscores
- Wrong order (Modifier _before_ Element)
- Mixed dash and underscore _in_ BEM separator (the separator should _only_ be `--` or `__`)

Multiple words within each entity separated with something other than hyphen `-`.

Combining all of these with various edge-cases brings the amount of combinations
to nearly 450.

(For full result see appendix B).

## Finding a Regular Expression

Armed with almost 500 lines to match against, we feel we have a fairly complete
dataset to set out with.

Being lazy, instead of creating a regex ourselves, we first looked for an
existing regex. There did not seem to be much out there...

### First miss

In the [Validating BEM using regex][validating-bem-blogpost] blog-post,
[Samir Alajmovic][samir-alajmovic-linkedin] made the following suggestion for
matching BEM class-names:

```
^\.((([a-z0-9]+(_[a-z0-9]+)?)+((--)([a-z0-9]+(-[a-z0-9]+)?)+)?)|(([a-z0-9]+(_[a-z0-9]+)?)+__([a-z0-9]+(_[a-z0-9]+)?)+((--)([a-z0-9]+(-[a-z0-9]+)?)+)?))$
```

However, this seemed to be geared more toward Classic BEM, as words within each
entity need to be separated by an underscore `_`. It also does not take modifier
values into account.

This is demonstrated here: https://regex101.com/r/5UG3ff/3/

### First hit

In [a comment, in a ticket in the postcss-bem-linter repository][postcss-bem-linter-issue-comment]
[Corey Bruyere][corey-bruyere-twitter] suggests a regex.


It also doesn't take modifier values into account, but that is easy enough to remedy:

```
^\.[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*(?:__[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*)?(?:--[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*){0,2}$
```

As demonstrated here: https://regex101.com/r/E87qSb/2/, this regex works!

### Batter up!

The next step would be to ask ourselves, is there anything way we can improve
this regex? Do we need to make it faster, shorter, more readable or anything
else?

This repository contains code that makes it easy to test this. Just run
`index.php` and edit the `test.pass`, `test.fail` or `BEM.regex` file:

- `test.pass` All class names that a BEM regex SHOULD match
- `test.fail` All class names that a BEM regex SHOULD NOT match
- `BEM.regex` Regular expression(s) to test.

In both the `test.pass`, `test.fail` and files, only lines that start with a dot
`.` character are used.

In the `BEM.regex` file, only lines that start with a caret `^` are used.

The easiest way to run the `index.php` is to use [PHP's built-in web
server][php-webserver]:
```
php -S localhost:8080
```

## Home run!

If improvements are found, the BEM regex in `version2` needs to be updated.

It is located in the `.sasslintrc` file in the root of the project.

---

[bem-methodology]: https://en.bem.info/methodology/css/
[bem-naming-convention]: https://en.bem.info/methodology/naming-convention/
[corey-bruyere-twitter]: https://twitter.com/coreybruyere
[php-webserver]: https://www.php.net/manual/en/features.commandline.webserver.php
[postcss-bem-linter-issue-comment]: https://github.com/postcss/postcss-bem-linter/issues/89#issuecomment-255482072
[samir-alajmovic-linkedin]: https://www.linkedin.com/in/samir-alajmovic-91126b60/
[sass-lint-class-name-format]: https://github.com/sasstools/sass-lint/blob/develop/docs/rules/class-name-format.md
[sass-lint]: https://github.com/sasstools/sass-lint
[validating-bem-blogpost]: https://www.alajmovic.com/posts/validating-bem-using-regex/
