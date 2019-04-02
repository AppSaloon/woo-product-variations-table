<?php

namespace woo_pvt\model;

use woo_pvt\config\Rest_Endpoint_Config;

class Product_Query {

	/**
	 * @var \wpdb
	 */
	private $wpdb;

	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Retrieves attributes used by the variations
	 *
	 * @param $productId integer
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function queryAttributesUsedByVariations( $productId ) {
		$attributes = array();

		$query = "SELECT DISTINCT(meta_key), GROUP_CONCAT(distinct(meta_value)) as terms
				  FROM {$this->wpdb->postmeta}
				  WHERE post_id IN (
				  	SELECT ID 
				  	FROM {$this->wpdb->posts} 
				  	WHERE post_parent = '{$productId}' 
				  	AND post_type='product_variation'
				  	)
				  AND meta_key LIKE 'attribute_%'
				  GROUP BY meta_key";

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		if ( sizeof( $result ) != 0 ) {
			foreach ( $result as $attr ) {
				// remove empty string values in the array
				$attributes[ $attr['meta_key'] ] = explode( ',', preg_replace( '/,+/', ',', $attr['terms'] ) );
			}
		}

		return $attributes;
	}

	/**
	 * Retrieve filtered variations
	 *
	 * @param $productId
	 * @param $filterAttributes
	 * @param $currentPage
	 * @param $perPage
	 *
	 * @return array|object|null
	 *
	 * @since 1.0.0
	 */
	public function queryVariationsByFilter( $productId, $filterAttributes, $currentPage, $perPage ) {
		$query = "SELECT ID" . $this->select_filter_attributes( $filterAttributes ) . "
				  FROM " . $this->wpdb->posts . "
				  " . $this->inner_join_filter_attributes( $filterAttributes ) . "
				  WHERE post_parent = " . $productId . " 
				  AND post_type='product_variation'
				  " . $this->limit( $currentPage, $perPage );

		return $this->wpdb->get_results( $query ) ?? array();
	}

	/**
	 * Query total of variations matched with the filter
	 *
	 * @param $productId
	 * @param $filterAttributes
	 *
	 * @return int
	 */
	public function queryTotalVariations( $productId, $filterAttributes ) {
		$query = "SELECT count(*) as max
				  FROM " . $this->wpdb->posts . "
				  " . $this->inner_join_filter_attributes( $filterAttributes ) . "
				  WHERE post_parent = " . $productId . " 
				  AND post_type='product_variation'";

		$result = $this->wpdb->get_row( $query );

		return $result->max ?? 0;
	}

	/**
	 * Build select listing for the filter attributes
	 *
	 * @param $filterAttributes array
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function select_filter_attributes( $filterAttributes ) {
		$select = '';

		foreach ( $filterAttributes as $attribute_key => $attribute_value ) {
			$select .= ", table_$attribute_key.meta_value as $attribute_key";
		}

		return $select;
	}

	/**
	 * Add attributes to FROM QUERY
	 *
	 * @param $filterAttributes array
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function inner_join_filter_attributes( $filterAttributes ) {
		$inner_join = '';

		foreach ( $filterAttributes as $attribute_key => $attribute_value ) {
			$inner_join .= " INNER JOIN " . $this->wpdb->postmeta . " as table_$attribute_key 
			ON table_$attribute_key.post_id = ID 
			AND table_$attribute_key.meta_key = '" . $attribute_key . "' ";

			if ( $attribute_value !== false ) {
				$inner_join .= "AND table_$attribute_key.meta_value = '" . $attribute_value . "' ";
			}
		}

		return $inner_join;
	}

	/**
	 * Limit variations to have performance gain
	 *
	 * @param $currentPage
	 * @param $perPage
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function limit( $currentPage, $perPage ) {
		if ( $currentPage == 1 ) {
			$start = 0;
		} else {
			$start = $currentPage * $perPage;
		}

		return "LIMIT $start,$perPage";
	}
}