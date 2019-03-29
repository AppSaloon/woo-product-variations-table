<?php
/*
Plugin Name: Woo Product Variations table
Plugin URI: https://appsaloon.be
Description: Show WooCommerce variable products variations as table with filters and sorting instead of normal dropdowns.
Author: AppSaloon
Author URI: https://appsaloon.be
Text Domain: woo-product-variations-table
Domain Path: /languages/
Version: 1.0.0
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

define( 'WOO_PVT_BASE_NAME', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Plugin version
 */
define( 'WOO_PVT_VERSION', '1.0.0' );

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
        $ioc_container->container->get('variation_table_config')->register_variation_table();
    }

}

/**
 * Initiate the cookiebot addons framework plugin
 */
new Premium_Plugin_Controller( Ioc_Container::getInstance() );