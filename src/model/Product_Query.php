<?php

namespace appsaloon\woo_pvt\model;

class Product_Query {

	/** @var \wpdb */
	private $wpdb;

	/** @var \WC_Product */
	public $product;

	public function __construct( \WC_Product $product ) {
		global $wpdb;

		$this->wpdb    = $wpdb;
		$this->product = $product;
	}

	/**
	 * Merges attribute slugs with names
	 *
	 * @param $usedAttributes
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 * @version 1.0.3
	 */
	public function getAttributesForEndpoint( $usedAttributes ) {
		$attributes = array();

		$attributesName  = $this->queryAttributesName( $usedAttributes );
		$attributesLabel = $this->queryAttributesLabel( $usedAttributes );

		foreach ( $attributesName as $slug => $value ) {

			foreach ( $value as $k => $v ) {
				if ( ! in_array( (string) $k, $usedAttributes[ $slug ] ) ) {
					unset( $value[ $k ] );
				}
			}

			$attributes[ $slug ] = array(
				'label'  => $attributesLabel[ $slug ],
				'values' => $value,
			);
		}

		return $attributes;
	}

	/**
	 * Retrieves attributes used by the variations
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function queryAttributesUsedByVariations( $usedAttributes ) {
		$attributes = array();

		$query = "SELECT DISTINCT(meta_key), GROUP_CONCAT(distinct(meta_value)) as terms
				  FROM {$this->wpdb->postmeta}
				  WHERE post_id IN (
				  	SELECT ID 
				  	FROM {$this->wpdb->posts} 
				  	WHERE post_parent = '{$this->product->get_id()}' 
				  	AND post_type='product_variation'
				  	)
				  AND meta_key LIKE 'attribute_%'
				  GROUP BY meta_key";

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		if ( sizeof( $result ) != 0 ) {
			foreach ( $result as $attr ) {
				$add = false;

				foreach ( $usedAttributes as $key => $value ) {
					if ( $key == $attr['meta_key'] ) {
						$add = true;
						break;
					}
				}

				// remove empty string values in the array
				if ( $add ) {
					$attributes[ $attr['meta_key'] ] = explode( ',', preg_replace( '/,+/', ',', $attr['terms'] ) );
				}
			}
		}

		return $attributes;
	}

	/**
	 * Filters the attributes
	 *
	 * @param $attributes
	 * @param  array  $filterAttributes
	 *
	 * @return array
	 *
	 * @since 1.0.2
	 */
	public function queryAttributesUsedByVariationsAndFilter( $attributes, $filterAttributes = array() ) {
		$innerJoinArray = array_merge( $attributes, $filterAttributes );

		$query = "SELECT " . $this->select_group_concat_filter_attributes( $attributes ) . "
				  FROM " . $this->wpdb->posts . "
				  " . $this->inner_join_filter_attributes( $innerJoinArray ) . "
				  WHERE post_parent = " . $this->product->get_id() . " 
				  AND post_type='product_variation' ";

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		if ( sizeof( $result ) == 0 ) {
			new \ErrorException( 'There was a problem during filtering the attributes', 500 );
		}

		$attributes = array();

		foreach ( $result[0] as $key => $row ) {
			$attributes[ $key ] = explode( ',', $row );
		}

		return $attributes;
	}

	/**
	 * Returns attributes name
	 *
	 * @param $attributes
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function queryAttributesName( $attributes ) {
		$attributes = array_keys( $attributes );
		$attributes = str_replace( 'attribute_', '', $attributes );

		$attributes_string = implode( "','", $attributes );

		$query = "SELECT {$this->wpdb->term_taxonomy}.taxonomy, replace({$this->wpdb->terms}.name, ',', '.') as name, {$this->wpdb->terms}.slug
				  FROM {$this->wpdb->term_taxonomy}
				  INNER JOIN {$this->wpdb->terms}
				  	ON {$this->wpdb->terms}.term_id = {$this->wpdb->term_taxonomy}.term_id
				  WHERE taxonomy in ('{$attributes_string}')
				  ORDER BY {$this->wpdb->term_taxonomy}.taxonomy";

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		$attributesName = array();

		foreach ( $result as $term ) {
			$attributesName[ 'attribute_' . $term['taxonomy'] ][ $term['slug'] ] = $term['name'];
		}

		return $attributesName;
	}

	/**
	 * Returns attributes label
	 *
	 * @param $attributes
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function queryAttributesLabel( $attributes ) {
		$attributes = array_keys( $attributes );
		$attributes = str_replace( 'attribute_pa_', '', $attributes );

		$attributes_string = implode( "','", $attributes );

		$query = "SELECT attribute_name, attribute_label
				  FROM {$this->wpdb->prefix}woocommerce_attribute_taxonomies
				  WHERE attribute_name in ('{$attributes_string}')";

		$result = $this->wpdb->get_results( $query, ARRAY_A );

		$labels = array();

		foreach ( $result as $taxonomies ) {
			$labels[ 'attribute_pa_' . $taxonomies['attribute_name'] ] = $taxonomies['attribute_label'];
		}

		return $labels;
	}

	/**
	 * Retrieve filtered variations
	 *
	 * @param $filterAttributes
	 * @param $currentPage
	 * @param $perPage
	 *
	 * @return array|object|null
	 *
	 * @since 1.0.0
	 */
	public function queryVariationsByFilter( $filterAttributes, $attributesOrder, $currentPage, $perPage ) {
		$query = "SELECT ID" . $this->select_filter_attributes( $filterAttributes ) . "
				  FROM " . $this->wpdb->posts . "
				  " . $this->inner_join_filter_attributes( $filterAttributes ) . "
				  WHERE post_parent = " . $this->product->get_id() . " 
				  AND post_type='product_variation' "
		         . $this->sort( $attributesOrder )
		         . $this->limit( $currentPage, $perPage );

		$result = $this->wpdb->get_results( $query ) ?? array();

		return $result;
	}

