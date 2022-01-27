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
 * Version:           0.0.4
 * Author:            Renzo Tejada
 * Author URI:        https://renzotejada.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       transfer-peru
 * Domain Path:       /language
 * WC tested up to:   6.1.1
 * WC requires at least: 2.6
 */
if (!defined('ABSPATH')) {
    exit;
}

$plugin_transfer_peru_version = get_file_data(__FILE__, array('Version' => 'Version'), false);

define('Version_RT_Transfer_Peru', $plugin_transfer_peru_version['Version']);

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

