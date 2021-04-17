<?php
/* $Id: cc_via_migs.php,v 1.2 2006/01/22 22:08:49 cmanderson Exp $
 * ====================================================================
 * License:	GNU Lesser General Public License (LGPL)
 *
 * Copyright (c) 2007-2008 Cameron Manderson (cameronmanderson@gmail.com).
 * All rights reserved.
 *
 * This file is part of the MIGS Payment Module intended for use in
 * ZenCart and is a portage of the version currently used on OSCommerce.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.

 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Credit Card Payments through the Mastercard Payment Gateway (MIGS)
 *
 * This Concrete Class allows payments to be processed under different Integrations
 * specified by the administrator
 * - Merchant Hosted Web Payment Pages
 * - Server Hosted transactions
 *
 * This Payment Module was developed by refering to MasterCard
 * Internet Gateway Service (MIGS) Virtual Payment Client Integration
 * Guide written by Patrick Hayes, Principal Consultant, eBusiness
 * Solutions, written 1st July 2004 for MIGS Payment Server 2.4,
 * Virtual Payment Client 1.0, Document Revision # 1.1
 *
 * Used originally in the osCommerce package developed by Open Source E-Commerce Solutions
 * http://www.oscommerce.com Copyright (c) 2003 osCommerce
 *
 * Changelog: 9 June 2008 Cameron Manderson 
 * - Updated server hosted VPC URL (Supplied/tested by jay@webextremecustomiser.com)
 * - Removed comments about what lines are modified for portage
 * Changelog: 5 December 2007 Cameron Manderson
 * - Fixed syntax line 551.
 * Ported on 13 November 2007 to ZenCart
 * Changelog: 13 November 2007 Cameron Manderson
 * - Used reference http://www.zen-cart.com/wiki/index.php/Developers_-_Porting_modules_from_osC
 * - Modified DB Queries to use ADODB style queries and 'global $db;'
 * - Search and Replace tep_ with zen_
 * - Search and Replace HTTP_POST_VARS with _POST
 * - Search and Replace HTTP_GET_VARS with _GET
 * - Removed reference to global HTTP_POST_VARS, HTTP_GET_VARS etc
 *
 * @author Cameron Manderson cameronmanderson@gmail.com
 * @copyright Copyright &copy; 2007, Cameron Manderson
 */

// NOTE: Change the naming of this module (ANZ/Commbank/Bendigo) through the includes/languages/english/modules/payment/cc_via_migs.php

// Customise to suit custom implementations of the process and return scripts if necessary
//define('MODULE_PAYMENT_MIGS_SERVER_HOSTED_PROCESS', 'migs_server_process.php'); // The Local script handler performing the Server-hosted Interation with the VPC
// define('MODULE_PAYMENT_MIGS_RETURN_HANDLER', 'migs_return.php'); // The Return Page that will update the order status and restore the session variables
// define('MODULE_PAYMENT_MIGS_MERCHANT_PROCESS', 'migs_merchant_process.php'); // The Local script handler performing the Merchant-hosted Interaction with the VPC
// define('MODULE_PAYMENT_MIGS_MAY_OMMIT_HASH', false); // The Secure Hash (optional request field) may be ommitted if allowing mayOmitHash by Payment Provider

// Customise the following defined variable to the server hosted VPC URL if required
define('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_SERVER_HOSTED_VPC_URL', 'https://migs.mastercard.com.au/vpcpay'); // Updated by Jay
// define('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_SERVER_HOSTED_VPC_URL', 'https://migs.mastercard.com.au/ma/ANZAU');
//define('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED_VPC_URL', 'https://migs.mastercard.com.au/vpcpay'); // Default Australian Virtual Payment Client

define('MODULE_PAYMENT_MIGS_TEXT_TITLE', 'MIGS');
define('MODULE_PAYMENT_MIGS_TEXT_DESCRIPTION', 'Payment module for MasterCard Internet Gateway Service (MIGS) based on both Integration Models');

class cc_via_migs {
	// Variable Decleration
	var $code;
	var $title;
	var $description;
	var $enabled;

	/**
	 * Instantiates a new object of class 'cc_via_migs'
	 * Initiates the Variables required for the payment module
	 */
	function cc_via_migs() {
		// Specify Object operation parameters
		$this->code = 'cc_via_migs';
		$this->title = (defined('MODULE_PAYMENT_MIGS_TEXT_TITLE') ? MODULE_PAYMENT_MIGS_TEXT_TITLE : 'Credit Card via MIGS'); // Change this for changing what appears on your site
		$this->description = (defined('MODULE_PAYMENT_MIGS_TEXT_DESCRIPTION') ? MODULE_PAYMENT_MIGS_TEXT_DESCRIPTION : 'Payment module for MasterCard Internet Gateway Service (MIGS) based on both Integration Models');
		$this->email_footer = (defined('MODULE_PAYMENT_MIGS_TEXT_EMAIL_FOOTER') ? MODULE_PAYMENT_MIGS_TEXT_EMAIL_FOOTER : '');

		/* Ensure that everything appears complete to enable this payment processor */
		if(defined('MODULE_PAYMENT_MIGS_STATUS') && strtoupper(MODULE_PAYMENT_MIGS_STATUS) == 'TRUE') {
			$merchantID = (defined('MODULE_PAYMENT_MIGS_MERCHANT_ID') ? MODULE_PAYMENT_MIGS_MERCHANT_ID : null);
			$accessCode = (defined('MODULE_PAYMENT_MIGS_ACCESS_CODE') ? MODULE_PAYMENT_MIGS_ACCESS_CODE : null);
			$secretHash = (defined('MODULE_PAYMENT_MIGS_SECRET_HASH') ? MODULE_PAYMENT_MIGS_SECRET_HASH : null);
			if(!empty($merchantID) && !empty($accessCode)) {
				// Check for required under scenario
				if($this->isServerHosted()) $this->enabled = (!empty($secretHash) ? true : false);
				else $this->enabled = true;
			} else $this->enabled = false;
		} else $this->enabled = false;

		// Specify the form action url
		$this->form_action_url = $this->getActionURL();

		// Contribution from Richard Lee Friday 7th October Begin
		$this->sort_order = MODULE_PAYMENT_MIGS_SORT_ORDER;
		// Contribution End
	}

