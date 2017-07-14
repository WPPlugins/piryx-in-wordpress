<?php
/*
Plugin Name: Piryx for WordPress
Plugin URI: http://www.winwithwp.com
Description: Allows you to embed a Piryx form into a post or page
Author: Win With Wordpress
Version: 1.0.1.5
Requires at least: 3.0
Author URI: http://www.winwithwp.com

*/ 
class Piryx {

	private $authorize_url = 'https://api.piryx.com/oauth/authorize';
	private $access_token_url = 'https://api.piryx.com/oauth/access_token';
	private $piryx_url = 'https://secure.piryx.com/api/accounts/';

	private $client_id = 'm1PRHsHkTDDSgqQrjvOpujz5EobvPdPA';
	private $client_secret = 'SQWYy6cgfrpVXtuCjWzpUUDbBSnurmFQXc8zD01mtHjc4Oim2Hk1OAP6nUxPURY2';
	
	private $test_mode = false;
	
	private $validation = false;
	private $payment_status = false;
	private $account_id = false;
	
	public function __construct() {

		if($this->test_mode) {
			$this->authorize_url = 'https://sandbox-api.piryx.com/oauth/authorize';
			$this->access_token_url = 'https://sandbox-api.piryx.com/oauth/access_token';
			$this->piryx_url = 'http://demo.secure.piryx.com/api/accounts/';
		}
		
		//if reauthenticating, clear options
		if($_GET['reauthenticate'])
			delete_option('piryx_options');

		include_once( 'views.php' );
		
		//Set up piryx URL
		$options = get_option( 'piryx_options' );
		// var_dump($options);

		if ( $options && isset( $options['account_id'] ) )
			$account_id=$options['account_id'];
		else
			$account_id="me";
		
		$this->piryx_url =  $this->piryx_url . $account_id;
		
		//add scripts
	   	add_action( 'wp_print_scripts', array( &$this, 'add_post_scripts' ) );
	  	//add _styles
		add_action( 'wp_print_styles', array( &$this, 'add_styles' ) );
		
		//shortcode and page detection
		add_shortcode( 'piryx', array( &$this, 'piryx_shortcode' ) );
		add_action( 'save_post', array( &$this, 'post_save' ) );

		//Initialize settings
		add_action( 'admin_init', array( &$this, 'options_init' ) );
		add_action( 'admin_menu', array( &$this, 'options_menu' ) );

		//For adding the button to the post editor
		add_action('media_buttons_context', array( &$this, 'add_piryx_button') );
		add_action('admin_head', array( &$this, 'add_piryx_admin_header' ) );
		add_action('admin_footer', array( &$this, 'add_piryx_button_popup' ) );

		add_action( 'wp', array( $this, 'validate' ) );

	}
	
	public function add_piryx_button( $context ) {
		
		$options = get_option( 'piryx_options' );
		if ( !$options || !isset( $options['campaigns'] ) ) return $context;
		
		$image_btn = plugins_url( '/images/application-form.gif', __FILE__ );
		$out = '<a href="#TB_inline?width=450&inlineId=piryx_form" class="thickbox" title="' . __("Add Piryx Form", 'piryx') . '"><img src="'.$image_btn.'" alt="' . __("Add Piryx Form", 'piryx') . '" /></a>';
		
		return $context . $out;

	}

