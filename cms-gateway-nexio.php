<?php
/**
 * Plugin Name: Nexio
 * Description: Take credit card payments on your store using Nexio.
 * Author: Complete Merchant Solutions
 * Author URI: https://www.cmsonline.com/
 * Version: 0.0.8
 * Requires at least: 4.4
 * Tested up to: 5.0
 * WC requires at least: 3.0
 * WC tested up to: 5.1
 * Text Domain: cms-gateway-nexio
 * Domain Path: /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 4.1.2
 * @return string
 */
function woocommerce_nexio_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Nexio requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-stripe' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

add_action( 'plugins_loaded', 'woocommerce_gateway_nexio_init' );

function woocommerce_gateway_nexio_init() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_nexio_missing_wc_notice' );
		return;
	}

	if ( ! class_exists( 'CMS_Nexio' ) ) :
		/**
		 * Required minimums and constants
		 */
		define( 'CMS_NEXIO_VERSION', '0.0.02' );
		define( 'CMS_NEXIO_MIN_PHP_VER', '5.6.0' );
		define( 'CMS_NEXIO_MIN_WC_VER', '2.6.0' );
		define( 'CMS_NEXIO_MAIN_FILE', __FILE__ );
		
		class CMS_Nexio {

			/**
			 * @var Singleton The reference the *Singleton* instance of this class
			 */
			private static $instance;

			/**
			 * Returns the *Singleton* instance of this class.
			 *
			 * @return Singleton The *Singleton* instance.
			 */
			public static function get_instance() {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			/**
			 * Private clone method to prevent cloning of the instance of the
			 * *Singleton* instance.
			 *
			 * @return void
			 */
			private function __clone() {}

			/**
			 * Private unserialize method to prevent unserializing of the *Singleton*
			 * instance.
			 *
			 * @return void
			 */
			private function __wakeup() {}

			/**
			 * Protected constructor to prevent creating a new instance of the
			 * *Singleton* via the `new` operator from outside of this class.
			 */
			private function __construct() {
				add_action( 'admin_init', array( $this, 'install' ) );
				$this->init();
			}

			/**
			 * Init the plugin after plugins_loaded so environment variables are set.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			public function init() {
				
				require_once dirname( __FILE__ ) . '/class-cms-gateway-nexio.php';

				add_filter( 'woocommerce_payment_gateways', array($this,'add_gateways') );				
			}

			/**
			 * Updates the plugin version in db
			 *
			 * @since 3.1.0
			 * @version 4.0.0
			 */
			public function update_plugin_version() {
				delete_option( 'CMS_NEXIO_VERSION' );
				update_option( 'CMS_NEXIO_VERSION', CMS_NEXIO_VERSION );
			}

			/**
			 * Handles upgrade routines.
			 *
			 * @since 3.1.0
			 * @version 3.1.0
			 */
			public function install() {
				if ( ! is_plugin_active( plugin_basename( __FILE__ ) ) ) {
					return;
				}

				if ( ! defined( 'IFRAME_REQUEST' ) && ( CMS_NEXIO_VERSION !== get_option( 'CMS_NEXIO_VERSION' ) ) ) {
					do_action( 'woocommerce_nexio_updated' );

					if ( ! defined( 'WC_NEXIO_INSTALLING' ) ) {
						define( 'WC_NEXIO_INSTALLING', true );
					}

					$this->update_plugin_version();
				}
			}

			/**
			 * Add the gateways to WooCommerce.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			public function add_gateways( $methods ) {
				
				$methods[] = 'CMS_Gateway_Nexio';

				return $methods;
			}

		}

		CMS_Nexio::get_instance();
	endif;
}
