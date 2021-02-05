<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 21 Modified in v1.5.7 $
 */
require('includes/application_top.php');
$action = (isset($_GET['action']) ? $_GET['action'] : '');
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
$product_type = (isset($_POST['product_type']) ? $_POST['product_type'] : (isset($_GET['pID']) ? zen_get_products_type($_GET['pID']) : 1));
$type_handler = $zc_products->get_admin_handler($product_type);
$zco_notifier->notify('NOTIFY_BEGIN_ADMIN_PRODUCTS', $action);

if (zen_not_null($action)) {
  switch ($action) {

    case 'insert_product_meta_tags':
    case 'update_product_meta_tags':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php');
      } else {
        require(DIR_WS_MODULES . 'update_product_meta_tags.php');
      }
      break;
    case 'insert_product':
    case 'update_product':
      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php');
      } else {
        require(DIR_WS_MODULES . 'update_product.php');
      }
      break;
    case 'new_product_preview':
      if (!isset($_POST['master_categories_id'])
          || ((isset($_POST['products_model']) ? $_POST['products_model'] : '') . (isset($_POST['products_url']) ? implode('', $_POST['products_url']) : '') . (isset($_POST['products_name']) ? implode('', $_POST['products_name']) : '') . (isset($_POST['products_description']) ? implode('', $_POST['products_description']) : '') == '')
      ) {
          $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
          zen_redirect(zen_href_link(FILENAME_PRODUCT, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product'));
//          zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '')));
      }

      if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php')) {
        require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php');
      } else {
        require(DIR_WS_MODULES . 'new_product_preview.php');
      }
      break;
    case 'new_product_preview_meta_tags':
      if (!isset($_POST['products_price_sorter']) || !isset($_POST['products_model'])) {
          $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
          zen_redirect(zen_href_link(FILENAME_PRODUCT, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_meta_tags'));
      }
      break;
  }
}

// check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
  if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  }
} else {
  $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}
$tax_class_array = array(array(
    'id' => '0',
    'text' => TEXT_NONE));
