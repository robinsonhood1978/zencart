<?php
/**
 * Common Template - tpl_footer.php
 *
 * this file can be copied to /templates/your_template_dir/pagename<br />
 * example: to override the privacy page<br />
 * make a directory /templates/my_template/privacy<br />
 * copy /templates/templates_defaults/common/tpl_footer.php to /templates/my_template/privacy/tpl_footer.php<br />
 * to override the global settings and turn off the footer un-comment the following line:<br />
 * <br />
 * $flag_disable_footer = true;<br />
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
require(DIR_WS_MODULES . zen_get_module_directory('footer.php'));
?>

<?php
if (!isset($flag_disable_footer) || !$flag_disable_footer) {
?>

<!--bof-navigation display -->
<div id="navSuppWrapper">
<div id="navSupp">
<ul>
<li><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'; ?><?php echo HEADER_TITLE_CATALOG; ?></a></li>
<?php if (EZPAGES_STATUS_FOOTER == '1' or (EZPAGES_STATUS_FOOTER == '2' && zen_is_whitelisted_admin_ip())) { ?>
<?php require($template->get_template_dir('tpl_ezpages_bar_footer.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_ezpages_bar_footer.php'); ?>
<?php } ?>
</ul>
</div>
</div>
<!--eof-navigation display -->

<!--bof-ip address display -->
<?php
if (SHOW_FOOTER_IP == '1') {
?>
<div id="siteinfoIP"><?php echo TEXT_YOUR_IP_ADDRESS . '  ' . $_SERVER['REMOTE_ADDR']; ?></div>
<?php
}
?>
<!--eof-ip address display -->

<!--bof-banner #5 display -->
<?php
  if (SHOW_BANNERS_GROUP_SET5 != '' && $banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET5)) {
    if ($banner->RecordCount() > 0) {
?>
<div id="bannerFive" class="banners"><?php echo zen_display_banner('static', $banner); ?></div>
<?php
    }
  }
?>
<!--eof-banner #5 display -->

<!--bof- site copyright display -->
<div id="siteinfoLegal" class="legalCopyright"><?php echo FOOTER_TEXT_BODY; ?></div>
<!--eof- site copyright display -->

<?php
} // flag_disable_footer
?>

<?php if (false || (isset($showValidatorLink) && $showValidatorLink == true)) { ?>
<a href="https://validator.w3.org/nu/?doc=<?php echo urlencode('http' . ($request_type == 'SSL' ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . (strstr($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . zen_session_name() . '=' . zen_session_id()); ?>" rel="noopener" target="_blank">VALIDATOR</a>
<?php } ?>

<script type="text/javascript">
	function parseDom(arg) {

                	var objE = document.createElement("div");

                	objE.innerHTML = "<div>"+arg+"</div>";

                	return objE.childNodes;

                }
	function getTables(json){
		var html = "";
		for(var p in json){//遍历json数组时，这么写p为索引，0,1

		  html += getTable(json[p]);

		}
		//var html = JSON.stringify(json);
		//console.log(html);
		return html;
	}
	function getModelTables(json){
		var html = "";
		for(var p in json){
		  html += getModelTable(json[p]);
		}
		return html;
	}
	function getTable(str){
		//console.log(str['name']);
		var table = "";
		//var header = getTableHeader(str['name']);
        table += '<table width="100%">\n' +
            '<tbody>\n' +
            '<tr width="100%"><td width="25%"><li><font color="green"><b>'+str['name']+'</b></font></li></td></tr>\n';

        table += '</tbody>\n' +
            '</table>';
		var rows = (str['child']==null)?'':getTableRows(str['child']);
		table += rows;
		return table;
	}
    function getSeries(child){
        var str = '';

        for(var i = 0; i < child.length; i=i+2){
            let body = '<td width="25%" nowrap=""><div id="series_'+i+'" onclick="javascript:rowSelectEffect(this);" class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">[part0]</div></td>' +
                '<td width="25%" nowrap=""><div id="series_'+(i+1)+'" onclick="javascript:rowSelectEffect(this);" class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">[part1]</div></td>';
            let name0 = (child[i]==null)?'':child[i].name;
            let name1 = (child[i+1]==null)?'':child[i+1].name;
            body = body.replaceAll('[part0]',name0);
            body = body.replaceAll('[part1]',name1);
            str += body;
        }
        return str;
    }
	function getModelTable(str){
		//console.log(str['name']);
		var table = "";

        table += '<table width="100%">\n' +
            '<tbody>\n' +
            '<tr width="100%"><td width="25%"><li><font color="green"><b>'+str['name']+'</b></font></li></td></tr>\n' +
            '<tr width="100%">\n';
        let series = getSeries(str['child']);
        table += series;
        table += '</tr>\n' +
            '</tbody>\n' +
            '</table>';

		for(let i = 0; i < str['child'].length; i++){
			//let header = getTableHeader(str['child'][i]['name']);
			let rows = (str['child'][i]['child']==null)?'':getTableRows(str['child'][i]['child']);
			table += '<div id="tmodel_series_'+i+'" class="tmodel" style="display:none">'+rows+'</div>';
		}
		
		return table;
	}
    function rowOverEffect(object) {
        if (object.className == 'moduleRow') object.className = 'moduleRowOver';
    }

    function rowOutEffect(object) {
        if (object.className == 'moduleRowOver' || object.className == 'moduleRowSelected') object.className = 'moduleRow';
    }

    function rowSelectEffect(object) {
        object.className = 'moduleRowSelected';
        $('.tmodel').hide();
        $('#tmodel_'+$(object).attr("id")).show();
        //console.log($(object).attr("id"));
    }
	function getTableHeader(name)
	{
		var html = '<div class="row header"><div class="cell">'+name+'</div><div class="cell"></div><div class="cell"></div><div class="cell"></div></div>';
		return html;
	}
	function getTableRows(child){
		var str = '';

		for(var i = 0; i < child.length; i=i+4){
            let body = '<div class="row"><div class="cell">[part0]</div><div class="cell">[part1]</div><div class="cell">[part2]</div><div class="cell">[part3]</div></div>';
            let name0 = (child[i]==null)?'':child[i].name;
			let name1 = (child[i+1]==null)?'':child[i+1].name;
			let name2 = (child[i+2]==null)?'':child[i+2].name;
			let name3 = (child[i+3]==null)?'':child[i+3].name;
			body = body.replaceAll('[part0]',name0);
			body = body.replaceAll('[part1]',name1);
			body = body.replaceAll('[part2]',name2);
			body = body.replaceAll('[part3]',name3);
			str += body;
		}
		return str;
	}
	function addPart(json){
		var sourceNode = document.getElementById("part_table"); // 获得被克隆的节点对象 
		//var clonedNode = sourceNode.cloneNode(true); // 克隆节点 
		//$node = $(clonedNode);
		//var html = $node.html().replaceAll('[brand]','Apple'); 
		var html = getTables(json);
		console.log(html);
		var newnode = parseDom(html);
		sourceNode.parentNode.appendChild($(newnode)[0]); // 在父节点插入克隆的节点 
	}
	function addModel(json){
		var sourceNode = document.getElementById("model_table"); 
		var html = getModelTables(json);
		console.log(html);
		var newnode = parseDom(html);
		sourceNode.parentNode.appendChild($(newnode)[0]); // 在父节点插入克隆的节点 
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
    var pid = getQueryVariable("products_id");
    if(pid!=''){
        console.log("pid:"+pid);
        //预加载数据格式
        var part_url = './fpart_api.php?pid='+pid;
        $.getJSON(part_url, function(json){
            if(json) {
                addPart(json);
            }
        });
        var model_url = './fmodel_api.php?pid='+pid;
        $.getJSON(model_url, function(json){
            if(json){
                addModel(json);
            }

        });
    }

	/*$("#ajaxModel").LuTree({
			arr: json,
			sign:true,
			simIcon: "fa fa-file-o",//叶子图标
			mouIconOpen: " fa fa-folder-open",//展开图标
			mouIconClose:"fa fa-folder",//关闭图标
			callback: function(id) {
				console.log("你选择的id是" + id);
			}
		});*/
	/*
	$.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function(arr){
                $("#ajaxModel").LuTree({
					arr: arr,
					sign:true,
					simIcon: "fa fa-file-o",//叶子图标
					mouIconOpen: " fa fa-folder-open",//展开图标
					mouIconClose:"fa fa-folder",//关闭图标
					callback: function(id) {
						console.log("你选择的id是" + id);
					}
				});
            }
        });*/

    //console.log(JSON.stringify(arr));
    //无限级菜单生成
    


    //可操作异步加载数据生成无限级树形菜单

    //无限级请求节点下的菜单
    /* $("#ajaxPart").LuAjaxTree({
        url:'./fpartapi.php?pid='+pid,
        simIcon: "fa fa-file-o",//叶子图标
        Demo:false,//模式，是否在服务器环境，true是演示模式，不需要后台，false是需要后台配合的使用模式
        Method: "GET",//请求方法
        mouIconOpen: " fa fa-folder-open",//展开图标
        mouIconClose:"fa fa-folder",//关闭图标
        callback: function(id) {
            console.log("你选择的id是" + id);
        }
    });

    $("#ajaxModel").LuAjaxTree({
        url:'./fmodelapi.php?pid='+pid,
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
