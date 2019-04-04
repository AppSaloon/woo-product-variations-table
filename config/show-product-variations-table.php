<?php

namespace woo_pvt\config;

use woo_pvt\model\Product;

class Show_Product_Variations_Table {

	public function register_product_variations_table() {
		add_action( 'plugins_loaded', array( $this, 'remove_variable_product_add_to_cart' ) );
		add_filter( 'woocommerce_after_single_product_summary', array( $this, 'show_product_variations' ), 9 );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'variations_table_scripts' ) );

	}

	public function variations_table_scripts() {
		if ( is_product() ) {
			wp_enqueue_script( 'woo-product-variations-table-style', WOO_PVT_URL.'js/woo-product-variations-table.js', array(), WOO_PVT_VERSION, true );
			wp_enqueue_style( 'woo-product-variations-table-style', WOO_PVT_URL.'css/woo-product-variations-table.css', array(), WOO_PVT_VERSION );
		}
	}

	public function remove_variable_product_add_to_cart() {
		remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	}

	public function show_product_variations() {
		global $product;
		$productId = $product->get_id();
		$apiEndPoint = Product::getApiEndpoint() . $productId;
		echo "<div id='variations-table' class='variations-table' data-ajax-url='admin-ajax.php?add_variation_to_cart=1' data-api-endpoint='" . $apiEndPoint . "' data-product-id='" . $productId . "' >test</div>";
	}
}