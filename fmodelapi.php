<?php
header('Content-type:application/json');
define('IS_ADMIN_FLAG', true);

define('DB_TYPE', 'mysql'); // always 'mysql'
define('DB_PREFIX', ''); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', 'utf8mb4'); // 'utf8mb4' or older 'utf8' / 'latin1' are most common
define('DB_SERVER', 'localhost');  // address of your db server
define('DB_SERVER_USERNAME', 'robin');
define('DB_SERVER_PASSWORD', 'robin123');
define('DB_DATABASE', 'zencart');

define('TABLE_PROJECT_VERSION', DB_PREFIX . 'project_version');

require('includes/classes/class.base.php');
require 'includes/init_includes/init_database.php';

$pid = empty($_GET['pid'])?'0':$_GET['pid'];
$id = $_GET['id'];
$part_array = [];
$level = $_GET['level'];


    if($pid!=''){
        if($id==0){
            $parts = $db->Execute("select b.brand_series_id,b.brand_name,ifnull(p.part_num,0) as part_num from fortech_brand_series b 
                                            left join (select brand_series_id,count(*) as part_num from fortech_series_model group by brand_series_id) p 
                                            on b.brand_series_id=p.brand_series_id where b.product_id=".$pid." order by b.brand_series_id");
            //print_r($parts);
            foreach ($parts as $part) {
                $part_array[] = [
                    'id' => $part['brand_series_id'],
                    'name' => $part['brand_name'],
                    'show' => 1,
                    'level' => 1,
                    'text' => 'Add New Series',
                    'type' => ($part['part_num']>0)?'mid':'leaf'
                ];
            }
        }
        else{
            if($level==1){
                $parts = $db->Execute("select b.series_model_id,b.series_code,ifnull(p.model_num,0) as model_num from fortech_series_model b left join 
                                                (select series_model_id,count(*) as model_num from fortech_model group by series_model_id) p on b.series_model_id=p.series_model_id 
                                                where b.brand_series_id=".$id." order by b.series_model_id");
                foreach ($parts as $part) {
                    $part_array[] = [
                        'id' => $part['series_model_id'],
                        'name' => $part['series_code'],
                        'show' => 1,
                        'level' => 2,
                        'text' => 'Add New Models',
                        'type' => ($part['model_num']>0)?'mid':'leaf'
                    ];
                }
            }
            else{
                $parts = $db->Execute("select model_id,model_code from fortech_model where series_model_id=".$id);
                foreach ($parts as $part) {
                    $part_array[] = [
                        'id' => $part['model_id'],
                        'name' => $part['model_code'],
                        'show' => 0,
                        'level' => 3,
                        'type' => 'leaf'
                    ];
                }
            }

        }
    }

//$str = '{"0":[{"id":1,"name":"APPLE","type":"leaf"},{"id":2,"name":"ACER","type":"mid"},{"id":3,"name":"HP","type":"leaf"}]}';

//$obj = json_decode($part_array);

    $arr = array ('code'=>200,'data'=>$part_array);
    echo json_encode($arr,JSON_UNESCAPED_UNICODE);


?>