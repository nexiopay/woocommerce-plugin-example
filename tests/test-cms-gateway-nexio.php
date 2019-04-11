<?php
/**
 * Class SampleTest
 *
 * @package Cms_Gateway_Nexio
 */

require dirname(dirname( __FILE__ )) . '/cms-gateway-nexio.php';

/**
 * cms-gateway-nexio test case.
 */

class TestCMSGatewayNexio extends WC_Unit_Test_Case{

	public function setUp()
    {
        
	}
	
	/*
	 * get_privacy_message test case
	 *
	 */
	public function test_woocommerce_nexio_missing_wc_notice()
	{
        $expected = '<div class="error"><p><strong>' . sprintf( esc_html__( 'Nexio requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-stripe' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
        $this->expectOutputString($expected);
        woocommerce_nexio_missing_wc_notice();  
	}

}
