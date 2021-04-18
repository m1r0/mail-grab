# Email Catcher Unit Tests

## Initial Setup

Install WordPress and the WP Unit Test lib using the `install.sh` script. Change to the plugin's root directory and type:

    $ tests/bin/install.sh <db-name> <db-user> <db-password> [db-host] [wp-version] [skip-database-creation]

Sample usage:

    $ tests/bin/install.sh email_catcher_tests root root localhost 4.8 false

## Running Tests

1. Install PHPUnit globally using `composer global require phpunit/phpunit`.
2. Run `phpunit` in the root directory of the plugin.

Refer to the [phpunit command line test runner reference](https://phpunit.com/manual/current/en/phpunit-book.html#textui) for more information and command line options.
