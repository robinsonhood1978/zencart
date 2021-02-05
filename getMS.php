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
$mbrand = $_GET['mbrand'];
$series_array = [];

$series = $db->Execute("select distinct a.series_code from fortech_series_model a join fortech_brand_series b on a.brand_series_id=b.brand_series_id where b.brand_name='".$mbrand."'");
foreach ($series as $ser) {
    $series_array[] = [
        'name' => $ser['series_code']
    ];
}


//$str = '{"0":[{"id":1,"name":"APPLE","type":"leaf"},{"id":2,"name":"ACER","type":"mid"},{"id":3,"name":"HP","type":"leaf"}]}';

//$obj = json_decode($part_array);

    //$arr = array ('code'=>200,'data'=>$part_array);
    echo json_encode($series_array,JSON_UNESCAPED_UNICODE);


?>
