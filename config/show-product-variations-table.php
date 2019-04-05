<?php

namespace woo_pvt\config;

use woo_pvt\model\Product;
use woo_pvt\model\Product_Query;

class Show_Product_Variations_Table {

	public function register_product_variations_table() {
		add_action( 'plugins_loaded', array( $this, 'remove_variable_product_add_to_cart' ) );
		add_filter( 'woocommerce_after_single_product_summary', array( $this, 'show_product_variations' ), 9 );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'variations_table_scripts' ) );


		// Add ajax callback to add variation to cart
		add_action( 'wp_ajax_variation_add_to_cart', array( $this, 'variations_table_ajax_variation_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_variation_add_to_cart',
			array(
				$this,
				'variations_table_ajax_variation_add_to_cart',
			) );
	}

	public function remove_variable_product_add_to_cart() {
		remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
	}

	public function variations_table_get_variation_data_from_variation_id( $variation_id ) {
		$_product       = new \WC_Product_Variation( $variation_id );
		$variation_data = $_product->get_variation_attributes();

		return $variation_data; // $variation_data will return only the data which can be used to store variation data
	}

	public function variations_table_ajax_variation_add_to_cart() {
		ob_start();

		$product_id   = apply_filters( 'vartable_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity     = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
		$variations   = $this->variations_table_get_variation_data_from_variation_id( $variation_id );

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation',
			true,
			$product_id,
			$quantity,
			$variation_id,
			$variations );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
				wc_add_to_cart_message( $product_id );
			}

			// Return fragments
			\WC_AJAX::get_refreshed_fragments();
		} else {
			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error',
					get_permalink( $product_id ),
					$product_id ),
			);

			wp_send_json( $data );
		}

		die();
	}

	public function variations_table_scripts() {
		if ( is_product() ) {
			wp_enqueue_script( 'woo-product-variations-table-style',
				WOO_PVT_URL . 'js/woo-product-variations-table.js',
				array(),
				WOO_PVT_VERSION,
				true );
			wp_enqueue_style( 'woo-product-variations-table-style',
				WOO_PVT_URL . 'css/woo-product-variations-table.css',
				array(),
				WOO_PVT_VERSION );
		}
	}

	public function show_product_variations() {
		global $product;
		$productId   = $product->get_id();
		$initialData = ( new Product( new Product_Query() ) )
			->setProduct( $product )
			->getProducVariationsByFilter()
			->getJson();

		$initialDataEncoded = base64_encode(json_encode($initialData));

		$apiEndPoint = Product::getApiEndpoint() . $productId;

		echo "<div id='variations-table' class='variations-table' data-initial-data='" . $initialDataEncoded . "' data-add-to-cart-url='/wp/wp-admin/admin-ajax.php?add_variation_to_cart=1' data-api-endpoint='" . $apiEndPoint . "' data-product-id='" . $productId . "' >test</div>";
	}
}