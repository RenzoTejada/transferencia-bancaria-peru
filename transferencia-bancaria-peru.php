<?php

/**
 *
 * @link              https://renzotejada.com/
 * @package           Transferencia Bancaria Perú
 * @author            Renzo Tejada
 * @wordpress-plugin
 * Plugin Name:       Transferencia Bancaria Perú
 * Plugin URI:        https://renzotejada.com/transferencia-bancaria-peru/
 * Description:       Accepts payments in person via CC or CCI. More commonly known as direct bank/wire transfer.
 * Version:           1.3.5
 * Author:            Renzo Tejada
 * Author URI:        https://renzotejada.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       transfer-peru
 * Domain Path:       /language
 * WC tested up to:   11.2.1
 * WC requires at least: 8.2
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Tested up to: 7.1.4
 */
if (!defined('ABSPATH')) {
    exit;
}

$plugin_transfer_peru_version = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('Version_RT_Transfer_Peru', $plugin_transfer_peru_version['Version']);

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

function rt_transfer_peru_load_textdomain()
{
    load_plugin_textdomain('transfer-peru', false, basename(dirname(__FILE__)) . '/language/');
}

add_action('init', 'rt_transfer_peru_load_textdomain');


/*
 * PAYMENT
 */
require dirname(__FILE__) . "/rt_transfer_payment.php";

add_filter('woocommerce_payment_gateways', 'rt_transfer_peru_add_gateway_class');
add_action('plugins_loaded', 'rt_transfer_peru_init_gateway_class');


/** Initialize Blocks adapter only when WooCommerce Blocks is present. */
add_action( 'woocommerce_blocks_loaded', function () {
    if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) { return; }
    require_once __DIR__ . '/includes/class-transfer-peru-blocks.php';
    add_action( 'woocommerce_blocks_payment_method_type_registration', function ( $registry ) {
        $registry->register( new RT_Transfer_Peru_Blocks() );
    } );
} );

/** Scoped UI refresh for the gateway settings only. */
add_action( 'admin_enqueue_scripts', function () {
    if ( ! isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) ||
         'wc-settings' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ||
         'checkout' !== sanitize_key( wp_unslash( $_GET['tab'] ) ) ||
         'transfer-peru' !== sanitize_key( wp_unslash( $_GET['section'] ) ) ) { return; }
    wp_enqueue_style( 'rt-transfer-peru-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.css', array(), Version_RT_Transfer_Peru );
} );


/** Add direct links from the plugins list. */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    $settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=transfer-peru' );
    array_unshift( $links, '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Ajustes', 'transfer-peru' ) . '</a>' );
    $links[] = '<a href="https://renzotejada.com/contacto/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Soporte', 'transfer-peru' ) . '</a>';
    return $links;
} );


/** Load styles only for WooCommerce order-received pages. */
add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
        wp_enqueue_style( 'rt-transfer-peru-thankyou', plugin_dir_url( __FILE__ ) . 'assets/thankyou.css', array(), Version_RT_Transfer_Peru );
    }
} );
