<?php
header('Content-type:application/json');
define('IS_ADMIN_FLAG', true);
define('DB_TYPE', 'mysql'); // always 'mysql'
define('DB_PREFIX', ''); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', 'utf8mb4'); // 'utf8mb4' or older 'utf8' / 'latin1' are most common
define('DB_SERVER', 'localhost');  // address of your db server
define('DB_SERVER_USERNAME', 'fortech');
define('DB_SERVER_PASSWORD', '586fortech#');
define('DB_DATABASE', 'zencart');

define('TABLE_PROJECT_VERSION', DB_PREFIX . 'project_version');

require('includes/classes/class.base.php');
require 'includes/init_includes/init_database.php';

//$str = '[{"id":1,"name":"一级","open":true,"child":[{"id":5,"name":"二级","type":"leaf"},{"id":6,"name":"二级","child":[{"id":7,"name":"三级","type":"leaf"},{"id":9,"name":"三级","child":[{"id":10,"name":"四级","child":[{"id":11,"name":"五级","type":"leaf"},{"id":12,"name":"五级","type":"leaf"}]}]}]}]},{"id":2,"name":"一级","child":[{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":4,"name":"二级","pid":2,"type":"leaf"}]},{"id":8,"name":"一级","pid":0,"type":"leaf"}]';
//$arr = json_decode($str,true);
$pid = empty($_GET['pid'])?'0':$_GET['pid'];
//echo "pid:".$pid;
$id = $_GET['id'];
$part_array = [];

    if($pid!=''){
        if($id==0){
            $brands = $db->Execute("select b.brand_part_id,b.brand_name,ifnull(p.part_num,0) as part_num from fortech_brand_part b 
            left join (select brand_part_id,count(*) as part_num from fortech_part group by brand_part_id) p on b.brand_part_id=p.brand_part_id
            where b.product_id=".$pid." order by b.brand_name");
            //print_r($parts);
            foreach ($brands as $brand) {
                $arr[] = [
                    'id' => $brand['brand_part_id'],
                    'name' => $brand['brand_name'],
                    'show' => 1,
                    'level' => 1,
                    'text' => 'Add New Part',
                    'type' => ($brand['part_num']>0)?'mid':'leaf'
                ];
            }
			$parts = $db->Execute("select b.brand_part_id,b.brand_name,p.part_id,p.part_code from fortech_brand_part b left join fortech_part p on b.brand_part_id=p.brand_part_id where b.product_id=".$pid." order by b.brand_name,p.part_code");
            foreach ($parts as $part) {
				foreach ($arr as &$value){
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
			
        }
        
    }

//$str = '{"0":[{"id":1,"name":"APPLE","type":"leaf"},{"id":2,"name":"ACER","type":"mid"},{"id":3,"name":"HP","type":"leaf"}]}';

//$obj = json_decode($part_array);

    //$arr = array ('code'=>200,'data'=>$arr);
    echo json_encode($arr,JSON_UNESCAPED_UNICODE);


?>