	/**
	 * @return mixed
	 *
	 * @since 1.1.2
	 */
	public function queryAttributesOrder() {
		$keys = $this->product->get_attributes();

		if ( is_array( $keys ) ) {
			foreach ( $keys as $k => $v ) {
				/**
				 * @var $v \WC_Product_Attribute
				 */
				if ( $v->get_variation() ) {
					$keys[ 'attribute_' . $k ] = array();
				}

				unset( $keys[ $k ] );
			}
		}


		return $keys;
	}

	/**
	 * Query total of variations matched with the filter
	 *
	 * @param $filterAttributes
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function queryTotalVariations( $filterAttributes ) {
		$query = "SELECT count(*) as max
				  FROM " . $this->wpdb->posts . "
				  " . $this->inner_join_filter_attributes( $filterAttributes ) . "
				  WHERE post_parent = " . $this->product->get_id() . " 
				  AND post_type='product_variation'";

		$result = $this->wpdb->get_row( $query );

		return $result->max ?? 0;
	}

	/**
	 * Build select listing for the filter attributes
	 *
	 * @param $filter_attributes array
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 * @version 1.1.4
	 */
	private function select_filter_attributes( $filter_attributes ) {
		$select = '';

		foreach ( array_keys( $filter_attributes ) as $attribute_key ) {
			$manipulated_key = str_replace( '-', '_', $attribute_key );

			$select .= ", table_$manipulated_key.meta_value as '$attribute_key'";
			$select .= ", {$manipulated_key}_terms.name as '{$attribute_key}_value'";
		}

		return $select;
	}

	/**
	 * Build select listing for the filter attributes
	 *
	 * @param $filterAttributes array
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 * @version 1.1.4
	 */
	private function select_group_concat_filter_attributes( $filterAttributes ) {
		$select = '';

		foreach ( $filterAttributes as $attribute_key => $attribute_value ) {
			$attribute_key_manipulated = str_replace( '-', '_', $attribute_key );

			$select .= "GROUP_CONCAT( DISTINCT( table_$attribute_key_manipulated.meta_value ) ) as $attribute_key, ";
		}

		return substr( $select, 0, - 2 );
	}

	/**
	 * Add attributes to FROM QUERY
	 *
	 * @param $filter_attributes array
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 * @version 1.1.4
	 */
	private function inner_join_filter_attributes( $filter_attributes ) {
		$inner_join = '';

		foreach ( $filter_attributes as $key => $attribute_value ) {
			$short_key = str_replace( 'attribute_', '', $key );
			$key_meta_key = $key;
			$key       = str_replace( '-', '_', $key );

			$inner_join .= ' INNER JOIN ' . $this->wpdb->postmeta . " as table_$key 
			ON table_$key.post_id = ID 
			AND table_$key.meta_key = '" . $key_meta_key . "' ";


			if ( $attribute_value !== false && ! is_array( $attribute_value ) ) {
				$inner_join .= "AND table_$key.meta_value = '" . $attribute_value . "' ";
			}

			$inner_join .= " INNER JOIN `wp_terms` AS {$key}_terms ON {$key}_terms.slug = table_${key}.meta_value
				AND {$key}_terms.term_id IN (
					SELECT term_id FROM wp_term_taxonomy
					WHERE wp_term_taxonomy.taxonomy = '{$short_key}'
					AND wp_term_taxonomy.term_id = {$key}_terms.term_id
				)
			";
		}

		return $inner_join;
	}

	/**
	 * Sorts variations by the attribute values
	 *
	 * @param  array  $filterAttributes
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 * @version 1.1.4
	 */
	private function sort( array $filterAttributes ) {
		if ( count( $filterAttributes ) == 0 ) {
			return '';
		}

		$sort = " ORDER BY  ";

		foreach ( array_keys( $filterAttributes ) as $attribute_key ) {
			$sort .= "CAST(`{$attribute_key}_value` as DEC),";
		}

		$sort = substr( $sort, 0, - 1 );

		$sort .= " DESC ";

		return $sort;
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
			/** Reduce this otherwise it will try to take next page */
			$currentPage --;

			$start = $currentPage * $perPage;
		}

		return " LIMIT $start,$perPage";
	}
}
