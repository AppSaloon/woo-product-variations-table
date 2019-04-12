<?php

namespace woo_pvt\model;

use woo_pvt\config\Rest_Endpoint_Config;

class Product {

	/** @var array Attributes used by the product variations */
	private $attributes = array();

	/** @var array Products */
	private $variations = array();

	/** @var int Total variations in the product */
	private $totalVariations = 0;

	/** @var int Current page */
	private $currentPage = 1;

	/** @var int Products per page * */
	private $perPage = 15;

	/** @var array Attributes order */
	private $attributesOrder = array();

	/** @var Product_Query Queries to get the data from the database */
	protected $productQuery;

	/** @var array Attributes to filter the variations */
	private $filterAttributes = array();

	/** Product constructor */
	public function __construct( Product_Query $product_query ) {
		$this->productQuery = $product_query;
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

		// get total variations
		$this->totalVariations = $this->productQuery->queryTotalVariations(
			$filterAttributes
		);

		$this->calculateAttributes();

		$variationsAttributes = $this->mergeAttributes( $filterAttributes, true );

		// calculate better with less database queries
		$this->variations = $this->productQuery->queryVariationsByFilter(
			$variationsAttributes,
			$this->attributesOrder,
			$this->currentPage,
			$this->perPage
		);

		return $this;
	}

	/**
	 * Calculate attributes
	 *
	 * @since 1.0.0
	 */
	private function calculateAttributes() {
		// get attributes order from the product
		$this->attributesOrder = $this->productQuery->queryAttributesOrder();

		// get used attributes
		$this->attributes = $this->productQuery->queryAttributesUsedByVariations();

		// update attributes with attribute name and slug
		$this->attributes = $this->productQuery->getAttributesForEndpoint( $this->attributes );

		// sort attributes
		if ( is_array( $this->attributesOrder ) ) {
			$this->attributes = $this->mergeAttributes( $this->attributesOrder );
		}
	}

	/**
	 * @param $filterAttributes
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	private function mergeAttributes( $filterAttributes, $filter = false ) {

		foreach ( $this->attributes as $attribute => $values ) {

			if ( ! isset( $filterAttributes[ $attribute ] ) ) {
				$filterAttributes[ $attribute ] = false;
				continue;
			}

			/** Do not run for the queryVariationsByFilter */
			if( $filter ) {
				continue;
			}

			$filterAttributes[ $attribute ] = $values;
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
			'attributes'  => $this->attributes,
			'variations'  => $this->variations,
			'currentPage' => $this->currentPage,
			'totalPages'  => floor( $this->totalVariations / $this->perPage ),
			'showFilter'  => ( get_option( 'woo_product_variations_table_show_attributes',
					false ) == '1' )
				? true
				: false,
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