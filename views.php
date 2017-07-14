<?php
class PiryxViews {
	private $validation = false;
	private $campaign_details = false;
	private $campaign_id = false;
	public function __construct( $campaign_details, $validation = false, $campaign_id  ) {
		if ( is_a( $validation, 'PiryxValidate' ) ) {
			$this->validation =  $validation;
		}
		$this->campaign_details = $campaign_details;
		$this->campaign_id = $campaign_id;
	}
	public function display_form( $validation = false, $redirect_uri = '' ) {
		?>
        <div id='piryx-donation-form'>
        <form id='donation-form' method='post' action="<?php echo esc_url( $_SERVER["REQUEST_URI"] ); ?>">
        <input type='hidden' name='piryx_redirect' value='<?php echo esc_attr( $redirect_uri ); ?>' />
        <input type='hidden' name='donation-form-validate' value='true' />
        <input type='hidden' name='campaignID' value='<?php echo $this->campaign_id; ?>' />
		<input type='hidden' name='AppKey' value='brEXAZAPH65hu9res7eka67ZuVuTruFa' />
        <div class="personal-information section" id="personal-information">
            <h2>Personal Information</h2>
            <ul class="fields">
            <?php
			$this->form_field( array( 'class' => 'first-name', 'text' => __('First Name', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'firstName' ) );
			$this->form_field( array( 'class' => 'middle-name', 'text' => __('Middle Name', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'middleName' ) );
			$this->form_field( array( 'class' => 'last-name', 'text' => __('Last Name', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'lastName' ) );
			$this->form_field( array( 'class' => 'email', 'text' => __('Email', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'email' ) );
			$this->form_field( array( 'class' => 'address1', 'text' => __('Home Address', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'address1' ) );
			$this->form_field( array( 'class' => 'address2', 'text' => __('Home Address (line 2)', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'address2' ) );
			$this->form_field( array( 'class' => 'city', 'text' => __('City', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'city' ) );
			?>
            
            <li class="state"><label for="state">State</label><span title="Required" class="req">Required</span><br><select name="state" id="state" gtbfieldid="8" <?php $this->display_error_class( 'state' ); ?>>
            <?php $this->display_states( ($_POST['state']) ? $_POST['state'] : 0 ); ?>
</select><?php $this->display_error( 'state' ); ?></li>
<?php
			$this->form_field( array( 'class' => 'zip', 'text' => __('Zip/Postal', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'zip', 'maxlength' => 10, 'size' => 10 ) );
			$this->form_field( array( 'class' => 'phone', 'text' => __('Home Phone', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'phone', 'maxlength' => 14, 'size' => 14 ) );
			$this->form_field( array( 'class' => 'work-phone', 'text' => __('Work Phone', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'workPhone', 'maxlength' => 14, 'size' => 14 ) );
			$this->form_field( array( 'class' => 'fax-phone', 'text' => __('Fax Phone', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'faxPhone', 'maxlength' => 14, 'size' => 14 ) );
			$this->form_field( array( 'class' => 'mobile-phone', 'text' => __('Mobile Phone', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'mobilePhone', 'maxlength' => 14, 'size' => 14 ) );
?>
            </ul>
        </div> <!--/personal-information-->
        <div class="section" id="employment-information">
            <h2>Employment Information</h2>
            <ul class="fields">
            <?php
			$this->form_field( array( 'class' => 'employer', 'text' => __('Employer', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'employer' ) );
			$this->form_field( array( 'class' => 'occupation', 'text' => __('Occupation', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'occupation' ) );
			?>
            </ul>
        </div> <!--/employment-information -->
        <?php
		$this->display_contribution_amount();
		$this->display_creditcards();
		
		?>
        <div class="section" id="billing-information">
            <h2>Billing Information</h2>
            <ul class="fields">
        <li class="same"><label><input type="checkbox" name="billingSameAsHome" value="1" id="billingSameAsHome" /> Same as home address.</label></li>

		<?php
		$this->form_field( array( 'class' => 'address1', 'text' => __('Billing Address', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'billingAddress1' ) );
		$this->form_field( array( 'class' => 'address2', 'text' => __('Billing Address (line 2)', 'piryx'), 'required' => false, 'type' => 'text', 'name' => 'billingAddress2' ) );
		$this->form_field( array( 'class' => 'city', 'text' => __('City', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'billingCity' ) );
		?>
            <li class="state"><label for="billingState">State</label><br><select name="billingState" id="billingState" gtbfieldid="70" <?php $this->display_error_class( 'state' ); ?>> 
			<?php $this->display_states( ($_POST['billingState']) ? $_POST['billingState'] : 0 ); ?>
</select><?php $this->display_error( 'billingState' ); ?></li>
<?php
			$this->form_field( array( 'class' => 'zip', 'text' => __('Zip/Postal', 'piryx'), 'required' => true, 'type' => 'text', 'name' => 'billingZip' ) );
			?>
            </ul>
        </div>
        <?php
		$this->display_contribution_types();
		$this->display_error( 'contribution_type' );
		$this->display_legal_compliance();
		?>
        <div class="buttons">
                <button onclick="jQuery(this).hide(); jQuery('#submitting').show();" type="submit"><img title="Submit" src="<?php echo esc_attr( plugins_url( '/images/btn-submit.png', __FILE__ ) ); ?>" alt="Submit"></button>
                <span style="display: none; color: green; font-weight: bold;" id="submitting">Your donation is being submitted. Please be patient.</span>
            </div>
            </form> <!--/donation-form-->
            <?php 		$this->display_sharing(); ?>
            </div><!--</piryx-donation-form'>-->
            <?php
	} //end display_form
	private function form_field( $args = array() ) {
		$required = false;
		extract( $args );
		?>
        <li<?php echo isset( $class ) ? " class='$class'" : '' ?>><label<?php echo isset( $name ) ? " for='$name'" : '' ?>><?php echo isset( $text ) ? $text : ''; ?> </label>
   		<?php
		if ( $required ) {
			?>
             <span title="Required" class="req"><?php _e( 'Required', 'piryx' ); ?></span>
            <?php
		}
		if ( isset( $_POST[$name] ) ) {
			$value = esc_attr( $_POST[$name] );
		}
		?><br>
        <input <?php $this->display_error_class( $name ); ?> type='<?php echo isset( $type ) ? $type : ''; ?>' id='<?php echo isset( $name ) ? $name : ''; ?>' value='<?php echo isset( $value ) ? $value : ''; ?>' name='<?php echo isset( $name ) ? $name : ''; ?>' <?php echo isset( $maxlength ) ? "maxlength='$maxlength'" : ''; ?> <?php echo isset( $size ) ? "size='$size'" : ''; ?> />
        	<?php
			$this->display_error( $name );
			
			?>
        </li>
        <?php
	} //end form_field
	private function display_error( $name = '' ) {
		if ( is_a( $this->validation, 'PiryxValidate' ) && isset( $name ) ) {
				$errors = $this->validation->get_errors();
				$error_codes = $errors->get_error_codes(); 
				$error_msg = $errors->get_error_message( $name );
				if ( $error_msg ) {
					?>
                    <div class='error-message'><?php echo $error_msg; ?></div>
                    <?php
				}
			}
	}
	private function display_error_class( $name = '' ) {
		if ( is_a( $this->validation, 'PiryxValidate' ) && isset( $name ) ) {
				$errors = $this->validation->get_errors();
				$error_codes = $errors->get_error_codes(); 
				$error_msg = $errors->get_error_message( $name );
				if ( $error_msg ) {
					echo "class='error'";
				}
			}
	}
	private function display_creditcards( ) {
		?>
        <div id="payment-information">
            <h2>Payment Information</h2>

            <ul class="card-type">
            <li><label><input type="radio" checked="checked" value="ECheck" name="payment" id="payment-echeck"> <span id="pt-echeck">E-Check</span></label></li><li><label><input type="radio" value="Visa" name="payment" id="payment-visa"> <span id="pt-visa">Visa</span></label></li><li><label><input type="radio" value="Mastercard" name="payment" id="payment-mastercard"> <span id="pt-mastercard">Mastercard</span></label></li><li><label><input type="radio" value="Amex" name="payment" id="payment-amex"> <span id="pt-amex">Amex</span></label></li><li><label><input type="radio" value="Discover" name="payment" id="payment-discover"> <span id="pt-discover">Discover</span></label><?php $this->display_error( 'payment' ); ?></li>
            </ul>

            <ul style="display: none;" id="cc-info"><li class="card-number"><label for="CardNumber">Card Number</label><br><input <?php $this->display_error_class( 'CardNumber' ); ?> type="text" value="" size="16" name="CardNumber" maxlength="16" id="CardNumber" autocomplete="off"><?php $this->display_error( 'CardNumber' ); ?></li><li class="cvv2"><label for="cardCVV2">CSC</label> <a title="What is this?" target="_blank" onclick="return popUpCscInfo()" href="/donate/card-security-code">?</a><br><input <?php $this->display_error_class( 'cardCVV2' ); ?> type="text" value="" size="4" name="cardCVV2" maxlength="4" id="cardCVV2" autocomplete="off"><?php $this->display_error( 'cardCVV2' ); ?></li><li class="expiration">
                <strong>Expiration</strong><br>
                <div class="month">
                    <label for="expirationMonth">Month</label><select <?php $this->display_error_class( 'expirationMonth' ); ?> name="expirationMonth" id="expirationMonth" gtbfieldid="41"><option value="">Month</option>
<option>01</option>
<option>02</option>
<option>03</option>
<option>04</option>
<option>05</option>
<option>06</option>
<option>07</option>
<option>08</option>
<option>09</option>
<option>10</option>
<option>11</option>
<option>12</option>
</select>
<?php $this->display_error( 'expirationMonth' ); ?>
                </div>
                <div class="year">
                    <label for="expirationYear">Year</label><select name="expirationYear" id="expirationYear" gtbfieldid="42" <?php $this->display_error_class( 'expirationYear' ); ?> ><option value="">Year</option>
<?php
//Print out years
$current_year = intval( date("Y", time() ) );
for ( $i = 0; $i < 12; $i++ ) {
	printf( "<option>%d</option>", $current_year );
	$current_year += 1;
}?>
</select>
                </div>
                <div class="clear"></div>
            </li></ul>
            
            <ul id="echeck-info">
            <li class="routing-number"><label for="routingNumber">Routing Number</label><br><input <?php $this->display_error_class( 'routingNumber' ); ?> type="text" value="" name="routingNumber" id="routingNumber" autocomplete="off"><?php $this->display_error( 'routingNumber' ); ?></li>
            <li class="account-number"><label for="accountNumber">Account Number</label><br><input <?php $this->display_error_class( 'accountNumber' ); ?> type="text" value="" name="accountNumber" id="accountNumber" autocomplete="off"><?php $this->display_error( 'accountNumber' ); ?></li>
            <li class="help"><img alt="Your routing number is a 9 digit number that typically appears on the left hand side of your checks.  The account number typically appears on the right hand side of the check and can vary in length." src="<?php echo plugins_url( '/images/echeck-help.gif', __FILE__ ); ?>"></li>
            </ul>
            
        </div>
        <?php
	} //end display_creditcards
	//Raw XML $campaign_details string
	private function display_contribution_amount( ) {
		$campaign_details = html_entity_decode( $this->campaign_details, ENT_QUOTES, 'UTF-8' );
		$xml = new SimpleXMLElement($campaign_details);
		$amounts = $xml->Amounts;
		$allow_custom_amounts = isset( $xml->AllowCustomAmounts ) ? (bool)$xml->AllowCustomAmounts : false; 
		$prices = array();
		foreach ( $amounts->children() as $amount ) {
			$prices[] = array(
				 'amount' => round( (float)$amount->Value, 2 ),
				 'caption' => isset( $amount->Caption ) ? (string)$amount->Caption : '' );
		}	
		?>
        <div id="amount">
            <h2>Contribution Amount</h2>
            
    <ul class="amounts">
        <?php
		foreach ( $prices as $price ) {
			$amount = esc_attr( $price['amount'] );
			$caption = sanitize_text_field( $price['caption'] );
			?>
            <li>
                <label>
                    <input type="radio" value="<?php echo $amount; ?>" name="amount" id="amount-<?php echo $amount; ?>" <?php checked($_POST['amount'],$amount); ?>>
                    <?php setlocale(LC_MONETARY, 'en_US'); ?>
                    <span class="amount"><?php echo money_format( '%n', $amount ); ?></span>
                    <?php if ( !empty( $caption ) ): ?>
                    <span class="caption"> - <?php echo $caption; ?></span>
                    <?php endif; ?>
                </label>
            </li>
            <?php
		} //end foreach
		?>
        <?php if ( $allow_custom_amounts ) : ?>
            <li class="other">
                <label>
                    <input type="radio" value="custom" name="amount" id="amount-custom">
                    <span class="amount">$</span>
                </label>
                <input <?php $this->display_error_class( 'customAmount' ); ?> type="text" value="" size="5" name="customAmount" maxlength="7" id="customAmount" gtbfieldid="64">
                <?php $this->display_error( 'amount' ); ?>&nbsp;<?php $this->display_error( 'customAmount' ); ?>
            </li>
          <?php endif; ?>
        
        <li class="clear"></li>
    </ul>    

            <div style="margin-bottom: 16px;" class="clear"></div>
        </div>
        <?php
	} //end display_contribution_amount
	//Raw XML $campaign_details
	private function display_contribution_types(  ) {
		$xml = new SimpleXMLElement( html_entity_decode( $this->campaign_details, ENT_QUOTES, 'UTF-8' ) );
		$payment_options = (string)$xml->PaymentOptions;
		include_once( 'payment_types.php' );
		switch ( $payment_options ) {
			case "Subscription":
				PiryxContributionTypes::yearly_subscription();
				break;
			case "SingleOrRecurring":
				PiryxContributionTypes::single_or_recurring();
				break;
			case "SingleOnly":
				PiryxContributionTypes::one_time();
				break;
			case "RecurringOnly":
				PiryxContributionTypes::recurring_only();
				break;
		}
	} //end display_contribution_types
	
	private function display_states($current_state) {
		?>
        <option value=""> </option> 
        <?php 
        $states_list = array ('AA', 'AE', 'AK', 'AL', 'AP', 'AR', 'AS', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'FM', 'GA', 'GU', 'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME', 'MH', 'MI', 'MN', 'MO', 'MP', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'PR', 'PW', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VA', 'VI', 'VT', 'WA', 'WI', 'WV', 'WY');
        foreach( $states_list as $state ) :
        	?><option value="<?php echo $state ?>" <?php selected($state, $current_state) ?>><?php echo $state ?></option><?php
        endforeach;
	}
	private function display_sharing() {
		global $post;
		if ( !is_object( $post ) ) return;
		?>
		<div id="sharecare-information">
        <h2>Sharing Is Caring</h2>
        <iframe frameborder="0" scrolling="no" allowtransparency="true" style="border: medium none; overflow: hidden; width: 340px; height: 75px;" src="https://www.facebook.com/plugins/like.php?href=<?php echo esc_url( get_permalink( $post->ID ) ); ?>&amp;width=340&amp;font=segoe%2Bui">
        </iframe>
    </div>
		<?php
	} //end display_sharing

	private function display_legal_compliance() {
		$options = get_option('piryx_options');
		$campaign_details = html_entity_decode( $this->campaign_details, ENT_QUOTES, 'UTF-8' );
		$xml = new SimpleXMLElement($campaign_details); 

		foreach( $xml->Page->LegalCompliance->children() as $child )
			$found_children = true;
		if ( ! $found_children ) {
			?><input type="hidden" value="yes" name="legalCompliance"><?php
			return;
		}
		// if($xml->Page->LegalCompliance->count() )
			// echo "yes";
		?>
		<div id="legal-compliance" class="legal-compliance section">
		<h2>Legal Compliance</h2> 
		<input type="checkbox" value="yes" id="legalCompliance" name="legalCompliance" <?php checked($_POST['legalCompliance'], "yes"); ?>><span title="Required" class="req">Required</span> I confirm that my donation meets the following requirements: <?php $this->display_error('legalCompliance');?>
		<div class="text"> 
		<?php echo $xml->Page->LegalCompliance->asXML(); ?>
		</div>
		</div> <?php
	}
}