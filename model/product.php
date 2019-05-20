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

	/** @var array Used attributes */
	private $usedAttributes = array();

	/** @var bool Keeps the state if the filter is used */
	private $hasFilter = false;

	/** Product constructor */
	public function __construct( Product_Query $product_query ) {
		$this->productQuery = $product_query;
	}

	/**
	 * @param array $filterAttributes
	 * @param int $currentPage
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 * @version 1.0.2
	 */
	public function getProducVariationsByFilter( $filterAttributes = array(), $currentPage = 1 ) {
		$this->filterAttributes = $filterAttributes;
		$this->currentPage      = $currentPage;
		$this->perPage          = get_option( 'woo_product_variations_per_page', 15 );

		/** @var  boolean hasFilter Check if the filter is used */
		$this->hasFilter = ( count( $filterAttributes ) !== 0 ) ? true : false;

		/** get total matched variations **/
		$this->totalVariations = $this->productQuery->queryTotalVariations(
			$filterAttributes
		);

		/** Get the attributes for the product */
		$this->calculateAttributes();

		/** @var  $variationsAttributes */
		$variationsAttributes = $this->mergeAttributes( $filterAttributes, true );

		/** Get the matched variations **/
		$this->variations = $this->productQuery->queryVariationsByFilter(
			$variationsAttributes,
			$this->attributesOrder,
			$this->currentPage,
			$this->perPage
		);

		/** Return this object */
		return $this;
	}

	/**
	 * Calculate attributes
	 *
	 * @since 1.0.0
	 * @version 1.0.2
	 */
	private function calculateAttributes() {
		/** get attributes order from the product model -> defined in the product backend **/
		$this->attributesOrder = $this->productQuery->queryAttributesOrder();

		/** get used attributes - without filtering */
		$this->usedAttributes = $this->productQuery->queryAttributesUsedByVariations();

		/** filters the attributes with available attributes - with filtering */
		if ( $this->hasFilter ) {
			$this->usedAttributes = $this->productQuery->queryAttributesUsedByVariationsAndFilter( $this->usedAttributes, $this->filterAttributes );
		}

		/** update attributes with attribute name and slug */
		$this->attributes = $this->productQuery->getAttributesForEndpoint( $this->usedAttributes );

		/** sort attributes - like the order in the product backend */
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
			if ( $filter ) {
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
	 * @version 1.0.5
	 */
	public function getJson() {
		return array(
			'attributes'     => $this->attributes,
			'variations'     => $this->variations,
			'currentPage'    => $this->currentPage,
			'totalPages'     => ceil( $this->totalVariations / $this->perPage ),
			'showFilter'     => ( get_option( 'woo_product_variations_table_show_attributes',
					false ) == '1' )
				? true
				: false
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