$tax_class = $db->Execute("SELECT tax_class_id, tax_class_title
                           FROM " . TABLE_TAX_CLASS . "
                           ORDER BY tax_class_title");
foreach ($tax_class as $item) {
  $tax_class_array[] = array(
    'id' => $item['tax_class_id'],
    'text' => $item['tax_class_title']);
}

$languages = zen_get_languages();
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php
    require DIR_WS_INCLUDES . 'admin_html_head.php';
    if ($action != 'new_product_meta_tags' && $editor_handler != '') {
      include ($editor_handler);
    }
    ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <!-- body_text //-->
    <?php
    if ($action == 'new_product_meta_tags') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info_metatags.php');
    } elseif ($action == 'new_product') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info.php');
    } elseif ($action == 'new_product_preview_meta_tags') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info_meta_tags.php');
    } elseif ($action == 'new_product_preview') {
      require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info.php');
    }
    ?>
    <!-- body_text_eof //-->
    <!-- body_eof //-->
    <!-- script for datepicker -->
    <script>
      $(function () {
        $('input[name="products_date_available"]').datepicker();
      })
    </script>
    <script type="text/javascript">
        var pid = getQueryVariable("pID");
        function DelPicture(i){
            layer.confirm('Data cannot be recovered after being deleted. Are you sure to delete it?', {
                title: ['Confirm', 'font-size:18px;'],
                btn: ['Yes','Cancel'] //按钮
            }, function(){
               var url = './delpic.php?id='+pid+'&img='+i;
                $.getJSON(url, function(json){
                    if(json) {
                        if(json.code==1){
                            //console.log('success');
                            layer.msg('Success', {icon: 1});
                            if(i==1){
                                $("input[name=products_previous_image]").val('');
                            }
                            else{
                                $("input[name=products_previous_image"+i+"]").val('');
                            }
                            $('#img'+i).empty();

                        }
                        else{
                            layer.msg('Fail', {icon: 1});
                        }
                    }
                });

            }, function(){
                layer.msg('Cancelled', {icon: 1});
            });

        }
        function DelPartNode(id,level){
            layer.confirm('Data cannot be recovered after being deleted. Are you sure to delete it?', {
                title: ['Confirm', 'font-size:18px;'],
                btn: ['Yes','Cancel'] //按钮
            }, function(){
                var part_url = './delpartapi.php?level='+level+'&id='+id;
                $.getJSON(part_url, function(json){
                    if(json) {
                        if(json.code==1){
                            //console.log('success');
                            layer.msg('Success', {icon: 1});
                            if(level==1){
                                $(".my-brand_"+id).hide();
                            }
                            else if (level==2){
                                $(".my-part_"+id).hide();
                            }
                        }
                        else{
                            //console.log('fail');
                            layer.msg('Fail', {icon: 1});
                        }
                    }
                });

            }, function(){
                layer.msg('Cancelled', {icon: 1});
            });

        }
        function DelModelNode(id,level){
            layer.confirm('Data cannot be recovered after being deleted. Are you sure to delete it?', {
                title: ['Confirm', 'font-size:18px;'],
                btn: ['Yes','Cancel'] //按钮
            }, function(){
                var part_url = './delmodelapi.php?level='+level+'&id='+id;
                $.getJSON(part_url, function(json){
                    if(json) {
                        if(json.code==1){
                            //console.log('success');
                            layer.msg('Success', {icon: 1});
                            if(level==1){
                                $(".my-model-brand_"+id).hide();
                            }
                            else if (level==2){
                                $(".my-model-series_"+id).hide();
                            }
                            else if (level==3){
                                $(".my-model_"+id).hide();
                            }
                        }
                        else{
                            //console.log('fail');
                            layer.msg('Fail', {icon: 1});
                        }
                    }
                });

            }, function(){
                layer.msg('Cancelled', {icon: 1});
            });

        }

        function getQueryVariable(variable){
            let query = window.location.search.substring(1);
            let vars = query.split("&");
            for (let i=0;i<vars.length;i++) {
                let pair = vars[i].split("=");
                if(pair[0] == variable){return pair[1];}
            }
            return 0;
        }
        function parseDom(arg) {

            var objE = document.createElement("div");

            objE.innerHTML = "<div>"+arg+"</div>";

            return objE.childNodes;

        }
        function getTables(json){
           var html = "<table><tbody>";
            for(var p in json){//遍历json数组时，这么写p为索引，0,1

                html += getTable(json[p]);

            }
            html += "</tbody></table>";
            //var html = JSON.stringify(json);
            //console.log(html);
            return html;
        }
        function getModelTables(json){
            var html = "<table><tbody>";
            for(var p in json){
                html += getModelTable(json[p]);
            }
            html += "</tbody></table>";
            return html;
        }
        function getTable(str){
            //console.log(str['name']);
            var table = "";
            var header = getTableHeader(str);
            var rows = (str['child']==null)?'':getTableRows(str['child'],str['id']);
            table = header+rows;
            return table;
        }
        function getModelTable(str){
            console.log(str['name']);
            var table = "";
            var header = getModelBrand(str);
            table += header;
            for(var i = 0; i < str['child'].length; i++){
                var series = getModelSeries(str['child'][i],str['id']);
                var rows = (str['child'][i]['child']==null)?'':getModels(str['child'][i]['child'],str['id'],str['child'][i]['id']);

                table += series+rows;
            }


            return table;
        }
        function getTableHeader(str)
        {
            var html = '<tr class="my-brand_'+str['id']+'"><td></td><td><input type="text" name="p_brand_'+str['id']+'" value="'+str['name']+'" size="21" maxlength="20"></td><td><img src="images/icon_delete.gif" onclick="DelPartNode('+str['id']+',1);"></td></tr>';
            return html;
        }
        function getModelBrand(str)
        {
            var html = '<tr class="my-model-brand_'+str['id']+'"><td></td><td><input type="text" name="p_model_brand_'+str['id']+'" value="'+str['name']+'" size="21" maxlength="20"></td><td><img src="images/icon_delete.gif" onclick="DelModelNode('+str['id']+',1);"></td></tr>';
            return html;
        }
        function getModelSeries(str,brand_id)
        {
            var html='<tr class="my-model-brand_'+brand_id+' my-model-series_'+str['id']+'"><td></td><td></td><td><img src="images/pixel_trans.gif" border="0" alt="" width="24" height="15">&nbsp;<input type="text" name="m_seriescode_'+brand_id+'_'+str['id']+'" value="'+str['name']+'" size="31" maxlength="30"></td><td onclick="DelModelNode('+str['id']+',2);"><img src="images/icon_delete.gif"></td></tr>';
            return html;
        }
        function getTableRows(child, brand_id){
            var str = '';
            var body = '<tr class="my-brand_'+brand_id+' my-part_[#partid]"><td></td><td><img src="images/pixel_trans.gif" border="0" alt="" width="24" height="15">&nbsp;<input type="text" name="p_partcode_[#partid]" value="[#partcode]" size="51" maxlength="50"></td><td onclick="DelPartNode([#partid],2);"><img src="images/icon_delete.gif"></td></tr>';
            for(var i = 0; i < child.length; i=i+1){
                row = body;
                let id = (child[i]==null)?'':child[i].id;
                let name = (child[i]==null)?'':child[i].name;
                row = row.replaceAll('[#partid]',id);
                row = row.replaceAll('[#partcode]',name);
                str += row;
            }
            return str;
        }
        function getModels(child, brand_id,series_id){
            var str = '';
            var body = '<tr class="my-model-brand_'+brand_id+' my-model-series_'+series_id+' my-model_[#modelid]"><td></td><td></td><td></td><td><img src="images/pixel_trans.gif" border="0" alt="" width="24" height="15">&nbsp;<input type="text" name="m_modelcode_'+brand_id+'_'+series_id+'_[#modelid]" value="[#modelname]" size="31" maxlength="30"></td><td onclick="DelModelNode([#modelid],3);"><img src="images/icon_delete.gif"></td></tr>';
            for(var i = 0; i < child.length; i=i+1){
                row = body;
                let id = (child[i]==null)?'':child[i].id;
                let name = (child[i]==null)?'':child[i].name;
                row = row.replaceAll('[#modelid]',id);
                row = row.replaceAll('[#modelname]',name);
                str += row;
            }
            return str;
        }
        function addParts(json){
            var sourceNode = document.getElementById("part_table"); // 获得被克隆的节点对象
            //var clonedNode = sourceNode.cloneNode(true); // 克隆节点
            //$node = $(clonedNode);
            //var html = $node.html().replaceAll('[brand]','Apple');
            var html = getTables(json);
            console.log(html);
            var newnode = parseDom(html);
            sourceNode.parentNode.appendChild($(newnode)[0]); // 在父节点插入克隆的节点
            console.log("robin test");
        }
        function addModel(json){
            var sourceNode = document.getElementById("model_table");
            var html = getModelTables(json);
            console.log(html);
            var newnode = parseDom(html);
            sourceNode.parentNode.appendChild($(newnode)[0]); // 在父节点插入克隆的节点
        }



        if(pid!=''){
            console.log("robin pid:"+pid);
            //预加载数据格式
            var part_url = '../fpart_api.php?pid='+pid;
            $.getJSON(part_url, function(json){

                if(json) {
                    addParts(json);
                }
            });
            var model_url = '../fmodel_api.php?pid='+pid;
            $.getJSON(model_url, function(json){
                if(json){
                    addModel(json);
                }

            });
        }
        function D2Array(iRows,iCols)
        {
            var i;
            var j;
            var a = new Array(iRows);
            for (i=0; i < iRows; i++)
            {
                a[i] = new Array(iCols);
                for (j=0; j < iCols; j++)
                {
                    a[i][j] = 1;
                }
            }
            return(a);
        }

        function D3Array(iRows,iCols,iDeps)
        {
            var i;
            var j;
            var k;
            var a = new Array(iRows);
            for (i=0; i < iRows; i++)
            {
                a[i] = new Array(iCols);
                for (j=0; j < iCols; j++)
                {
                    a[i][j] = new Array(iDeps);
                    for (k=0; k < iDep; k++) {
                        a[i][j][m] = 1;
                    }
                }
            }
            return(a);
        }
        var pn = new Array();
        var mn = new D2Array(20,20);
        var sn = new Array();
        var m;
        for(m=1;m<20;m++)
        {
            sn[m]=1;
            pn[m]=1;
        }

        function addbrand(id){
            var row,cell,str,i;
            var brandNum = document.forms["new_product"].p_brand_num.value;

            if(navigator.userAgent.indexOf("Firefox")>0){
                row = eval("document.getElementById("+'"'+id+'"'+")").insertRow(-1);
            }
            else{
                row = eval("document.all["+'"'+id+'"'+"]").insertRow();
            }



            if(row != null ){
                for(i=1;i<=brandNum;i++)
                {
                    if(navigator.userAgent.indexOf("Firefox")>0)
                        cell = row.insertCell(-1);
                    else{
                        cell = row.insertCell();
                    }

                    str="<table><tr><td>Brand #"+i+"<input type="+'"'+"text"+'"'+" name="+'"'+"p_brand"+i+'"'+"><br><input type="+'"'+"button"+'"'+" value="+'"'+"Add 1 Part to Brand #"+i+'"'+" onclick='addpart("+'"'+"tb2"+'"'+","+i+");'></td></tr></table>"
                    cell.innerHTML=str;
                }
            }

        }

        function addbrand2(id){
            var row,cell,str,i;
            var brandNum = document.forms["new_product"].m_brand_num.value;

            if(navigator.userAgent.indexOf("Firefox")>0)
                row = eval("document.getElementById("+'"'+id+'"'+")").insertRow(-1);
            else{
                row = eval("document.all["+'"'+id+'"'+"]").insertRow();
            }

            if(row != null ){
                for(i=1;i<=brandNum;i++)
                {
                    if(navigator.userAgent.indexOf("Firefox")>0)
                        cell = row.insertCell(-1);
                    else{
                        cell = row.insertCell();
                    }

                    str="<table><tr><td>Brand #"+i+"<input type="+'"'+"text"+'"'+" name="+'"'+"m_brand"+i+'"'+"><br><input type="+'"'+"button"+'"'+" value="+'"'+"Add 1 Series to Brand #"+i+'"'+" onclick='addseries2("+'"'+"tb4"+'"'+","+i+");'></td></tr></table>"
                    cell.innerHTML=str;
                }
            }
        }


        function addseries2(id,bnum){
            var row,cell,str,i;

            if(navigator.userAgent.indexOf("Firefox")>0)
                row = eval("document.getElementById("+'"'+id+'"'+")").insertRow(-1);
            else{
                row = eval("document.all["+'"'+id+'"'+"]").insertRow();
            }

            if(row != null ){
                if(navigator.userAgent.indexOf("Firefox")>0)
                    cell = row.insertCell(-1);
                else{
                    cell = row.insertCell();
                }
                str="<table><tr><td>Brand #"+bnum+" Series #"+sn[bnum]+"<input type="+'"'+"text"+'"'+" name="+'"'+"modelb"+bnum+"_models"+sn[bnum]+'"'+"><br><input type="+'"'+"button"+'"'+" value="+'"'+"Add 1 Model to Brand #"+bnum+" Series #"+sn[bnum]+'"'+" onclick='addmodel("+'"'+"tb44"+'"'+","+bnum+","+sn[bnum]+");'><br>File:<input type='file' name='modelb"+bnum+"_models"+sn[bnum]+"__file' size=\"10\" ></td></tr></table>"
                cell.innerHTML=str;
                sn[bnum]++;
            }

        }


        function addmodel(id,bnum,snum){
            var row,cell,str;

            if(navigator.userAgent.indexOf("Firefox")>0)
                row = eval("document.getElementById("+'"'+id+'"'+")").insertRow(-1);
            else{
                row = eval("document.all["+'"'+id+'"'+"]").insertRow();
            }

            if(row != null ){
                if(navigator.userAgent.indexOf("Firefox")>0)
                    cell = row.insertCell(-1);
                else{
                    cell = row.insertCell();
                }

                str="Brand #"+bnum+" Series #"+snum+" Model #"+mn[bnum][snum]+": <input type="+'"'+"text"+'"'+" name="+'"'+"modelb"+bnum+"_models"+snum+"_modelm"+mn[bnum][snum]+'"'+">"
                cell.innerHTML=str;
                mn[bnum][snum]++;

            }
        }


        function addpart(id,bnum){

            var row,cell,str;

            if(navigator.userAgent.indexOf("Firefox")>0)
                row = eval("document.getElementById("+'"'+id+'"'+")").insertRow(-1);
            else{
                row = eval("document.all["+'"'+id+'"'+"]").insertRow();
            }

            if(row != null ){
                if(navigator.userAgent.indexOf("Firefox")>0)
                    cell = row.insertCell(-1);
                else{
                    cell = row.insertCell();
                }

                str="Brand #"+bnum+" Part #"+pn[bnum]+": <input type="+'"'+"text"+'"'+" name="+'"'+"part"+bnum+"_"+pn[bnum]+'"'+">"
                cell.innerHTML=str;
                pn[bnum]++;

            }
        }


        //可操作异步加载数据生成无限级树形菜单

       /* $("#updateUl").LuUpdateTree({
            url:'./partapi.php?pid='+pid,
            simIcon: "fa fa-file-o",//叶子图标
            Demo:false,//模式，是否在服务器环境，true是演示模式，不需要后台，false是需要后台配合的使用模式
            Method: "GET",//请求方法
            mouIconOpen: " fa fa-folder-open",//展开图标
            mouIconClose:"fa fa-folder",//关闭图标
            callback: function(id) {
                console.log("你选择的id是" + id);
            }
        });

       $("#modelUl").ModelUpdateTree({
            url2:'./modelapi.php?pid='+pid,
            simIcon: "fa fa-file-o",//叶子图标
            Demo:false,//模式，是否在服务器环境，true是演示模式，不需要后台，false是需要后台配合的使用模式
            Method: "GET",//请求方法
            mouIconOpen: " fa fa-folder-open",//展开图标
            mouIconClose:"fa fa-folder",//关闭图标
            callback: function(id) {
                console.log("你选择的id是" + id);
            }
        });*/




        </script>
        <!-- footer //-->
        <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
