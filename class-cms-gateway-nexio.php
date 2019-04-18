<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CMS_Gateway_Nexio class.
 *
 * @extends WC_Payment_Gateway
 */
class CMS_Gateway_Nexio extends WC_Payment_Gateway_CC {
	/**
	 * API URL
	 *
	 * @var string
	 */
	public $api_url;

	/**
	 * Merchant ID
	 *
	 * @var string
	 */
	public $merchant_id;

	/**
	 * User Name
	 *
	 * @var string
	 */
	public $user_name;	

	/**
	 * PassWord
	 *
	 * @var string
	 */
	public $password;		

	/**
	 * CSS
	 *
	 * @var string
	 */
	public $css;	

	/**
	 * customtext_url
	 *
	 * @var string
	 */
	public $customtext_url;

	/**
	 * fraud
	 *
	 * @var bool
	 */
	public $fraud;

	/**
	 * requirecvc
	 *
	 * @var bool
	 */
	public $requirecvc;

	/**
	 * hidecvc
	 *
	 * @var bool
	 */
	public $hidecvc;
	

	/**
	 * hidebilling
	 *
	 * @var bool
	 */
	public $hidebilling;
	
	/**
	 * authonly
	 *
	 * @var bool
	 */
	public $authonly;


	/**
	 * shareSecret
	 *
	 * @var string
	 */
	public $shareSecret;




	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id             = 'nexio';
		$this->method_title   = __( 'Nexio', 'cms-gateway-nexio' );
		/* translators: 1) link to nexio register page 2) link to nexio api keys page */
		$this->method_description = sprintf( __( 'Nexio works by adding payment fields on the checkout and then sending the details to Nexio for verification. <a href="%1$s" target="_blank">Connect us</a> for a Nexio account, and get your Nexio account token</a>.', 'cms-gateway-nexio' ), 'https://nexiopay.com/contact/');
		$this->has_fields         = false;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title                       = $this->get_option( 'title' );
		$this->description                 = $this->get_option( 'description' );
		$this->enabled                     = $this->get_option( 'enabled' );
		$this->api_url		= $this->get_option('api_url');
		$this->user_name	= $this->get_option('user_name');
		$this->password	= $this->get_option('password');
		$this->merchant_id = $this->get_option('merchant_id');
		$this->css = trim($this->get_option('css'));
		$this->customtext_url = trim($this->get_option('customtext_url'));
		$this->fraud = $this->get_option('fraud');
		$this->requirecvc = $this->get_option('requirecvc');
		$this->hidecvc = $this->get_option('hidecvc');
		$this->hidebilling = $this->get_option('hidebilling');
		$this->authonly = $this->get_option('authonly');
		$this->order_button_text = __( 'Continue to payment', 'cms-gateway-nexio' );

