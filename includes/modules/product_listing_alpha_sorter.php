<?php
/**
 * product_listing_alpha_sorter module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// build alpha sorter dropdown
  if (PRODUCT_LIST_ALPHA_SORTER == 'true') {
    $letters_list = array();
    if (empty($_GET['alpha_filter_id'])) {
      $letters_list[] = array('id' => '0', 'text' => TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES);
    } else {
      $letters_list[] = array('id' => '0', 'text' => TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES_RESET);
    }
    for ($i=65; $i<91; $i++) {
      $letters_list[] = array('id' => sprintf('%02d', $i), 'text' => chr($i) );
    }
    for ($i=48; $i<58; $i++) {
      $letters_list[] = array('id' => sprintf('%02d', $i), 'text' => chr($i) );
    }

    $zco_notifier->notify('NOTIFY_PRODUCT_LISTING_ALPHA_SORTER_SELECTLIST', isset($prefix) ? $prefix : '', $letters_list);

    if (TEXT_PRODUCTS_LISTING_ALPHA_SORTER != '') {
        echo '<label class="inputLabel" for="select-alpha_filter_id">' . TEXT_PRODUCTS_LISTING_ALPHA_SORTER . '</label>';
    } else {
        echo '<label class="inputLabel sr-only" for="select-alpha_filter_id">' . TEXT_PRODUCTS_LISTING_ALPHA_SORTER_NAMES . '</label>';
    }
   // echo zen_draw_pull_down_menu('alpha_filter_id', $letters_list, (isset($_GET['alpha_filter_id']) ? $_GET['alpha_filter_id'] : ''), 'onchange="this.form.submit()"');
      $model_brands_array = [];
      $m_brands = $db->Execute("select DISTINCT brand_name from fortech_brand_series order by brand_name");
      foreach ($m_brands as $mbrand) {
          $model_brands_array[] = [
              'name' => $mbrand['brand_name'],
              'value' => $mbrand['brand_name']
          ];
      }

      $model_parts_array = [];
      $m_parts = $db->Execute("select DISTINCT brand_name from fortech_brand_part order by brand_name");
      foreach ($m_parts as $part) {
          $model_parts_array[] = [
              'name' => $part['brand_name'],
              'value' => $part['brand_name']
          ];
      }


  }
  ?>
<div class="limiter">
    <div class="container-table100">
        <div class="wrap-table100">
            <div class="table">

                <div class="row header">
                    <div class="cell">
                        Advanced Filter
                    </div>
                    <div class="cell">

                    </div>
                    <div class="cell">

                    </div>
                    <div class="cell">

                    </div>
                </div>

                <div class="row">
                    <div class="cell">
                        by Model
                    </div>
                    <div class="cell">
                        Brand
                        <select id="model_brand" data-placeholder="Choose a brand..." class="chosen-select" name="m_brand">
                            <option value="" selected="selected">Please Select</option>
                            <?php
                            foreach ($model_brands_array as $i => $value) {
                            ?>
                            <option value="<?php echo($value['name'])?>"><?php echo($value['name'])?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="cell">
                        Series <select id="model_series"  class="chosen-select" name="series_code">
                            <option value="" selected="selected">Please Select</option>
                        </select>
                    </div>
                    <div class="cell">
                        Model <select id="models"  class="chosen-select" name="model_code">
                            <option value="" selected="selected">Please Select</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="cell">
                        by Part No.
                    </div>
                    <div class="cell">
                        Brand <select data-placeholder="Choose a brand..." id="pbrand" class="chosen-select" name="p_brand">
                            <option value="" selected="selected">Please Select</option>
                            <?php
                            foreach ($model_parts_array as $i => $value) {
                                ?>
                                <option value="<?php echo($value['name'])?>"><?php echo($value['name'])?></option>
                            <?php } ?>

                        </select>
                    </div>
                    <div class="cell">
                        Part No. <select class="chosen-select" id="parts" name="part_code">
                                <option value="" selected="selected">Please Select</option>
                            </select>
                    </div>
                    <div class="cell">

                    </div>
                </div>



            </div>
        </div>
    </div>
</div>
