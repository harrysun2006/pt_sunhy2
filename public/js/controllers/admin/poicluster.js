// 关键词的缺省POI，这也是合并的目标
var g_def_pid = -1;

// 记录合并动作的数组
// 由于允许用户中途切换中心POI，这里既要记录合并的源，也要记录合并的目标。
// 
// 被合并的PID存储在数组下标中，合并目标存储在元素值中，eg.
// g_merges[from_pid] = to_pid
var g_merges = new Array();
// [pid][attr]=val
var g_poi_basic = new Array();
// [pid][attr]=val
var g_poi_extra = new Array();

// map
var g_gmap = null;
var g_markers = new Array();
var g_ignore_mouseover = false; // if true, the mouseover/out events of gmarker are ignored

// extract a param from url
// return string(maybe empty).
function get_url_param(url, name)
{
  url = decodeURIComponent(url);
  var i = url.indexOf("?" + name + "=");
  if(i == -1) i = url.indexOf("&" + name + "=");
  if(i != -1)
  {
    var s = url.substr(i + name.length + 2);
    i = s.indexOf('&');
    if(i != -1)
      s = s.substr(0, i);
    return s;
  }
  return '';
}

function goto_page()
{
  var idx = $('input#page_idx').val();
  var url = document.location.href;
  if(url.indexOf('?page=') != -1 || url.indexOf('&page=') != -1)
  {
    url = url.replace(/([\?&])page=[0-9]+/, '$1page=' + idx);
  }
  else
  {
    if(url.indexOf('?') != -1)
      url += '&page=' + idx;
    else
      url += '?page=' + idx;
  }
  window.open(url, '_self');
}

function init()
{
  map_init();
  map_load_poi(true);

  // 设定“删除关键词”事件句柄
  $('span.del_keyword').each(function(index, tag) {
    $(tag).bind("click", function(event){
      var keyw = $(tag).attr("rel");
      if(confirm("将删除所有 " + keyw + "，确定吗？"))
      {
        $.get(BETTER_ADMIN_URL + "/poicluster/delkeyword?w=" + keyw + "&r=" + Math.random(),
        function(data) {
          alert(data + " 项已删除");
          if(parseInt(data) > 0)
            window.location.reload();
        });
      }
    });
  });

  // 设置 HTML TAG 尺寸, 滚动的是列表，而非整个页面
  var client_height = (typeof window.innerHeight != 'undefined' ? window.innerHeight : document.body.clientHeight);
  document.getElementById('main_tbl').style.height = (client_height - document.getElementById('header').offsetHeight) + 'px';
  //alert(client_height + ' - ' + document.getElementById('header').offsetHeight + ' = ' + document.getElementById('main_tbl').style.height);
}

// 让用户输入搜索参数，并执行搜索
function keyword_custom_search()
{
  // 基于地图的当前状态初始化搜索范围
  var lat = round_float(g_gmap.getCenter().lat(), 6);
  var lon = round_float(g_gmap.getCenter().lng(), 6);
  var radius = Math.round(g_gmap.getBounds().getSouthWest().distanceFrom(g_gmap.getBounds().getNorthEast()) / 2);
  var keyw = get_url_param(document.location.href, 'namekeyword');
  if(keyw == '') keyw = '花园';
  
  var s = prompt("请按下列格式输入:\n" 
    + "关键词[,纬度,经度,搜索半径]\n"
    + "方括号表示可选内容，注意分隔符为半角逗号\n"
    + "示例，你可以输入\n"
    + "\t" + keyw + "," + lat + "," + lon + "," + radius + "\n"
    + "也可以输入\n"
    + "\t" + keyw,
    keyw/* + "," + lat + "," + lon + "," + radius*/); /* 为了方便用户用不同搜索级别尝试同一关键词，默认输入应该不包含可选参数 */
  if(s == null) return;
  var a = s.split(',');
  if(a.length == 1)
  {
    keyw = $.trim(s);
  }
  else if(a.length == 4)
  {
    keyw = $.trim(a[0]);
    lat = $.trim(a[1]);
    lon = $.trim(a[2]);
    radius = $.trim(a[3]);

    if(isNaN(lat) || isNaN(lon) || isNaN(radius))
    {
      alert("纬度、经度、搜索半径 应该是数值");
      return;
    }
  }
  else
  {
    alert("无效输入");
    return;
  }

  var max_radius = 100000;
  if(radius > max_radius)
  {
    alert("允许的最大搜索半径是 " + max_radius);
    return;
  }

  var params = 'namekeyword=' + keyw + '&city_lat=' + lat + '&city_lon=' + lon + '&range=' + radius;

  // 保持当前关键词页码
  var url = document.location.href;
  var page = get_url_param(url, 'page');
  if(page != '')
    params += '&page=' + page;
  var page_size = get_url_param(url, 'page_size');
  if(page_size != '')
    params += '&page_size=' + page_size;

  url = (BETTER_ADMIN_URL + '/poicluster?' + params);
  window.open(url, '_self');
}

