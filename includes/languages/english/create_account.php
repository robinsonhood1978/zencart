<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 Aug 22 Modified in v1.5.7 $
 */

define('NAVBAR_TITLE', 'Create an Account');

define('HEADING_TITLE', 'My Account Information');

define('TEXT_ORIGIN_LOGIN', '<strong class="note">NOTE:</strong> If you already have an account with us, please login at the <a href="%s">login page</a>.');

define('ERROR_CREATE_ACCOUNT_SPAM_DETECTED', 'Thank you, your account request has been submitted for review.');


// greeting salutation
define('EMAIL_SUBJECT', 'Welcome to ' . STORE_NAME);
define('EMAIL_GREET_MR', 'Dear Mr. %s,' . "\n\n");
define('EMAIL_GREET_MS', 'Dear Ms. %s,' . "\n\n");
define('EMAIL_GREET_NONE', 'Dear %s,' . "\n\n");

// First line of the greeting
define('EMAIL_WELCOME', 'Welcome to <strong>' . STORE_NAME . '</strong> online shopping.');
define('EMAIL_SEPARATOR', '--------------------');
define('EMAIL_COUPON_INCENTIVE_HEADER', 'Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!' . "\n\n");
// your Discount Coupon Description will be inserted before this next define
define('EMAIL_COUPON_REDEEM', 'To use the Discount Coupon, enter the ' . TEXT_GV_REDEEM . ' code during checkout:  <strong>%s</strong>' . "\n\n");
define('TEXT_COUPON_HELP_DATE', '<p>The coupon is valid between %s and %s</p>');

define('EMAIL_GV_INCENTIVE_HEADER', 'Just for stopping by today, we have sent you a ' . TEXT_GV_NAME . ' for %s!' . "\n");
define('EMAIL_GV_REDEEM', 'The ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' is: %s ' . "\n\n" . 'You can enter the ' . TEXT_GV_REDEEM . ' during Checkout, after making your selections in the store. ');
define('EMAIL_GV_LINK', ' Or, you may redeem it now by following this link: ' . "\n");
// GV link will automatically be included before this line

define('EMAIL_GV_LINK_OTHER','Once you have added the ' . TEXT_GV_NAME . ' to your account, you may use the ' . TEXT_GV_NAME . ' for yourself, or send it to a friend!' . "\n\n");

define('EMAIL_TEXT', 'We established business in 2001 and are based in Sydney.' . "\n\n" .
'We specialize in laptop parts (laptop keyboards, power adapters, batteries, and more ...) for all brands and models.' . "\n\n" .
'You now have an account with '. STORE_NAME . ' by providing the following:' . "\n" . '<strong>- Order History,</strong>' . "\n" .
 '- View order details,' . "\n"
    . '<strong>- Permanent Cart,</strong> where the product you have added to your Cart will remain until remove or purchased,' . "\n" .
    '<strong>- Address Book,</strong> where additional addresses may be added for sending orders to different locations,' . "\n" .
    '- Product Review to share your opinion on our products with other customers.' . "\n\n");
define('EMAIL_CONTACT', 'If help with our online services is required , please email to us: <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'
    . STORE_OWNER_EMAIL_ADDRESS ."</a>\n\n"
    . 'If you are an IT reseller, please email us after registering online. We can offer a reseller account with reseller prices and other special offers.' . "\n\n" .
    'We also provide laptop repair services, including expert component logic board repairs, screen replacement, system recovery, memory/hard drive upgrade and rectifying other hardware & software faults.' . "\n\n" );
define('EMAIL_GV_CLOSURE', "\n" . 'Sincerely,' . "\n\n" . 'James' . "\nFortech Services Pty Limited\n(T/as Fortech Computer & Network)\n\n". '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">' . HTTP_SERVER . DIR_WS_CATALOG ."</a>\n\n");

// email disclaimer - this disclaimer is separate from all other email disclaimers
define('EMAIL_DISCLAIMER_NEW_CUSTOMER', 'This email address was given to us by you or by one of our customers. If you did not signup for an account, or feel that you have received this email in error, please send an email to %s ');
