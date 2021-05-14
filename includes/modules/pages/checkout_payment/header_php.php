<?php
/**
 * checkout_payment header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 30 Modified in v1.5.6b $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_CHECKOUT_PAYMENT');
// if (!isset($_SESSION['jscript_enabled'])) {
//     $messageStack->add_session ('shopping_cart', PAYMENT_JAVASCRIPT_DISABLED, 'error');
//   zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
// }

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// if the customer is not logged on, redirect them to the login page
  if (!zen_is_logged_in()) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  } else {
    // validate customer
    if (zen_get_customer_validate_session($_SESSION['customer_id']) == false) {
      $_SESSION['navigation']->set_snapshot();
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping']) || !$_SESSION['shipping']) {
  zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}
if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] == 'free_free' && $_SESSION['cart']->get_content_type() != 'virtual' && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true' && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER') && $_SESSION['cart']->show_total() < MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
  zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && $_SESSION['cartID']) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}
$products = $_SESSION['cart']->get_products();
// Stock Check
if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {

  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
    $qtyAvailable = zen_get_products_stock($products[$i]['id']);
    // compare against product inventory, and against mixed=YES
    if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
      zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
      break;
    }
  }
}

//Robin
for ($i=0, $n=sizeof($products); $i<$n; $i++) {
    $flagStockCheck = '';
    if (($i/2) == floor($i/2)) {
        $rowClass="rowEven";
    } else {
        $rowClass="rowOdd";
    }
    switch (true) {
        case (SHOW_SHOPPING_CART_DELETE == 1):
            $buttonDelete = true;
            $checkBoxDelete = false;
            break;
        case (SHOW_SHOPPING_CART_DELETE == 2):
            $buttonDelete = false;
            $checkBoxDelete = true;
            break;
        default:
            $buttonDelete = true;
            $checkBoxDelete = true;
            break;
    } // end switch
    $attributeHiddenField = "";
    $attrArray = false;
    $productsName = $products[$i]['name'];
    // Push all attributes information in an array
    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
            $options_order_by= ' ORDER BY LPAD(popt.products_options_sort_order,11,"0")';
        } else {
            $options_order_by= ' ORDER BY popt.products_options_name';
        }
        foreach ($products[$i]['attributes'] as $option => $value) {
            $attributes = "SELECT popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                     FROM " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                     WHERE pa.products_id = :productsID
                     AND pa.options_id = :optionsID
                     AND pa.options_id = popt.products_options_id
                     AND pa.options_values_id = :optionsValuesID
                     AND pa.options_values_id = poval.products_options_values_id
                     AND popt.language_id = :languageID
                     AND poval.language_id = :languageID " . $options_order_by;

            $attributes = $db->bindVars($attributes, ':productsID', $products[$i]['id'], 'integer');
            $attributes = $db->bindVars($attributes, ':optionsID', $option, 'integer');
            $attributes = $db->bindVars($attributes, ':optionsValuesID', $value, 'integer');
            $attributes = $db->bindVars($attributes, ':languageID', $_SESSION['languages_id'], 'integer');
            $attributes_values = $db->Execute($attributes);
            if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
                $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . TEXT_PREFIX . $option . ']',  $products[$i]['attributes_values'][$option]);
                $attr_value = htmlspecialchars($products[$i]['attributes_values'][$option], ENT_COMPAT, CHARSET, TRUE);
            } else {
                $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
                $attr_value = $attributes_values->fields['products_options_values_name'];
            }

            $attrArray[$option]['products_options_name'] = $attributes_values->fields['products_options_name'];
            $attrArray[$option]['options_values_id'] = $value;
            $attrArray[$option]['products_options_values_name'] = $attr_value;
            $attrArray[$option]['options_values_price'] = $attributes_values->fields['options_values_price'];
            $attrArray[$option]['price_prefix'] = $attributes_values->fields['price_prefix'];
        }
    } //end foreach [attributes]

    // Stock Check
    if (STOCK_CHECK == 'true') {
        $qtyAvailable = zen_get_products_stock($products[$i]['id']);
        // compare against product inventory, and against mixed=YES
        if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
            $flagStockCheck = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
            $flagAnyOutOfStock = true;
        }
    }

    $linkProductsImage = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
    $linkProductsName = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
    $productsImage = (IMAGE_SHOPPING_CART_STATUS == 1 ? zen_image(DIR_WS_IMAGES ."small/". $products[$i]['image'], $products[$i]['name'], IMAGE_SHOPPING_CART_WIDTH, IMAGE_SHOPPING_CART_HEIGHT) : '');
    $show_products_quantity_max = zen_get_products_quantity_order_max($products[$i]['id']);
    $showFixedQuantity = (($show_products_quantity_max == 1 or zen_get_products_qty_box_status($products[$i]['id']) == 0) ? true : false);
    $showFixedQuantityAmount = $products[$i]['quantity'] . zen_draw_hidden_field('cart_quantity[]', $products[$i]['quantity']);
    $showMinUnits = zen_get_products_quantity_min_units_display($products[$i]['id']);
    $quantityField = zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4" class="cart_input_'.$products[$i]['id'].'" aria-label="' . ARIA_EDIT_QTY_IN_CART . '"');
    $ppe = $products[$i]['final_price'];
    $ppe = zen_round(zen_add_tax($ppe, zen_get_tax_rate($products[$i]['tax_class_id'])), $currencies->get_decimal_places($_SESSION['currency']));
    $ppt = $ppe * $products[$i]['quantity'];
    $productsPriceEach = $currencies->format($ppe) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
    $productsPriceTotal = $currencies->format($ppt) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
    $buttonUpdate = ((SHOW_SHOPPING_CART_UPDATE == 1 or SHOW_SHOPPING_CART_UPDATE == 3) ? zen_image_submit(ICON_IMAGE_UPDATE, ICON_UPDATE_ALT) : '') . zen_draw_hidden_field('products_id[]', $products[$i]['id']);
    $productArray[$i] = array('attributeHiddenField'=>$attributeHiddenField,
        'flagStockCheck'=>$flagStockCheck,
        'flagShowFixedQuantity'=>$showFixedQuantity,
        'linkProductsImage'=>$linkProductsImage,
        'linkProductsName'=>$linkProductsName,
        'productsImage'=>$productsImage,
        'productsName'=>$productsName,
        'showFixedQuantity'=>$showFixedQuantity,
        'showFixedQuantityAmount'=>$showFixedQuantityAmount,
        'showMinUnits'=>$showMinUnits,
        'quantityField'=>$quantityField,
        'buttonUpdate'=>$buttonUpdate,
        'productsPrice'=>$productsPriceTotal,
        'productsPriceEach'=>$productsPriceEach,
        'rowClass'=>$rowClass,
        'buttonDelete'=>$buttonDelete,
        'checkBoxDelete'=>$checkBoxDelete,
        'id'=>$products[$i]['id'],
        'attributes'=>$attrArray,
    );
} // end FOR loop

// get coupon code
if (!empty($_SESSION['cc_id'])) {
  $discount_coupon_query = "SELECT coupon_code
                            FROM " . TABLE_COUPONS . "
                            WHERE coupon_id = :couponID";

  $discount_coupon_query = $db->bindVars($discount_coupon_query, ':couponID', $_SESSION['cc_id'], 'integer');
  $discount_coupon = $db->Execute($discount_coupon_query);
}

// if no billing destination address was selected, use the customers own address as default
if (empty($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = "SELECT count(*) AS total FROM " . TABLE_ADDRESS_BOOK . "
                          WHERE customers_id = :customersID
                          AND address_book_id = :addressBookID";

  $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['billto'], 'integer');
  $check_address = $db->Execute($check_address_query);

  if ($check_address->fields['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    $_SESSION['payment'] = '';
  }
}

require(DIR_WS_CLASSES . 'order.php');
$order = new order;
// Load the selected shipping module(needed to calculate tax correctly)
require(DIR_WS_CLASSES . 'shipping.php');
$shipping_modules = new shipping($_SESSION['shipping']);
require(DIR_WS_CLASSES . 'order_total.php');
$order_total_modules = new order_total;
$order_total_modules->collect_posts();
$order_total_modules->pre_confirmation_check();

//  $_SESSION['comments'] = '';
$comments = !empty($_SESSION['comments']) ? $_SESSION['comments'] : '';

$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
require(DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment;
$flagOnSubmit = sizeof($payment_modules->selection());


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
  $messageStack->add('checkout_payment', $error['error'], 'error');
}
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_CHECKOUT_PAYMENT');
?>