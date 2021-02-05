<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=advanced_search.<br />
 * Displays options fields upon which a product search will be run
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
?>
<div class="centerColumn" id="advSearchDefault">

<?php echo zen_draw_form('advanced_search', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get', 'onsubmit="return check_form(this);"') . zen_hide_session_id(); ?>
<?php echo zen_draw_hidden_field('main_page', FILENAME_ADVANCED_SEARCH_RESULT); ?>

<h1 id="advSearchDefaultHeading"><?php echo HEADING_TITLE_1; ?></h1>


    <?php
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



  ?>
    <div class="limiter">
        <div class="container-table100">
            <div class="wrap-table100">
                <div class="table">



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
                            Series <select id="model_series" data-placeholder="Choose a series..."  class="chosen-select" name="series_code">
                                <option value="" selected="selected">Please Select</option>
                            </select>
                        </div>
                        <div class="cell">
                            Model <select id="models" data-placeholder="Choose a model..."  class="chosen-select" name="model_code">
                                <option value="" selected="selected">Please Select</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="cell">
                            by Part No.
                        </div>
                        <div class="cell">
                            Brand <select id="pbrand" data-placeholder="Choose a brand..." class="chosen-select" name="p_brand">
                                <option value="" selected="selected">Please Select</option>
                                <?php
                                foreach ($model_parts_array as $i => $value) {
                                ?>
                                <option value="<?php echo($value['name'])?>"><?php echo($value['name'])?></option>
                                <?php } ?>

                            </select>
                        </div>
                        <div class="cell">
                            Part No. <select data-placeholder="Choose a part..." class="chosen-select" id="parts" name="part_code">
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



   <br>
    <div style="text-align:right;">
        <button id="back" class="button button-pill button-primary button-small">Back</button>
        <button id="filter" class="button button-pill button-primary button-small">Search</button>
    </div>

</form>
</div>