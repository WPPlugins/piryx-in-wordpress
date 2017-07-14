<?php
class PiryxValidate {
	private $errors = '';
	private $form_data = array();
	private $payment_data = array();
	//$args contains form data
	public function __construct( $args = array() ) {
		$this->errors = new WP_Error();
		if ( !is_array( $args ) || count( $args ) == 0 ) {
			$this->errors->add( 'no_args', __( "No arguments have been passed", 'piryx' ) );
			return;
		}
		//Proceed with Validation
		$this->form_data = $_POST;
		$this->validate();
	}
	//Return errors, false if there are none
	public function has_errors() {
		$error_codes = $this->errors->get_error_codes();
		if ( count( $error_codes ) == 0 ) return false;
		return true;	
	} //end get_errors
	public function get_validated_data() {
		return $this->payment_data;
	}
	public function get_errors() {
		return $this->errors;
	}
	//Validates the form inputs and assigns errors if applicable
	private function validate() {
		//Sanitize
		foreach ( $this->form_data as &$data ) {
			$data = sanitize_text_field( trim( $data ) );
			if ( empty( $data ) ) {
				$data = false;
			}
		}
		//Check for required fields
		$required_fields = array(
			'firstName' => __( 'First Name', 'piryx' ),
			'lastName' => __( 'Last Name', 'piryx' ),
			'email' => __( 'E-mail Address', 'piryx' ),
			'address1' => __( 'Home Address', 'piryx' ),
			'city' => __( 'Home City', 'piryx' ),
			'state' => __( 'Home State', 'piryx' ),
			'zip' => __( 'Home Zip Code', 'piryx' ),
			'phone' => __( 'Phone Number', 'piryx' ),
			'employer' => __( 'Employer', 'piryx' ),
			'occupation' => __( 'Occupation', 'piryx' ),
			'billingAddress1' => __( 'Billing Address', 'piryx' ),
			'billingCity' => __( 'Billing City', 'piryx' ),
			'billingState' => __( 'Billing State', 'piryx' ),
			'billingZip' => __( 'Billing Zip', 'piryx' )
		);
		//Check for required or missing fields
		foreach ( $required_fields as $field => $value ) {
			if ( !array_key_exists( $field, $this->form_data ) ) {
				$this->errors->add( $field, $value . __( ' isn\'t present.', 'piryx' ), $field );
			} else {
				$field_value = trim( $this->form_data[ $field ] );
				if ( empty( $field_value ) ) {
					$this->errors->add( $field, $value . __( ' is required.', 'piryx' ), $field );
				}
			}
		} //end foreach
		//Map variables
		extract( $this->form_data );
		
		//Check for credit card of e-check errors
		switch( $payment ) {
			case "ECheck":
				//Validate account/routing number
				if ( !$accountNumber ) {
					$this->errors->add( 'accountNumber', __( 'Account Number must be a valid number.', 'piryx' ), 'accountNumber' );
				} else {
					$accountNumber = $this->numbers_only( $accountNumber );
				}
				if ( !$routingNumber ) {
					$this->errors->add( 'routingNumber', __( 'Routing Number must be 9 characters long.', 'piryx' ), 'routingNumber' );
				} else {
					$routingNumber = $this->numbers_only( $routingNumber );
				}
				break;
			case "Mastercard":
				break;
			case "Visa":
				break;
			case "Amex":
				break;
			case "Discover":
				//Validate credit card inputs
				$CardNumber = $this->numbers_only( $CardNumber );
				$cardCVV2 = $this->numbers_only( $cardCVV2 );
				$expirationMonth = $this->numbers_only( $expirationMonth );
				$expirationYear = $this->numbers_only( $expirationYear );
				if ( !$cardNumber ) 
					$this->errors->add( 'CardNumber', __( 'A Credit Card number is required', 'piryx' ), 'CardNumber' );
					
				if ( !$cardCVV2 )
					$this->errors->add( 'cardCVV2', __( 'Security Code must be valid.', 'piryx' ), 'cardCVV2' );
				
				if ( !$expirationMonth )
					$this->errors->add( 'expirationMonth', __( 'Expiration Month of your credit card must be selected.', 'piryx' ), 'expirationMonth' );
				
				if ( !$expirationYear )
					$this->errors->add( 'expirationYear', __( 'Expiration Year of your credit card must be selected.', 'piryx' ), 'expirationYear' );	
				break;
			default:
				$this->errors->add( 'payment', __( 'A payment method must be selected.', 'piryx' ), 'payment' );
		} //end switch
		//Check for payment accounts
		if ( !isset( $amount ) || $amount == 'custom' ) {
			if ( empty( $customAmount ) ) {
				$this->errors->add( 'amount', __( 'An amount must be selected.', 'piryx' ), 'amount' );
			} else {
				$amount = round( (float)$customAmount, 2 );
				if ( $customAmount <= 5 ) {
					$this->errors->add( 'customAmount', __( 'A custom amount must be above $5.00.', 'piryx' ), 'customAmount' );
				}
			}
		} else {
			$amount = round( (float)$amount, 2 );
				if ( $amount <= 0 ) {
					$this->errors->add( 'amount', __( 'Invalid amount type.', 'piryx' ), 'amount' );
				}
		}

		if ( ! isset( $legalCompliance ) )
			$this->errors->add( 'legalCompliance', __( 'You must agree to the legal compliance.', 'piryx' ), 'legalCompliance' );
		
		//Validate recurring
		$NumberOfRecurringMonths = $this->numbers_only( $NumberOfRecurringMonths );
		
		$this->payment_data = compact( 'campaignID', 'firstName', 'middleName', 'lastName', 'email', 'address1', 'address2', 'city', 'state', 'zip', 'phone', 'workPhone', 'faxPhone', 'mobilePhone', 'employer', 'occupation', 'customAmount', 'amount', 'payment', 'CardNumber', 'cardCVV2', 'expirationMonth', 'expirationYear', 'routingNumber', 'accountNumber', 'billingAddress1', 'billingAddress2', 'billingCity', 'billingState', 'billingZip', 'NumberOfRecurringMonths', 'IsRecurring', 'RecurringPeriod' );
		
		if ( $IsRecurring == 'True' && !isset( $RecurringPeriod ) ) {
			$this->errors->add( 'contribution_type', __( 'A billing period wasn\'t selected.', 'piryx' ), 'amount' );
		}
		//Validate subscription/payment types
		/* //one time or monthly recurring
		[RecurringPeriod] => Monthly [IsRecurring] => True [NumberOfRecurringMonths] =>
		
		//One time or monthly when you select single
		[RecurringPeriod] => Monthly [IsRecurring] => False [NumberOfRecurringMonths] => 
		
		
		//one-time only
		[IsRecurring] => False [NumberOfRecurringMonths] =>
		
		//Monthly
		[IsRecurring] => True [RecurringPeriod] => Monthly [NumberOfRecurringMonths] => 4 ) 
		
		//Monthly Unlimited
		[IsRecurring] => True [RecurringPeriod] => Monthly [NumberOfRecurringMonths] =>
		
		//Yearly Subscription
		=> [IsRecurring] => True [NumberOfRecurringMonths] => [RecurringPeriod] => SemiAnnually
		=> [IsRecurring] => True [NumberOfRecurringMonths] => [RecurringPeriod] => Quarterly 
		[IsRecurring] => True [NumberOfRecurringMonths] => [RecurringPeriod] => Annually
		*/
		
	} //end validate
	//Returns numbers only
	private function numbers_only( $string ) {
		$string = preg_replace('/[^\d]/','', $string );
		return intval( $string );
	}
}