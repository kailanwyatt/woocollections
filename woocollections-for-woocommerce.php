<?php
/*
 * Plugin Name: WooCommerce - Collections
 * Version: 1.0.1
 * Description: Adds the ability for users to create collections of items and share with friends
 * Plugin URI: http://suiteplugins.com
 * Author: SuitePlugins
 * Author URI: http://suiteplugins.com
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/
/**
 * Define constants
 * @since 1.0.0
 */
define('WOO_COLLECTION_URL', plugins_url( '' , __FILE__ ));
define('WOO_COLLECTION_PATH', plugin_dir_path( __FILE__ ));
/**
 * Add languages
 * @since 1.0.0
 */
function woo_collection_lang_init() {
	$domain = 'woocollections-for-woocommerce';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	// wp-content/languages/woocollections-for-woocommerce/plugin-name-de_DE.mo
	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	// wp-content/plugins/woocollections-for-woocommerce/languages/plugin-name-de_DE.mo
	load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Add actions
add_action( 'plugins_loaded', 'woo_collection_lang_init' );

require_once( WOO_COLLECTION_PATH . 'init.php');	
add_action('bp_include', 'woo_setup_bp_instance');

/**
 * Check if WooCommerce is active and include files
 * @since 1.0.0
 */
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	error_log( 'not active' );
	function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) {
			error_log( __LINE__ );
		    
		}
	}
}


/**
 * Include BuddyPress Navigation
 * @since 1.0.0
 */
function woo_setup_bp_instance(){
	require_once( WOO_COLLECTION_PATH . 'includes/bp-setup.php');	
}