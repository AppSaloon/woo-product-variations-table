<?php
/*
Plugin Name: Woo Product Variations table
Plugin URI: https://appsaloon.be
Description: Show WooCommerce variable products variations as table with filters and sorting instead of normal dropdowns.
Author: AppSaloon
Author URI: https://appsaloon.be
Text Domain: woo-product-variations-table
Domain Path: /languages/
Tags: woocommerce, product variations, list of product variations, filter product variations
Requires PHP: 7.0
Requires at least: 5.0
Tested up to: 5.5
Stable tag: 1.1.4
Version: 1.1.4
*/

use appsaloon\woo_pvt\config\Plugin_Config;
use appsaloon\woo_pvt\config\Rest_Endpoint_Config;
use appsaloon\woo_pvt\config\Show_Product_Variations_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * __DIR__ of this plugin
 */
define( 'WOO_PVT_DIR', __DIR__ . DIRECTORY_SEPARATOR );

define( 'WOO_PVT_URL', plugin_dir_url( __FILE__ ) );

define( 'WOO_PVT_BASE_NAME', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin version
 */
define( 'WOO_PVT_VERSION', '1.1.4' );

/**
 * Rest API namespace
 */
define( 'WOO_PVT_REST_API_NAMESPACE', 'woo-pvt' );

/**
 * Register autoloader to load files/classes dynamically
 */
include_once WOO_PVT_DIR . 'vendor/autoload.php';

/**
 * Register global functions
 */
include_once WOO_PVT_DIR . 'src/lib/helper.php';

/**
 * Class Woo_Product_Variatons_Table
 * @package apc
 */
class Woo_Product_Variatons_Table {

	/**
	 * Woo_Product_Variatons_Table constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		( new Plugin_Config() )->register_plugin_settings();
		( new Rest_Endpoint_Config() )->register_routes();
		( new Show_Product_Variations_Table() )->register_product_variations_table();
	}

}

/**
 * Initiate the cookiebot addons framework plugin
 */
new Woo_Product_Variatons_Table();
