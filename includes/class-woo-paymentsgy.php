<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
Payments.GY Payment Gateway Class
*/
class Woo_Paymentsgy extends WC_Payment_Gateway {
	public $api_key;
	// Setup class constructors with gateway id and other variables
	public function __construct() {
			global $woocommerce;

			$this->id                 = 'woopaymentsgy';
			$this->icon               = 'https://image.ibb.co/maajn5/OFFICIAL_Payments_logo_with_slogan.png';
			$this->has_fields         = false;
			$this->notify_url         = WC()->api_request_url( $this->id );
			$this->title              = "Payments.GY";
			$this->description        = "The faster, safer way to pay";
			$this->instructions       = $this->get_option( 'instructions' );
			$this->supports 		  = array('products');
			$this->environment 		  = 'live' === $this->get_option( 'environment', 'live' );
			$this->secret			  = $this->get_option(  'secret' );
			$this->wallet_hash        = $this->get_option( 'wallet_hash');
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->api_email 		  = $this->settings['api_email'];
			$this->api_password 	  = $this->settings['api_password'];

			if ( 'live' === $this->environment ) {
				$this->api_email       = $this->get_option( 'api_email' );
				$this->api_password    = $this->get_option( 'api_password' );
			} else {
				$this->api_email    = $this->get_option( 'sandbox_api_email' );
				$this->api_password    = $this->get_option( 'sandbox_api_password' );
			}

			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'admin_notices', array( $this,  'do_ssl_check' )); //check for SSL
                        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
                        add_action( 'woocommerce_api_' . $this->id , array( $this, 'validate' ) );

}

    public function validate_name_field($key, $value)
    {
        $name = sanitize_text_field($value);

        if (strlen($name) > 150) {
            throw new Exception("Application name is too long. Please use a shorter name.");
        }

        return $name;
    }

    public function validate_api_email_field($key, $value)
    {
        $email = sanitize_email($value);

        if (is_email($email)) {
            return $email;
        }

        throw new Exception("Your email is invalid!");
    }

function process_admin_options()
{
    if (isset($_POST['save']) && $_POST['save'] === 'unregister') {
        $this->update_option('registered', false);
    } else {
        $saved = parent::process_admin_options();

        if ($this->get_option('registered', false)) {
            $this->getWallets();
        } else {
            $this->update_option('token', bin2hex(random_bytes(10)));

            $registrationRequest = wp_remote_post($this->get_option('environment').'/register', 
                [
                    'body' => [
                        'name' => $this->get_option('name'),
                        'token' => $this->get_option('token'),
                        'callback_url' => $this->notify_url,
                    ],
                    'headers' => [ 'Authorization' => "Basic ".base64_encode($this->get_option('api_email').":".$this->get_option("api_password")) ],
                ]
            );

            if (is_wp_error($registrationRequest) || $registrationRequest['response']['code'] !== 201) {
                $notices = $this->get_option('woopayments_notices', []);
            
                if (is_wp_error($registrationRequest)) {
                    $this->add_error($registrationRequest->get_error_message());
                } else {
                    $httpcode = $registrationRequest['response']['code'];
                    $body = json_decode( $registrationRequest['body'] );

                    switch($httpcode) {
                        case 401:
                            $this->add_error("Registration failed! Have you verified your email address?");
                        break;
                        case 422:
                            $this->add_error("Failed to register your application: ".json_encode($body));
                        break;
                        default:
                            $this->add_error("Unknown error code $httpcode, ".json_encode($body));
                    }
                }
            
                $this->update_option('woopayments_notices', $notices);
            } else {
                $this->update_option('registered', true);
                $this->update_option('id', json_decode( $registrationRequest['body'] )->id );
                $this->update_option('secret', json_decode( $registrationRequest['body'] )->secret );

                $this->getWallets();

                echo '<div class="updated notice is-dismissible"><p><b>Successfully registered your application!</b> Welcome to Payments.GY</p></div>';
            }
        }
    }


}

    function getWallets() 
    {
        $result = wp_remote_get($this->get_option('environment').'/wallets', [
            'headers' => [ 'Authorization' => "Basic ".base64_encode($this->get_option('api_email').":".$this->get_option("api_password")) ],
        ]);
        
        if (is_wp_error($result) || $result['response']['code'] !== 200) {
            // Ensure the wallet_hash field has no options
            $this->update_option('wallets', []);
        
            $notices = $this->get_option('woopayments_notices', []);
        
            if (is_wp_error($result)) {
                $this->add_error( "Failed to retrieve wallets: ". $result->get_error_message());
            } else {
                switch($result['response']['code']) {
                    case 401:
                        $this->add_error( "Failed to retrieve wallets: Authentication failed! Have you verified your email address?" );
                    break;
                    default:
                        $this->add_error("Failed to retrieve wallets: Unknown error code ".$result['response']['code']);
                }
            }
        
            $this->update_option('woopayments_notices', $notices);
        } else {
            $wallets = json_decode($result['body']);
        
            $options = [];
        
            foreach ($wallets as $wallet) {
                $options[$wallet->hash] = __($wallet->location[0]->merchant->name, 'woo-paymentsgy');
            }
        
            $this->update_option('wallets', $options);

            if (count($options) === 1) {
                $this->update_option('wallet_hash', $wallets[0]->hash);
            }
        }
    }

