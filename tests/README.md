# WooCommerce Unit Tests

## Initial Setup

1) Install [PHPUnit](http://phpunit.de/) by following their [installation guide](https://phpunit.de/getting-started.html). If you've installed it correctly, this should display the version:

    ```
    $ phpunit --version
    ```

2) Install WordPress and the WP Unit Test lib using the `install.sh` script. Change to the plugin root directory and type:

    ```
    $ tests/bin/install.sh <db-name> <db-user> <db-password> [db-host]
    ```

The `<db-password>` will be set as given. Previously, you would have needed to escape certain characters (forward & backward slashes, and ampersand), but install.sh now escapes them when it needs to internally. You may still need to quote strings with backslashes to prevent them from being processed by the shell or other programs.

Sample usages:

    $ tests/bin/install.sh woocommerce_tests root root

    #  The actual password only has a single backslash, but it's escaped
	#  to prevent the shell and PHP from treating it as a backspace character
    $ tests/bin/install.sh woocommerce_tests root 'a\\b/&'
    #  Previously, the password would have had to be passed as 'a\\\\b\/\&'

**Important**: The `<db-name>` database will be created if it doesn't exist and all data will be removed during testing.

## Running Tests

Simply change to the plugin root directory and type:

    $ phpunit

The tests will execute and you'll be presented with a summary.

## Automated Tests

Tests are automatically run with [Travis-CI](https://travis-ci.org/woocommerce/woocommerce) for each commit and pull request.
