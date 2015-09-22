KnpUniversity Activity Runner
=============================

[![Build Status](https://travis-ci.org/knpuniversity/activity-runner.png)](https://travis-ci.org/knpuniversity/activity-runner)

## Usage

Setup a web server or start the built-in web server:

```
cd web
php -S localhost:8000
```

Grading is done via an API. But there is also a web interface to help grade and
create activities:

## Authoring Tool

To help create challenges, go to the authoring tool:

    http://localhost:8000/author

Here, enter the full path to a challenge class filename on your filesystem. The
next page will allow you to play with the challenge and test it.

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
