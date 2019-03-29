<?php

namespace woo_pvt\config;

use woo_pvt\model\Settings;

class Variation_Table_Config {

	private $settings;

	private $attributes;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	public function register_variation_table() {
		add_filter( 'woocommerce_after_single_product_summary', array( $this, 'build_variation_table' ), 9 );
	}

	public function build_variation_table() {
		global $product;
		if ( ! $product->is_type( 'variable' ) ) {
			return false;
		}
//		$variation_ids = $product->get_children( true );
//
//		$variation_query = $this->run_variation_query( $variation_ids );
//
//		$variations = $this->rearrange_variation_query( $variation_query );

		//var_dump( $variations );

		include_once WOO_PVT_DIR . 'views/front/variation-table.php';
	}

	private function rearrange_variation_query( $variation_query ) {
		foreach ( $variation_query as $var ) {
			array_merge($this->attributes, explode( ',', $var->attributes ));
			$values     = explode( ',', $var->attributes_values );

			$this->attributes = array_unique( $this->attributes );
			var_dump( $this->attributes );
			var_dump( $values );
		}
	}

}