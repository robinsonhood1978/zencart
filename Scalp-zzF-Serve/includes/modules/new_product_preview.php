<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jan 20 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// upload image, if submitted
  if (!isset($_GET['read']) || $_GET['read'] !== 'only') {
      $_POST['img_dir'] = 'large/';
      //pic1
    $products_image = new upload('products_image');
    $products_image->set_extensions(array('jpg','jpeg','gif','png','webp','flv','webm','ogg'));

    $products_image->set_destination(DIR_FS_CATALOG_IMAGES . (isset($_POST['img_dir']) ? $_POST['img_dir'] : ''));
    if ($products_image->parse() && $products_image->save(isset($_POST['overwrite']) ? $_POST['overwrite'] : false)) {
      $products_image_name =  $products_image->filename;
        resizeImage('../images/large/'.$products_image_name,'../images/small/'.$products_image->filename,75,75);
        resizeImage('../images/large/'.$products_image_name,'../images/medium/'.$products_image->filename,240,240);
    } else {
      $products_image_name = (isset($_POST['products_previous_image']) ? $_POST['products_previous_image'] : '');
    }
//pic2
      $products_image2 = new upload('products_image2');
      $products_image2->set_extensions(array('jpg','jpeg','gif','png','webp','flv','webm','ogg'));

      $products_image2->set_destination(DIR_FS_CATALOG_IMAGES . (isset($_POST['img_dir']) ? $_POST['img_dir'] : ''));
      if ($products_image2->parse() && $products_image2->save(isset($_POST['overwrite']) ? $_POST['overwrite'] : false)) {
          $products_image_name2 =  $products_image2->filename;
          resizeImage('../images/large/'.$products_image_name2,'../images/small/'.$products_image2->filename,75,75);
          resizeImage('../images/large/'.$products_image_name2,'../images/medium/'.$products_image2->filename,240,240);
      } else {
          $products_image_name2 = (isset($_POST['products_previous_image2']) ? $_POST['products_previous_image2'] : '');
      }

      //pic3
      $products_image3 = new upload('products_image3');
      $products_image3->set_extensions(array('jpg','jpeg','gif','png','webp','flv','webm','ogg'));

      $products_image3->set_destination(DIR_FS_CATALOG_IMAGES . (isset($_POST['img_dir']) ? $_POST['img_dir'] : ''));
      if ($products_image3->parse() && $products_image3->save(isset($_POST['overwrite']) ? $_POST['overwrite'] : false)) {
          $products_image_name3 =  $products_image3->filename;
          resizeImage('../images/large/'.$products_image_name3,'../images/small/'.$products_image3->filename,75,75);
          resizeImage('../images/large/'.$products_image_name3,'../images/medium/'.$products_image3->filename,240,240);
      } else {
          $products_image_name3 = (isset($_POST['products_previous_image3']) ? $_POST['products_previous_image3'] : '');
      }
  }



// hook to allow interception of product-image uploading by admin-side observer class
$zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_IMAGE_UPLOADED', $products_image, $products_image_name);

