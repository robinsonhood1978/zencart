<?php
header('Content-type:application/json');
require('includes/application_top.php');


//$str = '[{"id":1,"name":"一级","open":true,"child":[{"id":5,"name":"二级","type":"leaf"},{"id":6,"name":"二级","child":[{"id":7,"name":"三级","type":"leaf"},{"id":9,"name":"三级","child":[{"id":10,"name":"四级","child":[{"id":11,"name":"五级","type":"leaf"},{"id":12,"name":"五级","type":"leaf"}]}]}]}]},{"id":2,"name":"一级","child":[{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":4,"name":"二级","pid":2,"type":"leaf"}]},{"id":8,"name":"一级","pid":0,"type":"leaf"}]';
//$arr = json_decode($str,true);
$id = empty($_GET['id'])?0:$_GET['id'];
//echo "pid:".$pid;
$level = $_GET['level'];
$code = 0;
if($id!=0){
    if($level==1){
        $sql = "delete from fortech_model where series_model_id in (select series_model_id from fortech_series_model where brand_series_id= " . $id . ")";
        $db->Execute($sql);
        $sql = "delete from fortech_series_model where brand_series_id= " . $id;
        $db->Execute($sql);
        $sql = "delete from fortech_brand_series where brand_series_id= " . $id;
        $db->Execute($sql);
        $code = 1;
    }
    else if($level==2){
        $db->Execute("delete from fortech_model where series_model_id=".$id);
        $db->Execute("delete from fortech_series_model where series_model_id=".$id);
        $code = 1;
    }
    else if($level==3){
        $db->Execute("delete from fortech_model where model_id=".$id);
        $code = 1;
    }
}


$arr = array ('code'=>$code);
echo json_encode($arr,JSON_UNESCAPED_UNICODE);


?>