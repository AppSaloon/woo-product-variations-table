<?php

namespace woo_pvt\model;

use woo_pvt\config\Rest_Endpoint_Config;

class Product {

	/**
	 * @var \WC_Product_Variable
	 */
	public $product;

	/**
	 * @var array Attributes used by the product variations
	 */
	private $attributes = array();

	/**
	 * @var array Products
	 */
	private $variations = array();

	/**
	 * @var int Total variations in the product
	 */
	private $totalVariations = 0;

	/**
	 * @var int Current page
	 */
	private $currentPage = 1;

	/**
	 * @var int Products per page
	 */
	private $perPage = 15;

	/**
	 * @var Product_Query Queries to get the data from the database
	 */
	protected $productQuery;

	/**
	 * @var array Attributes to filter the variations
	 */
	private $filterAttributes = array();

	public function __construct( Product_Query $product_query ) {
		$this->productQuery = $product_query;
	}

	/**
	 * @param $product
	 *
	 * @return $this
	 */
	public function setProduct( $product ) {
		$this->product = $product;

		return $this;
	}

	/**
	 * @param array $filter_attributes
	 * @param int $currentPage
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	public function getProducVariationsByFilter( $filterAttributes = array(), $currentPage = 1 ) {

		$this->filterAttributes = $filterAttributes;
		$this->currentPage      = $currentPage;
		$this->perPage          = get_option( 'woo_product_variations_per_page', 15 );

		$this->attributes = $this->productQuery->queryAttributesUsedByVariations( $this->product->get_id() );

		$this->totalVariations = $this->productQuery->queryTotalVariations(
			$this->product->get_id(),
			$filterAttributes
		);

		$variationsAttributes = $this->mergeAttributes( $filterAttributes );

		// calculate better with less database queries
		$this->variations = $this->productQuery->queryVariationsByFilter(
			$this->product->get_id(),
			$variationsAttributes,
			$this->currentPage,
			$this->perPage
		);

		return $this;
	}

	/**
	 * @param $filterAttributes
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	private function mergeAttributes( $filterAttributes ) {
		foreach ( $this->attributes as $attribute => $values ) {
			if ( ! isset( $filterAttributes[ $attribute ] ) ) {
				$filterAttributes[ $attribute ] = false;
			}
		}

		return $filterAttributes;
	}

	/**
	 * Sends json data
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getJson() {
		return array(
			'attributes'     => $this->attributes,
			'attributesName' => $this->productQuery->queryAttributesName($this->attributes),
			'attributesLabel' => $this->productQuery->queryAttributesLabel($this->attributes),
			'variations'     => $this->variations,
			'currentPage'    => $this->currentPage,
			'totalPages'      => floor( $this->totalVariations / $this->perPage ),
			'showAttributes' => (get_option( 'woo_product_variations_table_show_attributes', false ) == '1') ? true : false,
		);
	}

	/**
	 * returns api endpoint
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function getApiEndpoint() {
		return home_url() . DIRECTORY_SEPARATOR . 'wp-json' . DIRECTORY_SEPARATOR . Rest_Endpoint_Config::NAMESPACE . '/product/';
	}
}