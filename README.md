TypoScript Lint: CGL validation for TypoScript
==============================================

[![Build Status](https://travis-ci.org/martin-helmich/typo3-typoscript-lint.svg?branch=master)](https://travis-ci.org/martin-helmich/typo3-typoscript-lint)

Author
------

Martin Helmich (typo3 at martin-helmich dot de)

Contents
--------

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


- [Synopsis](#synopsis)
- [Why?!](#why)
- [Getting started](#getting-started)
  - [Setup](#setup)
  - [Usage](#usage)
  - [Example](#example)
- [Code validation](#code-validation)
  - [Features](#features)
  - [Configuration](#configuration)
- [Articles](#articles)
- [Future features](#future-features)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

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

Getting started
---------------

### Setup

Install typo3-typoscript-lint with Composer:

    composer require --dev helmich/typo3-typoscript-lint

Of course, this works best if your TYPO3 project is also Composer-based. If it isn't, you can also install the Linter [globally using the `composer global`](https://getcomposer.org/doc/03-cli.md#global) command:

    composer global require helmich/typo3-typoscript-lint

### Usage

Call typo3-typoscript-lint as follows:

    vendor/bin/typoscript-lint path/to/your.ts

By default, it will print a report on the console. To generate a checkstyle-format XML file, call as follows:

    vendor/bin/typoscript-lint -f xml -o checkstyle.xml path/to/your.ts

### Example

[![asciicast](https://asciinema.org/a/1jOJv3Z6onWSdIkTAxAWsGgoy.png)](https://asciinema.org/a/1jOJv3Z6onWSdIkTAxAWsGgoy)

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

By default, the indentation sniff expects code inside TypoScript conditions to
be **not** indented. You can change this behaviour by setting the
`indentConditions` flag for the indentation sniff to `true` in your `tslint.yml`
configuration file (see below).

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

#### Empty blocks

Raises warnings about empty assignment blocks:

    foo {
    }

### Configuration

`typoscript-lint` looks for a file `tslint.yml` in the current working directory.
If such a file is found, it will be merged with the `tslint.dist.yml` from the
installation root directory. Have a look at [said file](tslint.dist.yml) for an
idea of what you can configure (granted, not much yet):

* The paths to lint can be set under the `paths` key:

  ```yaml
  paths:
    - directory/with/typoscript
    - ...
  ```

* Configure individual sniffs under the `sniff` key in the configuration file. This key
  consists of a list of objects, each with a `class` key and an optional `parameters`
  key.

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

* Configure file extensions that should be treated as TypoScript files in the
  `filePatterns` key. This key may contain a list of glob patterns that inspected files
  need to match. This is especially relevant when you're running `typoscript-lint`
  on entire directory trees:
  
  ```yaml
  filePatterns:
    - "*.ts"
    - "setup.txt"
    - # ...
  ```

Articles
--------

- [Code Quality in TYPO3 Projects](https://www.martin-helmich.de/en/blog/codequality-typo3.html)
- [Continuous Integration in TYPO3 Projects (_German_)](https://t3n.de/magazin/continuous-integration-typo3-236672/)
- [Integrate TYPO3 Linting with Gitlab CI](https://daniel-siepmann.de/Posts/2018/2018-01-30-gitlab-ci-linting.html) _by Daniel Siepmann_
- [Integrate Typoscript linter into VIM](https://daniel-siepmann.de/Posts/2018/2018-01-28-typoscript-linter-inside-vim.html) _by Daniel Siepmann_

Future features
---------------

- Sniffs for more code smells (ideas are welcome)
- Full test coverage (no, I did not do TDD. Shame on me.)
- Automated fixing of found errors

[issue-deadcode]: https://github.com/martin-helmich/typo3-typoscript-lint/issues/1
