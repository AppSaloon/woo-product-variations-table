<?php

namespace woo_pvt\config;

use woo_pvt\model\Settings;

class Plugin_Config
{

    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function register_plugin_settings()
    {
        add_action( 'admin_menu', array($this, 'woo_variations_table_settings'), 99 );
    }

    public function woo_variations_table_settings() {
        add_submenu_page(
            'woocommerce',
            __('Woo PVT', 'woo-product-variations-table'),
            __('Woo PVT', 'woo-product-variations-table'),
            'manage_options',
            'woo_product_variations_table',
            array($this, 'woo_variations_table_settings_page_callback')
        );

        add_action('admin_init', array($this, 'woo_variations_table_register_settings'));
    }

    public function woo_variations_table_register_settings() {
        register_setting( 'woo_variations_table_columns', 'woo_variations_table_columns' );
        register_setting( 'woo_variations_table_columns', 'woo_variations_table_show_attributes' );
    }

    // Settings page callback function
    public function woo_variations_table_settings_page_callback() {
        $default_columns = array(
            'image_link'            => 1,
            'sku'                   => 1,
            'variation_description' => 1,
            'dimensions'            => 0,
            'weight_html'           => 0,
            'stock'                 => 1,
            'price_html'            => 1,
        );
        $columns_labels  = array(
            'image_link'            => __( 'Thumbnail', 'woo-variations-table' ),
            'sku'                   => __( 'SKU', 'woo-variations-table' ),
            'variation_description' => __( 'Description', 'woo-variations-table' ),
            'dimensions'            => __( 'Dimensions', 'woo-variations-table' ),
            'weight_html'           => __( 'Weight', 'woo-variations-table' ),
            'stock'                 => __( 'Stock', 'woo-variations-table' ),
            'price_html'            => __( 'Price', 'woo-variations-table' ),
        );
        $columns         = get_option( 'woo_variations_table_columns', $default_columns );
        $showAttributes  = get_option( 'woo_variations_table_show_attributes', '' );
        ?>
        <div class="wrap">
            <h1><?php echo __( 'Woo Variations Table Settings', 'woo-variations-table' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'woo_variations_table_columns' ); ?>
                <?php do_settings_sections( 'woo_variations_table_columns' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo __( 'Columns to show', 'woo-variations-table' ); ?></th>
                        <td><?php $this->woo_variations_table_create_multi_select_options( 'woo-variations-table-columns', $default_columns, $columns, $columns_labels ); ?></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo __( 'Show Attributes', 'woo-variations-table' ); ?></th>
                        <td>
                            <ul style="margin-top: 5px;" class='mnt-checklist' id='woo-variations-table-attributes'>
                                <li>
                                    <input type='checkbox'
                                           name='woo_variations_table_show_attributes' <?php echo $showAttributes ? "checked='checked'" : ''; ?> />
                                    Show Attributes
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

    function woo_variations_table_create_multi_select_options( $id, $columns, $values, $labels ) {
        echo "<ul style='margin-top: 5px;' class='mnt-checklist' id='$id' >" . "\n";
        foreach ( $columns as $key => $value ) {
            $checked = " ";
            if ( isset( $values[ $key ] ) ) {
                $checked = " checked='checked' ";
            }
            echo "<li>\n";
            echo "<input type='checkbox' name='woo_variations_table_columns[$key]' $checked />" . $labels[ $key ] . "\n";
            echo "</li>\n";
        }
        echo "</ul>\n";
    }
}