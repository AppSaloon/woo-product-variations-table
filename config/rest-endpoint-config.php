<?php

namespace woo_pvt\config;

use woo_pvt\lib\Ioc_Container;
use woo_pvt\model\Product;
use woo_pvt\model\Product_Query;

class Rest_Endpoint_Config extends \WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	CONST NAMESPACE = WOO_PVT_REST_API_NAMESPACE . '/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'product/(?P<product_id>[\d]+)';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $product_type = 'variable';

	/**
	 * Register API init
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	/**
	 * Register REST API endpoint
	 *
	 * @since 1.0.0
	 */
	public function routes() {
		register_rest_route( static::NAMESPACE,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'The variable product ID.', 'woo-pvt' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_item' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get order tracking code from an order.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 *
	 * @since 1.0.0
	 */
	public function get_item( $request ) {
		$product = wc_get_product( (int) $request->get_param( 'product_id' ) );

		if ( ! $product || $this->product_type !== $product->get_type() ) {
			return new \WP_Error( "woocommerce_rest_{$this->product_type}_invalid_id",
				__( 'Invalid product ID.', 'woocommerce' ),
				array( 'status' => 404 ) );
		}

		$currentPage = $request->get_param( 'currentPage' ) ?? 1;

		$attributes = $this->get_attributes_from_request_params( $request->get_params() );

		$container = Ioc_Container::getInstance();

		$response_data = ( new Product( new Product_Query( $product ) ) )
			->getProducVariationsByFilter( $attributes, $currentPage )
			->getJson();

		$response = new \WP_REST_Response();
		$response->set_status( 200 );
		$response->set_data( $response_data );

		return $response;

	}

	/**
	 * Retrieve attribute params
	 *
	 * @param $params
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function get_attributes_from_request_params( $params ) {
		$attributes = array();

		foreach ( woo_pvt_generator( $params ) as $key => $param ) {
			if ( strpos( $key, 'attribute_' ) !== false ) {
				$attributes[ $key ] = $param;
			}
		}

		return $attributes;
	}
}