// return: true if do the saving.
function keyword_user_save(silent)
{
  var sum = keyword_summary();
  if(sum == "")
  {
    if(!silent) alert("没有需要保存的动作");
    return false;
  }

  if(confirm("保存下列修改吗？\n" + sum))
  {
    $.post(BETTER_ADMIN_URL + "/poicluster/update", keyword_summary_xml(),
    function(data) {
      alert(data);
      if(data.substr(0, 2) == 'ok')
      {
        // empty change logs
        g_merges = new Array();
        g_poi_basic = new Array();
        g_poi_extra = new Array();
      }
    });
    return true;
  }
  return false;
}

// 生成关于已修改内容的报告
// return: plain text, empty if there is no changes.
function keyword_summary()
{
  var sum = "";

  // merge
  var s = "";
  for(fpid in g_merges)
  {
    if(g_merges[fpid] != -1)
    {
      var to_name = $("li#poi_item_" + g_merges[fpid]).attr('name');
      var from_name = $("li#poi_item_" + fpid).attr('name');
      s += "\t" + g_merges[fpid] + ":" + to_name + " << "+ fpid  + ":" + from_name + "\n";
    }
  }
  if(s != "")
    sum += "合并 -------------------------\n" + s;

  // updating basic properties
  s = '';
  for(pid in g_poi_basic)
  {
    var poi_name = $("li#poi_item_" + pid).attr('name');
    for(attr in g_poi_basic[pid])
    {
      var new_val = g_poi_basic[pid][attr];
      s += "\t" + pid + ":" + poi_name + "." + attr + " = "  + new_val + "\n";
    }
  }
  if(s != "")
    sum += "普通属性 -------------------------\n" + s;

  // set radius
  s = "";
  for(pid in g_poi_extra)
  {
    var poi_name = $("li#poi_item_" + pid).attr('name');
    for(attr in g_poi_extra[pid])
    {
      var new_val = g_poi_extra[pid][attr];
      s += "\t" + pid + ":" + poi_name + "." + attr + " = "  + new_val + "\n";
    }
  }
  if(s != "")
    sum += "扩展属性 -------------------------\n" + s;

  return sum;
}

// 生成XML格式的修改信息摘要
/* eg.
<?xml version="1.0" encoding="UTF-8"?>
<updates>
<merge>
</merge>
<basic>
  <poi id="19057360">
    <lat>31.300317</lat>
    <lon>120.681531</lon>
    <level_adjust>15</level_adjust>
  </poi>
</basic>
<extra>
  <poi id="19057360">
    <radius>35</radius>
  </poi>
</extra>
</updates>
*/
function keyword_summary_xml()
{
  var xml = '<?xml version="1.0" encoding="UTF-8"?>\n<updates>\n<merge>\n';

  // merging
  for(pid in g_merges)
  {
    if(g_merges[pid] != -1)
      xml += '\t<item from="' + pid + '" to="' + g_merges[pid] + '"/>\n';
  }
  xml += '</merge>\n<basic>\n';

  // updating basic properties
  for(pid in g_poi_basic)
  {
    xml += '\t<poi id="' + pid + '">\n';
    for(attr in g_poi_basic[pid])
    {
      var new_val = g_poi_basic[pid][attr];
      xml += '\t\t<' + attr + '>' + new_val + '</' + attr + '>\n';
    }
    xml += '\t</poi>\n';
  }
  xml += '</basic>\n<extra>\n';

  // updating extra properties
  for(pid in g_poi_extra)
  {
    xml += '\t<poi id="' + pid + '">\n';
    for(attr in g_poi_extra[pid])
    {
      var new_val = g_poi_extra[pid][attr];
      xml += '\t\t<' + attr + '>' + new_val + '</' + attr + '>\n';
    }
    xml += '\t</poi>\n';
  }
  xml += '</extra>\n</updates>';
  return xml;
}