	/**
	 * Update the status of whether this payment module is available
	 * to check out with
	 * Reference: OSCommerce Payment Module
	 */
	function update_status() {
		/* Check whether the zones/geo_zones is valid */
		global $order;
		if (((int) MODULE_PAYMENT_MIGS_VALID_ZONE > 0)) {
			$checkFlag = false;
			global $db;
			$sql = "select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MIGS_VALID_ZONE . "' and zone_country_id = '".$order->delivery['country']['id']."' order by zone_id";
			$result = $db->Execute($sql);
			if($result) while(!$result->EOF) {
				if ($result->fields['zone_id'] < 1) {
					$checkFlag = true;
					break;
				}
				elseif ($result->fields['zone_id'] == $order->delivery['zone_id']) {
					$checkFlag = true;
					break;
				}
				// Move Next
			}
			
			/* Set whether this should be valid or not */
			if ($checkFlag == false) {
				$this->enabled = false;
			}
		}
	}

	/**
	 * Return with the Javascript validation for receiving CC details
	 * from the form (help make validation more efficient/effective)
	 *
	 * @return String Validation to be used in the checkout form if required
	 */
	function javascript_validation() {
		// If operating as Merchant Hosted Web Payment Pages
		if($this->isMerchantHosted()) {
			// Specify Javascript to validate the form elements
			// Credits: OSCommerce Javascript Validation from cc.php
			$js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
			    '    var cc_owner = document.checkout_payment.cc_via_migs_cc_owner.value;' . "\n" .
			    '    var cc_number = document.checkout_payment.cc_via_migs_cc_number.value;' . "\n" .
			    '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
			    '      payment_error = payment_error + "' . MODULE_PAYMENT_MIGS_TEXT_JS_CC_OWNER . '";' . "\n" .
			    '      error = 1;' . "\n" .
			    '    }' . "\n" .
			    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
			    '      payment_error = payment_error + "' . MODULE_PAYMENT_MIGS_TEXT_JS_CC_NUMBER . '";' . "\n" .
			    '      error = 1;' . "\n" .
			    '    }' . "\n" .
			    '  }' . "\n";

			return $js;
		} else {
			// Else return empty String (No Validation required, just a selection)
			return '';
		}
	}

