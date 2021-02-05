<?php
header('Content-type:application/json');
require('includes/application_top.php');


$id = empty($_GET['id'])?0:$_GET['id'];
$img = $_GET['img'];
$code = 0;
if($img==1){
    $column = 'products_image';
}
if($img==2){
    $column = 'products_image2';
}
if($img==3){
    $column = 'products_image3';
}

if($id!=0){
    $product_image = $db->Execute("select ".$column."
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = " . (int)$id);

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_PRODUCTS . "
                                     where ".$column." = '" . zen_db_input($product_image->fields[$column]) . "'");


    if ($duplicate_image->fields['total'] < 2 and $product_image->fields[$column] != '') {
        $products_image = $product_image->fields[$column];

        $filename_small = 'small/' .$products_image;
        $filename_medium = 'medium/' . $products_image;
        $filename_large = 'large/' . $products_image;


        if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_small)) {
            @unlink(DIR_FS_CATALOG_IMAGES . $filename_small);
        }
        if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
            @unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
        }
        if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
            @unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
        }
    }
    $db->Execute("update " . TABLE_PRODUCTS . " set ".$column."='' where products_id = " . (int)$id);
        $code = 1;
}


$arr = array ('code'=>$code);
echo json_encode($arr,JSON_UNESCAPED_UNICODE);


?>