<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 13 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$languages = zen_get_languages();
if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['mID'])) {
        $type_id = zen_db_prepare_input($_GET['mID']);
      }
      $type_name = zen_db_prepare_input($_POST['type_name']);

      $sql_data_array = array('type_name' => $type_name);

      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_TYPE, $sql_data_array);
        $type_id = zen_db_insert_id();
      } elseif ($action == 'save') {
        $update_sql_data = array('last_modified' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_TYPE, $sql_data_array, 'update', "type_id = " . (int)$type_id);
      }


      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TYPE, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $type_id));
      break;
    case 'deleteconfirm':
      $type_id = zen_db_prepare_input($_POST['mID']);



      $db->Execute("DELETE FROM " . TABLE_TYPE . "
                    WHERE type_id = " . (int)$type_id);

      
        $db->Execute("UPDATE " . TABLE_PRODUCTS_EXT . "
                      SET type_id = 0
                      WHERE type_id = " . (int)$type_id);


      zen_redirect(zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page']));
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
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent">&nbsp;</th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php
                $type_query_raw = "SELECT type_id, type_name
                                            FROM " . TABLE_TYPE . "
                                            ORDER BY type_name";

// reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['mID'])) {
                  $check_page = $db->Execute($type_query_raw);
                  $check_count = 1;
                  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                    foreach ($check_page as $item) {
                      if ($item['type_id'] == $_GET['mID']) {
                        break;
                      }
                      $check_count++;
                    }
                    $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                  } else {
                    $_GET['page'] = 1;
                  }
                }

                $type_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $type_query_raw, $type_query_numrows);
                $manufacturers = $db->Execute($type_query_raw);
                foreach ($manufacturers as $manufacturer) {
                  if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $manufacturer['type_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
                    $mInfo = new objectInfo($manufacturer);
                  }

                  if (isset($mInfo) && is_object($mInfo) && ($manufacturer['type_id'] == $mInfo->type_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $manufacturer['type_id'] . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $manufacturer['type_id'] . '&action=edit') . '\'" role="button">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $manufacturer['type_id']; ?></td>
              <td class="dataTableContent"><?php echo $manufacturer['type_name']; ?></td>
              <td class="dataTableContent" align="right">
                  <?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $manufacturer['type_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                  <?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $manufacturer['type_id'] . '&action=delete') . '">' . zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>'; ?>
                  <?php
                  if (isset($mInfo) && is_object($mInfo) && ($manufacturer['type_id'] == $mInfo->type_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, zen_get_all_get_params(array('mID')) . 'mID=' . $manufacturer['type_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_MANUFACTURER . '</h4>');

                $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCTS_TYPE, 'action=insert', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_MANUFACTURERS_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', '', zen_set_field_length(TABLE_TYPE, 'type_name') . 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_MANUFACTURER . '</h4>');

                $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=save', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_MANUFACTURERS_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', htmlspecialchars($mInfo->type_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_TYPE, 'type_name') . ' class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_MANUFACTURER . '</h4>');

                $contents = array('form' => zen_draw_form('manufacturers', FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $mInfo->type_id));
                $contents[] = array('text' => TEXT_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $mInfo->type_name . '</b>');
                

                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($mInfo) && is_object($mInfo)) {
                  $heading[] = array('text' => '<h4>' . $mInfo->type_name . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');

                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
        <!-- body_text_eof //-->
      </div>
        <?php
        if (empty($action)) {
          ?>
          <div class="row text-right"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_TYPE, 'page=' . $_GET['page'] . '&mID=' . $mInfo->type_id . '&action=new') . '" class="btn btn-primary">' . IMAGE_INSERT . '</a>'; ?></div>
          <?php
        }
        ?>
        <div class="row">
          <table class="table">
            <tr>
              <td><?php echo $type_split->display_count($type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_TYPE); ?></td>
              <td class="text-right"><?php echo $type_split->display_links($type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
          </table>
        </div>
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
