<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 11 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$parameters = [
  'products_name' => '',
  'products_description' => '',
  'products_url' => '',
  'products_id' => '',
  'products_quantity' => '0',
  'products_model' => '',
  'products_image' => '',
    'products_image2' => '',
    'products_image3' => '',
  'products_price' => '0.0000',
  'products_virtual' => DEFAULT_PRODUCT_PRODUCTS_VIRTUAL,
  'products_weight' => '0',
  'products_date_added' => '',
  'products_last_modified' => '',
  'products_date_available' => '',
  'products_status' => '1',
  'products_tax_class_id' => DEFAULT_PRODUCT_TAX_CLASS_ID,
  'manufacturers_id' => '',
    'type_id' => '',//Robin
    'condition_id' => '',
    'color_id' => '',
    'products_show_part' => '',
    'products_show_model' => '',
    'products_show_warranty' => '',
    'products_show_disclaimer' => '',
    'products_dimension' => '',
    'products_net_weight' => '0',
    'products_handle' => '0',
  'products_quantity_order_min' => '1',
  'products_quantity_order_units' => '1',
  'products_priced_by_attribute' => '0',
  'product_is_free' => '0',
  'product_is_call' => '0',
  'products_quantity_mixed' => '1',
  'product_is_always_free_shipping' => DEFAULT_PRODUCT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING,
  'products_qty_box_status' => PRODUCTS_QTY_BOX_STATUS,
  'products_quantity_order_max' => '0',
  'products_sort_order' => '0',
  'products_discount_type' => '0',
  'products_discount_type_from' => '0',
  'products_price_sorter' => '0',
  'master_categories_id' => '',
];

