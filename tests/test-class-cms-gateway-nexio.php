<?php
/**
 * Class SampleTest
 *
 * @package Cms_Gateway_Nexio
 */

require dirname(dirname( __FILE__ )) . '/class-cms-gateway-nexio.php';

/**
 * cms-gateway-nexio test case.
 */

class TestClassCMSGatewayNexio extends WC_Unit_Test_Case{
    public $nexio_class;
    public $order;
    

	public function setUp()
    {
        $this->nexio_class = new CMS_Gateway_Nexio();
        //create test order
        $this->order  = WC_Helper_Order::create_order();
        
	}
    
    //a sample of stunk a function
    public function test_stub_gettoken()
    {
        
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(CMS_Gateway_Nexio::class);

        // Configure the stub.
        $stub->method('get_creditcard_token')
             ->willReturn('error');

        $this->assertEquals('error', $stub->get_creditcard_token($this->order->get_id()));
    }

    /*
	 * get_privacy_message test case
	 *
	 */
	public function test_complete_order_fraudtrue()
	{
        $order_id = $this->order->get_id();
        
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                            ),

                            );
        
        $data = json_decode(json_encode($callbackdata));
        
        $return = $this->nexio_class->complete_order($order_id,$data,true);
        
        $testorder = wc_get_order($order_id);
        $this->assertEquals('processing',$testorder->get_status());

        $notes = $this->get_private_order_notes($order_id);
          
    }
    
    public function test_complete_order_fraudfalse()
	{
        $order_id = $this->order->get_id();
        
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                            ),

                            );
        
        $data = json_decode(json_encode($callbackdata));
        
        $return = $this->nexio_class->complete_order($order_id,$data,false);
        
        $testorder = wc_get_order($order_id);
        $this->assertEquals('processing',$testorder->get_status());

        $notes = $this->get_private_order_notes($order_id);
      
    }

    public function test_checking_success_data_1()
    {
        
        $this->nexio_class->fraud = 'yes';
        //mock callback data, trans success
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                                'result' => 'Approved',
                                            ),
            'data' => array(
                                'customer' => array(
                                                      'orderNumber' => $this->order->get_id(),//$testorder->get_id(),
                                                    ),
                            ),
            'kountResponse' => array(
                                        'status' => 'success',
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_success_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());
        
        $this->order = wc_get_order($this->order->get_id());
        $this->assertEquals('processing',$this->order->get_status());;
    }

    public function test_checking_success_data_2()
    {
        
        $this->nexio_class->fraud = 'no';
        //mock callback data, trans success
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                                'result' => 'Approved',
                                            ),
            'data' => array(
                                'customer' => array(
                                                      'orderNumber' => $this->order->get_id(),//$testorder->get_id(),
                                                    ),
                            ),
            'kountResponse' => array(
                                        'status' => 'success',
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_success_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());
        
        $this->order = wc_get_order($this->order->get_id());
        $this->assertEquals('processing',$this->order->get_status());
    }

    public function test_checking_success_data_3()
    {
        $this->nexio_class->fraud = 'no';
        //mock callback data, trans failed
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                                'result' => 'Denied',
                                            ),
            'data' => array(
                                'customer' => array(
                                                      'orderNumber' => $this->order->get_id(),//$testorder->get_id(),
                                                    ),
                            ),
            'kountResponse' => array(
                                        'status' => 'success',
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_success_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());

        //since payment is not completed, nothing should happen.
        $this->assertNull($notes);
        
        $this->order = wc_get_order($this->order->get_id());
        
        $this->assertEquals('pending',$this->order->get_status());
    }

    public function test_checking_success_data_4()
    {
        //set fraud to be false
        $this->nexio_class->fraud = 'yes';
        //mock callback data, trans approved, kount status is review.
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                                'result' => 'Approved',
                                            ),
            'data' => array(
                                'customer' => array(
                                                      'orderNumber' => $this->order->get_id(),//$testorder->get_id(),
                                                    ),
                            ),
            'kountResponse' => array(
                                        'status' => 'review',
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_success_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());
        
        //since kount status is review, only one note should be added.
        $this->assertEquals(1,count($notes));
        
        $this->order = wc_get_order($this->order->get_id());
       
        $this->assertEquals('on-hold',$this->order->get_status());
    }


    public function test_checking_fail_data_1()
    {
        //mock callback data, Kount fail
        $callbackdata = array(
            'kountError' => true,
            'merchantId' => '100039',
            'kountResults' => array(
                                        'result' => 'declined',
                                        'data' => array(
                                                            'ORDR' => $this->order->get_id(),
                                                        ),
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_fail_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());
        
        $this->order = wc_get_order($this->order->get_id());
        $this->assertEquals('failed',$this->order->get_status());;
    }

    public function test_checking_fail_data_2()
    {
        //mock callback data, gateway fail
        $callbackdata = array(
            'gatewayProcessingError' => true,
            'message' => 'Invalid Transaction',
            'merchantId' => '100039',
            'gatewayResponse' => array(
                                        'result' => 'Declined',
                                        'status' => 'declined',
                                        'type' => 'sale',
                                        'refNum' => '3102447185',
                                        'authCode' => '000000',
                                        'batchId' => '409143',
                                    ),
                            );
        $data = json_decode(json_encode($callbackdata));
        
        $this->nexio_class->checking_fail_data($data);

        $notes = $this->get_private_order_notes($this->order->get_id());
        
        $this->order = wc_get_order($this->order->get_id());
        $this->assertEquals('pending',$this->order->get_status());;
    }


	/*
	 * test_build_gettoken_json test case
	 *
	 */
	public function test_build_gettoken_json()
	{
        $order_id = $this->order->get_id();
        $return = $this->nexio_class->build_gettoken_json($order_id);
        
        $this->assertStringStartsWith('{"data":{',$return);        
    }
    

    /*
	 * test_custom_content_thankyou test case
	 *
	 */
	public function test_custom_content_thankyou_1()
	{
        $order_id = $this->order->get_id();
        //now, the order status is pending
        $expected = '<p>'.__('Payment is successfully processed by Nexio, but your order status is not processing, please check!').'</p>';
        $this->expectOutputString($expected);
        $this->nexio_class->custom_content_thankyou($order_id);   
    }
    
    public function test_custom_content_thankyou_2()
	{
        //first of all, complete the order so the order status is processing
        $order_id = $this->order->get_id();
        
        $callbackdata = array(
            'id' => "eyJuYW1lIjoidXNhZXBheSIsIm1lcmNoYW50SWQiOiIxMDAwMzkiLCJyZWZOdW1iZXIiOiIzMTAxODc0MTY5IiwicmFuZG9tIjoiMzEwMTg3NDE2OSIsImN1cnJlbmN5IjoidXNkIn0",
            'gatewayResponse'       => array(
                                                'batchRef' => "123456",
                                                'refNumber' => "112233",
                                            ),

                            );
        
        $data = json_decode(json_encode($callbackdata));
        
        $return = $this->nexio_class->complete_order($order_id,$data,false);
        //now, the order status is pending
        $expected = '<p>'.__('Payment is successfully processed by Nexio!').'</p>';
        $this->expectOutputString($expected);
        $this->nexio_class->custom_content_thankyou($order_id);   
    }

    /*
	 * test_payment_fields test case
	 *
	 */
    public function test_payment_fields()
    {
        //set api_url api.nexiopay.com
        $this->nexio_class->api_url = 'https://api.nexiopay.com/';

        $expected = '<p id="cms_checkout_message">Please click below button to continue payment</p><div id="checkoutspinner" class="loader" style="display: none;"></div>';
        $this->expectOutputString($expected);
        $this->nexio_class->payment_fields(); 
    }

    public function test_payment_fields_2()
    {
        //set api_url api.nexiopaysandbox.com
        $this->nexio_class->api_url = 'https://api.nexiopaysandbox.com/';

        $expected = '<p id="cms_checkout_message">Please click below button to continue payment</p><div id="checkoutspinner" class="loader" style="display: none;"></div>';
        $this->expectOutputString($expected);
        $this->nexio_class->payment_fields(); 
    }

    /*
	 * test_process_payment test case
	 *
	 */
    public function test_process_payment()
    {
        $order_id = $this->order->get_id();
        
        $return = $this->nexio_class->process_payment($order_id);

        $this->assertEquals('success',$return['result']);
        $this->order = wc_get_order($order_id);
        $this->assertEquals($return['redirect'],$this->order->get_checkout_payment_url(true));
    }

    /*
	 * test_get_iframe_src test case
	 *
	 */
    public function test_get_iframe_src()
    {
        $value = 'testvalue';
        $return = $this->nexio_class->get_iframe_src($value);

        $this->assertEquals($this->nexio_class->api_url."pay/v3/?token=".$value,$return);
    }

    /*
	 * test_get_failure_callback_url test case
	 *
	 */
    public function test_get_failure_callback_url()
    {
        
        $return = $this->nexio_class->get_failure_callback_url();

        $this->assertEquals(get_site_url(null,null,'https').'/wc-api/CALLBACK/',$return);
    }

    /*
	 * test_get_callback_url test case
	 *
	 */
    public function test_get_callback_url()
	{
        $return = $this->nexio_class->get_callback_url();
        $this->assertEquals(get_site_url(null,null,'https').'/wc-api/'.strtolower( get_class( $this->nexio_class ) ),$return);
	}

    /*
	 * test_generate_nexio_form test case
	 *
	 */
    public function test_generate_nexio_form()
	{
        $order_id = $this->order->get_id();
        
        $mockedObject = $this->getMockBuilder(CMS_Gateway_Nexio::class)
            ->setMethods(['get_creditcard_token'])
            ->getMock();
        $mockedObject->expects($this->any())
             ->method("get_creditcard_token")
             ->willReturn('123456');

        
        
        //set api.nexiopay.com as api_url
        $mockedObject->api_url = 'https://api.nexiopay.com/';
        $onetimetoken = $mockedObject->get_iframe_src('123456');

        $return = $mockedObject->generate_nexio_form($order_id);
        $return2 = '<p id="p1">Thank you for your order, please input your payment information in blow form and click the button to submit transaction.</p><form id="cms_payment_form" height="900px" width="400px" action="'.esc_url( $onetimetoken ).'" method="post">
		<iframe type="iframe" class="cms_iframe" id="iframe1" src="'.$onetimetoken.'"></iframe>
		<input type="submit" class="button" id="submit_cms_payment_form" value="'.__('Pay via Nexio', 'cms-gateway-nexio').'" />
		</form>
		<div id="loader"></div>';
        $return = trim(preg_replace('/\s\s+/', ' ', $return));
        $return2 = trim(preg_replace('/\s\s+/', ' ', $return2));

        $this->assertEquals($return,$return2);
    }
    
    public function test_generate_nexio_form_2()
	{
        $order_id = $this->order->get_id();
        
        $mockedObject = $this->getMockBuilder(CMS_Gateway_Nexio::class)
            ->setMethods(['get_creditcard_token'])
            ->getMock();
        $mockedObject->expects($this->any())
             ->method("get_creditcard_token")
             ->willReturn('error');

        
        
        //set api.nexiopay.com as api_url
        $mockedObject->api_url = 'https://api.nexiopay.com/';
        $onetimetoken = $mockedObject->get_iframe_src('123456');


        $return = $mockedObject->generate_nexio_form($order_id);
        $return2 = '<p id="tokenerror" class="woocommerce-error"> Fail to generate payment form, please go back to checkout page and retry!</p>
        <a href="'.wc_get_checkout_url().'"><input type="button" value="Back to Checkout"></a>';
        $return = trim(preg_replace('/\s\s+/', ' ', $return));
        $return2 = trim(preg_replace('/\s\s+/', ' ', $return2));

        $this->assertEquals($return,$return2);
    }

    public function test_generate_nexio_form_3()
	{
        

        $order_id = $this->order->get_id();
        
        $mockedObject = $this->getMockBuilder(CMS_Gateway_Nexio::class)
            ->setMethods(['get_creditcard_token'])
            ->getMock();
        $mockedObject->expects($this->any())
             ->method("get_creditcard_token")
             ->willReturn('123456');
        //set api.nexiopaysandbox.com as api_url
        $mockedObject->api_url = 'https://api.nexiopaysandbox.com/';
        $onetimetoken = $mockedObject->get_iframe_src('123456');
        

        $return = $mockedObject->generate_nexio_form($order_id);
        $return2 = '<p id="p1">Thank you for your order, please input your payment information in blow form and click the button to submit transaction.</p><form id="cms_payment_form" height="900px" width="400px" action="'.esc_url( $onetimetoken ).'" method="post">
		<iframe type="iframe" class="cms_iframe" id="iframe1" src="'.$onetimetoken.'"></iframe>
		<input type="submit" class="button" id="submit_cms_payment_form" value="'.__('Pay via Nexio', 'cms-gateway-nexio').'" />
		</form>
		<div id="loader"></div>';
        $return = trim(preg_replace('/\s\s+/', ' ', $return));
        $return2 = trim(preg_replace('/\s\s+/', ' ', $return2));

        $this->assertEquals($return,$return2);
	}

    public function test_generate_nexio_form_4()
	{
        $order_id = $this->order->get_id();
        
        $mockedObject = $this->getMockBuilder(CMS_Gateway_Nexio::class)
            ->setMethods(['get_creditcard_token'])
            ->getMock();
        $mockedObject->expects($this->any())
             ->method("get_creditcard_token")
             ->willReturn('error');

        //set api.nexiopaysandbox.com as api_url
        $mockedObject->api_url = 'https://api.nexiopaysandbox.com/';
        $onetimetoken = $mockedObject->get_iframe_src('error');
        

        $return = $mockedObject->generate_nexio_form($order_id);
        $return2 = '<p id="tokenerror" class="woocommerce-error"> Fail to generate payment form, please go back to checkout page and retry!</p>
        <a href="'.wc_get_checkout_url().'"><input type="button" value="Back to Checkout"></a>';
        $return = trim(preg_replace('/\s\s+/', ' ', $return));
        $return2 = trim(preg_replace('/\s\s+/', ' ', $return2));

        $this->assertEquals($return,$return2);
	}


    //get order notes
    function get_private_order_notes( $order_id){
        global $wpdb;
    
        $table_perfixed = $wpdb->prefix . 'comments';
        $results = $wpdb->get_results("
            SELECT *
            FROM $table_perfixed
            WHERE  `comment_post_ID` = $order_id
            AND  `comment_type` LIKE  'order_note'
        ");
        if(!$results)
            return null;
        
        foreach($results as $note){
            $order_note[]  = array(
                'note_id'      => $note->comment_ID,
                'note_date'    => $note->comment_date,
                'note_author'  => $note->comment_author,
                'note_content' => $note->comment_content,
            );
        }
        return $order_note;
    }

}