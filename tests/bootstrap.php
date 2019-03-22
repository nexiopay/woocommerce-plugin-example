<?php
require_once 'wordpress-tests-lib/functions.php';
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/cms-gateway-nexio.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

//require_once 'wordpress-tests-lib/bootstrap.php';
require_once 'woocommerce/tests/bootstrap.php';
?>