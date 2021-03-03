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

//$cPath = empty($_GET['cPath'])?'0':$_GET['cPath'];
$pbrand = $_GET['pbrand'];
$parts_array = [];

$parts = $db->Execute("select a.part_id,a.part_code from fortech_part a join fortech_brand_part b on a.brand_part_id=b.brand_part_id where b.brand_name='".$pbrand."' order by a.part_code");
foreach ($parts as $part) {
    $parts_array[] = [
        'name' => $part['part_code'],
        'value' => $part['part_code']
    ];
}


//$str = '{"0":[{"id":1,"name":"APPLE","type":"leaf"},{"id":2,"name":"ACER","type":"mid"},{"id":3,"name":"HP","type":"leaf"}]}';

//$obj = json_decode($part_array);

    //$arr = array ('code'=>200,'data'=>$part_array);
    echo json_encode($parts_array,JSON_UNESCAPED_UNICODE);


?>
