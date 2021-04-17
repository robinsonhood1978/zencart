<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: DIRBANKAUS.php 1970 2009-11-24 06:57:21Z CRYSTAL JONES $
//
$id=$_SESSION['customer_id'];
$ln=$_SESSION['customer_last_name'];


  define('EMAIL_TEXT_NO_DELIVERY',
  '<p>No Delivery, see below:');
  define('MODULE_PAYMENT_DIRBANKAUS_TEXT_EMAIL_FOOTER', 
  "Please use the following details to transfer your total order value:\n\n" .
  "\nAccount No.:  " . MODULE_PAYMENT_DIRBANKAUS_ACCNUM .
  "\nBSB Number:   " . MODULE_PAYMENT_DIRBANKAUS_BSB . 
  "\nAccount Name: " . MODULE_PAYMENT_DIRBANKAUS_ACCNAM . 
  "\nBank Name:    " . MODULE_PAYMENT_DIRBANKAUS_BANKNAM .
  "\nSwift Code:   " . MODULE_PAYMENT_DIRBANKAUS_SWIFT . 
  "\nReference:    "  . $ln ."-" . $id . "-%s" .
  "\n\nSend Cheques/Money Orders To:    " . MODULE_PAYMENT_DIRBANKAUS_ADDRESS . 
  "\nCheques/Money Orders Payable To:   " . MODULE_PAYMENT_DIRBANKAUS_PAYABLE .
  "\n\nThanks for your order which will ship immediately once we receive payment in the above account.\n");

  define('MODULE_PAYMENT_DIRBANKAUS_HTML_EMAIL_FOOTER', 
  '<BR>Please use the following details to transfer your total order value:<br><pre>' .
  "\nAccount No.:  " . MODULE_PAYMENT_DIRBANKAUS_ACCNUM .
  "\nBSB Number:   " . MODULE_PAYMENT_DIRBANKAUS_BSB . 
  "\nAccount Name: " . MODULE_PAYMENT_DIRBANKAUS_ACCNAM . 
  "\nBank Name:    " . MODULE_PAYMENT_DIRBANKAUS_BANKNAM .
  "\nSwift Code:   " . MODULE_PAYMENT_DIRBANKAUS_SWIFT . 
  "\nReference:    "  . $ln ."-" . $id . "-%s" .
  "\n\nSend Cheques/Money Orders To:    " . MODULE_PAYMENT_DIRBANKAUS_ADDRESS . 
  "\nCheques/Money Orders Payable To:   " . MODULE_PAYMENT_DIRBANKAUS_PAYABLE .
  '</pre><p>Thanks for your order which will ship immediately once we receive payment in the above account.');

  define('MODULE_PAYMENT_DIRBANKAUS_TEXT_TITLE', 'Australia Bank Deposit/Transfer Payment');
   define('MODULE_PAYMENT_DIRBANKAUS_TEXT_DESCRIPTION', 
  '<BR>Banking and Address details will be sent to your email once the order is confirmed.<br><pre>' . 
  '</pre><p>Thanks for your order which will ship immediately once we receive payment.');
?>