// 判断是否需要保存
function keyword_dirty()
{
  return keyword_summary() != '';
}

function map_init()
{
  if (GBrowserIsCompatible()) {
    g_gmap = new GMap2(document.getElementById("map_convas"));
    g_gmap.setCenter(new GLatLng(31.2955, 120.6711), 13);
    g_gmap.enableScrollWheelZoom();
  }
  g_gmap.addControl(new GSmallMapControl(false));
}

// 从 DOM 读取 POI 列表并显示在地图上。
// 可重复执行(reload)。
function map_load_poi(reset_center)
{
  g_gmap.clearOverlays();
  g_markers = new Array();

  var lat, lon;
  $('li:[id^=poi_item_]').each(function(index, tag) {
    lat = parseFloat($(tag).attr('lat'));
    lon = parseFloat($(tag).attr('lon'));
    var name = $(tag).attr('name');
    var pid = parseInt($(tag).attr('pid'));
    var radius = parseInt($(tag).attr('radius'));
    var address = $(tag).attr('address');
    map_add_poi(index, lat, lon, radius, pid, name, pid + '\n' + address);
    if(radius > 0)
      map_add_circle(lat, lon, radius);
  });

  // set map center to last poi
  if(reset_center && typeof(lat) != 'undefined')
    g_gmap.setCenter(new GLatLng(lat, lon), 16);
}

/* 绘制半透明圆形区域 */
function map_add_circle(center_lat, center_lon, radius)
{
  var pnts = new Array();

  radius /= 95000; //86175 把长度单位从米近似折算为经纬度
  var n = 24;
  for(var i = 0; i <= n; ++i)
  {
    var a = 2 * 3.14159 * i / n;
    var r = radius;// * (1 - 0.2 * abs(sin($a))); // 0.2146 把长度从平面坐标系近似折算为球面坐标系
    var lat = center_lat + r * Math.sin(a);
    var lon = center_lon + r * Math.cos(a);
    pnts.push(new GLatLng(lat, lon));
  }

  var polygon = new GPolygon(pnts, "#0000FF", 1, 1, "#00FF00", 0.2);
  g_gmap.addOverlay(polygon);
}

/*
 * index: 0-based;
 */
