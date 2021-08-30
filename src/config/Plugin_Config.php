<?php

namespace appsaloon\woo_pvt\config;

use appsaloon\woo_pvt\model\Settings;

class Plugin_Config {

	public function register_plugin_settings() {
		add_action( 'admin_menu', array( $this, 'woo_variations_table_settings' ), 99 );
		add_action( 'woocommerce_update_product', array( $this, 'woo_after_product_save' ), 10, 1 );
	}

	public function woo_variations_table_settings() {
		add_submenu_page(
			'woocommerce',
			__( 'Woo PVT', 'woo-product-variations-table' ),
			__( 'Woo PVT', 'woo-product-variations-table' ),
			'manage_options',
			'woo_product_variations_table',
			array( $this, 'woo_variations_table_settings_page_callback' )
		);

		add_action( 'admin_init', array( $this, 'woo_variations_table_register_settings' ) );
	}

	public function woo_variations_table_register_settings() {
		register_setting( 'woo_variations_table_columns', 'woo_product_variations_per_page' );
		register_setting( 'woo_variations_table_columns', 'woo_product_variations_table_show_attributes' );
	}

	// Settings page callback function
	public function woo_variations_table_settings_page_callback() {
		$perPage        = get_option( 'woo_product_variations_per_page', '' );
		$showAttributes = get_option( 'woo_product_variations_table_show_attributes', '' );
		?>
        <div class="wrap">
            <h1><?php echo __( 'Woo Product Variations Table Settings', 'woo-product-variations-table' ); ?></h1>
            <form method="post" action="options.php">
				<?php settings_fields( 'woo_variations_table_columns' ); ?>
				<?php do_settings_sections( 'woo_variations_table_columns' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo __( 'Products per page', 'woo-product-variations-table' ); ?></th>
                        <td>
                            <ul style="margin-top: 5px;" class='mnt-checklist'
                                id='woo-product-variations-table-attributes'>
                                <li>
                                    <input type='number'
                                           name='woo_product_variations_per_page'
                                           value="<?php echo $perPage ? $perPage : 15; ?>"/>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo __( 'Show variation filter',
								'woo-product-variations-table' ); ?></th>
                        <td>
                            <ul style="margin-top: 5px;" class='mnt-checklist'
                                id='woo-product-variations-table-attributes'>
                                <li>
                                    <input type='checkbox'
                                           name='woo_product_variations_table_show_attributes' <?php echo $showAttributes ? "checked='checked'" : ''; ?>
                                           value="1"/>
                                </li>
                            </ul>
                        </td>
                    </tr>
                </table>

				<?php submit_button(); ?>

            </form>
        </div>
		<?php
	}

	/**
     * Delete cache after the product is updated
     *
	 * @param $product_id
     *
     * @since 1.1.0
	 */
	public function woo_after_product_save( $product_id ) {
		$transientId = Show_Product_Variations_Table::PRODUCT_TRANSIENT_KEY . $product_id;

		delete_transient( $transientId );
	}
}