<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (!isset($_GET['cID'])) $_GET['cID'] = '';
if (!isset($_GET['gID'])) $_GET['gID'] = '';

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (!isset($_POST['description'])) {
        break;
      }
      if (isset($_GET['ptID'])) $show_id = zen_db_prepare_input($_GET['ptID']);
      $description = zen_db_prepare_input($_POST['description']);
      $msg_id = zen_db_prepare_input($_POST['msg_id']);
      $message_text = htmlspecialchars_decode(zen_db_prepare_input($_POST['message_text']));
      $message_group = zen_db_prepare_input($_POST['message_group']);
      if($msg_id===''){
          $msg_id = 0;
      }

      $sql_data_array = array(
        'description' => $description,
          'msg_id' => $msg_id,
        'message_text' => $message_text,
        'message_group' => $message_group);

      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_PRODUCTS_EXTRA_MESSAGE, $sql_data_array);
        $show_id = $db->Insert_ID();
      } elseif ($action == 'save') {
        $update_sql_data = array(
          'last_modified' => 'now()'
        );

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_PRODUCTS_EXTRA_MESSAGE, $sql_data_array, 'update', "show_id = " . (int)$show_id);
      }



      zen_redirect(zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ptID=' . $show_id));
      break;
    case 'deleteconfirm':
      $show_id = zen_db_prepare_input($_POST['ptID']);



      $db->Execute("DELETE FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE . " WHERE show_id = " . (int)$show_id);
//        $db->Execute("UPDATE " . TABLE_PRODUCTS_EXT . " SET products_show_part = 0 WHERE products_show_part = " . (int)$show_id);
//        $db->Execute("UPDATE " . TABLE_PRODUCTS_EXT . " SET products_show_model = 0 WHERE products_show_model = " . (int)$show_id);
//        $db->Execute("UPDATE " . TABLE_PRODUCTS_EXT . " SET products_show_warranty = 0 WHERE products_show_warranty = " . (int)$show_id);
//        $db->Execute("UPDATE " . TABLE_PRODUCTS_EXT . " SET products_show_disclaimer = 0 WHERE products_show_disclaimer = " . (int)$show_id);

      zen_redirect(zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page']));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
    </script>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
        <h1><?php echo HEADING_TITLE; ?></h1>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <!-- body_text //-->

            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT_TYPES; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCT_TYPES_ALLOW_ADD_TO_CART; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $product_types_query_raw = "SELECT * FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE;
                $product_types_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $product_types_query_raw, $product_types_query_numrows);
                $product_types = $db->Execute($product_types_query_raw);
                foreach ($product_types as $product_type) {
                  if ((!isset($_GET['ptID']) || (isset($_GET['ptID']) && ($_GET['ptID'] == $product_type['show_id']))) && !isset($ptInfo) && (substr($action, 0, 3) != 'new')) {


                    //$ptInfo_array = array_merge($product_type, $product_type_products->fields);

                    $ptInfo = new objectInfo($product_type);
                     //$ptInfo = $product_types;
                  }

                  if (isset($ptInfo) && is_object($ptInfo) && ($product_type['show_id'] == $ptInfo->show_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $product_type['show_id'] . '&action=layout') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $product_type['show_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
                <td class="dataTableContent"><?php echo $product_type['description']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $product_type['message_group']; ?></td>
                <td class="dataTableContent" align="right">
                    <?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $product_type['show_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                    <?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $product_type['show_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                    <?php
                  if ((isset($ptInfo) && is_object($ptInfo)) && ($product_type['show_id'] == $ptInfo->show_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'ptID=' . $product_type['show_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>&nbsp;</td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_PRODUCT_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('new_product_type', FILENAME_PRODUCTS_EXTRA_MESSAGE, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_NAME, 'description', 'class="control-label"') . zen_draw_input_field('description', '', zen_set_field_length(TABLE_PRODUCTS_EXTRA_MESSAGE, 'description') . ' class="form-control"'));
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_EXTRA_MESSAGE_ID, 'msg_id', 'class="control-label"') . zen_draw_input_field('msg_id', '', zen_set_field_length(TABLE_PRODUCTS_EXTRA_MESSAGE, 'msg_id') . ' class="form-control"'));
                  $sql = "SELECT message_group, group_name FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE_GROUP;
                  $product_type_list = $db->Execute($sql);
                  foreach ($product_type_list as $item) {
                      $product_type_array[] = array(
                          'text' => $item['group_name'],
                          'id' => $item['message_group']);
                  }
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_MASTER_TYPE, 'message_group', 'class="control-label"') . zen_draw_pull_down_menu('message_group', $product_type_array, '', 'class="form-control"'));
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_EXTRA_MESSAGE_TEXT, 'message_text', 'class="control-label"') . zen_draw_textarea_field('message_text', 'soft', '50', '5', '', 'id="message_text" class="form-control"'));
                  $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_PRODUCT_TYPE . ' :: ' . $ptInfo->description . '</h4>');

                $contents = array('form' => zen_draw_form('product_types', FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=save', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_TYPES_NAME, 'description', 'class="control-label"') . zen_draw_input_field('description', $ptInfo->description, zen_set_field_length(TABLE_PRODUCTS_EXTRA_MESSAGE, 'description') . ' class="form-control"'));
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_EXTRA_MESSAGE_ID, 'msg_id', 'class="control-label"') . zen_draw_input_field('msg_id', $ptInfo->msg_id, zen_set_field_length(TABLE_PRODUCTS_EXTRA_MESSAGE, 'msg_id') . ' class="form-control"'));
                  $sql = "SELECT message_group, group_name FROM " . TABLE_PRODUCTS_EXTRA_MESSAGE_GROUP;
                $product_type_list = $db->Execute($sql);
                foreach ($product_type_list as $item) {
                  $product_type_array[] = array(
                    'text' => $item['group_name'],
                    'id' => $item['message_group']);
                }
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_MASTER_TYPE, 'message_group', 'class="control-label"') . zen_draw_pull_down_menu('message_group', $product_type_array, $ptInfo->message_group, 'class="form-control"'));
                  $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_PRODUCT_EXTRA_MESSAGE_TEXT, 'message_text', 'class="control-label"') . zen_draw_textarea_field('message_text', 'soft', '50', '5', $ptInfo->message_text, 'id="message_text" class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_PRODUCT_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ptID', $ptInfo->show_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $ptInfo->description . '</b>');

                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');

                  break;
              default:
                if (isset($ptInfo) && is_object($ptInfo)) {
                  $heading[] = array('text' => '<h4>' . $ptInfo->description . '</h4>');
// remove delete for now to avoid issues
//        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=layout') . '">' . zen_image_button('button_layout.gif', IMAGE_LAYOUT) . '</a>' );
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($ptInfo->date_added));
                  if (zen_not_null($ptInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($ptInfo->last_modified));
                  }
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
            <!-- body_text_eof //-->
          </div>
        </div>
        <?php
        if (empty($action)) {
            ?>
            <div class="row text-right"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXTRA_MESSAGE, 'page=' . $_GET['page'] . '&ptID=' . $ptInfo->show_id . '&action=new') . '" class="btn btn-primary">' . IMAGE_INSERT . '</a>'; ?></div>
            <?php
        }
        ?>
        <div class="row">
          <div class="col-cm-6"><?php echo $product_types_split->display_count($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCT_EXTRA_MESSAGE); ?></div>
          <div class="col-sm-6 text-right"><?php echo $product_types_split->display_links($product_types_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
        </div>

      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