function map_add_poi(index, lat, lon, radius, pid, name, description)
{
  var letteredIcon = new GIcon(G_DEFAULT_ICON);
  //letteredIcon.image = "http://www.google.cn/mapfiles/marker" + letter + ".png";
  //letteredIcon.image = "nums/" + index + ".gif";
  //letteredIcon.size  = iSize; var iSize  = GSize(40,64);
  letteredIcon.shadow = ""; // no shadow (flat)

  // Set up our GMarkerOptions object
  var markerOptions = {icon:letteredIcon, draggable:true, title:(name + "\n" + description)};
  var marker = new GMarker(new GLatLng(lat, lon), markerOptions);
  marker['name'] = name;
  marker['description'] = description;
  g_markers[index] = marker;

  g_gmap.addOverlay(marker);

  GEvent.addListener(marker, "mouseover", function() {
    if(!g_ignore_mouseover)
    {
      //气球的问题是当它位于地图边缘时，会自动平移地图以确保自身完整显示，所以还是 title 好一些
      //marker.openInfoWindow(name + "<br/>" + description.replace("\n", "<br/>"));

      $('#poi_item_' + pid).addClass('poi_item_highlight');

      // 移动POI列表到当前项目，从而不需要用户主动退拽列表滚动条
      $('#poi_container ol').animate({
        scrollTop: $('#poi_container ol li:eq(0)').height() * index
      }, 'slow');
    }
  });

  GEvent.addListener(marker, "mouseout", function() {
    if(!g_ignore_mouseover)
    {
      marker.closeInfoWindow();
    }
    $('#poi_item_' + pid).removeClass('poi_item_highlight');
  });

  // 点击图钉将锁定地图气球，以便用户把鼠标移动到POI列表
  GEvent.addListener(marker, "click", function() {
      g_ignore_mouseover = !g_ignore_mouseover;
  });

  // 拖动 marker 可以调整 POI 位置
  GEvent.addListener(marker, "dragstart", function(pos) {
      marker.closeInfoWindow();
      g_ignore_mouseover = true;
  });

  GEvent.addListener(marker, "dragend", function(pos) {
    var lat = round_float(pos.lat(), 6);
    var lon = round_float(pos.lng(), 6);
    if(typeof(g_poi_basic[pid.toString()]) == 'undefined')
    {
      g_poi_basic[pid.toString()] = {lat:lat, lon:lon};
    }
    else
    {
      g_poi_basic[pid.toString()].lat = lat;
      g_poi_basic[pid.toString()].lon = lon;
    }
    g_ignore_mouseover = false;

    // 让 POI 半径圆圈也跟着动起来……
    $('li#poi_item_' + pid).attr('lat', lat);
    $('li#poi_item_' + pid).attr('lon', lon);
    map_load_poi(false);
  });
}

// POI列表中的事件句柄可以调用此函数
function map_highlight_poi(idx)
{
  // trigger marker.mouseover 会导致 addClass

  var marker = g_markers[idx];
  marker.openInfoWindow(marker.name + "<br/>" + marker.description.replace("\n", "<br/>"));

  // 此时地图有可能不响应 mouseover 事件，但我们不希望这样
  g_ignore_mouseover = false;
}

function poi_add()
{
  var pid = prompt("请输入 poi_id");
  if(pid == null) return;

  if(isNaN(pid))
  {
    alert("poi_id 必须是数字");
    return;
  }

  // refresh page with extra param, appending a pidxxx param to url
  var url = document.location.href;
  if(url.indexOf('?') != -1)
    url += '&pid' + pid;
  else
    url += '?pid' + pid;
  window.open(url, '_self');
}

// 记录用户对 poi level_adjust 的编辑
function poi_set_level_adjust(pid)
{
  pid = pid.toString();
  var val = $('#poi_detail_level_adjust_' + pid).val();
  if(typeof(g_poi_basic[pid.toString()]) == 'undefined')
    g_poi_basic[pid.toString()] = {level_adjust:val};
  else
    g_poi_basic[pid.toString()].level_adjust = val;
}

// 记录用户对 poi radius 的编辑
function poi_set_radius(pid)
{
  pid = pid.toString();
  var val = $('#poi_detail_radius_' + pid).val();
  if(val == '')
    return;
  if(typeof(g_poi_extra[pid]) == 'undefined')
    g_poi_extra[pid] = {radius:val};
  else
    g_poi_extra[pid].radius = val;

  // 更新 DOM 中的 poi radius 信息，然后重载地图，使用户能立即看到半径
  $('li#poi_item_' + pid).attr('radius', val);
  map_load_poi(false);
}

function round_float(x,n)
{
  if(!parseInt(n))
    var n=0;
  if(!parseFloat(x))
    return false;
  return Math.round(x*Math.pow(10,n))/Math.pow(10,n);
}

// 设置或取消缺省POI
//
// 副作用：
// 1, 更新 g_def_pid 全局变量;
// 2, 更新 UI 外观;
function toggle_default(pid)
{
  // toggle
  if(g_def_pid != pid)
  {
    g_def_pid = pid;
  }
  else
  {
    g_def_pid = -1;
  }
  update_ui();
}

