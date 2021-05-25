<?php

function rt_transfer_peru_add_gateway_class($methods) {
    $methods[] = 'WC_WooTransferPeru_Gateway';
    return $methods;
}

function rt_transfer_peru_init_gateway_class() {

    class WC_WooTransferPeru_Gateway extends WC_Payment_Gateway {

        /**
         * Init and hook in the integration.
         */
        function __construct() {
            global $woocommerce;
            $this->id                   = "transfer-peru";
            $this->has_fields           = false;
            $this->method_title         = __("Peru Bank Transfer", 'transfer-peru');
            $this->method_description   = __("Accepts payments in person via CC or CCI. More commonly known as direct bank/wire transfer.",'transfer-peru');
            $this->method_description   = $this->init_descripcion();

            //Initialize form methods
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions' );
            $this->icon = plugins_url('image/flag.png', __FILE__);

            $this->account_details = get_option(
                'woocommerce_transfer_accounts',
                array(
                    array(
                        'account_type'          => $this->get_option( 'account_type' ),
                        'account_name'          => $this->get_option( 'account_name' ),
                        'account_number'        => $this->get_option( 'account_number' ),
                        'account_number_cci'    => $this->get_option( 'account_number_cci' ),
                        'account_bank_name'     => $this->get_option( 'account_bank_name' ),
                        'account_swift'         => $this->get_option( 'account_swift' ),
                    ),
                )
            );

            // Actions.
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_account_details'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

            // Customer Emails
            add_action('woocommerce_email_before_order_table', array($this, 'rt_transfer_peru_email_steps'), 10, 3);
        }

        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled'         => array(
                    'title'   => __( 'Enable/Disable', 'transfer-peru' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable bank transfer', 'transfer-peru' ),
                    'default' => 'no',
                ),
                'title'           => array(
                    'title'       => __( 'Title', 'transfer-peru' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'transfer-peru' ),
                    'default'     => __( 'Peru bank transfer', 'transfer-peru' ),
                    'desc_tip'    => true,
                ),
                'description'     => array(
                    'title'       => __( 'Description', 'transfer-peru' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'transfer-peru' ),
                    'default'     => __( 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.', 'transfer-peru' ),
                    'desc_tip'    => true,
                ),
                'instructions'    => array(
                    'title'       => __( 'Instructions', 'transfer-peru' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'transfer-peru' ),
                    'default'     => __("Enter the total amount of the order and make the payment. <br>
                                        Send us the proof of transfer.<br>
                                        We will verify your payment and complete your order shortly.",'transfer-peru'),
                    'desc_tip'    => true,
                ),
                'account_details' => array(
                    'type' => 'account_details',
                ),
            );

        }

        public function init_descripcion()
        {
            return '<div class="rt-header-logo">
                   <div class="rt-left-header">
                    <img  src="' .  plugins_url('image/flag.png', __FILE__) . '" width="20" >
                </div>
                <div>' . __("Accepts payments in person via CC or CCI. More commonly known as direct bank/wire transfer.", 'transfer-peru') . '</div>
            </div>';
        }

        /**
         * Generate account details html.
         *
         * @return string
         */
        public function generate_account_details_html()
        {
            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php esc_html_e( 'Account details:', 'transfer-peru' ); ?></th>
                <td class="forminp" id="transfer_accounts">
                    <div class="wc_input_table_wrapper">
                        <table class="widefat wc_input_table sortable" cellspacing="0">
                            <thead>
                            <tr>
                                <th class="sort">&nbsp;</th>
                                <th><?php esc_html_e( 'Bank Name', 'transfer-peru' ); ?></th>
                                <th><?php esc_html_e( 'Account type', 'transfer-peru' ); ?></th>
                                <th><?php esc_html_e( 'Account name', 'transfer-peru' ); ?></th>
                                <th><?php esc_html_e( 'Account number', 'transfer-peru' ); ?></th>
                                <th><?php esc_html_e( 'Account number CCI', 'transfer-peru' ); ?></th>
                                <th><?php esc_html_e( 'Swift', 'transfer-peru' ); ?></th>
                            </tr>
                            </thead>
                            <tbody class="accounts">
                            <?php
                            $i = -1;
                            if ( $this->account_details ) {
                                foreach ( $this->account_details as $account ) {
                                    $i++;

                                    echo '<tr class="account">
										<td class="sort"></td>
                                        <td><input type="text" value="' . esc_attr( wp_unslash( $account['bank_name'] ) ) . '" name="transfer_bank_name[' . esc_attr( $i ) . ']" /></td>
										<td><input type="text" value="' . esc_attr( wp_unslash( $account['account_type'] ) ) . '" name="transfer_account_type[' . esc_attr( $i ) . ']" /></td>
										<td><input type="text" value="' . esc_attr( wp_unslash( $account['account_name'] ) ) . '" name="transfer_account_name[' . esc_attr( $i ) . ']" /></td>
										<td><input type="text" value="' . esc_attr( $account['account_number'] ) . '" name="transfer_account_number[' . esc_attr( $i ) . ']" /></td>
										<td><input type="text" value="' . esc_attr( $account['account_number_cci'] ) . '" name="transfer_account_number_cci[' . esc_attr( $i ) . ']" /></td>
										<td><input type="text" value="' . esc_attr( $account['swift'] ) . '" name="transfer_swift[' . esc_attr( $i ) . ']" /></td>
									</tr>';
                                }
                            }
                            ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="7">
                                    <a href="#" class="add button">
                                        <?php esc_html_e( '+ Add account', 'transfer-peru' ); ?>
                                    </a>
                                    <a href="#" class="remove_rows button">
                                        <?php esc_html_e( 'Remove selected account(s)', 'transfer-peru' ); ?>
                                    </a>
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <script type="text/javascript">
                        jQuery(function() {
                            jQuery('#transfer_accounts').on( 'click', 'a.add', function(){

                                var size = jQuery('#transfer_accounts').find('tbody .account').length;

                                jQuery('<tr class="account">\
									<td class="sort"></td>\
									<td><input type="text" name="transfer_bank_name[' + size + ']" /></td>\
									<td><input type="text" name="transfer_account_type[' + size + ']" /></td>\
									<td><input type="text" name="transfer_account_name[' + size + ']" /></td>\
									<td><input type="text" name="transfer_account_number[' + size + ']" /></td>\
									<td><input type="text" name="transfer_account_number_cci[' + size + ']" /></td>\
									<td><input type="text" name="transfer_swift[' + size + ']" /></td>\
								</tr>').appendTo('#transfer_accounts table tbody');

                                return false;
                            });
                        });
                    </script>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        /**
         * Save account details table.
         */
        public function save_account_details()
        {
            $accounts = array();

            if ( isset( $_POST['transfer_bank_name'] ) && isset( $_POST['transfer_account_type'] )
                && isset( $_POST['transfer_account_name'] ) && isset( $_POST['transfer_account_number'] )
                && isset( $_POST['transfer_account_number_cci'] ) && isset( $_POST['transfer_swift'] ) ) {

                $bank_names             = wc_clean( wp_unslash( $_POST['transfer_bank_name'] ) );
                $account_types          = wc_clean( wp_unslash( $_POST['transfer_account_type'] ) );
                $account_names          = wc_clean( wp_unslash( $_POST['transfer_account_name'] ) );
                $account_numbers        = wc_clean( wp_unslash( $_POST['transfer_account_number'] ) );
                $account_numbers_cci    = wc_clean( wp_unslash( $_POST['transfer_account_number_cci'] ) );
                $swift                  = wc_clean( wp_unslash( $_POST['transfer_swift'] ) );

                foreach ( $account_names as $i => $name ) {
                    if ( ! isset( $account_names[ $i ] ) ) {
                        continue;
                    }

                    $accounts[] = array(
                        'bank_name'             => $bank_names[ $i ],
                        'account_type'         => $account_types[ $i ],
                        'account_name'          => $account_names[ $i ],
                        'account_number'        => $account_numbers[ $i ],
                        'account_number_cci'    => $account_numbers_cci[ $i ],
                        'swift'                 => $swift[ $i ],
                    );
                }
            }
            update_option( 'woocommerce_transfer_accounts', $accounts );
        }

        public function process_payment($order_id)
        {

            $order = wc_get_order( $order_id );

            if ( $order->get_total() > 0 ) {
                // Mark as on-hold (we're awaiting the payment).
                $order->update_status( apply_filters( 'woocommerce_transfer_process_payment_order_status', 'on-hold', $order ), __( 'Awaiting Peru Transfer Bank payment', 'transfer-peru' ) );
            } else {
                $order->payment_complete();
            }

            // Remove cart.
            WC()->cart->empty_cart();

            // Return thankyou redirect.
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page( $order_id )
        {

            if ( $this->instructions ) {
                echo wp_kses_post( wpautop( wptexturize( wp_kses_post( $this->instructions ) ) ) );
            }
            $this->bank_details( $order_id );

        }

        /**
         * Get bank details and place into a list format.
         *
         * @param int $order_id Order ID.
         */
        private function bank_details( $order_id = '' )
        {

            if ( empty( $this->account_details ) ) {
                return;
            }

            $order = wc_get_order( $order_id );
            $transfer_accounts = apply_filters( 'woocommerce_transfer_accounts', $this->account_details, $order_id );

            if ( ! empty( $transfer_accounts ) ) {
                $account_html = '';
                $has_details  = false;

                foreach ( $transfer_accounts as $transfer_account ) {
                    $transfer_account = (object) $transfer_account;

                    if ( $transfer_account->bank_name ) {
                        $account_html .= '<h3 class="wc-transfer-bank-details-bank-name">' . wp_kses_post( wp_unslash( $transfer_account->bank_name ) ) . ':</h3>' . PHP_EOL;
                    }

                    $account_html .= '<ul class="wc-transfer-bank-details order_details transfer_details">' . PHP_EOL;

                    // transfer account fields shown on the thanks page and in emails.
                    $account_fields = apply_filters(
                        'woocommerce_transfer_account_fields',
                        array(
                            'account_type' => array(
                                'label' => __( 'Account type', 'transfer-peru' ),
                                'value' => $transfer_account->account_type,
                            ),
                            'account_name' => array(
                                'label' => __( 'Account name', 'transfer-peru' ),
                                'value' => $transfer_account->account_name,
                            ),
                            'account_number' => array(
                                'label' => __( 'Account number', 'transfer-peru' ),
                                'value' => $transfer_account->account_number,
                            ),
                            'account_number_cci' => array(
                                'label' => __( 'Account number CCI', 'transfer-peru' ),
                                'value' => $transfer_account->account_number_cci,
                            ),
                            'swift'            => array(
                                'label' => __( 'Swift', 'transfer-peru' ),
                                'value' => $transfer_account->swift,
                            ),
                        ),
                        $order_id
                    );

                    foreach ( $account_fields as $field_key => $field ) {
                        if ( ! empty( $field['value'] ) ) {
                            $account_html .= '<li class="' . esc_attr( $field_key ) . '">' . wp_kses_post( $field['label'] ) . ': <strong>' . wp_kses_post( wptexturize( $field['value'] ) ) . '</strong></li>' . PHP_EOL;
                            $has_details   = true;
                        }
                    }

                    $account_html .= '</ul>';
                }

                if ( $has_details ) {
                    echo '<section class="woocommerce-transfer-bank-details"><h2 class="wc-transfer-bank-details-heading">' . esc_html__( 'Our bank details', 'transfer-peru' ) . '</h2>' . wp_kses_post( PHP_EOL . $account_html ) . '</section>';
                }
            }

        }

         /**
         * Add content to the WC emails.
         *
         * @param WC_Order $order Order object.
         * @param bool     $sent_to_admin Sent to admin.
         * @param bool     $plain_text Email format: plain text or HTML.
         */
        public function rt_transfer_peru_email_steps( $order, $sent_to_admin, $plain_text = false )
        {
            if ( ! $sent_to_admin && 'transfer-peru' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
                if ( $this->instructions ) {
                    echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
                }
                $this->bank_details( $order->get_id() );
            }

        }

    }
}
