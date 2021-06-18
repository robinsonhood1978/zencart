<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 13 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_GET['pID'])) {
  $products_id = zen_db_prepare_input($_GET['pID']);
}
if (isset($_POST['edit']) && $_POST['edit'] == 'edit') {
  $action = 'new_product';
}
elseif ((isset($_POST['products_model']) ? $_POST['products_model'] : '') . (isset($_POST['products_url']) ? implode('', $_POST['products_url']) : '') . (isset($_POST['products_name']) ? implode('', $_POST['products_name']) : '') . (isset($_POST['products_description']) ? implode('', $_POST['products_description']) : '') != '') {

    $products_date_available = zen_db_prepare_input($_POST['products_date_available']);
  if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd') {
    $local_fmt = zen_datepicker_format_fordate(); 
    $dt = DateTime::createFromFormat($local_fmt, $products_date_available); 
    $products_date_available = $dt->format('Y-m-d'); 
  }
  $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

  // Data-cleaning to prevent data-type mismatch errors:
  $sql_data_array = array(
    'products_quantity' => convertToFloat($_POST['products_quantity']),
    'products_type' => (int)$_POST['product_type'],
    'products_model' => zen_db_prepare_input($_POST['products_model']),
    'products_price' => convertToFloat($_POST['products_price']),
    'products_date_available' => $products_date_available,
    'products_weight' => convertToFloat($_POST['products_weight']/1000),
    'products_status' => (int)$_POST['products_status'],
    'products_virtual' => (int)$_POST['products_virtual'],
    'products_tax_class_id' => (int)$_POST['products_tax_class_id'],
    'manufacturers_id' => (int)$_POST['manufacturers_id'],
    'products_quantity_order_min' => convertToFloat($_POST['products_quantity_order_min']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_min']),
    'products_quantity_order_units' => convertToFloat($_POST['products_quantity_order_units']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_units']),
    'products_priced_by_attribute' => (int)$_POST['products_priced_by_attribute'],
    'product_is_free' => (int)$_POST['product_is_free'],
    'product_is_call' => (int)$_POST['product_is_call'],
    'products_quantity_mixed' => (int)$_POST['products_quantity_mixed'],
    'product_is_always_free_shipping' => (int)$_POST['product_is_always_free_shipping'],
    'products_qty_box_status' => (int)$_POST['products_qty_box_status'],
    'products_quantity_order_max' => convertToFloat($_POST['products_quantity_order_max']),
    'products_sort_order' => (int)$_POST['products_sort_order'],
    'products_discount_type' => (int)$_POST['products_discount_type'],
    'products_discount_type_from' => (int)$_POST['products_discount_type_from'],
    'products_price_sorter' => convertToFloat($_POST['products_price_sorter']),
  );

    $ext_sql_data = array(
        'products_id' => 0,
        'type_id' => (int)$_POST['type_id'],
        'condition_id' => (int)$_POST['condition_id'],
        'color_id' => (int)$_POST['color_id'],
        'products_show_part' => (int)$_POST['products_show_part'],
        'products_show_model' => (int)$_POST['products_show_model'],
        'products_show_warranty' => (int)$_POST['products_show_warranty'],
        'products_show_disclaimer' => (int)$_POST['products_show_disclaimer'],
        'products_dimension' => $_POST['products_dimension'],
        'capacity' => $_POST['capacity'],
        'voltage' => $_POST['voltage'],
        'products_net_weight' => convertToFloat($_POST['products_net_weight']/1000),
        'products_handle' => (int)$_POST['products_handle_fee']
    );


    $db_filename = zen_limit_image_filename($_POST['products_image'], TABLE_PRODUCTS, 'products_image');
  $sql_data_array['products_image'] = zen_db_prepare_input($db_filename);

    $db_filename2 = zen_limit_image_filename($_POST['products_image2'], TABLE_PRODUCTS, 'products_image2');
    $sql_data_array['products_image2'] = zen_db_prepare_input($db_filename2);

    $db_filename3 = zen_limit_image_filename($_POST['products_image3'], TABLE_PRODUCTS, 'products_image3');
    $sql_data_array['products_image3'] = zen_db_prepare_input($db_filename3);

  $new_image = 'true';
  if (isset($_POST['image_delete']) && $_POST['image_delete'] == '1') {
    $sql_data_array['products_image'] = '';
    $new_image = 'false';
  }

  if ($action == 'insert_product') {
      $sql_data_array['products_date_added'] = 'now()';
      $sql_data_array['master_categories_id'] = (int)$current_category_id;

      zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
      $products_id = zen_db_insert_id();
      $ext_sql_data['products_id'] = (int)$products_id;
      zen_db_perform(TABLE_PRODUCTS_EXT, $ext_sql_data);


      foreach ($_POST as $key => $value) {
         // echo $key."=".$value."<br>";
          if ($key!='m_brand_num' && strpos("*#".$key,'m_brand') ) {

              $mb_arr[] = [
                  'column' => $key,
                  'name' => $value
              ];
          }
      }
      foreach ($_POST as $key => $value) {
          if (strpos("*#".$key,'#modelb') && strpos($key,'models') && !strpos($key,'modelm') && !strpos($key,'__file') ) {
              //echo $key."=".$value."<br>";
              $i = 0;
              foreach ($mb_arr as &$val){
                  $i++;
                  if( strpos('*#'.$val['column'],'#m_brand'.$i) == strpos('*#'.$key,'#modelb'.$i)){
                      $val['child'][] = [
                          'column' => $key,
                          'name' => $value
                      ];
                  }
              }

          }
      }
      foreach ($_POST as $key => $value) {
          if (strpos("*#".$key,'#modelb') && strpos($key,'models') && strpos($key,'modelm')  ) {
              //echo $key."=".$value."<br>";


              foreach ($mb_arr as &$val){
                  foreach ($val['child'] as &$series){
                      if( strpos('*#'.$key,'#'.$series['column'])){
                          $series['child'][] = [
                              'column' => $key,
                              'name' => $value
                          ];
                          break 2;
                      }
                  }

              }

          }
      }
//file
      foreach ($_POST as $key => $value) {
          if (strpos("*#".$key,'#modelb') && strpos($key,'models') && strpos($key,'__file')  ) {

              $series_file = explode('__',$key)[0];
              // echo $series_file."=".$value."<br>";

              foreach ($mb_arr as &$val){
                  foreach ($val['child'] as &$series){
                      if( strpos('*#'.$series_file,'#'.$series['column'])){
                          $models_array = explode(",",$value);
                          $foot = 0;
                          foreach ($models_array as $mod){
                              $series['child'][] = [
                                 'column' => $series_file."_".$foot,
                                 'name' => $mod
                             ];
                              $foot++;
                          }
                          break 2;
                      }
                  }

              }

          }
      }
     // print_r($mb_arr);
      //exit();

      foreach ($mb_arr as $key=>$value) {
          //$brand .= $value['column'] . "=" . $value['name']."<br/>";
          $sql_mbrand = "INSERT INTO fortech_brand_series (brand_name, product_id) VALUES ('" . $value['name'] . "', " . $products_id . ")";
          $db->Execute($sql_mbrand);
          $brand_series_id = zen_db_insert_id();
          $child_size = count($value['child']);
          for($init = 0 ; $init < $child_size ; $init++){
              //$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
              if(!empty( $value['child'][$init]['name'])){
                  $sql_series = "INSERT INTO fortech_series_model (series_code, brand_series_id) VALUES ('" . $value['child'][$init]['name'] . "', " . $brand_series_id . ")";
                  $db->Execute($sql_series);

                  $series_model_id = zen_db_insert_id();
                  $m_child_size = count($value['child'][$init]['child']);
                  for($initm = 0 ; $initm < $m_child_size ; $initm++){
                      //$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
                      if($value['child'][$init]['child'][$initm]['name']!=''){
                          $sql_series = "INSERT INTO fortech_model (model_code, series_model_id) VALUES ('" . $value['child'][$init]['child'][$initm]['name'] . "', " . $series_model_id . ")";
                          $db->Execute($sql_series);
                      }
                  }
              }
          }
      }
      //print_r($mb_arr);
      //echo $parts;
      //exit();



//echo $products_id ;

	$p_brand_num = $_POST['p_brand_num'];
      if($p_brand_num>0){
      $brand = '';
      $parts = '';
      foreach ($_POST as $key => $value) {
          if ($key!='p_brand_num' && strpos("*#".$key,'p_brand') ) {
              
              $arr[] = [
                  'column' => $key,
                  'name' => $value
              ];
          }
      }
	 

     // echo $brand."<br/>";
          foreach ($_POST as $key => $value) {
              if ($key!='p_brand_num' && strpos("*#".$key,'#part') ) {
                  $i = 0;
                 foreach ($arr as &$val){
                      $i++;
                      if( strpos('*#'.$val['column'],'#p_brand'.$i) == strpos('*#'.$key,'#part'.$i)){
                              $val['child'][] = [
								  'column' => $key,
                                  'name' => $value
                              ];
                        
                           //$parts .= $key . "=" . $value."<br/>";
                      }
                  }
                 
              }
          }
           //var_dump($arr);
			 foreach ($arr as $key=>$value) {
				$brand .= $value['column'] . "=" . $value['name']."<br/>";
				 	$sql_brand = "INSERT INTO fortech_brand_part (brand_name, product_id) VALUES ('" . $value['name'] . "', " . $products_id . ")";
					$db->Execute($sql_brand);
					$brand_part_id = zen_db_insert_id();
					$child_size = count($value['child']);
					for($init = 0 ; $init < $child_size ; $init++){
							//$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
							$sql_part = "INSERT INTO fortech_part (part_code, brand_part_id) VALUES ('" . $value['child'][$init]['name'] . "', " . $brand_part_id . ")";
							$db->Execute($sql_part);
					}
			 }
			 //echo $brand;
//echo $parts;

      }
      //exit();

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($products_id);

    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                  VALUES (" . (int)$products_id . ", " . (int)$current_category_id . ")");

    $db->Execute("update fortech_brand_part set product_id=". (int)$products_id . " where product_id=0");
    $db->Execute("update fortech_brand_series set product_id=". (int)$products_id . " where product_id=0");

    zen_record_admin_activity('New product ' . (int)$products_id . ' added via admin console.', 'info');

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *INSERTS* HERE //////
    ////    *END OF PRODUCT-TYPE-SPECIFIC INSERTS* ////////
    ///////////////////////////////////////////////////////
  } elseif ($action == 'update_product') {
      //print_r($_POST);
      foreach ($_POST as $key => $value) {
          if (strpos("*#" . $key, 'p_model_brand_')) {

              $model_brand[] = [
                  'id' => str_replace('p_model_brand_','',$key),
                  'name' => $value
              ];
          }
          else if (strpos("*#".$key,'#m_seriescode_') ) {
              $model_series[] = [
                  'id' => explode("_",str_replace('m_seriescode_','',$key))[1],
                  'name' => $value
              ];
          }
          else if (strpos("*#".$key,'#m_modelcode_') ) {
              $model_arr[] = [
                  'id' => explode("_",str_replace('m_modelcode_','',$key))[2],
                  'name' => $value
              ];
          }
      }

     foreach ($model_brand as $key=>$value) {
          $sql_model_brand = "update fortech_brand_series set brand_name='" . $value['name'] . "' where brand_series_id= " . $value['id'] ;
          $db->Execute($sql_model_brand);
      }

       foreach ($model_series as $key=>$value) {
          $sql_series = "update fortech_series_model set series_code='" . $value['name'] . "' where series_model_id= " . $value['id'] ;
          $db->Execute($sql_series);
      }
      foreach ($model_arr as $key=>$value) {
          $sql_model = "update fortech_model set model_code='" . $value['name'] . "' where model_id= " . $value['id'] ;
          $db->Execute($sql_model);
      }

      $m_brand_num = $_POST['m_brand_num'];

      if($m_brand_num>0){
          foreach ($_POST as $key => $value) {
              // echo $key."=".$value."<br>";
              if ($key!='m_brand_num' && strpos("*#".$key,'m_brand') ) {

                  $mb_arr[] = [
                      'column' => $key,
                      'name' => $value
                  ];
              }
          }
          foreach ($_POST as $key => $value) {
              if (strpos("*#".$key,'#modelb') && strpos($key,'models') && !strpos($key,'modelm') && !strpos($key,'__file')  ) {
                  //echo $key."=".$value."<br>";

                  $i = 0;
                  foreach ($mb_arr as &$val){
                      $i++;
                      if( strpos('*#'.$val['column'],'#m_brand'.$i) == strpos('*#'.$key,'#modelb'.$i)){
                          $val['child'][] = [
                              'column' => $key,
                              'name' => $value
                          ];
                      }
                  }

              }
          }
          foreach ($_POST as $key => $value) {
              if (strpos("*#".$key,'#modelb') && strpos($key,'models') && strpos($key,'modelm')  ) {
                  //echo $key."=".$value."<br>";


                  foreach ($mb_arr as &$val){
                      foreach ($val['child'] as &$series){
                          if( strpos('*#'.$key,'#'.$series['column'])){
                              $series['child'][] = [
                                  'column' => $key,
                                  'name' => $value
                              ];
                              break 2;
                          }
                      }

                  }

              }
          }
//          print_r($_POST);
//          exit;
          //file
          foreach ($_POST as $key => $value) {
              if (strpos("*#".$key,'#modelb') && strpos($key,'models') && strpos($key,'__file')  ) {

                  $series_file = explode('__',$key)[0];
                  // echo $series_file."=".$value."<br>";

                  foreach ($mb_arr as &$val){
                      foreach ($val['child'] as &$series){
                          if( strpos('*#'.$series_file,'#'.$series['column'])){
                              $models_array = explode(",",$value);
                              $foot = 0;
                              foreach ($models_array as $mod){
                                  $series['child'][] = [
                                      'column' => $series_file."_".$foot,
                                      'name' => $mod
                                  ];
                                  $foot++;
                              }
                              break 2;
                          }
                      }

                  }

              }
          }

          $my_model_brands = $db->Execute("select b.brand_series_id,b.brand_name,ifnull(p.part_num,0) as part_num from fortech_brand_series b 
                                            left join (select brand_series_id,count(*) as part_num from fortech_series_model group by brand_series_id) p 
                                            on b.brand_series_id=p.brand_series_id where b.product_id=".$products_id." order by b.brand_series_id");
          //print_r($parts);
          foreach ($my_model_brands as $my_brand) {


              $my_series_arr = $db->Execute("select b.series_model_id,b.series_code,ifnull(p.model_num,0) as model_num from fortech_series_model b left join 
                                                (select series_model_id,count(*) as model_num from fortech_model group by series_model_id) p on b.series_model_id=p.series_model_id 
                                                where b.brand_series_id=".$my_brand['brand_series_id']." order by b.series_model_id");
              $my_series_array = array();
              foreach ($my_series_arr as $my_sery) {
                  $my_parts_arr = $db->Execute("select model_id,model_code from fortech_model where series_model_id=".$my_sery['series_model_id']);
                  $my_part_array = array();
                  foreach ($my_parts_arr as $my_part) {
                      $my_part_array[] = [
                          'id' => $my_part['model_id'],
                          'name' => $my_part['model_code'],
                          'show' => 0,
                          'level' => 3,
                          'type' => 'leaf'
                      ];
                  }

                  $my_series_array[] = [
                      'id' => $my_sery['series_model_id'],
                      'name' => $my_sery['series_code'],
                      'show' => 1,
                      'level' => 2,
                      'text' => 'Add New Models',
                      'type' => ($my_sery['model_num']>0)?'mid':'leaf',
                      'child' => $my_part_array
                  ];


              }

              $md_brand_array[] = [
                  'id' => $my_brand['brand_series_id'],
                  'name' => $my_brand['brand_name'],
                  'show' => 1,
                  'level' => 1,
                  'text' => 'Add New Series',
                  'type' => ($my_brand['part_num']>0)?'mid':'leaf',
                  'child' => $my_series_array
              ];
          }

          foreach ($mb_arr as $key=>$value) {
              $mb_brand .= $value['column'] . "=" . $value['name']."<br/>";

          }

          $my_new_size = count($mb_arr);
          $my_size = count($md_brand_array);
          for ($begin=0;$begin<$my_new_size;$begin++) {
              $my_new_brandname = $mb_arr[$begin]['name'];
              $my_flag=0;
              $my_db_index = 0;
              for ($ini=0;$ini<$my_size;$ini++) {
                  if($md_brand_array[$ini]['name']==$my_new_brandname){
                      $my_flag = 1;
                      $my_db_index = $ini;
                      $mb_arr[$begin]['id']= $md_brand_array[$ini]['id'];
                      break;
                  }
                  //echo "<br>";
              }
              //数据库中存在同名brand
              if($my_flag==1){
                  $my_new_series_size = count($mb_arr[$begin]['child']);
                  for($new_begin=0;$new_begin<$my_new_series_size;$new_begin++){
                      $series_flag = 0;
                      $my_series_db_index = 0;
                      $my_db_ser_model_size = count($md_brand_array[$my_db_index]['child']);
                      for($ne_begin=0;$ne_begin<$my_db_ser_model_size;$ne_begin++){
                          if($mb_arr[$begin]['child'][$new_begin]['name']==$md_brand_array[$my_db_index]['child'][$ne_begin]['name']){
                              $series_flag=1;
                              $my_series_db_index = $ne_begin;
                              $mb_arr[$begin]['child'][$new_begin]['id']= $md_brand_array[$my_db_index]['child'][$ne_begin]['id'];
                              //echo $val['name']."<br>";
                              break;
                          }
                      }

                      //存在同名系列
                      if($series_flag==1){
                         $my_new_model_size = count($mb_arr[$begin]['child'][$new_begin]['child']);
                         for($nn_begin=0;$nn_begin<$my_new_model_size;$nn_begin++){
                             $new_model_name = $mb_arr[$begin]['child'][$new_begin]['child'][$nn_begin]['name'];
                             $series_model_id = $mb_arr[$begin]['child'][$new_begin]['id'];
                             $model_flag = 0;
                             foreach ($md_brand_array[$my_db_index]['child'][$my_series_db_index]['child'] as $kk=>$vval){
                                 if($new_model_name==$vval['name']){
                                     $model_flag=1;
                                     //echo $vval['name']."<br>";
                                     break;
                                 }
                             }
                             //model不存在同名
                             if($model_flag==0){
                                 if($new_model_name!=''){
                                     $sql_series = "INSERT INTO fortech_model (model_code, series_model_id) VALUES ('" . $new_model_name . "', " . $series_model_id . ")";
                                     $db->Execute($sql_series);
                                 }
                             }
                         }
                      }
                      //不存在同名系列
                      else{
                          $sql_series = "INSERT INTO fortech_series_model (series_code, brand_series_id) VALUES ('"
                              . $mb_arr[$begin]['child'][$new_begin]['name'] . "', " . $mb_arr[$begin]['id'] . ")";
                          $db->Execute($sql_series);

                          $series_model_id = zen_db_insert_id();
                          $m_child_size = count($mb_arr[$begin]['child'][$new_begin]['child']);
                          for($initm = 0 ; $initm < $m_child_size ; $initm++){
                              //$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
                              if($mb_arr[$begin]['child'][$new_begin]['child'][$initm]['name']!=''){
                                  $sql_series = "INSERT INTO fortech_model (model_code, series_model_id) VALUES ('" . $mb_arr[$begin]['child'][$new_begin]['child'][$initm]['name'] . "', " . $series_model_id . ")";
                                  $db->Execute($sql_series);
                              }
                          }
                          //echo $mb_arr[$begin]['child'][$new_begin]['name']."__".$mb_arr[$begin]['id'];
                      }
                  }

              }
              //数据库中不存在同名brand
              else{

                  $sql_mbrand = "INSERT INTO fortech_brand_series (brand_name, product_id) VALUES ('" . $my_new_brandname . "', " . $products_id . ")";
                  $db->Execute($sql_mbrand);
                  $brand_series_id = zen_db_insert_id();
                  $child_size = count($mb_arr[$begin]['child']);
                  for($init = 0 ; $init < $child_size ; $init++){
                      //$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
                      if(!empty( $mb_arr[$begin]['child'][$init]['name'])){
                          $sql_series = "INSERT INTO fortech_series_model (series_code, brand_series_id) VALUES ('" . $mb_arr[$begin]['child'][$init]['name'] . "', " . $brand_series_id . ")";
                          $db->Execute($sql_series);

                          $series_model_id = zen_db_insert_id();
                          $m_child_size = count($mb_arr[$begin]['child'][$init]['child']);
                          for($initm = 0 ; $initm < $m_child_size ; $initm++){
                              //$parts .= $value['child'][$init]['column'] . "=" . $value['child'][$init]['name']."<br/>";
                              if($mb_arr[$begin]['child'][$init]['child'][$initm]['name']!=''){
                                  $sql_series = "INSERT INTO fortech_model (model_code, series_model_id) VALUES ('" . $mb_arr[$begin]['child'][$init]['child'][$initm]['name'] . "', " . $series_model_id . ")";
                                  $db->Execute($sql_series);
                              }
                          }
                      }
                  }
              }

          }
          //print_r($mb_arr);
          //echo "<br>";
          //print_r($md_brand_array);
          //exit();
      }

          foreach ($_POST as $key => $value) {
              if ($key != 'p_brand_num' && strpos("*#" . $key, 'p_brand_')) {

                  $arr[] = [
                      'id' => str_replace('p_brand_','',$key),
                      'name' => $value
                  ];
              }
              else if (strpos("*#".$key,'#p_partcode_') ) {
                  $myparts[] = [
                      'id' => str_replace('p_partcode_','',$key),
                      'name' => $value
                  ];
              }
          }

      foreach ($arr as $key=>$value) {
          $sql_brand = "update fortech_brand_part set brand_name='" . $value['name'] . "' where brand_part_id= " . $value['id'] ;
          $db->Execute($sql_brand);
      }
      foreach ($myparts as $key=>$value) {
          $sql_part = "update fortech_part set part_code='" . $value['name'] . "' where part_id= " . $value['id'] ;
          $db->Execute($sql_part);
      }

      $p_brand_num = $_POST['p_brand_num'];

      if($p_brand_num>0){
          foreach ($_POST as $key => $value) {
              if ($key!='p_brand_num' && strpos("*#".$key,'#p_brand') && !strpos("*#".$key,'#p_brand_') ) {

                  $brandArr[] = [
                      'column' => $key,
                      'name' => $value
                  ];
              }
          }
          foreach ($_POST as $key => $value) {
              if ($key!='p_brand_num' && strpos("*#".$key,'#part') ) {
                  $i = 0;
                  foreach ($brandArr as &$val){
                      $i++;
                      if( strpos('*#'.$val['column'],'#p_brand'.$i) == strpos('*#'.$key,'#part'.$i)){
                          $val['child'][] = [
                              'column' => $key,
                              'name' => $value
                          ];

                          //$parts .= $key . "=" . $value."<br/>";
                      }
                  }

              }
          }

          $brands = $db->Execute("select b.brand_part_id,b.brand_name,ifnull(p.part_num,0) as part_num from fortech_brand_part b 
            left join (select brand_part_id,count(*) as part_num from fortech_part group by brand_part_id) p on b.brand_part_id=p.brand_part_id
            where b.product_id=".$products_id." order by b.brand_part_id");
          //print_r($parts);
          foreach ($brands as $brand) {
              $db_arr[] = [
                  'id' => $brand['brand_part_id'],
                  'name' => $brand['brand_name'],
                  'show' => 1,
                  'level' => 1,
                  'text' => 'Add New Part',
                  'type' => ($brand['part_num']>0)?'mid':'leaf'
              ];
          }
          $parts = $db->Execute("select b.brand_part_id,b.brand_name,p.part_id,p.part_code from fortech_brand_part b left join fortech_part p on b.brand_part_id=p.brand_part_id where b.product_id=".$products_id." order by b.brand_part_id");
          foreach ($parts as $part) {
              foreach ($db_arr as &$value){
                  if( $value['id'] == $part['brand_part_id']){
                      if(!empty($part['part_id'])){
                          $value['child'][] = [
                              'id' => $part['part_id'],
                              'name' => $part['part_code'],
                              'show' => 0,
                              'level' => 2,
                              'type' => 'leaf'
                          ];
                      }
                  }
              }

          }

          $new_size = count($brandArr);
          $size = count($db_arr);
          for ($begin=0;$begin<$new_size;$begin++) {
              $new_brandname = $brandArr[$begin]['name'];
              $flag=0;
              $db_index = 0;
              for ($ini=0;$ini<$size;$ini++) {
                  if($db_arr[$ini]['name']==$new_brandname){
                      $flag = 1;
                      $db_index = $ini;
                      $brandArr[$begin]['id']= $db_arr[$ini]['id'];
                      break;
                  }
                  // echo "<br>";
              }
              if($flag==1){
                    $new_part_size = count($brandArr[$begin]['child']);
                    for($new_begin=0;$new_begin<$new_part_size;$new_begin++){
                        $part_flag = 0;
                        foreach ($db_arr[$db_index]['child'] as $key=>$value){
                            if($brandArr[$begin]['child'][$new_begin]['name']==$value['name']){
                                $part_flag=1;
                                //echo $value['name']."<br>";
                                break;
                            }
                        }
                        if($part_flag==0){
                            $sql_part = "INSERT INTO fortech_part (part_code, brand_part_id) VALUES ('"
                                . $brandArr[$begin]['child'][$new_begin]['name'] . "', " . $brandArr[$begin]['id'] . ")";
                            $db->Execute($sql_part);
                        }
                    }

              }
              else{

                  $sql_brand = "INSERT INTO fortech_brand_part (brand_name, product_id) VALUES ('" . $brandArr[$begin]['name'] . "', " . $products_id . ")";
                  $db->Execute($sql_brand);
                  $new_brand_id = zen_db_insert_id();
                  foreach ($brandArr[$begin]['child'] as $key=>$value){
                      $sql_part = "INSERT INTO fortech_part (part_code, brand_part_id) VALUES ('"
                          . $value['name'] . "', " . $new_brand_id . ")";
                      $db->Execute($sql_part);
                  }
                  $brandArr[$begin]['id']=$new_brand_id;
              }

          }


      }
//print_r($brandArr);
  //    exit();
    $sql_data_array['products_last_modified'] = 'now()';
    $sql_data_array['master_categories_id'] = (!empty($_POST['master_category']) && (int)$_POST['master_category'] > 0 ? (int)$_POST['master_category'] : (int)$_POST['master_categories_id']);

    zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = " . (int)$products_id);
//Robin
    $ext_sql_data['products_id'] = (int)$products_id;
    zen_db_perform(TABLE_PRODUCTS_EXT, $ext_sql_data, 'update', "products_id = " . (int)$products_id);

    zen_record_admin_activity('Updated product ' . (int)$products_id . ' via admin console.', 'info');

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter((int)$products_id);

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *UPDATES* HERE //////


    ////    *END OF PRODUCT-TYPE-SPECIFIC UPDATES* ////////
    ///////////////////////////////////////////////////////
  }

  $languages = zen_get_languages();
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $language_id = $languages[$i]['id'];

    $sql_data_array = array(
      'products_name' => zen_db_prepare_input($_POST['products_name'][$language_id]),
      'products_description' => zen_db_prepare_input($_POST['products_description'][$language_id]),
      'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id]));

    if ($action == 'insert_product') {
      $insert_sql_data = array(
        'products_id' => (int)$products_id,
        'language_id' => (int)$language_id);

      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
    } elseif ($action == 'update_product') {
      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = " . (int)$products_id . " and language_id = " . (int)$language_id);
    }
  }

  $zco_notifier->notify('NOTIFY_MODULES_UPDATE_PRODUCT_END', array('action' => $action, 'products_id' => $products_id));

  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
} else {
  $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
}

/**
 * NOTE: THIS IS HERE FOR BACKWARD COMPATIBILITY. The function is properly declared in the functions files instead.
 * Convert value to a float -- mainly used for sanitizing and returning non-empty strings or nulls
 * @param int|float|string $input
 * @return float|int
 */
if (!function_exists('convertToFloat')) {

  function convertToFloat($input = 0) {
    if ($input === null) {
      return 0;
    }
    $val = preg_replace('/[^0-9,\.\-]/', '', $input);
    // do a non-strict compare here:
    if ($val == 0) {
      return 0;
    }

    return (float)$val;
  }

}
