KnpUniversity Activity Runner
=============================

## Usage

You can evaluate activities straight from the command line by running the
following command:

    $ php runner.php [-c|--config="..."] [-i|--input-format="..."] [-o|--output-format="..."] activity [file1] ... [fileN]

For more details on the specific arguments please refer to the command help by
running the following:

    $ php runner.php --help

## Installation

There are 2 ways to install. The first method creates a new standalone project
and the second one adds it your own project as a library. Composer is used for
both methods.

### New project

Use the `create-project` command of composer:

    $ php composer.phar create-project knpuniversity/activity-runner my/path 0.1.0

### Inclusion as a library

Simply add Activity Runner in your `composer.json`:

    {
        "require": {
            "knpuniversity/activty-runner": "~0.1"
        }
    }

Then simply run the following command:

    $ php composer.phar update knpuniversity/activity-runner

Composer will then install the library to your project's vendor/knpuniversity
directory.

## Contributing

Isses and feature requests go to  [GitHub Issue Tracker][issue-tracker]. Pull
requests are very welcome, as always!

## Running the Tests

Activity Runner uses PHPUnit for tests. Simply execute PHPUnit from the
project's root directory:

    $ phpunit

## License

KnpUniversity Activity Runner is released under the MIT License. See the bundled
LICENSE file for details.

[issue-tracker]: https://github.com/knpuniversity/activity-runner/issues
