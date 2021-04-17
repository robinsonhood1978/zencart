<?php
/* $Id: cc_via_migs.php,v 1.2 2006/01/22 22:08:49 cmanderson Exp $
 * ====================================================================
 * License:	GNU Lesser General Public License (LGPL)
 *
 * Copyright (c) 2005, 2006 Cameron Manderson (cameron@mink.net.au).
 * All rights reserved.
 *
 * This file is part of the MIGS Payment Module intended for use in
 * osCommerce.
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com - Copyright (c) 2003 osCommerce
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

/* Define the Constant Variables that will be used in the Payment Module 
 * @author Cameron Manderson cameronmanderson@gmail.com 
 */
// Locale
define('MODULE_PAYMENT_MIGS_LOCALE', 'en_AU');

// Here the detail is about what payment method you will be using
// NOTE: Just becuase it says ANZ eGate doesn't mean it can't be Commbank or Bendigo Bank
define('MODULE_PAYMENT_MIGS_TEXT_EMAIL_FOOTER', 'You have paid for your order online using ANZ eGate');
define('MODULE_PAYMENT_MIGS_TEXT_TITLE', 'Credit Card (online via ANZ eGate)');
define('MODULE_PAYMENT_MIGS_TEXT_DESCRIPTION', 'For Credit Card Payments you will be redirected to ANZ eGate, a Secure Online Payment Service provided by ANZ Bank of Australia. Please have your Credit Card details ready. Thank you.<br /><br /><strong><small>IMPORTANT: After confirming this page you will be redirected to a ANZ eGate secure page, please accept any prompts and do not close your browser. If you have any problems with your card please contact your Credit Card Provider.</small></strong>');
//define('MODULE_PAYMENT_MIGS_TEXT_EMAIL_FOOTER', 'You have paid for your order online using Commonwealth Bank of Australia CommWeb');
//define('MODULE_PAYMENT_MIGS_TEXT_TITLE', 'Credit Card (online via Commonwealth Bank of Australia CommWeb)');
//define('MODULE_PAYMENT_MIGS_TEXT_DESCRIPTION', 'For Credit Card Payments you will be redirected to CommWeb, a Secure Online Payment Service provided by Commonwealth Bank of Australia. Please have your Credit Card details ready. Thank you.<br /><br /><strong><small>IMPORTANT: After confirming this page you will be redirected to a CommWeb secure page, please accept any prompts and do not close your browser. If you have any problems with your card please contact your Credit Card Provider.</small></strong>');

// Credit Card Form Fields
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiration Date:');
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_CARD_SECURITY_CODE', 'Credit Card Security Code (CSC):');
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_CARD_SECURITY_CODE_LOCATION', 'Your CSC is usually a 3 digit value printed on the signature panel on the back of your card, following the credit card account number.');

// Validation
define('MODULE_PAYMENT_MIGS_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least '.CC_OWNER_MIN_LENGTH.' characters.\n');
define('MODULE_PAYMENT_MIGS_TEXT_JS_CC_NUMBER', '* The credit card number must be at least '.CC_NUMBER_MIN_LENGTH.' characters.\n');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_MESSAGE', 'There has been an error processing your credit card. Please try again.');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR', 'Credit Card Error!');
define('MODULE_PAYMENT_MIGS_TEXT_CREDIT_CARD_CVV', 'Check CVV');

// Error Handling
define('MODULE_PAYMENT_MIGS_TEXT_ERROR', 'Credit Card Error');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION', 'A credit card error has occured. Please verify your submitted details and resubmit. If this problem persists and please contact us.');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO', 'There was an error when processing a transaction response from the MIGS Payment Gateway');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_EMAIL_INTRO_DETAILS', 'Details of the Error are as follows');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_PRESENT', 'Secure Hash not present in Response from MIGS Payment Server for a Payment');
define('MODULE_PAYMENT_MIGS_TEXT_ERROR_DESCRIPTION_SECURE_HASH_NOT_VALID', 'Secure Hash was not able to be verified against the Secret Hash, possible tampering with response');
?>