		//get merchant share secret at the beginning
		$this->shareSecret = $this->get_secret();

		
		// Hooks.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_nexio', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'nexio_checkout_return_handler' ) );
		add_action( 'woocommerce_api_callback', array( $this, 'nexio_checkout_return_failure_handler' ) );
		add_action( 'woocommerce_thankyou_nexio', array( $this,'custom_content_thankyou'), 10,1);

	}
	
	/**
	 * Show customized message in order received page after payment success.
	 *
	 * @since 0.0.1
	 */
	public function custom_content_thankyou($order_id) {
		$order = wc_get_order($order_id); 

		$orderstat = $order->get_status();
		if($orderstat == 'processing')
			echo '<p>'.__('Payment is successfully processed by Nexio!').'</p>';
		else
			echo '<p>'.__('Payment is successfully processed by Nexio, but your order status is not processing, please check!').'</p>';
	}

	/**
	 * payment_fields function.
	 *
	 * @since 0.0.1
	 * @version 1.0.0
	 * @return null
	 */
	public function payment_fields() {
		$testwarning = ''; 
		
		if (strpos($this->api_url, 'sandbox') !== false) {
			//it is a test URL
			$testwarning = '<p id="testwarn1" style="color:red;">!!!YOU ARE IN TEST MODE!!!</p>';
		}
		echo $testwarning.wpautop(wptexturize('Please click below button to continue payment'));
	}

	/**
	 * payment_scripts function.
	 *
	 * @since 0.0.1
	 * @version 1.0.0
	 * @return null
	 */
	public function payment_scripts() {
		
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = require( dirname( __FILE__ ) . '/nexio-settings.php' );
	}

	/**
	 * processing the callback data from nexio
	 *
	 * @since 0.0.4
	 */
	public function checking_success_data($callbackdata)
	{	
		if(isset($callbackdata->data) && !empty($callbackdata->data) &&
		isset($callbackdata->gatewayResponse) && !empty($callbackdata->gatewayResponse) &&
		isset($callbackdata->data->customer->orderNumber) && !empty($callbackdata->data->customer->orderNumber))
		{
			$order_id = $callbackdata->data->customer->orderNumber;
				
			error_log('ORDER Number:'.$order_id);
			
			$order    = wc_get_order( $order_id );

			try
			{
				//check gateway response.
				if($callbackdata->gatewayResponse->result !== 'Approved')
				{
					//although succes webhook is called, but the result in gateway response is not 'Approved', something wrong, do nothing
					error_log('Success webhook is called, but gateway response is not approved, please check with Nexio!',0);
					return;
				}
				
				if($this->fraud === 'yes')
				{
					//need check kount response first
					if(isset($callbackdata->kountResponse) && !empty($callbackdata->kountResponse) &&
						isset($callbackdata->kountResponse->status) && !empty($callbackdata->kountResponse->status))
					{
						//check the status
						if($callbackdata->kountResponse->status === 'success')
						{
							//everything is fine, complete the order
							$this->complete_order($order_id, $callbackdata, true);
							
							return;
						}
						else if($callbackdata->kountResponse->status === 'review')
						{
							//kount response is review, clear cart, change order status to on hold, add note
							$order->update_status('on-hold', 'Transaction is AuthOnly, please login Nexio dashboard to aprrove or decline it!');
							// Remove cart.
							WC()->cart->empty_cart();
							error_log('kount response status is review!',0);
							return;
						}
						else
						{
							//kount fails, update status to failed, acutally it should not happen, since it comes from payment success callback URL
							$order->update_status('failed', sprintf(__('kount response status is '.$callbackdata->kountResponse->status.', please check with Nexio!', 'cms-gateway-nexio')));
							
							error_log('kount response status is not success, please check with Nexio!',0);
							return;
						}
						
					}
					else
					{
						//fraud is set to enable but no kount response, something wrong, update status to failed,acutally it should not happen, since it comes from payment success callback URL
						$order->update_status('failed', sprintf(__('fraud check is set to enable but no kount response. Please contact Nexio', 'cms-gateway-nexio')));
						
						error_log('Fraud check is selected but no kount response or status, please check with Nexio!',0);
						return;
					}
				}
				else
				{
					// Remove cart.
					$this->complete_order($order_id, $callbackdata, false);
					
				}
				
			}
			catch(Exception $e)
			{
				$order->add_order_note(sprintf(__('CALL BACK get exception:'.$e->getMessage(), 'cms-gateway-nexio')));
				error_log('CALL BACK get exception:'.$e->getMessage(),0);
			}
			
			
		} 
	}

	/**
	 * Handles the return from processing the payment.
	 *
	 * @since 0.0.1
	 */
	public function nexio_checkout_return_handler() {
		error_log('CALLBACK WORKS!!!!!',0);
		
		$headerStringValue = $_SERVER['HTTP_NEXIO_SIGNATURE'];
		
		if($headerStringValue === null)
			error_log('value is null');

		error_log('nexio-signature: '.$headerStringValue,0);
		$data = file_get_contents('php://input');
		error_log('data:'.$data);
		

		if($this->check_signature($headerStringValue,$data))
		{
			error_log('signature verification pass');
			
			$callbackdata = json_decode($data);
			
			
			$this->checking_success_data($callbackdata->data);
		}
		else
		{
			error_log('signature verification not pass, try agin');
			//try to get secret and do it again.
			$this->shareSecret = $this->get_secret();
			if($this->check_signature($headerStringValue,$data))
			{
				error_log('signature verification pass');
				
				$callbackdata = json_decode($data);
				
				
				$this->checking_success_data($callbackdata->data);
			}
			else
			{
				error_log('signature verification not pass, give up retry');
				error_log('nexio-signature verification failed, dump the data',0);
			}
			
		}

		
	}

	/**
	 * update_secret
	 * update the share secret of merchant
	 * @since 0.0.5
	 * @return string
	 * 
	 */
	private function update_secret()
	{
		try {
			$request = array(
				'merchantId' => $this->merchant_id
			);

			$data = json_encode($request);
		
			$basicauth = "Basic ". base64_encode($this->user_name . ":" . $this->password);
			
			$ch = curl_init($this->api_url.'webhook/v3/updateMerchantWebhookSecret');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Authorization: $basicauth",
				"Content-Type: application/json",
				"Content-Length: " . strlen($data)));
			$result = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);
			
			

			if ($error) {
				return "error";
			} else {
				if(json_decode($result)->error)
				{
					
					return "error";
				}
				
				$secert = json_decode($result)->secret;
				error_log('update secert: '.$secert);
				return $secert;
			}
		} catch (Exception $e) {
			
			error_log("update secert failed:".$e->getMessage(),0);
			return "error";
		}
	}

	/**
	 * get_secret
	 * get the share secret of merchant
	 * @since 0.0.5
	 * @return string
	 * 
	 */
	private function get_secret()
	{
		try {
			$request = array(
				'merchantId' => $this->merchant_id
			);

			$data = json_encode($request);
			
			
			$basicauth = "Basic ". base64_encode($this->user_name . ":" . $this->password);
			
			$ch = curl_init($this->api_url.'webhook/v3/merchantWebhookSecret/'.$this->merchant_id);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Authorization: $basicauth",
				"Content-Type: application/json"));
			$result = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);
			
			error_log('get secert response: '.$result);

			if ($error) {
				
				return "error";
			} else {
				if(json_decode($result)->error)
				{
					
					return "error";
				}
				
				$secert = json_decode($result)->secret;
				error_log('get secert: '.$secert);
				return $secert;
			}
		} catch (Exception $e) {
			
			error_log("Get secert failed:".$e->getMessage(),0);
			return "error";
		}
	}

	/**
	 * check_signature
	 * check the signature
	 * @since 0.0.5
	 * @param string $nexiosignature 	   the value of nexio-signature header
	 * @param string $rawpayload  		   raw playload of the callback post data
	 * @return bool
	 */
	private function check_signature($nexiosignature,$rawpayload)
	{
		$firstpos = strrpos($nexiosignature,'t=');
		$commonpos = strrpos($nexiosignature, ',');
		$secondpos = strrpos($nexiosignature,'v1=');
		$len = strlen($nexiosignature);

		$timestamp = substr($nexiosignature, $firstpos + 2, $commonpos - 2);
		$signature = substr($nexiosignature, $secondpos + 3, $len - $secondpos - 3);

		$newpayload = $timestamp.'.'.$rawpayload;

		error_log('shareSecret: '.$this->shareSecret);
		
		if($this->shareSecret === 'error' || is_null($this->shareSecret))
		{
			//try to get shareSecret again
			error_log('shareSecret is not set, get it first');
			$this->shareSecret = $this->get_secret();
			if($this->shareSecret === 'error' || is_null($this->shareSecret))
			{			
				error_log('shareSecret is not setted failed');
				return false;
			}
		}

		$verifysig = hash_hmac('sha256',$newpayload,$this->shareSecret);

		error_log('newpayload sig: '.$verifysig);

		if($verifysig === $signature)
			return true;
		else
			return false;
	}


	/**
	 * Payment completed successfully, process the order accrodingly.
	 *
	 * @since 0.0.3
	 * @param WC_order $order 	   the order object
	 * @param array $callbackdata  array data contains content come from Nexio server
	 * @param bool $fraudchecking  
	 * 
	 */
	public function complete_order($order_id, $callbackdata, $fraudchecking) {
		//add transStatus, batchRef, refNumber, gatewayName, result and message as order note.
		$order    = wc_get_order( $order_id );
		$order->payment_complete();

		$note = 'Nexio Payment Completed, ';
		if($fraudchecking)
			$note = $note.'fraud check passed';
		else
			$note = $note.'no fraud check executed.';
		
		$order->add_order_note(sprintf(__($note, 'cms-gateway-nexio')));

		$order->add_order_note(sprintf(__('batchRef: '.$callbackdata->gatewayResponse->batchRef, 'cms-gateway-nexio')));
		
		$order->add_order_note(sprintf(__('refNumber: '.$callbackdata->gatewayResponse->refNumber, 'cms-gateway-nexio')));

		
	}

	/**
	 * processing the failure callback data from nexio
	 *
	 * @since 0.0.8
	 */
	public function checking_fail_data($callbackdata)
	{
		//right now, only kount failure can get order id.
		if(isset($callbackdata->kountError) && !empty($callbackdata->kountError === true) &&
		isset($callbackdata->kountResults->data->ORDR) && !empty($callbackdata->kountResults->data->ORDR) &&
		isset($callbackdata->kountResults->result) && !empty($callbackdata->kountResults->result))
		{
			//get order id
			$order_id = $callbackdata->kountResults->data->ORDR;
			error_log('Kount failed ORDER Number:'.$order_id);
			
			$order    = wc_get_order( $order_id );

			$order->update_status('failed', sprintf(__('kount result is '.$callbackdata->kountResults->result.'!', 'cms-gateway-nexio')));
		}
	}

	/**
	 * Handles the failure return from processing the payment.
	 *
	 * @since 0.0.1
	 */
	public function nexio_checkout_return_failure_handler() {
		//nothing need to do now, since failure operation is processed by JavaScript.
		error_log('FAILURE CALLBACK WORKS!!!!!',0);
		
		$headerStringValue = $_SERVER['HTTP_NEXIO_SIGNATURE'];
		
		if($headerStringValue === null)
			error_log('failure callback header nexio-signature is null');

		error_log('failure callback nexio-signature: '.$headerStringValue,0);
		$data = file_get_contents('php://input');
		error_log('payment fail callback data:'.$data);
		

		if($this->check_signature($headerStringValue,$data))
		{
			error_log('failure callback signature verification pass');
			
			$callbackdata = json_decode($data);
			
			
			$this->checking_fail_data($callbackdata->data);
		}
		else
		{
			error_log('failure callback signature verification not pass, try agin');
			//try to get secret and do it again.
			$this->shareSecret = $this->get_secret();
			if($this->check_signature($headerStringValue,$data))
			{
				error_log('failure callback signature verification pass');
				
				$callbackdata = json_decode($data);
				
				
				$this->checking_fail_data($callbackdata->data);
			}
			else
			{
				error_log('failure callback signature verification not pass, give up retry');
				error_log('failure callback nexio-signature verification failed, dump the data',0);
			}
			
		}

	}

	/**
	 * Generate the form of pre-order page.
	 *
	 * @since 0.0.1
	 */
	public function generate_nexio_form( $order_id ) {
		global $woocommerce;

		$order = wc_get_order($order_id);

		//get one time token first
		$onetimetoken = $this->get_iframe_src($this->get_creditcard_token($order_id));
		
		$testwarning = ''; 
		error_log('api_url is: '.$this->api_url);
		if (strpos($this->api_url, 'sandbox') !== false) {
			error_log('api_url is test url');
			//it is a test URL
			$testwarning = '<p id="testwarn1" style="color:red;">!!!YOU ARE IN TEST MODE!!!</p>';
		}

		$tokenerror = '';
		if (strpos($onetimetoken, 'error') !== false) {
			//get one time token return error, need info user to try again.
			return $testwarning.'<p id="tokenerror" class="woocommerce-error"> Fail to generate payment form, please go back to checkout page and retry!</p>
			<a href="'.wc_get_checkout_url().'"><input type="button" value="Back to Checkout"></a>';
		}

		wc_enqueue_js('
				cms_payment_form.addEventListener("submit", function processPayment(event) {
				event.preventDefault();
				iframe1.contentWindow.postMessage("posted", "'.$onetimetoken.'");
				return false;
			});

			window.addEventListener("message", function messageListener(event) {
				if (event.origin === "'.rtrim($this->api_url, '/\\').'") {
					if (event.data.event === "loaded") {
						window.document.getElementById("iframe1").style.display = "block";
						window.document.getElementById("loader").style.display = "none";
					}
					if (event.data.event === "processed") {
						if("'.$this->fraud.'" === "yes")
						{
							if(event.data.data.kountResponse.status === "review")
							{
								window.document.getElementById("p1").innerHTML = "";
								window.document.getElementById("cms_payment_form").innerHTML = "<p>Your payment is Authorized</p><p>Wait merchant to approve it.</p><a href=\"'.get_permalink( wc_get_page_id( 'shop' ) ).'\"><input type=\"button\" value=\"Back to Shop\"/></a>";
								return;
							}
						}
							
						window.document.getElementById("p1").innerHTML = "";
						var jsonStr = JSON.stringify(event.data.data, null, 1);
						window.document.getElementById("cms_payment_form").innerHTML = "<p>Successfully Processed Credit Card Transaction</p><p>You will be direct to order received page soon...</p>";
						setTimeout(function () {
							window.location = "'.$this->get_return_url( $order ).'";
						}, 5000);

						
					}
					if (event.data.event === "error"){
						var msg = event.data.data.message;
						
						window.document.getElementById("p1").innerHTML = "";
						window.document.getElementById("cms_payment_form").innerHTML = "<p>Transaction Declined</p><p>Response from Nexio: " + msg + "</p><p>please click Back to Checkout button to try again.</p><a href=\"'.wc_get_checkout_url().'\"><input type=\"button\" value=\"Back to Checkout\"/></a>";
						
					}
				}
			});
		');

		return $testwarning.'<p id="p1">Thank you for your order, please input your payment information in blow form and click the button to submit transaction.</p><form id="cms_payment_form" height="900px" width="400px" action="'.esc_url( $onetimetoken ).'" method="post">
		<iframe type="iframe" id="iframe1" src="'.$onetimetoken.'" style="border:0" height="750px"></iframe>
		<input type="submit" class="button" id="submit_cms_payment_form" value="'.__('Pay via Nexio', 'cms-gateway-nexio').'" />
		</form>
		<div id="loader"></div>';

	}
		

	/**
	 * Generate the json string for getting one time token.
	 *
	 * @since 0.0.1
	 */
	function build_gettoken_json($order_id)
	{
		$order = wc_get_order($order_id);
		//1. get data array first
			//1.1 get customer array first.
		$customer = array(
			'orderNumber' => $order_id,
			'firstName' => $order->get_billing_first_name(),
			'lastName' => $order->get_billing_last_name(),
			'billToAddressOne' => $order->get_billing_address_1(),
			'billToAddressTwo' => $order->get_billing_address_2(),
			'billToCity' => $order->get_billing_city(),
			'billToState' => $order->get_billing_state(),
			'billToPostal' => $order->get_billing_postcode(),
			'billToCountry' => $order->get_billing_country()
		);

			//1.2 build data array
		$data = array(
			'paymentMethod'=>'creditCard',
			'allowedCardTypes'=>[ 'visa', 'mastercard','discover','amex' ],
			'amount' => $order->get_total(),
			'currency' => get_woocommerce_currency(),
			'customer' => $customer
		);

		//2. processingOptions
		$processingOptions = array(
			'webhookUrl' => $this->get_callback_url(),
			'webhookFailUrl' => $this->get_failure_callback_url(),
			'checkFraud' => ($this->fraud === 'yes'?true:false),
			'verboseResponse' => false
		);
		

		//3. uiOptions
		$uiOptions = array(
			'css' => (!empty($this->css)?$this->css:''),
			'displaySubmitButton' => false,
			'hideCvc' => ($this->hidecvc === 'yes'?true:false),
			'requireCvc' => ($this->requirecvc === 'yes'?true:false),
			'hideBilling' => ($this->hidebilling === 'yes'?true:false),
		);
		
		if(!is_null($this->customtext_url))
		{
			$uiOptions['customTextUrl'] = $this->customtext_url;
		}

		//4. card
		$card = array(
			'cardHolderName' => $order->get_billing_first_name().' '.$order->get_billing_last_name()
		);

		//5. TODO cart


		//build the whole array
		$request = array(
			'data' => $data,
			'processingOptions' => $processingOptions,
			'uiOptions' => $uiOptions,
			'card' => $card,
			'isAuthOnly' => ($this->authonly === 'yes'?true:false),
		);

		//convert to json
		$jsondata = json_encode($request);
		
		return $jsondata;
	}

	/**
	 * get_callback_url function.
	 *
	 * @since 0.0.1
	 * @version 1.0.0
	 * @return string
	 */
	public function get_callback_url()
	{
		$callbackurl = get_site_url(null,null,'https').'/wc-api/'.strtolower( get_class( $this ) );
		
		return $callbackurl;
	}

	/**
	 * get_failure_callback_url function.
	 *
	 * @since 0.0.1
	 * @version 1.0.0
	 * @return string
	 */
	public function get_failure_callback_url()
	{
		$callbackurl = get_site_url(null,null,'https').'/wc-api/CALLBACK/';
		
		
		return $callbackurl;
	}

	/**
	 * Get one time token for fetch iFrame.
	 *
	 * @since 0.0.1
	 */
	public function get_creditcard_token($order_id)
	{		
		$order = wc_get_order($order_id);
		try {
			$data = $this->build_gettoken_json($order_id);
			error_log('one timetoken request: '.$data);
			$basicauth = "Basic ". base64_encode($this->user_name . ":" . $this->password);
			error_log('basic auth: '.$basicauth);
			$ch = curl_init($this->api_url.'pay/v3/token');
			
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Authorization: $basicauth",
				"Content-Type: application/json",
				"Content-Length: " . strlen($data)));
			$result = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);
			error_log('getonetimetoken response: '.$result);
			if ($error) {
				
				return "error";
			} else {
				
				$onetimetoken = json_decode($result)->token;
				if(json_decode($result)->error)
				{
					
					return "error";
				}
				$this->token = $onetimetoken;
				
				error_log("Get One Time Token:".$onetimetoken,0);
				return json_decode($result)->token;
			}
		} catch (Exception $e) {
			
			error_log("Get One Time Token:".$e->getMessage(),0);
			return "error";
		}
	}

	/*
	 * Process the payment
	 * 
	 */
	function receipt_page( $order_id ) {
		$order = wc_get_order($order_id);
		
		
		echo $this->generate_nexio_form( $order_id );
	}


	/**
	 * Get iFrame src url.
	 *
	 * @since 0.0.1
	 * @return string
	 */
	public function get_iframe_src($newvalue)
	{
		$src = $this->api_url."pay/v3/?token=".$newvalue;
		return $src;
	}
	

	public function process_payment( $order_id, $retry = true, $force_save_source = false, $previous_error = false ) {

		$order = wc_get_order($order_id);

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true),
		);
		
	}
}
