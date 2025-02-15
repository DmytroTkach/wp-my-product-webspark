<?php
/**
 * Plugin Name: WP My Product Webspark
 * Description: Custom plugin for managing products through My Account in WooCommerce.
 * Version: 1.0.0
 * Author: Dmytro_T
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpmpw_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>WP My Product Webspark requires active WooCommerce!</p></div>';
        } );
        return false;
    }
    return true;
}
add_action( 'plugins_loaded', 'wpmpw_check_woocommerce' );

function my_frontend_enqueue_scripts() {
    if (!is_admin()) {
        wp_enqueue_media();
    }
}
add_action('wp_enqueue_scripts', 'my_frontend_enqueue_scripts');

require_once plugin_dir_path(__FILE__) . 'includes/class-wpmpw-add-product.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wpmpw-my-products.php';

function wpmpw_add_my_account_tabs( $items ) {
    $items['add-product'] = 'Add Product';
    $items['my-products'] = 'My Products';
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'wpmpw_add_my_account_tabs' );

function wpmpw_add_endpoints() {
    add_rewrite_endpoint( 'add-product', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'my-products', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'wpmpw_add_endpoints' );

add_action( 'woocommerce_account_add-product_endpoint', array('WPMPW_Add_Product', 'render') );
add_action( 'woocommerce_account_my-products_endpoint', array('WPMPW_My_Products', 'render') );

function wpmpw_my_products_query_vars( $vars ) {
    $vars[] = 'my-products';
    return $vars;
}
add_filter( 'query_vars', 'wpmpw_my_products_query_vars' );

function wpmpw_handle_product_deletion() {
    if ( isset( $_POST['delete_product'] ) && isset( $_POST['wpmpw_delete_product_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['wpmpw_delete_product_nonce'], 'wpmpw_delete_product_action' ) ) {
            wp_die( 'Invalid request' );
        }

        $product_id = intval( $_POST['product_id'] );
        if ( get_post_field( 'post_author', $product_id ) == get_current_user_id() ) {
            wp_trash_post( $product_id );

            wp_safe_redirect( wc_get_account_endpoint_url( 'my-products' ) );
            exit;
        }
    }
}
add_action( 'init', 'wpmpw_handle_product_deletion' );

function wpmpw_register_custom_email( $email_classes ) {
    if ( ! class_exists( 'WC_Email' ) ) {
        return $email_classes;
    }
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmpw-email-new-product.php';
    
    if ( class_exists( 'WC_Email_New_Product' ) ) {
        $email_classes['WC_Email_New_Product'] = new WC_Email_New_Product();
    }
    return $email_classes;
}
add_filter( 'woocommerce_email_classes', 'wpmpw_register_custom_email', 20, 1 );
