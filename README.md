TypoScript Lint: CGL validation for TypoScript
==============================================

Author
------

Martin Helmich (typo3 at martin-helmich dot de)

Synopsis
--------

This package contains a tool that can parse TYPO3's configuration language,
"TypoScript", into an syntax tree and perform static code analysis on the
parsed code. `typoscript-lint` can generate [Checkstyle](http://checkstyle.sourceforge.net/)-compatible output and can be used
in Continuous Integration environments.

Why?!
-----

This project started of as a private programming excercise. I was writing an
article for the [T3N](http://t3n.de) magazine on Continuous Integration for
TYPO3 projects, introducing tools like [JSHint](http://www.jshint.com/) or [CSSLint](http://csslint.net/),
and I noticed that no comparable tools exist for TypoScript. So I thought,
"What the heck, let's go" and at some point realized that my little programming
excercise might actually be useful to someone. So, that's that. Enjoy.

Code validation
---------------

### Features

Certain aspects of code validation are organized into so-called "sniffs" (I
borrowed the term from PHP's CodeSniffer project). Currently, there are sniffs
for checking the following common mistakes or code-smells in TypoScript:

#### Indentation

The indentation level should be increased with each nested statement. In the
configuration file, you can define whether you prefer
[tabs or spaces](http://www.jwz.org/doc/tabs-vs-spaces.html) for indentation.

    foo {
        bar = 2
      baz = 5
    # ^----------- This will raise a warning!
    }

#### Dead code

Code that was commented out just clutters your source code and obstructs
readability. Remove it, that's what you have version control for (you **do**
use version control, do you?).

    foo {
        bar.baz = 5
        #baz.foo = Hello World
    #   ^----------- This will raise a warning!
    }

#### Whitespaces

Check that no superflous whitespace float around your operators.

    #   v----------- This will raise a warning (one space too much)
    foo  {
        bar= 3
    #      ^-------- This will also raise a warning (one space too few)
    }

#### Repeating values

If the same value is assigned to different objects, it might be useful to
extract this into a TypoScript constant.

    foo {
        bar = Hello World
        baz = Hello World
    #         ^----- Time to extract "Hello World" into a constant!

#### Duplicate assignments

Assigning a value to the same object multiple times. Works across nested statements, too.

    foo {
        bar = baz
    #   ^----------- This statement is useless, because foo.bar is unconditionally overwritten!
    }
    foo.bar = test

The sniff is however smart enough to detect conditional overwrites. So the
following code will *not* raise a warning:

    foo {
        bar = baz
    }

    [globalString = ENV:foo = bar]
    foo.bar = test
    [global]

#### Nesting consistency

This sniff checks if nesting assignments are used in a consistent manner. Consider
the following example:

    foo {
        bar = test1
    }

    foo {
        baz = test2
    }

In this case, the two nested statements might very well be merged into one statement.

Consider another example:

    foo {
        bar = test1
    }

    foo.baz {
        bar = test2
    }

In this case, both statements could be nested in each other.

### Calling tslint

Call tslint as follows:

    bin/typoscript-lint lint path/to/your.ts

By default, it will print a report on the console. To generate a checkstyle-format XML file, call as follows:

    bin/typoscript-lint -f xml -o checkstyle.xml path/to/your.ts

### Configuration

`typoscript-lint` looks for a file `tslint.yml` in the current working directory.
If such a file is found, it will be merged with the `tslint.dist.yml` from the
installation root directory. Have a look at [said file](tslint.dist.yml) for an
idea of what you can configure (granted, not much yet).

Since a local configuration file will be merged with the distributed
configuration file, you *cannot* disable sniffs by simply removing them from the
local configuration file (see this [bug report][issue-deadcode]
for more information). To disable a sniff, use the `disabled` configuration
property. For example, to disable the `DeadCode` sniff:

```yaml
sniffs:
  - class: DeadCode
    disabled: true
```

### Future features

- Sniffs for more code smells (ideas are welcome)
- Full test coverage (no, I did not do TDD. Shame on me.)

[issue-deadcode]: https://github.com/martin-helmich/typo3-typoscript-lint/issues/1
