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
Stable tag: 1.1.3
Version: 1.1.3
*/

namespace woo_pvt;

use woo_pvt\lib\Ioc_Container_Interface;
use woo_pvt\lib\Ioc_Container;

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
define( 'WOO_PVT_VERSION', '1.1.3');

/**
 * Rest API namespace
 */
define( 'WOO_PVT_REST_API_NAMESPACE', 'woo-pvt' );

/**
 * Register autoloader to load files/classes dynamically
 */
include_once WOO_PVT_DIR . 'lib/autoloader.php';

/**
 * Register global functions
 */
include_once WOO_PVT_DIR . 'lib/helper.php';

/**
 * Load composer
 *
 * "php-di/php-di": "5.0"
 */
include_once WOO_PVT_DIR . 'lib/ioc/autoload.php';

/**
 * Class Appsaloon_Plugin_Controller
 * @package apc
 */
class Premium_Plugin_Controller {

    /**
     * Appsaloon_Plugin_Controller constructor.
     *
     * @param Ioc_Container_Interface $container
     *
     * @since 1.0.0
     */
    public function __construct( Ioc_Container_Interface $ioc_container ) {
        $ioc_container->container->get('plugin_config')->register_plugin_settings();
        $ioc_container->container->get('rest_endpoint_config')->register_routes();
        $ioc_container->container->get('show_product_variations_table')->register_product_variations_table();
    }

}

/**
 * Initiate the cookiebot addons framework plugin
 */
new Premium_Plugin_Controller( Ioc_Container::getInstance() );