$pInfo = new objectInfo($parameters);
//Robin
if (isset($_GET['pID']) && empty($_POST)) {

  $product = $db->Execute("SELECT pe.type_id, pe.condition_id, pe.color_id, pe.products_dimension,pe.products_show_part,pe.products_show_model,pe.products_show_warranty,pe.products_show_disclaimer, pe.products_net_weight, pe.products_handle, pd.products_name, pd.products_description, pd.products_url,
                                  p.*, 
                                  date_format(p.products_date_available, '" .  zen_datepicker_format_forsql() . "') as products_date_available
                           FROM " . TABLE_PRODUCTS . " p left join
                                " . TABLE_PRODUCTS_EXT . " pe on p.products_id = pe.products_id left join  
                                " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id
                           AND pd.language_id = " . (int)$_SESSION['languages_id']."
                           WHERE p.products_id = " . (int)$_GET['pID'] );

  $pInfo->updateObjectInfo($product->fields);
} elseif (zen_not_null($_POST)) {
  $pInfo->updateObjectInfo($_POST);
  $products_name = isset($_POST['products_name']) ? $_POST['products_name'] : '';
  $products_description = isset($_POST['products_description']) ? $_POST['products_description'] : '';
  $products_url = isset($_POST['products_url']) ? $_POST['products_url'] : '';
}

$category_lookup = $db->Execute("SELECT *
                                 FROM " . TABLE_CATEGORIES . " c,
                                      " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                 WHERE c.categories_id = " . (int)$current_category_id . "
                                 AND c.categories_id = cd.categories_id
                                 AND cd.language_id = " . (int)$_SESSION['languages_id']);
if (!$category_lookup->EOF) {
  $cInfo = new objectInfo($category_lookup->fields);
} else {
  $cInfo = new objectInfo([]);
}

$manufacturers_array = [
    [
    'id' => '',
    'text' => TEXT_NONE
    ]
];
$manufacturers = $db->Execute("SELECT manufacturers_id, manufacturers_name
                               FROM " . TABLE_MANUFACTURERS . "
                               ORDER BY manufacturers_name");
foreach ($manufacturers as $manufacturer) {
  $manufacturers_array[] = [
    'id' => $manufacturer['manufacturers_id'],
    'text' => $manufacturer['manufacturers_name']
  ];
}
//Robin
$types_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$types = $db->Execute("SELECT type_id, type_name
                               FROM " . TABLE_TYPE . "
                               ORDER BY type_name");
foreach ($types as $type) {
    $types_array[] = [
        'id' => $type['type_id'],
        'text' => $type['type_name']
    ];
}

$conditions_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$conditions = $db->Execute("SELECT condition_id, condition_description
                               FROM " . TABLE_PRODUCTS_CONDITION . "
                               ORDER BY condition_description");
foreach ($conditions as $condition) {
    $conditions_array[] = [
        'id' => $condition['condition_id'],
        'text' => $condition['condition_description']
    ];
}

$colors_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$colors = $db->Execute("SELECT pcolors_id, pcolors_name
                               FROM " . TABLE_PRODUCTS_COLOR . "
                               ORDER BY pcolors_name");
foreach ($colors as $color) {
    $colors_array[] = [
        'id' => $color['pcolors_id'],
        'text' => $color['pcolors_name']
    ];
}

$part_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$parts = $db->Execute("SELECT msg_id, description
                               FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE . "
                               WHERE message_group='p' ORDER BY description");
foreach ($parts as $part) {
    $part_array[] = [
        'id' => $part['msg_id'],
        'text' => $part['description']
    ];
}

$model_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$models = $db->Execute("SELECT msg_id, description
                               FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE . "
                               WHERE message_group='m' ORDER BY description");
foreach ($models as $model) {
    $model_array[] = [
        'id' => $model['msg_id'],
        'text' => $model['description']
    ];
}

$warranty_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$warrantys = $db->Execute("SELECT msg_id, description
                               FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE . "
                               WHERE message_group='w' ORDER BY description");
foreach ($warrantys as $warranty) {
    $warranty_array[] = [
        'id' => $warranty['msg_id'],
        'text' => $warranty['description']
    ];
}

$disclaimer_array = [
    [
        'id' => '',
        'text' => TEXT_NONE
    ]
];
$disclaimers = $db->Execute("SELECT msg_id, description
                               FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE . "
                               WHERE message_group='d' ORDER BY description");
foreach ($disclaimers as $disclaimer) {
    $disclaimer_array[] = [
        'id' => $disclaimer['msg_id'],
        'text' => $disclaimer['description']
    ];
}
// set to out of stock if categories_status is off and new product or existing products_status is off
if (zen_get_categories_status($current_category_id) == 0 && $pInfo->products_status != 1) {
  $pInfo->products_status = 0;
}
?>
<div class="container-fluid">
    <?php
    echo zen_draw_form('new_product', FILENAME_PRODUCT, 'cPath=' . $current_category_id . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ( (isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"');
    if (isset($product_type)) {
      echo zen_draw_hidden_field('product_type', $product_type);
    }
    ?>
  <h3 class="col-sm-11"><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></h3>
  <div class="col-sm-1"><?php echo zen_info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></div>
    <div class="floatButton text-right">
      <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button>&nbsp;&nbsp;<a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $current_category_id . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ( (isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
    </div>
  <div class="form-group">
      <?php
// show when product is linked
      if (isset($_GET['pID']) && zen_get_product_is_linked($_GET['pID']) == 'true' && (int)$_GET['pID'] > 0) {
        ?>
        <?php echo zen_draw_label(TEXT_MASTER_CATEGORIES_ID, 'master_category', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
        <div class="input-group">
          <span class="input-group-addon">
              <?php
              echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
              ?>
          </span>
          <?php
          echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($_GET['pID']), $pInfo->master_categories_id, 'class="form-control" id="master_category"');
          ?>
        </div>
      </div>
    <?php } else { ?>
      <div class="col-sm-3 text-right">
        <strong>
            <?php echo TEXT_MASTER_CATEGORIES_ID; ?>
        </strong>
      </div>
      <div class="col-sm-9 col-md-6"><?php echo TEXT_INFO_ID . (!empty($_GET['pID']) ? $pInfo->master_categories_id . ' ' . zen_get_category_name($pInfo->master_categories_id, $_SESSION['languages_id']) : $current_category_id . ' ' . zen_get_category_name($current_category_id, $_SESSION['languages_id'])); ?></div>
    <?php } ?>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9 col-md-6">
        <?php echo TEXT_INFO_MASTER_CATEGORIES_ID; ?>
    </div>
  </div>
  <?php
// hidden fields not changeable on products page
  echo zen_draw_hidden_field('master_categories_id', $pInfo->master_categories_id);
  echo zen_draw_hidden_field('products_discount_type', $pInfo->products_discount_type);
  echo zen_draw_hidden_field('products_discount_type_from', $pInfo->products_discount_type_from);
  echo zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter);
  ?>
  <div class="col-sm-12 text-center"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . (isset($out_status) && $out_status == true ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_STATUS; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_status', '1', ($pInfo->products_status == 1)) . TEXT_PRODUCT_AVAILABLE; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_status', '0', ($pInfo->products_status == 0)) . TEXT_PRODUCT_NOT_AVAILABLE; ?></label>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_DATE_AVAILABLE, 'products_date_available', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <div class="date input-group" id="datepicker">
        <span class="input-group-addon datepicker_icon">
          <i class="fa fa-calendar fa-lg">&nbsp;</i>
        </span>
        <?php echo zen_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="form-control" id="products_date_available" autocomplete="off"'); ?>
      </div>
        <span class="help-block errorText">(<?php echo zen_datepicker_format_full();?>)</span>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_MANUFACTURER, 'manufacturers_id', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id, 'class="form-control" id="manufacturers_id"'); ?>
    </div>
  </div>

    <div class="form-group">
        <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_NAME; ?></p>
        <div class="col-sm-9 col-md-6">
            <?php
            for ($i = 0, $n = count($languages); $i < $n; $i++) {
                ?>
                <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
                    <?php echo zen_draw_input_field('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : zen_get_products_name($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_name') . ' class="form-control"'); ?>
                </div>
                <br>
                <?php
            }
            ?>
        </div>
    </div>
	 <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY, 'products_quantity', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity', $pInfo->products_quantity, 'class="form-control" id="products_quantity"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_MODEL, 'products_model', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_model', htmlspecialchars(stripslashes($pInfo->products_model), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS, 'products_model') . ' class="form-control" id="products_model"'); ?>
    </div>
  </div>
    <!--Robin-->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_TYPE, 'type_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('type_id', $types_array, $pInfo->type_id, 'class="form-control" id="type_id"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_CONDITION, 'condition_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('condition_id', $conditions_array, $pInfo->condition_id, 'class="form-control" id="condition_id"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_COLOR, 'color_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('color_id', $colors_array, $pInfo->color_id, 'class="form-control" id="color_id"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_DIMENSION, 'products_dimension', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('products_dimension', htmlspecialchars(stripslashes($pInfo->products_dimension), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS_EXT, 'products_dimension') . ' class="form-control" id="products_dimension"'); ?>
        </div>
    </div>

   

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PART_MSG, 'products_show_part', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('products_show_part', $part_array, $pInfo->products_show_part, 'class="form-control" id="products_show_part"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_MODEL_MSG, 'products_show_model', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('products_show_model', $model_array, $pInfo->products_show_model, 'class="form-control" id="products_show_model"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_WARRANTY_MSG, 'products_show_warranty', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('products_show_warranty', $warranty_array, $pInfo->products_show_warranty, 'class="form-control" id="products_show_warranty"'); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_DISCLAIMER_MSG, 'products_show_disclaimer', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('products_show_disclaimer', $disclaimer_array, $pInfo->products_show_disclaimer, 'class="form-control" id="products_show_disclaimer"'); ?>
        </div>
    </div>


<?php
    // -----
    // Give an observer the chance to supply some additional product-related inputs.  Each
    // entry in the $extra_product_inputs returned contains:
    //
    // array(
    //    'label' => array(
    //        'text' => 'The label text',   (required)
    //        'field_name' => 'The name of the field associated with the label', (required)
    //        'addl_class' => {Any additional class to be applied to the label} (optional)
    //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
    //    ),
    //    'input' => 'The HTML to be inserted' (required)
    // )
    //
    // Note: The product's type can be found in the 'product_type' element of the passed $pInfo object.
    //
    $extra_product_inputs = [];
    $zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_COLLECT_INFO_EXTRA_INPUTS', $pInfo, $extra_product_inputs);
    if (!empty($extra_product_inputs)) {
        foreach ($extra_product_inputs as $extra_input) {
            $addl_class = (isset($extra_input['label']['addl_class'])) ? (' ' . $extra_input['label']['addl_class']) : '';
            $parms = (isset($extra_input['label']['parms'])) ? (' ' . $extra_input['label']['parms']) : '';
?>
            <div class="form-group">
                <?php echo zen_draw_label($extra_input['label']['text'], $extra_input['label']['field_name'], 'class="col-sm-3 control-label' . $addl_class . '"' . $parms); ?>
                <div class="col-sm-9 col-md-6"><?php echo $extra_input['input']; ?></div>
            </div>
<?php
        }
    }
?>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCT_IS_FREE; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_free', '1', ($pInfo->product_is_free == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_free', '0', ($pInfo->product_is_free == 0)) . TEXT_NO; ?></label>
      <?php echo ($pInfo->product_is_free == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCT_IS_CALL; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_call', '1', ($pInfo->product_is_call == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_call', '0', ($pInfo->product_is_call == 0)) . TEXT_NO; ?></label>
      <?php echo ($pInfo->product_is_call == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_priced_by_attribute', '1', ($pInfo->products_priced_by_attribute == 1)) . TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_priced_by_attribute', '0', ($pInfo->products_priced_by_attribute == 0)) . TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE; ?></label>
      <?php echo ($pInfo->products_priced_by_attribute == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="well" style="color: #31708f;background-color: #d9edf7;border-color: #bce8f1;padding: 10px 10px 0 0;">
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_TAX_CLASS, 'products_tax_class_id', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()" class="form-control" id="products_tax_class_id"'); ?>
      </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PRICE_NET, 'products_price', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('products_price', $pInfo->products_price, 'onkeyup="updateGross()" class="form-control" id="products_price"'); ?>
      </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PRICE_GROSS, 'products_price_gross', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('products_price_gross', $pInfo->products_price, 'onkeyup="updateNet()" class="form-control" id="products_price_gross"'); ?>
      </div>
    </div>
  </div>
  <script>
    updateGross();
  </script>
  <div class="form-group">
    <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_VIRTUAL; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_virtual', '1', ($pInfo->products_virtual == 1)) . TEXT_PRODUCT_IS_VIRTUAL; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_virtual', '0', ($pInfo->products_virtual == 0)) . TEXT_PRODUCT_NOT_VIRTUAL; ?></label>
      <?php echo ($pInfo->products_virtual == 1 ? '<span class="help-block errorText">' . TEXT_VIRTUAL_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '1', ($pInfo->product_is_always_free_shipping == 1)) . TEXT_PRODUCT_IS_ALWAYS_FREE_SHIPPING; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '0', ($pInfo->product_is_always_free_shipping == 0)) . TEXT_PRODUCT_NOT_ALWAYS_FREE_SHIPPING; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '2', ($pInfo->product_is_always_free_shipping == 2)) . TEXT_PRODUCT_SPECIAL_ALWAYS_FREE_SHIPPING; ?></label>
      <?php echo ($pInfo->product_is_always_free_shipping == 1 ? '<span class="help-block errorText">' . TEXT_FREE_SHIPPING_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_QTY_BOX_STATUS; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_qty_box_status', '1', ($pInfo->products_qty_box_status == 1 ? true : false)) . TEXT_PRODUCTS_QTY_BOX_STATUS_ON; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_qty_box_status', '0', ($pInfo->products_qty_box_status == 0 ? true : false)) . TEXT_PRODUCTS_QTY_BOX_STATUS_OFF; ?></label>
      <?php echo ($pInfo->products_qty_box_status == 0 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MIN_RETAIL, 'products_quantity_order_min', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min == 0 ? 1 : $pInfo->products_quantity_order_min), 'class="form-control" id="products_quantity_order_min"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MAX_RETAIL, 'products_quantity_order_max', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <?php echo zen_draw_input_field('products_quantity_order_max', $pInfo->products_quantity_order_max, 'class="form-control" id="products_quantity_order_max"'); ?>&nbsp;&nbsp;<?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL, 'products_quantity_order_units', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units == 0 ? 1 : $pInfo->products_quantity_order_units), 'class="form-control" id="products_quantity_order_units"'); ?>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_MIXED; ?></p>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_quantity_mixed', '1', ($pInfo->products_quantity_mixed == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_quantity_mixed', '0', ($pInfo->products_quantity_mixed == 0)) . TEXT_NO; ?></label>
    </div>
  </div>
  <div class="form-group">
      <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_DESCRIPTION; ?></p>
    <div class="col-sm-9 col-md-6">
        <?php
        for ($i = 0, $n = count($languages); $i < $n; $i++) {
          ?>
        <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
          <?php echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '100', '30', htmlspecialchars((isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
        </div>
        <br>
        <?php
      }
      ?>
    </div>
  </div>
 
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_EXIST_PRODUCTS_PART_COM, 'products_part_no', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">

            <div id="part_table" class="table">
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_EXIST_PRODUCTS_MODEL_COM, 'products_model', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">

            <div id="model_table" class="table">
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PART_COM, 'products_part_no', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <div id="NewPart">
                How Many Brands?
                <select name="p_brand_num" onchange='addbrand("tb")'>
                    <option selected>--</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                </select>
                <table id="tb"></table>
                <table id="tb2"></table>
                <table id="tb22"></table>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_MODEL_COM, 'products_model', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <div id="NewModel">
                How Many Brands?             <select name="m_brand_num" onchange='addbrand2("tb3")'>
                    <option selected>--</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                </select>
                <table id="tb3"></table>
                <table id="tb4"></table>
                <table id="tb44"></table>
            </div>
        </div>
    </div>

    <hr>
    <h2><?php echo TEXT_PRODUCTS_IMAGE; ?></h2>

    <div class="form-group">

        <?php echo zen_draw_label(TEXT_EDIT_PRODUCTS_IMAGE.' #1', 'products_image', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-4 col-lg-4">
            <?php echo zen_draw_file_field('products_image', '', 'class="form-control" id="products_image"'); ?>
            <?php echo zen_draw_hidden_field('products_previous_image', $pInfo->products_image); ?>
        </div>
        <div id="img1">
        <?php
        if (!empty($pInfo->products_image)) { ?>

            <div id="img1" class="col-sm-9 col-md-3 col-lg-3">
                <?php echo zen_info_image('small/'.$pInfo->products_image, $pInfo->categories_name); ?>
                <br>
                <?php echo $pInfo->products_image; ?>
            </div>
            <div class="col-sm-9 col-md-1 col-lg-1">

                <div onclick="DelPicture(1);"><img src="images/icon_delete.gif"></div>
            </div>

        <?php }?>
        </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label('#2', 'products_image', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-4 col-lg-4">
            <?php echo zen_draw_file_field('products_image2', '', 'class="form-control" id="products_image2"'); ?>
            <?php echo zen_draw_hidden_field('products_previous_image2', $pInfo->products_image2); ?>

        </div>
        <div id="img2">
        <?php
        if (!empty($pInfo->products_image2)) { ?>

            <div id="img2" class="col-sm-9 col-md-3 col-lg-3">
                <?php echo zen_info_image('small/'.$pInfo->products_image2, $pInfo->categories_name); ?>
                &nbsp;&nbsp;&nbsp;
                <br>
                <?php echo $pInfo->products_image2; ?>

            </div>
            <div class="col-sm-9 col-md-1 col-lg-1">
                <div onclick="DelPicture(2);"><img src="images/icon_delete.gif"></div>
            </div>

        <?php }?>
        </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label('#3', 'products_image', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-4 col-lg-4">
            <?php echo zen_draw_file_field('products_image3', '', 'class="form-control" id="products_image3"'); ?>
            <?php echo zen_draw_hidden_field('products_previous_image3', $pInfo->products_image3); ?>
        </div>
        <div id="img3">
        <?php
        if (!empty($pInfo->products_image3)) { ?>

            <div id="img3" class="col-sm-9 col-md-3 col-lg-3">
                <?php echo zen_info_image('small/'.$pInfo->products_image3, $pInfo->categories_name); ?>

                <br>
                <?php echo $pInfo->products_image3; ?>
            </div>
        <div class="col-sm-9 col-md-1 col-lg-1">
            <div onclick="DelPicture(3);"><img src="images/icon_delete.gif"></div>
        </div>

        <?php }?>
        </div>
    </div>
    <?php
    $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
    $default_directory = substr($pInfo->products_image, 0, strpos($pInfo->products_image, '/') + 1);
    ?>

    <div class="form-group">
        <p class="col-sm-3 control-label"><?php echo TEXT_IMAGES_OVERWRITE; ?></p>
        <div class="col-sm-9 col-md-9 col-lg-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('overwrite', '0', false) . TABLE_HEADING_NO; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('overwrite', '1', true) . TABLE_HEADING_YES; ?></label>
        </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_IMAGE_MANUAL, 'products_image_manual', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-9 col-lg-6">
            <?php echo zen_draw_input_field('products_image_manual', '', 'class="form-control" id="products_image_manual"'); ?>
        </div>
    </div>
    <hr>
  <div class="form-group">
    <p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_URL; ?><span class="help-block"><?php echo TEXT_PRODUCTS_URL_WITHOUT_HTTP; ?></span></p>
    <div class="col-sm-9 col-md-6">
        <?php
        for ($i = 0, $n = count($languages); $i < $n; $i++) {
          ?>
        <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
          <?php echo zen_draw_input_field('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($products_url[$languages[$i]['id']]) ? $products_url[$languages[$i]['id']] : zen_get_products_url($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_url') . ' class="form-control"'); ?>
        </div>
        <br>
        <?php
      }
      ?>
    </div>
  </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_NET_WEIGHT, 'products_net_weight', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('products_net_weight', $pInfo->products_net_weight, 'class="form-control" id="products_net_weight"'); ?>
        </div>
    </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_WEIGHT, 'products_weight', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_weight', $pInfo->products_weight, 'class="form-control" id="products_weight"'); ?>
    </div>
  </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_HANDLE_FEE, 'products_handle_fee', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('products_handle_fee', $pInfo->products_handle, 'class="form-control" id="products_handle"'); ?>
        </div>
    </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_SORT_ORDER, 'products_sort_order', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <?php echo zen_draw_input_field('products_sort_order', $pInfo->products_sort_order, 'class="form-control" id="products_sort_order"'); ?>
    </div>
    <?php
    echo zen_draw_hidden_field('products_date_added', (zen_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));
    echo ((isset($_GET['search']) && !empty($_GET['search'])) ? zen_draw_hidden_field('search', $_GET['search']) : '');
    echo ((isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? zen_draw_hidden_field('search', $_POST['search']) : '');
    ?>
  </div>
  <?php echo '</form>'; ?>
</div>
