<?php
header('Content-type:application/json');
require('includes/application_top.php');
//$str = '[{"id":1,"name":"一级","open":true,"child":[{"id":5,"name":"二级","type":"leaf"},{"id":6,"name":"二级","child":[{"id":7,"name":"三级","type":"leaf"},{"id":9,"name":"三级","child":[{"id":10,"name":"四级","child":[{"id":11,"name":"五级","type":"leaf"},{"id":12,"name":"五级","type":"leaf"}]}]}]}]},{"id":2,"name":"一级","child":[{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":4,"name":"二级","pid":2,"type":"leaf"}]},{"id":8,"name":"一级","pid":0,"type":"leaf"}]';
//$arr = json_decode($str,true);
$pid = empty($_GET['pid'])?'0':$_GET['pid'];
//echo "pid:".$pid;
$id = $_GET['id'];
$part_array = [];
$type = $_GET['type'];
if($type=='createChild'){
    $name = $_GET['name'];
    if($id==0){
        $sql = "insert into fortech_brand_part (brand_name, product_id) values (
            '" . $name. "',
            '" . $pid . "')";
    }
    else{
        $sql = "insert into fortech_part (part_code, brand_part_id) values (
            '" . $name. "',
            '" . $id . "')";
    }
    $db->Execute($sql);
}
if($type=='delete') {
    $show = $_GET['show'];
    if($show==1){
        $sql = "delete from fortech_part where brand_part_id= " . $id;
        $db->Execute($sql);
        $sql = "delete from fortech_brand_part where brand_part_id= " . $id;
    }
    else{
        $sql = "delete from fortech_part where part_id= " . $id;
    }

    $db->Execute($sql);
}
if($type=='createChild'){
    $obj = new stdClass();//$oVal = (object)[];
    if($id==0){
        $brand = $db->Execute("select * from fortech_brand_part order by brand_part_id desc limit 1");
        $bd = $brand->fields;

        $obj->id = $bd['brand_part_id'];
        $obj->name = $bd['brand_name'];
        $obj->show = 1;
        $obj->level = 1;
        $obj->api = 'part';
        $obj->text = 'Add New Part';
        $obj->type = 'leaf';
    }
    else{
        $brand = $db->Execute("select * from fortech_part order by part_id desc limit 1");
        $bd = $brand->fields;

        $obj->id = $bd['part_id'];
        $obj->name = $bd['part_code'];
        $obj->show = 0;
        $obj->level = 2;
        $obj->api = 'part';
        $obj->type = 'leaf';
    }


    $arr = array ('code'=>200,'data'=>$obj);
    echo json_encode($arr,JSON_UNESCAPED_UNICODE);
}
else{
    if($pid!=''){
        if($id==0){
            $parts = $db->Execute("select b.brand_part_id,b.brand_name,ifnull(p.part_num,0) as part_num from fortech_brand_part b 
            left join (select brand_part_id,count(*) as part_num from fortech_part group by brand_part_id) p on b.brand_part_id=p.brand_part_id
            where b.product_id=".$pid." order by b.brand_part_id");
            //print_r($parts);
            foreach ($parts as $part) {
                $part_array[] = [
                    'id' => $part['brand_part_id'],
                    'name' => $part['brand_name'],
                    'show' => 1,
                    'level' => 1,
                    'text' => 'Add New Part',
                    'type' => ($part['part_num']>0)?'mid':'leaf'
                ];
            }
        }
        else{
            $parts = $db->Execute("select part_id,part_code from fortech_part where brand_part_id=".$id);
            foreach ($parts as $part) {
                $part_array[] = [
                    'id' => $part['part_id'],
                    'name' => $part['part_code'],
                    'show' => 0,
                    'level' => 2,
                    'type' => 'leaf'
                ];
            }
        }
    }

//$str = '{"0":[{"id":1,"name":"APPLE","type":"leaf"},{"id":2,"name":"ACER","type":"mid"},{"id":3,"name":"HP","type":"leaf"}]}';

//$obj = json_decode($part_array);

    $arr = array ('code'=>200,'data'=>$part_array);
    echo json_encode($arr,JSON_UNESCAPED_UNICODE);
}

?>