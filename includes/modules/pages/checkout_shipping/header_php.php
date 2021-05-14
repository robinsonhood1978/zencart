<?php
/**
 * Checkout Shipping Page
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Mar 27 Modified in v1.5.7 $
 */

// This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_CHECKOUT_SHIPPING');
  require_once(DIR_WS_CLASSES . 'http_client.php');

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
      $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_SHIPPING));
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// Validate Cart for checkout
  $_SESSION['valid_to_checkout'] = true;
  $_SESSION['cart']->get_products(true);
  if ($_SESSION['valid_to_checkout'] == false) {
    $messageStack->add('header', ERROR_CART_UPDATE, 'error');
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
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

// if no shipping destination address was selected, use the customers own address as default
  if (empty($_SESSION['sendto'])) {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
  } else {
// verify the selected shipping address
    $check_address_query = "SELECT count(*) AS total
                            FROM   " . TABLE_ADDRESS_BOOK . "
                            WHERE  customers_id = :customersID
                            AND    address_book_id = :addressBookID";

    $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
    $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['sendto'], 'integer');
    $check_address = $db->Execute($check_address_query);

    if ($check_address->fields['total'] != '1') {
      $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      unset($_SESSION['shipping']);
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
if (isset($_SESSION['cart']->cartID)) {
  if (!isset($_SESSION['cartID']) || $_SESSION['cart']->cartID != $_SESSION['cartID']) {
    $_SESSION['cartID'] = $_SESSION['cart']->cartID;
  }
} else {
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual') {
    $_SESSION['shipping'] = array();
    $_SESSION['shipping']['id'] = 'free_free';
    $_SESSION['shipping']['title'] = 'free_free';
    $_SESSION['shipping']['cost'] = 0;
    $_SESSION['sendto'] = false;
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  $pass = true;
  $free_shipping = false;
  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    if ( ($pass == true) && ($_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;
    }
  }

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

  if (isset($_SESSION['comments'])) {
    $comments = $_SESSION['comments'];
  }


// process the selected shipping method

  if ( isset($_POST['action']) && ($_POST['action'] == 'process') ) {

    if (isset($_POST['comments'])) {
      $_SESSION['comments'] = zen_output_string_protected($_POST['comments']);
    }

    $comments = isset($_SESSION['comments']) ? $_SESSION['comments'] : '';
    $quote = array();

    if ( (zen_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
        /**
         * check to be sure submitted data hasn't been tampered with
         */
        if ($_POST['shipping'] == 'free_free' && ($order->content_type != 'virtual' && !$pass)) {
          $quote['error'] = 'Invalid input. Please make another selection.';
        }
        list($module, $method) = explode('_', $_POST['shipping']);
        if ( (isset($$module) && is_object($$module)) || ($_POST['shipping'] == 'free_free') ) {
          if ($_POST['shipping'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
            $quote[0]['methods'][0]['icon'] = '';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote[0]['error'])) {
            unset($_SESSION['shipping']);
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $_SESSION['shipping'] = array('id' => $_POST['shipping'],
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                'cost' => $quote[0]['methods'][0]['cost']);

              zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          unset($_SESSION['shipping']);
        }
      }
    } else {
      unset($_SESSION['shipping']);

      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }

// get all available shipping quotes
$quotes = $shipping_modules->quote();

  // check that the currently selected shipping method is still valid (in case a zone restriction has disabled it, etc)
  if (isset($_SESSION['shipping']['id'])) {

    $checklist = array();
    foreach ($quotes as $key=>$val) {
      if ($val['methods'] != '') {
        foreach($val['methods'] as $key2=>$method) {
          $checklist[] = $val['id'] . '_' . $method['id'];
        }
      } else {
        // skip
      }
    }

    $checkval = $_SESSION['shipping']['id'];
    if (!in_array($checkval, $checklist)) {
      $messageStack->add('checkout_shipping', ERROR_PLEASE_RESELECT_SHIPPING_METHOD, 'error');
      unset($_SESSION['shipping']); // Prepare $_SESSION to determine lowest available price/force a default selection mc12345678 2018-04-03
    }
  }

// If no shipping method has been selected, automatically select the cheapest method.
// If the module's status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  if ((!isset($_SESSION['shipping']) || (!isset($_SESSION['shipping']['id']) || $_SESSION['shipping']['id'] == '') && zen_count_shipping_modules() >= 1)) $_SESSION['shipping'] = $shipping_modules->cheapest();

  // Should address-edit button be offered?
  $displayAddressEdit = (MAX_ADDRESS_BOOK_ENTRIES >= 2);

  // if shipping-edit button should be overridden, do so
  $editShippingButtonLink = zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL');
  if (isset($_SESSION['payment']) && isset(${$_SESSION['payment']}) && method_exists(${$_SESSION['payment']}, 'alterShippingEditButton')) {
    $theLink = ${$_SESSION['payment']}->alterShippingEditButton();
    if ($theLink) {
      $editShippingButtonLink = $theLink;
      $displayAddressEdit = true;
    }
  }

  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_CHECKOUT_SHIPPING');
