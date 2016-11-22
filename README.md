# Burntromi Exception-Generator

In software projects and libraries it is very common and needed to generate exception
classes. For generating these classes, the following best-practice enforced in the
PHP environment:

http://ralphschindler.com/2010/09/15/exception-best-practices-in-php-5-3

The manual creation of those classes is time-consuming and error-prone.
To save time and reduce errors, this tool was made!

[![Build Status](https://travis-ci.org/burntromi/exception-generator.svg?branch=master)](https://travis-ci.org/burntromi/exception-generator)

## Installation/Usage

```
composer global require burntromi/exception-generator
```

Make sure you have `~/.composer/vendor/bin/` in your path.

```
exception-generator [PATH]
```

Where `PATH` is your source code path where the exception classes should be generated.

## Possible Options

`--overwrite (-o)`:

Give permissions to overwrite all existing files, without asking for each file to
be overwritten.

`--template-path=PATH (-t PATH)`:

Can be usesd to determine the PATH you want the application to look for templates
for creating the exception classes/interface. If a template cannot be found in this
PATH, the application will search for other ways to find a template to use. At first
it will check the config file (.exception-generator.json), which is located in `$HOME`.
If this also fails, it will use given templates from the tool itself.

`--no-parents (-p)`:

Disables feature for searching and using parent exception classes.

`--help (-h)`:

Display help text and exit.

## How is the namespace determined?

The tool starts from the current dir. If there can be a PHP file found, it trys
to resolve a namespace out of this file. Otherwise, it iterates, starting from the
current dir, up through all folders until a PHP file is found and a namespace can
be resolved.
After this, the iterated paths will be added to the resolved namespace.

If the application finds a composer.json, while iterating through, it attempts to
determine the namespace out of it by using the entry "autoload" (psr-4 und psr-0),
where psr-4 has a higher priority than psr-0.

If the application encounters a .git folder, while iterating through and also neither
a PHP file, nor a composer.json are found in this directory and therfore no namespace
can be determined, the iteration will be aborted and you are forced to input the
namespace manually.

If you are not using the "p" paramter, the application will also check for parent
exceptions, which will be used as base exceptions for inheritance when found.

The determined namespace will be shown after all and you are asked to verify it
with "enter" or to correct it.

## The config file (.exception-generator.json)

This should be located in `$HOME` and use the following pattern:

```json
{
    "templatepath": {
        "global": "/home/user/exception-generator/global",
        "projects": {
            "/data/projects/myproject": "/home/user/exception-generator/myproject/"
        }
    }
}
```

At first it will try to match a path from "projects" with the current dir you are
in and will use the most likely path, which contains a template, if there are more
than one match.
If this fails, it will check the entry in "global" for a template.
Otherwise the tool will use given templates from itself.


## Tests

run PHPUnit:

```
./vendor/bin/phpunit
```

run Behat:

```
./vendor/bin/behat
```

# License

MIT see (LICENSE.md)[LICENSE.md]
