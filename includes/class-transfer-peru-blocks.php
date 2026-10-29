<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
class RT_Transfer_Peru_Blocks extends AbstractPaymentMethodType {
    protected $name = 'transfer-peru';
    public function initialize() { $this->settings = get_option( 'woocommerce_transfer-peru_settings', array() ); }
    public function is_active() { return 'yes' === ( $this->settings['enabled'] ?? 'no' ); }
    public function get_payment_method_script_handles() {
        wp_register_script( 'rt-transfer-peru-blocks', plugins_url( '../assets/blocks.js', __FILE__ ), array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ), Version_RT_Transfer_Peru, true );
        return array( 'rt-transfer-peru-blocks' );
    }
    public function get_payment_method_data() {
        return array(
            'title' => sanitize_text_field( $this->settings['title'] ?? __( 'Transferencia bancaria Perú', 'transfer-peru' ) ),
            'description' => wp_strip_all_tags( $this->settings['description'] ?? '' ),
            'supports' => array( 'products' ),
        );
    }
}