	function add_piryx_admin_header() {
		?>
		<script type="text/javascript">
            function InsertCampaign(){
                var campaign_id = jQuery("#add_campaign_id").val();
                if(campaign_id == ""){
                    return;
                }

				var redirect_uri = jQuery.trim( jQuery("#piryx_redirect_uri").val() );
				var piryx_editor_string = "[piryx";
				piryx_editor_string += " id='" + campaign_id + "'";
				if ( redirect_uri != "" ) {
					piryx_editor_string += " redirect_uri='" + redirect_uri + "'";
				}
				piryx_editor_string += "]";
                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor( piryx_editor_string );
            }
        </script>
        <?php
	}
	//Action target that displays the popup to insert a form to a post/page
	//Code from Gravity Forms
    function add_piryx_button_popup(){
		
		$options = get_option( 'piryx_options' );
		
		if ( !$options || !isset( $options['campaigns'] ) ) return;
        
        ?>

        <div id="piryx_form" style="display:none;">
            <div class="wrap">
                <div>
                    <div style="padding:15px 15px 0 15px;">
                        <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;"><?php _e("Insert Piryx Form", "piryx"); ?></h3>
                        <span>
                            <?php _e("Select a campaign below to add it to your post or page.", "piryx"); ?>
                        </span>
                    </div>
                    <div style="padding:15px 15px 0 15px;">
                    <?php
					//Get campaigns
						$options = get_option( 'piryx_options' );
						if ( !isset( $options['campaigns'] ) || !$options ) :
							?>
                            <p><?php _e( 'There are no campaigns.  Have you authenticated?', 'piryx' ); ?></p>
                            <?php
						endif;
					?>
                        <select id="add_campaign_id">
                            <option value="">  <?php _e("Select a Campaign", "piryx"); ?>  </option>
                            <?php
                                $campaigns = $options['campaigns'];
                                foreach($campaigns as $code => $campaign){
									if ( $campaign['active'] != 'true' ) continue;
                                    ?>
                                    <option value="<?php echo esc_attr( $code ) ?>"><?php echo esc_html( $campaign['title']) ?></option>
                                    <?php
                                }
                            ?>
                        </select> <br/>
                       
                    </div>
                    <div style="padding: 15px 15px 0 15px;">
                     <span>
                            <label for="piryx_redirect_uri"><?php _e("Please enter a URL where you would like donors to land after they donate", "piryx"); ?></label><br />
                            <input id="piryx_redirect_uri" type="text" size="30" />
                        </span>
                    </div>
                    <div style="padding:15px;">
                        <input type="button" class="button-primary" value="<?php _e( 'Insert Campaign', 'piryx' ); ?>" onclick="InsertCampaign();"/>&nbsp;&nbsp;&nbsp;
                    <a class="button" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "piryx"); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <?php
    } //end add_piryx_button_popup
	public function add_post_scripts() {
		if ( !is_admin() && $this->has_shortcode() ) {
			wp_enqueue_script( 'piryx.donate', plugins_url( 'js/piryx.donate.js', __FILE__ ), array( "jquery-ui-core" ), '1.0', true );
			wp_localize_script( 'piryx.donate', 'piryx_donate', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'csc_url' => 'https://secure.piryx.com/donate/card-security-code' ) );
		}
	}
	public function add_styles() {
		if ( !is_admin() && !$this->has_shortcode() ) return;
		wp_enqueue_style( 'piryx', plugins_url( 'css/piryx.css', __FILE__ ), array(), '1.0', 'all' );
	}

	//Called by admin_init action
	public function options_init() {
		register_setting( 'piryx_options', 'piryx_options', array( &$this, 'options_validate' ) );
	}

	public function options_menu() {
		$page_hook = add_options_page('Piryx', 'Piryx', 'manage_options', __FILE__, array( &$this, 'options_output' ) );
		
		add_action( "admin_print_styles-{$page_hook}", array( &$this, 'add_styles' ) );
	}

	public function options_output() {
		
		if ( !current_user_can( 'administrator' ) ) return;
		$options = get_option('piryx_options');
		?>
        <div class="wrap">
		<h2><?php _e( 'Piryx for WordPress', 'piryx' ); ?></h2>
        <?php
			//Check if need to authenticate
		$code_key = $_GET['code'] ? 'code' : '?code';

		if ( isset( $_GET[ $code_key ] ) && !isset( $_GET['reauthenticate'] ) && ( $options['authenticated'] != 'true' ) ):

			$authorization_code = $_GET[ $code_key ];
			$headers = array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded'
					),
				'body' => http_build_query( array( 
						'grant_type' => 'authorization_code',
						'client_id' => $this->client_id,
						'client_secret' => $this->client_secret,
						'redirect_uri' => admin_url("options-general.php?page=piryx-in-wordpress/piryx.php&" ),
						'code' => $authorization_code ), '' )
			);
			//Retrieve authentication token
			$response = wp_remote_retrieve_body( wp_remote_post( esc_url( $this->access_token_url ), $headers ) );
			// echo "RESPONSE ";
			// var_dump($response);
			// echo "<BR>";
			$json_vars = json_decode( $response );
			$token = (string)$json_vars->access_token;
			$options['token'] = $token;
			$options['authenticated'] = 'true';
			update_option( 'piryx_options', $options );
			//Save campaigns
			$this->save_campaigns();

			$this->save_account_info();
		
		elseif ( isset( $_GET['?error'] ) ):
			?>
            <div class='error'><?php _e( 'Authentication failed.  Reason: ', 'piryx' ); echo esc_attr( $_GET['?error'] ); ?></div>
            <?php
		endif;
		if ( $options['authenticated'] == 'true' ) :
			?>
            <div class='updated'><?php echo _e( 'You have been authenticated', 'piryx' ); ?> - <?php printf( '<a href="%s">%s</a>', esc_url( add_query_arg( array( 'reauthenticate' => 1 ) , $_SERVER["REQUEST_URI"] ) ), __( 'Re-authenticate', 'piryx' ) ); ?></div>
            <?php
			//Check to see if we're re-syncing the campaigns
			if ( isset( $_POST['piryx-resync-campaigns'] ) ) :
				//check_admin_referer( 'piryx-resync-campaigns' );
				$this->save_campaigns();
				?>
                <div class='updated'><?php _e( 'Campaigns have been saved', 'piryx' ); ?></div>
                <?php
			endif;
			
			?>
            <?php
				//Display campaigns
				$options = get_option( 'piryx_options' );
				if( !isset( $options['account_id'] ) ) 
					$this->save_account_info();
				if ( isset( $options['campaigns'] ) ):
					?>
                    <table class="form-table">
                    <thead>
                    	<th><strong><?php _e( 'Campaign Code', 'piryx'); ?></strong></th><th><strong><?php _e( 'Title', 'piryx'); ?></strong></th><th><strong><?php _e( 'Status', 'piryx' ); ?></strong></th><th><strong><?php _e( 'Public URL', 'piryx' ); ?></strong></th>
                     </thead>
                     <tbody>
                    <?php
					foreach ( $options['campaigns'] as $code => $campaign ) :
						?>
						<tr valign="top">
                        	<td><?php echo $code ?></td>
                        	<td><?php echo $campaign['title']; ?></td>
                            <td><?php echo $campaign['active'] == 'true' ? __( 'Active', 'piryx' ) : __( 'Inactive', 'piryx' ); ?></td>
                            <td><?php echo $campaign['public_url']; ?></td>
					</tr>
						<?php
					endforeach;
					?>
                    </tbody>
                    </table>
                    <?php
				endif;
			?>
			</table>
             <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<?php wp_nonce_field('piryx-resync-campaigns') ?>
            <p class="submit">
				<input id="piryx-resync-campaigns" name='piryx-resync-campaigns' type="submit" value='<?php _e( 'Re-sync Campaigns', 'piryx' ); ?>' />
				</p>
            </form>
            <?php 
            
		elseif ( empty( $options['authenticated'] ) || $options['authenticated'] == 'false' ):
			//User hasn't been authenticated.  Show authentication option.
				$authenticate_permissions = array(
				'never_expire',
				'payment_details', 
				'payment_summary',
				'create_payment', 
				'create_campaign', 
				'campaign_details'
			);
			?>
            <p><?php echo __( 'Click "Login with Piryx" to authenticate this application with Piryx.', 'piryx' ); ?></p>
            <p>The Piryx for WordPress Plugin is free! There are no monthly fees, no startup fees, no contracts, and no initial costs. The only costs associated with using Piryx for WordPress is a 1.0% transaction fee on money that you raise through the plugin and Piryx Fundraising. Piryx Fundraising includes merchant, credit card (Visa, MC, AMEX, Discover) and eCheck processing fees.</p>

			<p>WARNING: The plugin will only work on a secure site. You must set up a SSL certificate for the plugin to work.</p>

			<h3>Authenticate your Piryx Account</h3>
			<form method="get" action="<?php echo esc_url( $this->authorize_url ); ?>">
				
				<input type='hidden' name='scope' value="<?php echo esc_attr( implode( ' ', $authenticate_permissions ) ); ?>" />
				<input type='hidden' name='response_type' value='code' />
				<input type='hidden' name='client_id' value='<?php echo esc_attr( $this->client_id ) ; ?>' />
				<input type='hidden' name='redirect_uri' value='<?php echo esc_url( admin_url('options-general.php?page=piryx-in-wordpress/piryx.php&' ) ); ?>' />
				<?php /*Need an & at the end since Piryx adds a ? automatically*/ ?>
				<p class="submit">
				<input class="piryx-authenticate" type="submit" value='' />
				</p>
			</form>
		<?php
		endif;
	} //end options_output
	//Validates all the options
	public function options_validate( $options ) {
		$options['account'] = sanitize_text_field( $options['account'] );
		$options['api_key'] = sanitize_text_field( $options['api_key'] );
		$options['api_secret_key'] = sanitize_text_field( $options['api_secret_key'] );
		
		return $options;
	}
	//Saves a custom field for page detection if a post is the piryx shortcode on it or not
	public function post_save( $post_id ) {
		//Retrieve the post object - If a revision, get the original post ID
		$revision = wp_is_post_revision( $post_id );
		if ( $revision )	
			$post_id = $revision;
		$post = get_post( $post_id );
		//Perform a test for a shortcode in the post's content
		preg_match('/\[piryx[^\]]*\]/is', $post->post_content, $matches); //replace yourshortcode with the name of your shortcode
		
		
		if ( count( $matches ) == 0 ) {
			delete_post_meta( $post_id, '_piryx_shortcode' );
		} else {
			update_post_meta( $post_id, '_piryx_shortcode', '1' );
		}
	} //end post_save
	//Save campaigns to an option
	private function save_account_info() {
		$options = get_option( 'piryx_options' );
		$token = $options['token'];
		$response = wp_remote_retrieve_body( wp_remote_get( $this->piryx_url . "/{$code}?oauth_token={$token}") );
		$response_xml = simplexml_load_string( $response );
		$options['account_id'] = (string) $response_xml->Id;
		update_option( 'piryx_options', $options );
	}

	private function save_campaigns() {
		//Get campaigns
		$options = get_option( 'piryx_options' );
		$token = $options['token'];
		$response = wp_remote_retrieve_body( wp_remote_get( $this->piryx_url . "/campaigns?oauth_token=$token") );

		$campaigns = function_exists('simplexml_load_string') ? simplexml_load_string( $response ) : array();
		//Parse through campaigns and save
		$options['campaigns'] = array();
		if ( isset( $campaigns->TotalItems ) && $campaigns->TotalItems[0] > 0 ) {
			foreach ( $campaigns->Campaigns as $campaign ) {
				foreach ( $campaign as $campaign_data ) {
					$code = (string)$campaign_data->Code;
					$options['campaigns'][$code] = array(
						'title' => (string)$campaign_data->Title,
						'active' => (string)$campaign_data->Active,
						'public_url' => (string)$campaign_data->PublicUrl,
						'xml' => ''
					);
					
					//Save XML Data as well
					$response = wp_remote_retrieve_body( wp_remote_get( $this->piryx_url . "/campaigns/{$code}?oauth_token={$token}") );
					// echo "RESPONSE ";
					// var_dump($response);
					// echo "<BR>";
					// die;
					if ( !is_wp_error( $response ) ) {
						$options['campaigns'][$code]['xml'] = esc_html( $response );
					}
				}
			}
		}
		update_option( 'piryx_options', $options );
	} //end save_campaigns
	//Retrieve campaign details from stored data
	private function get_campaign( $code = '') {
		//Get campaigns
		$options = get_option( 'piryx_options' );
		if ( !$options ) return;
		
		$campaign = $options[ 'campaigns' ][ $code ];
		$campaign = str_replace( "&nbsp;", "&#160;", htmlspecialchars_decode( $campaign['xml']  ) );

		return $campaign;
		
	} //end get_campaign
	//Returns true if a post has the piryx shortcode, false if not
	private function has_shortcode() {
		global $post;
		if ( !is_object($post) ) return false; 
		if ( get_post_meta( $post->ID, '_piryx_shortcode', true ) ) 
			return true;
		else
			return false;	
	}
	public function validate() {
		//The form has been submitted, now to spit out the output
		if ( isset( $_POST['donation-form-validate'] ) ) :
			include_once( 'validate.php' );
			$redirect_uri = !empty( $_POST['piryx_redirect'] ) ? $_POST['piryx_redirect'] . "?payment_status=accepted": false;
			$this->validation = new PiryxValidate( $_POST );
			if ( !$this->validation->has_errors() ) {
				
				//Submit the payment for processing
				$validated_data = $this->validation->get_validated_data();
				
				//Build payment array
				$payment_data = array(
					'CampaignCode' => $validated_data['campaignID'],
					'Amount' => isset( $validated_data['amount'] ) ? $validated_data['amount'] : $validated_data['customAmount'],
					'Payment' => $validated_data['payment'],
					'BillingAddress1' => $validated_data['billingAddress1'],
					'BillingAddress2' => isset( $validated_data['billingAddress2'] ) ? $validated_data['billingAddress2'] : '',
					'BillingCity' => $validated_data['billingCity'],
					'BillingState' => $validated_data['billingState'],
					'BillingZip' => $validated_data['billingZip'],
					'Address1' => $validated_data['address1'],
					'Address2' => isset( $validated_data['address2'] ) ? $validated_data['address2'] : '',
					'City' => $validated_data['city'],
					'State' => $validated_data['state'],
					'Zip' => $validated_data['zip'],
					'Email' => $validated_data['email'],
					'FirstName' => $validated_data['firstName'],
					'LastName' => $validated_data['lastName'],
					'Phone' => $validated_data['phone']
				);	
				if ( $validated_data['payment'] == 'ECheck' ) {
					$payment_data['RoutingNumber'] = $validated_data['routingNumber'];
					$payment_data['AccountNumber'] = $validated_data['accountNumber'];
				} else {
					$payment_data['CardNumber'] = $validated_data['CardNumber'];
					$payment_data['CardSecurityCode'] = $validated_data['cardCVV2'];
					$payment_data['CardExpirationMonth'] = $validated_data['expirationMonth'];
					$payment_data['CardExpirationYear'] = $validated_data['expirationYear'];
				}
				//Set up payment recurring details
				if ( $_POST['IsRecurring'] == 'True' ) {
					$payment_data['BillingPeriod'] = $validated_data['RecurringPeriod'];
					if ( isset( $payment_data['NumberOfRecurringMonths'] ) && !empty( $payment_data['NumberOfRecurringMonths'] ) ) {
						$payment_data['TotalPayments'] = $validated_data['NumberOfRecurringMonths'];
					} else {
						$payment_data['TotalPayments'] = 0;
					}
				}	
				
				$payment_data['AppKey']	= 'brEXAZAPH65hu9res7eka67ZuVuTruFa'; //add app_key to POST object for 1% transaction fee

				//$payment_data['Account'] = basename($this->piryx_url);
				$payments_url = $this->piryx_url . "/payments";
				ksort($payment_data);
				
				foreach($payment_data as $key => $payment_single_data) {
					//$payment_data[$key] = str_replace(" ", "%2520", $payment_single_data);
					$payment_data[$key] = urlencode($payment_single_data);
				}
				
				$normalized_parameter_list = http_build_query( $payment_data, '', '&' );
				$normalized_parameter_list = str_replace('%25', '%', $normalized_parameter_list);
				$normalized_parameter_list = str_replace('%2B', '%20', $normalized_parameter_list);

				$options = get_option( 'piryx_options' );
				$token = $options['token'];

				$headers = array( 
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
						'Authorization' => 'OAuth ' . $token
						),
					'body' => $normalized_parameter_list
				);
			//Send payment information

			$response = wp_remote_retrieve_body( wp_remote_post(  $payments_url, $headers ) );

			if ( !is_wp_error( $response ) ) {
				//Now need to parse the XML and check status
				$response_xml = simplexml_load_string( $response );
				if ( !isset( $response_xml->Status ) || $response_xml->Status == 'Declined' ) {
					$this->payment_status = 'declined';
					
				} else {
					//Redirect
					$this->payment_status = 'accepted';
					if ( $redirect_uri ) {
						wp_redirect( $redirect_uri . "?payment_status=accepted" );
						exit;
					}
					
					return;
				}
			} else {
				$this->payment_status = 'try_again';
				
			}
			
			
		}
		endif;
	}
	//Called by add_shortcode piryx
	public function piryx_shortcode( $atts ) {
		$validation = false;

		$options = get_option( 'piryx_options' );
		
		$atts = wp_parse_args( $atts, array( 'id' => 0, 'redirect_uri' => '' ) );
		
		extract($atts);

		ob_start();
		//For showing errors
		if ( isset( $_POST['donation-form-validate'] ) ) :
			if ( $this->validation->has_errors() ) {
				$validation = $this->validation;
				?>
                <div class='error-message'><?php _e( 'There are errors present.  Please correct the errors and re-submit the form.', 'piryx' ); ?></div>
                <?php
			} elseif( $_GET['payment_status']=="accepted" ) {
				printf( "<div class='success'>%s</div>", __( 'Your payment has been successfully received.  Please check your e-mail for more information.', 'piryx' ) );
			} else {
				//A payment has been submitted
				switch ( $this->payment_status ) {
					case 'declined':
						printf( "<div class='error-message'>%s</div>", __( 'The payment has been denied.  Please re-check your payment information.', 'piryx' ) );
					break;
					case 'accepted':
						$campaign_details = $this->get_campaign( $id ); 
				
						$response = new SimpleXMLElement( $campaign_details );
						if ( isset( $response->Page->ThankYouMessage ) ) {
							echo '<div class="success">';
							foreach ( $response->Page->ThankYouMessage->children() as $child ) {
								echo (string) $child->asXML();
							}
							echo '</div>';
						} else {
							printf( "<div class='success'>%s</div>", __( 'Your payment has been successfully received.  Please check your e-mail for more information.', 'piryx' ) );
						}
					break;
					case 'try_again':
						printf( "<div class='error-message'>%s</div>", __( 'There was a problem submitting your information.  Please try again in a few moments.', 'piryx' ) );
					break;
				} //end switch				
				

			} //end validation if
		endif;
		
		//Retrieve the campaign and display it
		$campaigns = isset( $options['campaigns'] ) ? $options['campaigns'] : false;
		if ( $campaigns ) {
			if ( isset( $campaigns[$id] ) ) {
				$campaign_details = $this->get_campaign( $id ); 
				
				if ( $campaign_details ) {
					//Output form fields
					$piryxviews = new PiryxViews( $campaign_details, $validation, $id );
					$piryxviews->display_form( false, $redirect_uri);
				}				
			}
		}
		//return '';
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	} //end piryx_shortcode
} //end class
//Instantiate
$piryx = new Piryx();
?>