// 合并或取消合并当前POI到目标POI
//
// 副作用：
// 1, 记录合并动作;
// 2, 更新 UI 外观;
function toggle_merge(pid)
{
  if(typeof(g_merges[pid]) == 'undefined' || g_merges[pid] == -1)
  {
    // 设置合并

    if(g_def_pid == -1)
    {
      alert("请先设置目标POI");
      return;
    }

    if(g_def_pid == pid)
    {
      alert("不能合并到自己");
      return;
    }

    g_merges[pid] = g_def_pid;
  }
  else
  {
    // 取消合并
    g_merges[pid] = -1;
  }
  update_ui();
}

function toggle_poi_detail(pid)
{
  if($("div#poi_detail_" + pid).attr("class") == "poi_detail_off")
    $("div#poi_detail_" + pid).removeClass("poi_detail_off").addClass("poi_detail_on");
  else
    $("div#poi_detail_" + pid).removeClass("poi_detail_on").addClass("poi_detail_off");
}

/*
 * setup jquery tooltip.
 */
function tooltip()
{
  //Select all anchor tag with rel set to tooltip
    $('[rel=tooltip]').mouseover(function(e) {
         
        //Grab the title attribute's value and assign it to a variable
        var tip = $(this).attr('title');   
         
        //Remove the title attribute's to avoid the native tooltip from the browser
        $(this).attr('title','');
         
        //Append the tooltip template and its value
        $(this).append('<div id="tooltip"><div class="tipHeader"></div><div class="tipBody">' + tip + '</div><div class="tipFooter"></div></div>');    
         
        //Set the X and Y axis of the tooltip
        $('#tooltip').css('top', e.pageY + 10 );
        $('#tooltip').css('left', e.pageX + 20 );
         
        //Show the tooltip with faceIn effect
        $('#tooltip').fadeIn('500');
        $('#tooltip').fadeTo('10',1);
         
    }).mousemove(function(e) {
     
        //Keep changing the X and Y axis for the tooltip, thus, the tooltip move along with the mouse
        $('#tooltip').css('top', e.pageY + 10 );
        $('#tooltip').css('left', e.pageX + 20 );
         
    }).mouseout(function() {
     
        //Put back the title attribute's value
        $(this).attr('title',$('.tipBody').html());
     
        //Remove the appended tooltip template
        $(this).children('div#tooltip').remove();
         
    });
}

// 更新 UI 外观
// 比如按钮字体、可用性、提示信息等
function update_ui()
{
  // assign css class poi_default_stat_on/off
  $("span:[id^=poi_toggle_def_]").removeClass("poi_default_state_on").addClass("poi_default_state_off");
  $("span:[id^=poi_toggle_def_]").attr("title", "设为目标");
  if(g_def_pid != -1)
  {
    $("#poi_toggle_def_" + g_def_pid).removeClass("poi_default_state_off").addClass("poi_default_state_on");
    $("#poi_toggle_def_" + g_def_pid).attr("title", "取消目标");
  }

  // reset #toggle_merge_* buttons
  $("li:[id^=poi_item_]").removeClass("merged_poi");
  $("[id^=poi_toggle_merge_]").removeClass("disable_merge");

  // allow merging
  if(g_def_pid != -1)
  {
    var poi_name = $("li#poi_item_" + g_def_pid).attr('name');
    $("span:[id^=poi_toggle_merge_]").attr("title", "合并到 " + g_def_pid + ":" + poi_name);
    $("#poi_toggle_merge_" + g_def_pid).addClass("disable_merge");
  }
  else
    $("span:[id^=poi_toggle_merge_]").attr("title", "合并到 (null)");

  // allow negative merging
  for(pid in g_merges)
  {
    if(g_merges[pid] != -1)
    {
      $("#poi_item_" + pid).addClass("merged_poi");
      $("#poi_toggle_merge_" + pid).attr("title", "取消合并");
    }
  }
}

window.onload = function(){
  update_ui();
  //tooltip(); // todo: make sure jquery has loaded
};
