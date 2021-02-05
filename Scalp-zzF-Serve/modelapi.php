<?php
header('Content-type:application/json');
require('includes/application_top.php');
//$str = '[{"id":1,"name":"一级","open":true,"child":[{"id":5,"name":"二级","type":"leaf"},{"id":6,"name":"二级","child":[{"id":7,"name":"三级","type":"leaf"},{"id":9,"name":"三级","child":[{"id":10,"name":"四级","child":[{"id":11,"name":"五级","type":"leaf"},{"id":12,"name":"五级","type":"leaf"}]}]}]}]},{"id":2,"name":"一级","child":[{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":3,"name":"二级","pid":2,"type":"leaf"},{"id":4,"name":"二级","pid":2,"type":"leaf"}]},{"id":8,"name":"一级","pid":0,"type":"leaf"}]';
//$arr = json_decode($str,true);
$pid = empty($_GET['pid'])?'0':$_GET['pid'];
$id = $_GET['id'];
$part_array = [];
$type = $_GET['type'];
$level = $_GET['level'];
if($type=='createChild'){
    $name = $_GET['name'];
    if($id==0){
        $sql = "insert into fortech_brand_series (brand_name, product_id) values (
            '" . $name. "',
            '" . $pid . "')";
    }
    else{
        if($level==1){
            $sql = "insert into fortech_series_model (series_code, brand_series_id) values (
            '" . $name. "',
            '" . $id . "')";
        }
        else{
            $sql = "insert into fortech_model (model_code, series_model_id) values (
            '" . $name. "',
            '" . $id . "')";
        }
    }
    $db->Execute($sql);
}
if($type=='delete') {
    $show = $_GET['show'];
    if($show==1){
        if($level==2){
            $sql = "delete from fortech_model where series_model_id= " . $id;
            $db->Execute($sql);
            $sql = "delete from fortech_series_model where series_model_id= " . $id;
        }
        else if ($level==1){
            $sql = "delete from fortech_model where series_model_id in (select series_model_id from fortech_series_model where brand_series_id= " . $id . ")";
            $db->Execute($sql);
            $sql = "delete from fortech_series_model where brand_series_id= " . $id;
            $db->Execute($sql);
            $sql = "delete from fortech_brand_series where brand_series_id= " . $id;
        }
    }
    else{
        if($level==3){
            $sql = "delete from fortech_model where model_id= " . $id;
        }

    }

    $db->Execute($sql);
}
if($type=='createChild'){
    $obj = new stdClass();//$oVal = (object)[];
    if($id==0){
        $brand = $db->Execute("select * from fortech_brand_series order by brand_series_id desc limit 1");
        $bd = $brand->fields;

        $obj->id = $bd['brand_series_id'];
        $obj->name = $bd['brand_name'];
        $obj->show = 1;
        $obj->level = 1;
        $obj->api = 'model';
        $obj->text = 'Add New Series';
        $obj->type = 'leaf';
    }
    else{
        if($level==1){
            $brand = $db->Execute("select * from fortech_series_model order by series_model_id desc limit 1");
            $bd = $brand->fields;

            $obj->id = $bd['series_model_id'];
            $obj->name = $bd['series_code'];
            $obj->show = 1;
            $obj->level = 2;
            $obj->api = 'model';
            $obj->text = 'Add New Models';
            $obj->type = 'leaf';
        }
        else{
            $brand = $db->Execute("select * from fortech_model order by model_id desc limit 1");
            $bd = $brand->fields;

            $obj->id = $bd['model_id'];
            $obj->name = $bd['model_code'];
            $obj->show = 0;
            $obj->level = 3;
            $obj->api = 'model';
            $obj->type = 'leaf';
        }

    }


    $arr = array ('code'=>200,'data'=>$obj);
    echo json_encode($arr,JSON_UNESCAPED_UNICODE);
}
else{
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
}

?>