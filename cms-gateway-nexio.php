<?php
/**
 * Plugin Name: CMS Nexio Gateway
 * Plugin URI: https://wordpress.org/plugins/cms-gateway-nexio/
 * Description: Take credit card payments on your store using Nexio.
 * Author: Complete Merchant Solutions
 * Author URI: https://www.cmsonline.com/
 * Version: 0.0.01
 * Requires at least: 4.4
 * Tested up to: 5.0
 * WC requires at least: 2.6
 * WC tested up to: 3.5
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
	//load_plugin_textdomain( 'cms-gateway-nexio', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_nexio_missing_wc_notice' );
		return;
	}

	if ( ! class_exists( 'CMS_Nexio' ) ) :
		/**
		 * Required minimums and constants
		 */
		define( 'CMS_NEXIO_VERSION', '0.0.01' );
		define( 'CMS_NEXIO_MIN_PHP_VER', '5.6.0' );
		define( 'CMS_NEXIO_MIN_WC_VER', '2.6.0' );
		define( 'CMS_NEXIO_MAIN_FILE', __FILE__ );
		//define( 'WC_NEXIO_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		//define( 'WC_NEXIO_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

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
				if ( is_admin() ) {
					require_once dirname( __FILE__ ) . '/class-cms-nexio-privacy.php';
				}
				require_once dirname( __FILE__ ) . '/class-cms-gateway-nexio.php';
				/*
				

				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-exception.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-logger.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-helper.php';
				include_once dirname( __FILE__ ) . '/includes/class-wc-nexio-api.php';
				
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-webhook-handler.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-sepa-payment-token.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-apple-pay-registration.php';
				require_once dirname( __FILE__ ) . '/includes/compat/class-wc-nexio-pre-orders-compat.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-nexio.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-bancontact.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-sofort.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-giropay.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-eps.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-ideal.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-p24.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-alipay.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-sepa.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-nexio-multibanco.php';
				require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-nexio-payment-request.php';
				require_once dirname( __FILE__ ) . '/includes/compat/class-wc-nexio-subs-compat.php';
				require_once dirname( __FILE__ ) . '/includes/compat/class-wc-nexio-sepa-subs-compat.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-order-handler.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-payment-tokens.php';
				require_once dirname( __FILE__ ) . '/includes/class-wc-nexio-customer.php';

				if ( is_admin() ) {
					require_once dirname( __FILE__ ) . '/includes/admin/class-wc-nexio-admin-notices.php';
				}
				*/


				add_filter( 'woocommerce_payment_gateways', array($this,'add_gateways') );
				//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

				
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
			 * Adds plugin action links.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			/*
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="admin.php?page=wc-settings&tab=checkout&section=stripe">' . esc_html__( 'Settings', 'cms-gateway-nexio' ) . '</a>',
					'<a href="https://docs.woocommerce.com/document/stripe/">' . esc_html__( 'Docs', 'cms-gateway-nexio' ) . '</a>',
					'<a href="https://woocommerce.com/contact-us/">' . esc_html__( 'Support', 'cms-gateway-nexio' ) . '</a>',
				);
				return array_merge( $plugin_links, $links );
			}
			*/

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

			
			/**
			 * Modifies the order of the gateways displayed in admin.
			 *
			 * @since 4.0.0
			 * @version 4.0.0
			 */
			/*
			public function filter_gateway_order_admin( $sections ) {
				unset( $sections['stripe'] );
				unset( $sections['stripe_bancontact'] );
				unset( $sections['stripe_sofort'] );
				unset( $sections['stripe_giropay'] );
				unset( $sections['stripe_eps'] );
				unset( $sections['stripe_ideal'] );
				unset( $sections['stripe_p24'] );
				unset( $sections['stripe_alipay'] );
				unset( $sections['stripe_sepa'] );
				unset( $sections['stripe_multibanco'] );

				$sections['stripe']            = 'Stripe';
				$sections['stripe_bancontact'] = __( 'Stripe Bancontact', 'cms-gateway-nexio' );
				$sections['stripe_sofort']     = __( 'Stripe SOFORT', 'cms-gateway-nexio' );
				$sections['stripe_giropay']    = __( 'Stripe Giropay', 'cms-gateway-nexio' );
				$sections['stripe_eps']        = __( 'Stripe EPS', 'cms-gateway-nexio' );
				$sections['stripe_ideal']      = __( 'Stripe iDeal', 'cms-gateway-nexio' );
				$sections['stripe_p24']        = __( 'Stripe P24', 'cms-gateway-nexio' );
				$sections['stripe_alipay']     = __( 'Stripe Alipay', 'cms-gateway-nexio' );
				$sections['stripe_sepa']       = __( 'Stripe SEPA Direct Debit', 'cms-gateway-nexio' );
				$sections['stripe_multibanco'] = __( 'Stripe Multibanco', 'cms-gateway-nexio' );

				return $sections;
			}
			*/
		}

		CMS_Nexio::get_instance();
	endif;
}
