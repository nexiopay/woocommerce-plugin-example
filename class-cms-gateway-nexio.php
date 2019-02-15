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
	 * Constructor
	 */
	public function __construct() {
		$this->id             = 'nexio';
		$this->method_title   = __( 'Nexio Credit Card', 'cms-gateway-nexio' );
		/* translators: 1) link to nexio register page 2) link to nexio api keys page */
		$this->method_description = sprintf( __( 'Nexio works by adding payment fields on the checkout and then sending the details to Nexio for verification. <a href="%1$s" target="_blank">Connect us</a> for a Nexio account, and get your Nexio account token</a>.', 'cms-gateway-nexio' ), 'https://nexiopay.com/contact/');
		$this->has_fields         = false;
		/*
		$this->supports           = array(
			'products',
			'refunds',
			'tokenization',
			'add_payment_method',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
			'pre-orders',
		);
		*/

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
		$this->order_button_text = __( 'Continue to payment', 'cms-gateway-nexio' );
		

		// Hooks.
		add_action( 'init', array( $this, 'process_complete' ) );
		add_action( 'woocommerce_api_woocommerce_nexio', array( $this, 'successful_request' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_nexio', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'nexio_checkout_return_handler' ) );
		add_action( 'woocommerce_api_callback', array( $this, 'nexio_checkout_return_handler' ) );

	}
	
	public function get_callback_url()
	{
		$callbackurl = get_site_url().'/wc-api/'.strtolower( get_class( $this ) );
		//$callbackurl = get_site_url().'/wc-api/CALLBACK';
		
		return $callbackurl;
	}
	
	/**
	 * Checks if gateway should be available to use.
	 *
	 * @since 4.0.2
	 */
	public function is_available() {
		if ( is_add_payment_method_page() && ! $this->saved_cards ) {
			return false;
		}

		return parent::is_available();
	}

	public function payment_fields() {
		echo wpautop(wptexturize('Please click below button to continue payment'));
	}

	public function payment_scripts() {
		
	}
	/**
	 * Get_icon function.
	 *
	 * @since 1.0.0
	 * @version 4.0.0
	 * @return string
	 */
	/*
	public function get_icon() {
		$icons = $this->payment_icons();

		$icons_str = '';

		$icons_str .= isset( $icons['visa'] ) ? $icons['visa'] : '';
		$icons_str .= isset( $icons['amex'] ) ? $icons['amex'] : '';
		$icons_str .= isset( $icons['mastercard'] ) ? $icons['mastercard'] : '';

		if ( 'USD' === get_woocommerce_currency() ) {
			$icons_str .= isset( $icons['discover'] ) ? $icons['discover'] : '';
			$icons_str .= isset( $icons['jcb'] ) ? $icons['jcb'] : '';
			$icons_str .= isset( $icons['diners'] ) ? $icons['diners'] : '';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icons_str, $this->id );
	}
	*/

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = require( dirname( __FILE__ ) . '/nexio-settings.php' );
	}

	/**
	 * Handles the return from processing the payment.
	 *
	 * @since 4.1.0
	 */
	public function nexio_checkout_return_handler() {
		if(isset($_POST['cmsnexio_action']) && !empty($_POST['cmsnexio_action']) &&
		   isset($_POST['order_id']) && !empty($_POST['order_id']))
		{
			try
			{
				$order_id = wc_clean( $_POST['order_id'] );
				$order    = wc_get_order( $order_id );
				// Remove cart.
				$order->add_order_note(sprintf(__('Nexio Payment Completed.', 'cms-gateway-nexio')));
				$order->payment_complete();
				WC()->cart->empty_cart();
				
				//wp_redirect($this->get_return_url( $order ));
				//if fail, right now, we do not consider payment failure process because we only do status update and clear cart after transaction success.
				//wc_add_notice( sprintf(__('Transaction Failed.', 'cms-gateway-nexio')), $notice_type = 'error' );
				//wp_redirect( get_permalink(get_option( 'woocommerce_checkout_page_id' )) ); exit;


				//before exit, send complete back to iFrame page so it can now where to redirect.
				echo 'complete';
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
			}
			
			exit;
		}
	}


	public function generate_nexio_form( $order_id ) {

		global $woocommerce;

		$order = new WC_Order( $order_id );

		//get one time token first
		$onetimetoken = $this->get_iframe_src($this->get_creditcard_token($order_id));


		$gateway_url = $this->get_iFrameURL();//dirname( __FILE__ ) . '/iframe_CreditCardTransactioin.php';
		
		$redirect_url_success = $this->get_return_url( $order );
		$redirect_url_fail = get_permalink(get_option( 'woocommerce_checkout_page_id' ));
		$phpurl = $this->get_callback_url();
		return  '<form action="'.$gateway_url.'" method="post" id="cms_payment_form">
		<input type="hidden" name="iframeurl" value="'.$this->api_url."pay/v3/".'">
		<input type="hidden" name="token" value="'.$onetimetoken.'">
		<input type="hidden" name="orderid" value="'.$order_id.'">
		<input type="hidden" name="phpurl" value="'.$phpurl.'">
		<input type="hidden" name="redirecturl_success" value="'.$redirect_url_success.'">
		<input type="hidden" name="redirecturl_fail" value="'.$redirect_url_fail.'">
		<input type="submit" class="button" id="submit_cms_payment_form" value="'.__('Pay via CMS Nexio', 'cms-gateway-nexio').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'cms-gateway-nexio').'</a>
		</form>';

	}


	function build_gettoken_json($order_id)
	{
		$order = new WC_Order( $order_id );
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
			'webhookUrl' => '',
			'webhookFailUrl' => '',
			'checkFraud' => true,
			'verifyCvc' => false,
			'verifyAvs' => 0,
			'verboseResponse' => true
		);

		//3. uiOptions
		$uiOptions = array(
			'customTextUrl' => '',
			'displaySubmitButton' => false,
			'hideCvc' => false,
			'requireCvc' => true,
			'hideBilling' => false,
			);
		
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
			'card' => $card
		);

		//convert to json
		return json_encode($request);
	}

	public function get_creditcard_token($order_id)
	{
		
		$order = new WC_Order( $order_id );
		try {
			$data = $this->build_gettoken_json($order_id);
			$basicauth = "Basic ". base64_encode($this->user_name . ":" . $this->password);
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
		
			if ($error) {
				echo "CURL Error #: $error";
			} else {
				$onetimetoken = json_decode($result)->token;
				$this->token = $onetimetoken;
				//echo "Get One Time Token #: $onetimetoken";
				return json_decode($result)->token;
			}
		} catch (Exception $e) {
			echo "Get token failed #: $e->getMessage()";
			return $e->getMessage();
		}
	}

	public function get_iFrameURL()
	{
		
		$siteurl = get_site_url();
		$wpconfigfilepath = $this->fs_get_wp_config_path();
		if($wpconfigfilepath !=false)
		{
			$wordpressroot = dirname($wpconfigfilepath);
			$pluginurl = plugin_dir_path( __FILE__ );
	
	
	
			//$finallink = str_replace( 'my-account/', 'iframe_CreditCardTransaction.php', $myaccount_page_url );
	
			$finallink = str_replace( $wordpressroot, $siteurl, $pluginurl );
	
			$path = str_replace("\\", "/", $finallink);
	
			return $path."iframe_CreditCardTransaction.php";
		}
		else
			return false;
	}

	/*
	 * Process the payment
	 * 
	 */
	function receipt_page( $order_id ) {
		
		echo '<p>'.__('Thank you for your order, please click the button below to pay with CMS Nexio Form.', 'cms-gateway-nexio').'</p>';
		echo $this->generate_nexio_form( $order_id );
	}

	public function fs_get_wp_config_path()
	{
		$base = dirname(__FILE__);
		$path = false;

		if (@file_exists(dirname(dirname($base))."\wp-config.php"))
		{
			$path = dirname(dirname($base))."\wp-config.php";
		}
		else
		if (@file_exists(dirname(dirname(dirname($base)))."\wp-config.php"))
		{
			$path = dirname(dirname(dirname($base)))."\wp-config.php";
		}
		else
			$path = false;

		return $path;
	}


	public function get_iframe_src($newvalue)
	{
		
		$src = $this->api_url."pay/v3/?token=".$newvalue;
		return $src;
	}
	

	public function process_payment( $order_id, $retry = true, $force_save_source = false, $previous_error = false ) {
		/*$order->payment_complete();
		$order = wc_get_order( $order_id );

		// Remove cart.
		WC()->cart->empty_cart();*/

		// Return thank you page redirect.
		$order = new WC_Order( $order_id );
		
		//$GLOBALS['token'] = $this->get_creditcard_token($order_id);
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true),//$this->get_return_url( $order ),
		);
	}

}