// End __construct1
function init_form_fields() {
    $defaults = include plugin_dir_path( __DIR__ ) . 'admin/woo-paymentsgy-default.php';

    if (!$this->get_option('registered', false)) {
        $fields = include plugin_dir_path( __DIR__ ) . 'admin/woo-paymentsgy-login.php';
    } else {
        $fields = include plugin_dir_path( __DIR__ ) . 'admin/woo-paymentsgy-admin-display.php';
    }

    $this->form_fields = array_merge($defaults, $fields);
}
// Currency support
public function is_valid_for_use() {
	return in_array( get_woocommerce_currency(), apply_filters( 'paymentsgy_supported_currencies', array( 'GYD', 'USD' ) ) );
}
public function admin_options() {
	if ( $this->is_valid_for_use() ) {
		?>
		<p><strong><h1><?php _e('Payments.GY', 'woo-paymentsgy'); ?></strong></h1>
		<?php _e('Customers must have a verified payments.gy account to process transactions.', 'woo-paymentsgy'); ?>
		<hr>

                <?php
                    if ($this->get_option('registered', false)) { ?>
                        <button name="save" class="button-primary woocommerce-save-button" value="unregister" type="submit">Unregister?</button><?php
                    }

		parent::admin_options();

	}else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway disabled', 'woo-paymentsgy' ); ?></strong>: <?php _e( 'Payments.GY does not support your store currency.', 'woo-paymentsgy' ); ?></p></div>
			<?php
		}
}

// Submit payment and handle response
public function process_payment($order_id) {
		// Get order information
		$order = new WC_Order($order_id);
		$order_id = $order->get_id();
		$order_shipping_total = $order->get_total_shipping();
		foreach(WC()->cart->get_cart() as $cart_item){
			// Get an instance of Product WP_Post object
			$post_obj = get_post( $cart_item['product_id'] );
			// HERE the product description
			$product_desciption = $post_obj->post_content;
			// The product short description
			$product_short_desciption = $post_obj->post_excerpt;
		}
		foreach($order->get_items() as $item_key => $item_values) {
			$item_id = $item_values->get_id();
			$item_name = $item_values->get_name();
			$item_type = $item_values->get_type();
			$item_data = $item_values->get_data();

			$quantity = $item_data['quantity'];

			$line_items[] = array(
				"name" => $item_data['name'],
				"description" => $product_desciption,
				"cost" => (int) wc_format_decimal( $order->get_item_total( $item_values ), 2 ),
				"quantity" => $item_data['quantity'],
			);
		}
		$line_items[] = array(
			"name" => "Shipping",
			"description" => "Shipping charges",
			"cost" => (int) $order_shipping_total,
			"quantity" => 1
		);

		// Are we testing right now or is it a real transaction
		$environment = 'live' === $this->environment ? 'live' : 'sandbox';
		$auth = $this->secret;

                $total = (int) $order->order_total;

                $result = wp_remote_post($this->get_option('environment').'/payment_requests', [
                    'body' => [
			// Order total
			'total' => $total,
			'wallet_hash' => $this->wallet_hash,
			'email' => $order->billing_email,
			'callback_data' => $order_id,
			'request_hash' => $order_id,
			'order_number' => str_pad(str_replace( "#", "", $order->get_order_number() ), 4, STR_PAD_LEFT),
			'line_items' => $line_items,
                    ],
                    'headers' => [
                        'client_secret' => $auth,
                        'client_id' => $this->get_option('id')
                    ]
                ]);

                if (is_wp_error($result)) {
                    $order->update_status( 'failed', 'Because of Wordpress error: ' );
                    $status = 'failed';
                } elseif ($result['response']['code'] !== 201) {
                    $order->update_status( 'failed', 'Because of API error: ' );
                    $status = 'failed';
                } else {
		    // Mark as on-hold (we're awaiting payment)
		    $order->update_status( 'pending-payment', _x( 'Awaiting Payment', 'Check payment method', 'woo-paymentsgy' ) );
		    wc_reduce_stock_levels( $order_id );
		    WC()->cart->empty_cart();

                    $status = 'success';
                }

		return array(
			'result'   => $status,
			'redirect'  => $this->get_return_url( $order ),
		);
}

public function thankyou_page() {
	if ( $this->instructions ) {
		echo wpautop( wptexturize( $this->instructions ) );
	}
}

// Check if we are forcing SSL on checkout pages
    public function do_ssl_check() {
        $this->display_errors();
        if( $this->enabled == "yes" ) {
            if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) 
            {
                echo "<div class=\"error\"><p>". sprintf(
                    __( "<strong>Payments.GY</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ),
                    $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) 
                ) ."</p></div>";
            }
        }
    }


    public function validate() 
    {
        if (isset($_REQUEST['challenge']) && isset($_REQUEST['token'])) {
            $challenge = $_REQUEST['challenge'];
            $token = $_REQUEST['token'];

            if ($token === $this->get_option('token')) {
                return die(esc_html( $challenge ));
            }
        }

        // Get raw php input since this request isn't a validation request and must be a callback
        $data = json_decode(file_get_contents('php://input'));

        // If both callback_data and transaction are set, this must mean the payment request was updated
        if (isset($data->callback_data) && isset($data->transaction)) {
            $order = new WC_Order($data->callback_data);

            if ($data->paid) {
                $order->update_status( 'on-hold' );
            } else {
                $order->update_status( 'cancelled' );
            }
            
        }
    }
}

