<?php

namespace woo_pvt\config;

class Show_Product_Variations_Table {

	public function register_product_variations_table() {
		add_action( 'plugins_loaded', array( $this, 'remove_variable_product_add_to_cart' ) );
		add_filter( 'woocommerce_after_single_product_summary', array( $this, 'show_product_variations' ), 9 );
	}

	public function remove_variable_product_add_to_cart() {
		remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	}

	public function show_product_variations() {
		echo "todo build the table for the product variations";
	}
}