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

The `<db-password>` will be set as given. install.sh will escape forward and back slashes and ampersands when required. However, you may still need to quote strings with backslashes to prevent them from being processed by shell or other programs.

Sample usages:

    $ tests/bin/install.sh woocommerce_tests root root
    #  The <db-name> is woocommerce_tests in above sample.
    #  The <db-user> is root in above sample.
    #  The <db-password> is root in above sample.

**Important**: The `<db-name>` database will be created if it doesn't exist and all data will be removed during testing.

## Running Tests

Simply change to the plugin root directory and type:

    $ phpunit

The tests will execute and you'll be presented with a summary.

## Automated Tests

Tests are automatically run with [Travis-CI](https://travis-ci.org/woocommerce/woocommerce) for each commit and pull request.