	/**
	 * Creates a selection array that defines the required fields
	 * when providing this module as a potential payment method
	 * @return Array containing the module/form information to be used rendering
	 * 				 the form
	 */
	function selection() {
		// If operating as Merchant Hosted Web Payment Pages
		if($this->isMerchantHosted()) {
			// Specify the required form fields
      		global $order;
			// Determine Expiry Month Field
			for ($i = 1; $i < 13; $i ++) {
				$expires_month[] = array ('id' => sprintf('%02d', $i), 'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)));
			}
			// Determine Expiry Year Field
			$today = getdate();
			for ($i = $today['year']; $i < $today['year'] + 10; $i ++) {
				$expires_year[] = array ('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
			}
			// Build the array with the fields
			// TODO: Use hidden Payment Attempt field
			$selection = array ('id' => $this->code, 'module' => $this->title, 'fields' => array (array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_OWNER, 'field' => zen_draw_input_field('cc_via_migs_cc_owner', $order->billing['firstname'].' '.$order->billing['lastname'])), array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_NUMBER, 'field' => zen_draw_input_field('cc_via_migs_cc_number')), array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_EXPIRES, 'field' => zen_draw_pull_down_menu('cc_via_migs_cc_expires_month', $expires_month).'&nbsp;'.zen_draw_pull_down_menu('cc_via_migs_cc_expires_year', $expires_year)), array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_CARD_SECURITY_CODE, 'field' => zen_draw_input_field('cc_via_migs_cc_securitycode', '', 'size="4" maxlength="4"').'&nbsp;<small>'.MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_CARD_SECURITY_CODE_LOCATION.'</small>')));
			return $selection;

		} else {
			// Else return the payment form as a selection
			// TODO: Include some information about the process
			$selection = array('id' => $this->code, 'module' => $this->title);
      		return $selection;
		}
	}

	/**
	 * Performs required validation to check and ensure that the
	 * submitted form information appears correct before performming the
	 * transaction through the confirmation screen.
	 *
	 * Uses the cc_validation.php class to check the CC Number, expiry date
	 * and redirects an $error string containing the Friendly Error Message
	 * to the Checkout Payment screen
	 */
	function pre_confirmation_check() {
		// If operating as Merchant Hosted Web Payment Pages
		if($this->isMerchantHosted()) {
			include (DIR_WS_CLASSES.'cc_validation.php');

			// Perform validation through the cc_validation class
			$cc_validation = new cc_validation();
			$result = $cc_validation->validate($_POST['cc_via_migs_cc_number'], $_POST['cc_via_migs_cc_expires_month'], $_POST['cc_via_migs_cc_expires_year']);
			$error = '';
			switch ($result) {
				case -1 :
					$error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
					break;
				case -2 :
				case -3 :
				case -4 :
					$error = TEXT_CCVAL_ERROR_INVALID_DATE;
					break;
				case false :
					$error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
					break;
			}

			// Redirect the user if contains an error
			if (($result == false) || ($result < 1)) {
				$payment_error_return = 'payment_error='.$this->code.'&error='.urlencode($error).'&cc_owner='.urlencode($_POST['cc_via_migs_cc_owner']).'&cc_via_migs_cc_expires_month='.$_POST['cc_via_migs_cc_expires_month'].'&cc_via_migs_cc_expires_year='.$_POST['cc_via_migs_cc_expires_year'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
			}

			// Treat the CC details to ensure they are ready to be sent to a payment gateway
			$this->cc_card_type = $cc_validation->cc_type;
			$this->cc_card_number = $cc_validation->cc_number;
			$this->cc_expiry_month = $cc_validation->cc_expiry_month;
			$this->cc_expiry_year = $cc_validation->cc_expiry_year;
		}
		// Else allow filter through as no validation required
	}

	/**
	 * Return form fields displaying the confirmation information that will
	 * be used once they press the process button.
	 *
	 * @return Array Confirmation elements array required to process the
	 * 				 form
	 */
	function confirmation() {
		// If operating as Merchant Hosted Web Payment Pages
		if($this->isMerchantHosted()) {
			// Display the form details back to the user masking the credit card details
			$confirmation = array ('title' => $this->title.': '.$this->cc_card_type, 'fields' => array (array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_OWNER, 'field' => $_POST['cc_via_migs_cc_owner']), array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_NUMBER, 'field' => substr($this->cc_card_number, 0, 4).str_repeat('X', (strlen($this->cc_card_number) - 8)).substr($this->cc_card_number, -4)), array ('title' => MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_EXPIRES, 'field' => strftime('%B, %Y', mktime(0, 0, 0, $_POST['cc_via_migs_cc_expires_month'], 1, '20'.$_POST['cc_via_migs_cc_expires_year'])))));
			//return $confirmation;
		} else {
			// TODO: Display information to the user based on the process
			$confirmation = array ('title' => $this->description);
		}
		return $confirmation;
	}

	/**
	 * Create the required Hidden Fields that contain the payment
	 * information to be used by the Payment Gateway (MIGS)
	 *
	 * @return String Hidden Form Fields submitted to the action url
	 */
	function process_button() {
		global $_POST, $order;
		// If Submitting through the Merchant-Hosted Payment
		if($this->isMerchantHosted()) {
			// Create all the required fields to be sent to the action url
			// (Defined on page 44 of VPCIG)
			// Required Fields
			$required_field_string = zen_draw_hidden_field('vpc_Version', $this->getVPCVersion()) .
									 zen_draw_hidden_field('vpc_Command', $this->getCommand()) .
									 zen_draw_hidden_field('vpc_MerchTxnRef', $this->getMerchantTransactionReference()) .
									 // Don't include the Variables to the form
									 //zen_draw_hidden_field('vpc_AccessCode', $this->getAccessCode()) .
									 zen_draw_hidden_field('vpc_Merchant', $this->getMerchantID()) .
									 zen_draw_hidden_field('vpc_OrderInfo', $this->getCustId()) .
									 //zen_draw_hidden_field('vpc_anzExtendedOrderInfo', $this->getCustName()) .
									 //zen_draw_hidden_field('vpc_Amount', intval((number_format($order->info['total'])) * 100)); // Convert to Cents by Multiplying by 100
									 zen_draw_hidden_field('vpc_CardNum', $this->cc_card_number) .
									 zen_draw_hidden_field('vpc_CardExp', $_POST['cc_via_migs_cc_expires_year'] . $this->cc_expiry_month); // Optionally use: $_POST['cc_via_migs_cc_expires_month'] . $_POST['cc_via_migs_cc_expires_year']

			// Optional Fields
			$optional_field_string = '';
			if(!empty($_POST['cc_via_migs_cc_securitycode'])) {
				$optional_field_string .= zen_draw_hidden_field('vpc_CardSecurityCode' , $_POST['cc_via_migs_cc_securitycode']);
			}
			if($this->isCustomCSCLevelDefined()) {
				$optional_field_string .= zen_draw_hidden_field('vpc_CSCLevel', $this->getCSCLevel());
			}
			if($this->isOptionalTransactionSource()) {
				$optional_field_string .= zen_draw_hidden_field('vpc_TransSource', $this->getTransactionSource());
			}
			$optional_field_string .= zen_draw_hidden_field('vpc_TicketNo', $this->getTicketNumber());
			// Build the hidden fields
			$process_button_string = $required_field_string . $optional_field_string;
			return $process_button_string;
		} else { // Else submitting directly to the Server Hosted
			// Create all the fields required eg Return URL etc (Defined page 34 of VPCIG)
			// Required Fields
			$required_field_string = zen_draw_hidden_field('vpc_Version', $this->getVPCVersion()) .
									 zen_draw_hidden_field('vpc_Command', $this->getCommand()) .
									 zen_draw_hidden_field('vpc_MerchTxnRef', $this->getMerchantTransactionReference()) .
									 // Don't include the Variables to the form, place them in after
									 //zen_draw_hidden_field('vpc_AccessCode', $this->getAccessCode()) .
									 zen_draw_hidden_field('vpc_Merchant', $this->getMerchantID()) .
									 zen_draw_hidden_field('vpc_OrderInfo', $this->getCustId()) .
 								     //zen_draw_hidden_field('vpc_anzExtendedOrderInfo', $this->getCustName()) .
									 //zen_draw_hidden_field('vpc_Amount', intval((number_format($order->info['total'])) * 100)) // Convert to Cents by Multiplying by 100
									 zen_draw_hidden_field('vpc_Locale', $this->getLocale()) .
									 zen_draw_hidden_field('vpc_ReturnURL', $this->getReturnURL());

			// Optional Fields
			$optional_field_string = '';
			//$optional_field_string .= zen_draw_hidden_field('vpc_SecureHash', $this->getSecureHash());
			if($this->isCustomCSCLevelDefined()) {
				$optional_field_string .= zen_draw_hidden_field('vpc_CSCLevel', $this->getCSCLevel());
			}
			$optional_field_string .= zen_draw_hidden_field('vpc_TicketNo', $this->getTicketNumber());

			// Ensure that Session Variables and Transaction Reference is attached
			$session_variables = '';
			$session_variables .= zen_draw_hidden_field('session_name',zen_session_name());
			$session_variables .= zen_draw_hidden_field('session_id', zen_session_id());
			//$session_variables .= zen_draw_hidden_field(zen_session_name(), zen_session_id()); // Value should be present by OSCommerce

			// Build the hidden fields
			$process_button_string = $required_field_string . $optional_field_string . $session_variables;
			return $process_button_string;
		}
	}

	/**
	 * Perform additional required tasks before the processing
	 * @return boolean Function response
	 */
	function before_process() {
		global $order;
		$verifiedHash = false;
		$responseArray = array();
		// Check the status of the process
		if(empty($_GET['vpc_ReceiptNo'])) { // Payment hasn't been processed
			$verifiedHash = false;
			// Determine how to process the transaction
			if($this->isMerchantHosted()) {
				// TODO: Process the Merchant hosted requirements
				$verifiedHash = true;
				// Determine Required Fields
				$_POST['vpc_AccessCode'] = $this->getAccessCode();
//				$_POST['vpc_Amount'] = intval((number_format($order->info['total'])) * 100); // Convert to Cents by Multiplying by 100
				$_POST['vpc_Amount'] = intval((round($order->info['total'],2)) * 100);

				unset($_POST['x']);
				unset($_POST['y']);

				// Create the Request String
				$vpcURL = $this->getVPCUrl();
				$postRequestData = '';
				$amp = '';
				foreach($_POST as $key => $value) {
					if(!empty($value) && $key !== zen_session_name()) { // Eliminate the empty variables
						$postRequestData .= $amp . urlencode($key) . '=' . urlencode($value);
						$amp = '&';
					}
				}
				// Send the test information to the notify email
			    if($this->isTestMode()) $this->sendNotifyEmail($postRequestData, $responseArray);

				// Get the Secure Connection to the VPC and Buffer communication
				ob_start();
				$clientURL = curl_init();
				// Initialise the client url variables
				// REFER to curl_setopt for more options as suits your requirements
				// such as Verify SSL, Proxy etc
				curl_setopt ($clientURL, CURLOPT_URL, $vpcURL);
				curl_setopt ($clientURL, CURLOPT_POST, 1);
				curl_setopt ($clientURL, CURLOPT_POSTFIELDS, $postRequestData);
				curl_exec ($clientURL); // Open connection
				$vpcResponse = ob_get_contents(); // Get result
				ob_end_clean(); // Finish with the buffer
				// Check for errors
				if(strchr($vpcResponse,"<html>")) $errorMessage = $vpcResponse;
				else if(curl_error($clientURL)) $errorMessage = "CURL ERROR: " . curl_errno($clientURL) . " " . curl_error($clientURL);
				// Communication Issues should be sent to Administrator, not to screen
				curl_close($clientURL); // Close the connection
				$responseKeyVals = split("&", $vpcResponse);
			    foreach ($responseKeyVals as $val) {
			        $param = split("=", $val);
			        $responseArray[urldecode($param[0])] = urldecode($param[1]);
			    }
			    // Send the test information to the notify email
			    if($this->isTestMode()) $this->sendNotifyEmail($errorMessage, $responseArray);
				if(!empty($errorMessage)) {
					$this->sendNotifyEmail($errorMessage, $responseArray);
					$transactionResponse = '1';
				} else {
					// Process the results and determine the transactions status
					$transactionResponse = $responseArray['vpc_TxnResponseCode'];
				}
			} else {
				// Process the Server hosted requirements
				// Key Sort the variables and extract any unwanted variables
				unset($_POST['x']);
				unset($_POST['y']);
				// Specify the Access Code
				$_POST['vpc_AccessCode'] = $this->getAccessCode();
//				$_POST['vpc_Amount'] = intval((number_format($order->info['total'])) * 100); // Convert to Cents by Multiplying by 100
				$_POST['vpc_Amount'] = intval((round($order->info['total'],2)) * 100);

				ksort($_POST);

				// Get the URL and append the variables
				$vpcURL = $this->getVPCUrl() . "?";
				$secureSecret = $this->getSecretHash();
				$md5HashData = $secureSecret;
				foreach($_POST as $key => $value) {
					if(!empty($value)) { // Eliminate the empty variables
						$vpcURL .= urlencode($key) . '=' . urlencode($value) . '&';
						$md5HashData .= $value;	// Append to md5 hash data
					}
				}

				// Calculate the Hash
				// Handle mayOmmitHash privilege on the Server
				// Change MODULE_PAYMENT_MIGS_MAY_OMMIT_HASH at top of file to toggle this function
				if(!defined('MODULE_PAYMENT_MIGS_MAY_OMMIT_HASH') || MODULE_PAYMENT_MIGS_MAY_OMMIT_HASH == false) {
					if(!empty($secureSecret)) {
						$vpcURL .= "vpc_SecureHash=" . strtoupper(md5($md5HashData));
					}
				}

				// Perform the process
				//die($vpcURL); // Testing Purposes you can uncomment to verify details before submitted
				header("Location: " . $vpcURL);
				die();
			}
		} else {
			// Assume resuming processing from check out process
			// Verify the secure hash to ensure that the communication wasn't altered or forged
			$secureHashResponse = $_GET['vpc_SecureHash'];
			$transactionResponse = $_GET['vpc_TxnResponseCode'];
			unset($_GET['vpc_SecureHash']);
			$responseArray = $_GET;

			if(!empty($secureHashResponse) && $transactionResponse !== '7') {
				// Create the md5 based off fields and our secret hash
				$md5HashData = $this->getSecretHash();
				ksort($responseArray); // Should arrive in order
				foreach($responseArray as $key => $value) {
			        if (strlen($value) > 0) {
			            $md5HashData .= $value;
			        }
			    }
			    // Check the SecureHashResponse against our generated md5 hash to verify the key
			    if (strtoupper($secureHashResponse) == strtoupper(md5($md5HashData))) {
			    	$verifiedHash = true;
			    	//die('Verified Successfully');
			    }
			} else {
				// Notify System Admin of the event that the SecretHash could not be verified as it wasn't present
				$errorMsg = (defined(MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_PRESENT) ? MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_PRESENT : "Secure Hash not present in Response from MIGS Payment Server for Payment");
				$this->sendNotifyEmail($errorMsg, $responseArray);
				// Redirect user with an error message saying that the response from the server was not complete and
				// that we can not verify your payment. Please contact Admin to resolve
				//zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . urlencode($errorMsg), 'SSL', true, false));
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=cc_via_migs', 'SSL', true, false));
			}
		}
		// Jay has suggested only sending this using merchant hosted payments 
		if($verifiedHash == false && $this->isMerchantHosted()) {
			// Notify System Admin of the alteration or forgery by attempting to collect information from
			// the user, such as their Customer ID, Order ID, IP etc, to double check records CC Merchant Account against
			// incase the order was infact processed
			$errorMsg = (defined(MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_VALID) ? MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_VALID : "Secure Hash was not able to be verified against the Secret Hash, possible tampering with response");
			$this->sendNotifyEmail($errorMsg, $responseArray);

			// Redirect with a error message such as Tampering has occured with the response and to contact Admin to resolve
			//zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . urlencode($errorMsg), 'SSL', true, false));
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=cc_via_migs', 'SSL', true, false));
		}
		

		// Check the status of the order
		if($transactionResponse !== '0') {
			// There was an error, redirect to appropriate screen and display text message
			//zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . urlencode((empty($responseArray['vpc_Message'])) ? MODULE_PAYMENT_MIGS_TEXT_ERROR : $responseArray['vpc_Message']), 'SSL', true, false));
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=cc_via_migs', 'SSL', true, false));
		} else {
			// Attempt to update some fields of the order in order to cache to database
			$order->info['cc_type'] = "AcqResponseCode: ".$responseArray['vpc_AcqResponseCode'];
			$order->info['cc_owner'] = "TransactionNo: " . $responseArray['vpc_TransactionNo'];
			$order->info['cc_number'] = "ReceiptNo: " . $responseArray['vpc_ReceiptNo'];
			$this->email_footer .= "\nReceipt No: " .$responseArray['vpc_ReceiptNo'];//append transaction no. to footer
		}
	}

	/**
	 * Perform additional required tasks after the processing
	 * EG Mail details to store owner etc
	 * @return boolean Function response
	 */
	function after_process() {
		// TODO: Update Status of Order to Processing
		return false;
	}

	/**
	 * Create an array that represents the possible module errors
	 * @return Array Errors that have arrived from HTTP
	 */
	function get_error() {
		$error = array('title' => MODULE_PAYMENT_MIGS_TEXT_ERROR, 'error' => ((isset($_GET['error'])) ? stripslashes(urldecode($_GET['error'])) : MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION));
		return $error;
	}

	/**
	 * Checks whether the module has been installed
	 * @return int 1 for installed
	 */
	function check() {
		if (!isset ($this->_check)) {
			// ZenCart Modification Begin: We don't use tep_db anymore, we use ADODB style
			//$check_query = tep_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_MIGS_STATUS'");
			//$this->_check = tep_db_num_rows($check_query);
			global $db; // ZenCart uses global db reference
			$sql = "select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_MIGS_STATUS'";
			$result = $db->Execute($sql);
			if($result) {
				// Return whether we have matched the configuration value (could use record count here also)
				$this->_check = (!empty($result->fields['configuration_value']) ? 1 : 0);
			}
			// :ZenCart Modification End
			
		}
		return $this->_check;
	}

	/**
	 * Performed when installing the MIGS Payment Processor
	 * SQL Inserts all the required configuration variables to be
	 * assigned by the store owner in the administration screen
	 */
	function install() {
		global $db;
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable MIGS Payment Module', 'MODULE_PAYMENT_MIGS_STATUS', 'False', 'Do you want to accept payments through MasterCard Internet Gateway Service (MIGS)?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('MIGS Test Host Simulator', 'MODULE_PAYMENT_MIGS_TEST_MODE', 'True', 'Do you wish to operate under the MIGS Test Host Simulator? It is important to recognise that the under this you will not be able to perform live transactions. Live transactions need to be specifically configured by the bank to perform.', '6', '2', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MIGS Merchant ID', 'MODULE_PAYMENT_MIGS_MERCHANT_ID', '', 'The unique Merchant ID assigned to you by your Payment Provider. (alphanumeric, upto 16 digits long)', '6', '3', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MIGS Access Code', 'MODULE_PAYMENT_MIGS_ACCESS_CODE', '', 'The Access Code authenticates you on the Payment Server so that a merchant cannot access another merchant\'s Merchant ID (alphanumeric, 8 digits long)', '6', '4', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Merchant-Hosted Integration Model', 'MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED', 'False', 'Select TRUE to use the Merchant-Hosted Payments Integration Model. Select FALSE to use the Server-Hosted Payments Integration Model', '6', '5', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MIGS Secure Hash Secret', 'MODULE_PAYMENT_MIGS_SECRET_HASH', '', 'Required when operating under MIGS Server-Hosted Integration Model', '6', '6', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Optional Notification Email Address', 'MODULE_PAYMENT_MIGS_EMAIL', '', 'You may optionally specify an email address to have payment error reported to such as Tampering Of Response and No Secure Hash Present notifications. By default if this is left empty the Site Admin will be notified', '6', '7', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_MIGS_VALID_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display', 'MODULE_PAYMENT_MIGS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '2', now())");
	}

	/**
	 * Performs a SQL delete statement to remove all configuration
	 * variables for this payment module
	 */
	function remove() {
		$keys = $this->keys();
		foreach($keys as $configurationKeys) {
			global $db; // ZenCart uses global db reference
			$db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . $configurationKeys . "'");
		}
	}

	/**
	 * Returns immediately with a listing of all the keys used
	 * with this payment module
	 * @return Array All the defined variables used by this module
	 */
	function keys() {
		return array('MODULE_PAYMENT_MIGS_STATUS',
					 'MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED',
					 'MODULE_PAYMENT_MIGS_TEST_MODE',
					 'MODULE_PAYMENT_MIGS_MERCHANT_ID',
					 'MODULE_PAYMENT_MIGS_ACCESS_CODE',
					 'MODULE_PAYMENT_MIGS_SECRET_HASH',
					 'MODULE_PAYMENT_MIGS_EMAIL',
					 'MODULE_PAYMENT_MIGS_VALID_ZONE',
					 'MODULE_PAYMENT_MIGS_SORT_ORDER'
					 );
	}

	/**
	 * Returns immediately with whether this is operating as a
	 * Merchant-Hosted Integration by referencing the administration const
	 * MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED
	 * @return boolean Check condition whether integration is merchant-hosted
	 */
	function isMerchantHosted() {
		if(defined('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED')) {
			return(strtoupper(MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED) == 'TRUE');
		}
		return false; // Default to running Server Hosted
	}

	/**
	 * Returns immediately with whether this is operating as a
	 * Sever-Hosted Integration
	 */
	function isServerHosted() {
		return(!$this->isMerchantHosted());
	}

	/**
	 * Returns immediately with whether this is operating in test mode
	 * References defined variable MODULE_PAYMENT_MIGS_TEST_MODE which
	 * should be configured in the administration panel.
	 * @return boolean Test Mode check
	 */
	function isTestMode() {
		if(defined('MODULE_PAYMENT_MIGS_TEST_MODE')) {
			return(strtoupper(MODULE_PAYMENT_MIGS_TEST_MODE) == 'TRUE');
		}
		return true; // Default to running test mode
	}

	/**
	 * Returns with the Action URL appropriate for this transaction
	 * Defaults to the Australian VPC URL
	 * Optional: Specify
	 * MODULE_PAYMENT_MIGS_MERCHANT_HOSTED_PROCESS
	 * MODULE_PAYMENT_MIGS_SERVER_HOSTED_PROCESS
	 * in the define statements at top of the file to change the handler
	 * script
	 * @return String URL to action
	 */
	function getActionURL() {
		if($this->isMerchantHosted()) {
			if(defined('MODULE_PAYMENT_MIGS_MERCHANT_HOSTED_PROCESS')) {
				$handlerScript = MODULE_PAYMENT_MIGS_MERCHANT_HOSTED_PROCESS;
			} else {
				$handlerScript = FILENAME_CHECKOUT_PROCESS; // Don't change here, change at top of the file in the define(MODULE_PAYMENT_MIGS_MERCHANT_HOSTED_PROCESS)
			}
			return zen_href_link($handlerScript, '', 'SSL');
		} else {
			if(defined('MODULE_PAYMENT_MIGS_SERVER_HOSTED_PROCESS')) {
				$handlerScript = MODULE_PAYMENT_MIGS_SERVER_HOSTED_PROCESS;
			} else {
				$handlerScript = FILENAME_CHECKOUT_PROCESS; // Don't change here, change at top of the file in the define(MODULE_PAYMENT_MIGS_SERVER_HOSTED_PROCESS)
			}
			return zen_href_link($handlerScript, '', 'SSL');
		}
	}

	/**
	 * Returns with the VPC used for Server Hosted Transactions
	 * Note: Specify the correct VPC for your application in the
	 * MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_SERVER_HOSTED_VPC_URL
	 * MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED_VPC_URL
	 * defined variable at the top of this message
	 * @return String VPC used for the Server Hosted Transactions
	 */
	function getVPCUrl() {
		if($this->isMerchantHosted()) {
			// Get the Merchant Hosted
			if(defined('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED_VPC_URL')) {
				return MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_MERCHANT_HOSTED_VPC_URL;
			}
			// Default is the Australian VPC
			return 'https://migs.mastercard.com.au/vpcdps'; // Don't Change here, change in the commented code at top of file
		} else {
			if(defined('MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_SERVER_HOSTED_VPC_URL')) {
				return MODULE_PAYMENT_MIGS_INTEGRATION_MODEL_SERVER_HOSTED_VPC_URL; // Trailing ? for variables
			}
			// Default is the Australian VPC
			return 'https://migs.mastercard.com.au/vpcpay'; // Don't Change here, change in the commented code at top of file
		}
	}

	/**
	 * Returns with the Merchant ID
	 * References defined variable MODULE_PAYMENT_MIGS_MERCHANT_ID
	 * @return String Treated Merchant ID
	 */
	function getMerchantID() {
		// PREFIX the word 'TEST' to the merchant ID if in Simulator infrastructure
		if($this->isTestMode()) $prefix = 'TEST';
		return($prefix . trim(MODULE_PAYMENT_MIGS_MERCHANT_ID));
	}

	/**
	 * Returns immediately with the Access Code
	 * Note: Better to leave this to the migs_server_process.php
	 * @return String Alphanumeric MIGS Account Access Code
	 */
	function getAccessCode() {
		if(defined('MODULE_PAYMENT_MIGS_ACCESS_CODE')) {
			return(trim(MODULE_PAYMENT_MIGS_ACCESS_CODE));
		}
		return 'notfound';
	}

	/**
	 * Returns with the Secure Hash
	 * @return String Secure Hash
	 */
	function getSecretHash() {
		if(defined('MODULE_PAYMENT_MIGS_SECRET_HASH')) {
			return(trim(MODULE_PAYMENT_MIGS_SECRET_HASH));
		}
		return ''; // Default to empty
	}

	/**
	 * Returns with the Merchant Transaction Reference
	 * Builds from the order info
	 * @return String Merchant Transaction Reference
	 */
	function getMerchantTransactionReference() {
		// TODO: Integrate payment attempt logging
		$attempt = '1';
		return($this->getCustId() . '/' . $attempt);
	}

	/**
	 * Returns with the Order Information for the process
	 * @return String Order Info String
	 */
	function getCustId() {
		global $customer_id;
		// Todo: Set a way to match up the records
		$uniqueKey = $customer_id . 'AT' . date("YmdHis");
		// PREFIX the word 'test' to the merchant transaction reference if in Simulator infrastructure
		if($this->isTestMode()) $prefix = 'test';
		// TODO: Use a unique Transaction Reference Number for this order
		$transactionReferenceNumber = $uniqueKey; // Should be based off a shopping cart number, an order number or and invoice number
		return($prefix . $transactionReferenceNumber);
	}

	function getCustName(){
		global $order;
		// Remove any extra white space
		$name = trim($order->customer['firstname'].' '.$order->customer['lastname']);
		// Remove any troublesome quotes
		return  preg_replace('/(\s\s+|\'|\"|\`)/', '', $name);
	}

	/**
	 * Return with the Locale for this order
	 * Used in SSL type transactions for specifying the language that is used on the
	 * Payment Server pages that are displayed to the cardholder.
	 * Specified using MODULE_PAYMENT_MIGS_LOCALE in the Language File
	 * @return String Locale
	 */
	function getLocale() {
		// Get the locale from the language specification
		if(defined('MODULE_PAYMENT_MIGS_LOCALE')) {
			return MODULE_PAYMENT_MIGS_LOCALE;
		}
		// Default for Australia
		return 'en_AU'; // Don't change here, change in the define(MODULE_PAYMENT_MIGS_LOCALE) in the language file
	}

	/**
	 * Return with the URL to forward the response from the server to
	 * The URL that is displayed to the cardholder's browser when the
	 * Payment Server sends the transaction response
	 *
	 * Based off HTTPS_SERVER specified in OSCommerce Configuration
	 * and MODULE_PAYMENT_MIGS_RETURN_HANDLER
	 *
	 * @return String URL handling the response from the Payment Server
	 */
	function getReturnURL() {
		//$url = HTTPS_SERVER . MODULE_PAYMENT_MIGS_RETURN_HANDLER; // https://www.mink.net.au/migs_return.php
		if(defined('MODULE_PAYMENT_MIGS_RETURN_HANDLER')) {
			$returnHandler = MODULE_PAYMENT_MIGS_RETURN_HANDLER;
		} else {
			$returnHandler = FILENAME_CHECKOUT_PROCESS; // Don't change here, change in the define(MODULE_PAYMENT_MIGS_RETURN_HANDLER) at top of file
		}
		$url = zen_href_link($returnHandler, '', 'SSL', false);
		return($url);
	}

	/**
	 * Return immediately with the VPC version
	 * Optional: Specify MODULE_PAYMENT_MIGS_VIRTUAL_PAYMENT_CLIENT_VERSION
	 * to overwrite the default
	 * @return int VPC Version
	 */
	function getVPCVersion() {
		if(defined('MODULE_PAYMENT_MIGS_VIRTUAL_PAYMENT_CLIENT_VERSION')) {
			return(MODULE_PAYMENT_MIGS_VIRTUAL_PAYMENT_CLIENT_VERSION);
		}
		return 1; // Default to version 1
	}

	/**
	 * Returns immediately with the Command
	 * Optional: Specify MODULE_PAYMENT_MIGS_COMMAND to overwrite default
	 * @return String Transaction Type
	 */
	function getCommand() {
	 	if(defined('MODULE_PAYMENT_MIGS_COMMAND')) {
	 		return(MODULE_PAYMENT_MIGS_COMMAND);
	 	}
	 	return 'pay';
	}

	/**
	 * Returns with the Secure Hash
	 * @return String Secure Hash
	 */
	function getSecureHash() {
		// Secure Hash cannot be created before the submit
		return ''; // Default to empty
	}

	/**
	 * Checks whether the application has specified the transaction source
	 * Optional: Specify MODULE_PAYMENT_MIGS_TRANSACTION_SOURCE
	 * @return boolean Transaction Source been specified
	 */
	function isOptionalTransactionSource() {
		return defined('MODULE_PAYMENT_MIGS_TRANSACTION_SOURCE');
	}

	/**
	 * Returns immediately with the Transaction Source
	 * Optional: Specify MODULE_PAYMENT_MIGS_TRANSACTION_SOURCE
	 * @return String Tranaction Source
	 */
	function getTransactionSource() {
	 	if(defined('MODULE_PAYMENT_MIGS_TRANSACTION_SOURCE')) {
	 		return(MODULE_PAYMENT_MIGS_TRANSACTION_SOURCE);
	 	}
	 	return 'MOTOCC'; // Default to Mail Order Telephone Order (MOTO) transaction source
	}

	/**
	 * Returns with whether a Custom CSC Level
	 * @return boolean check whether Custom CSC Level has been specified
	 */
	function isCustomCSCLevelDefined() {
		return defined('MODULE_PAYMENT_MIGS_CSC_LEVEL');
	}

	/**
	 * Returns with the CSC Level that that client has specified
	 * Note: Default is used on the Payment Server if not specified
	 */
	function getCSCLevel() {
		if(defined('MODULE_PAYMENT_MIGS_CSC_LEVEL')) {
			return(MODULE_PAYMENT_MIGS_CSC_LEVEL);
		}
		return 'AUS'; // Default to AUS
	}

	/**
	 * Returns with the Ticket Number to be associated with the Payment
	 * @return String Ticket Number
	 */
	function getTicketNumber() {
		//TODO: Implement a way to uniquely identify the Order Contents (such as airline ticket number)
		global $customer_id;
		return($customer_id);
	}

	/**
	 * Returns with the selected currency to perform the payment under
	 * @return String Currency
	 */
	function getCurrency() {
		// TODO: If required to perform under different currencies
	}

	/**
	 * Returns with the Email to Notify of Transaction Errors
	 * @return String Email address of Account Admin
	 */
	function getNotifyEmailAddress() {
		if(defined('MODULE_PAYMENT_MIGS_EMAIL') && zen_validate_email(MODULE_PAYMENT_MIGS_EMAIL)) {
			return MODULE_PAYMENT_MIGS_EMAIL;
		} else {
			if(defined('STORE_OWNER_EMAIL_ADDRESS') && zen_validate_email(STORE_OWNER_EMAIL_ADDRESS)) {
				return STORE_OWNER_EMAIL_ADDRESS;
			}
		}
		return null; // No valid email specified
	}

	/**
	 * Sends an email alert to the registered MIGS Admins email address
	 * @param String $error Verbose Error
	 */
	function sendNotifyEmail($error, &$responseArray) {
		global $order;
		$notifyAddress = $this->getNotifyEmailAddress();
		if (!empty($notifyAddress)) {
			$subject = $error;
			$message = (defined('MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO') ? MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO : 'Error:') . "\n\n";
			$message .= $error . "\n\n";
			$message .= (defined('MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO_DETAILS') ? MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO_DETAILS : 'Payment Details') . "\n\n";
			$message .= "MIGS:\n";
			// Ensure that accesscode etc is ommitted
			foreach($responseArray as $key => $value) $message .= $key . '=' . $value . "\n";
			$message .= "\n\nORDER CUSTOMER:\n";
			foreach($order->customer as $key => $value) $message .= $key . '='. $value . "\n";
			$message .= "\n\nSERVER VARS:\n";
			foreach($_SERVER as $key => $value) $message .= $key . '='. $value . "\n";
			zen_mail('', $notifyAddress, $subject, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');
		}
	}
}
?>