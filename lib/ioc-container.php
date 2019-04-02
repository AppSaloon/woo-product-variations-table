<?php

namespace woo_pvt\lib;

use \DI;
use \DI\ContainerBuilder;
use mysql_xdevapi\Exception;

/**
 * Inversion of Control Container
 *
 * This is the container where we save all the classes used in this plugin.
 *
 * Class Ioc_Container
 * @package ppm\lib
 *
 * @since 1.0.0
 */
Final class Ioc_Container implements Ioc_Container_Interface {

	/**
	 * @var \DI\ContainerBuilder
	 */
	protected $builder;

	/**
	 * @var \DI\Container
	 */
	public $container;

	/**
	 * @var Ioc_Container
	 */
	protected static $instance;

	/**
	 * Build Container.
	 */
	public function __construct() {
		$this->builder = new ContainerBuilder();

		$this->build_container();
	}

	/**
	 * Instance of this class
	 *
	 * @return Ioc_Container
	 */
	public static function getInstance() {
		if ( null == static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Build Container
	 *
	 * @since 1.0.0
	 */
	public function build_container() {
		$this->builder->addDefinitions( [
			'settings'      => DI\object( 'woo_pvt\model\Settings' ),
		] );

		$this->container = $this->builder->build();

		$this->set_plugin_config();

		$this->set_variation_table();

		$this->set_endpoint_config();

		$this->set_table_for_product_variations();
	}

	/**
	 * Set Plugin config
	 *
	 * @since 1.0.0
	 */
	public function set_plugin_config() {
		$this->container->set( 'plugin_config',
			DI\object( 'woo_pvt\config\Plugin_Config' )
				->constructor(
					$this->container->get( 'settings' )
				)
		);
	}

	/**
	 * Set Variation table
	 *
	 * @since 1.0.0
	 */
	public function set_variation_table() {
		$this->container->set( 'variation_table_config',
			DI\object( 'woo_pvt\config\Variation_Table_Config' )
				->constructor(
					$this->container->get( 'settings' )
				)
		);
	}

	/**
	 * Set Endpoint Config
	 *
	 * @since 1.0.0
	 */
	public function set_endpoint_config() {
		$this->container->set( 'rest_endpoint_config', DI\object( 'woo_pvt\config\Rest_Endpoint_Config' ) );
	}

	/**
	 * Set set_table_for_product_variations
	 *
	 * @since 1.0.0
	 */
	public function set_table_for_product_variations() {
		$this->container->set( 'show_product_variations_table', DI\object( 'woo_pvt\config\Show_Product_Variations_Table' ) );
	}
}