function p1Lock() {
    //$("#itemUrl").attr("disabled", "disabled");
	$("#itemUrl").attr("readonly", "readonly");
    $(".addpanel_address").attr("class", "addpanel_address_");
}

//输入商品网址后提交方法
var CrawlSubmit = function() {
    var url = $("#itemUrl").val();
    var reg = new RegExp("http(s)?://([\\w-]+\\.)+[\\w-]+(/[\\w- ./?%&=]*)?");
    if (url.length <= 0) {
        $("#promptInfo").attr("class", "addpanel_wrong");
        $("#promptInfo p").text("请输入您想代购商品的详细页网址！");
    }
    else {
        if (url.indexOf("http://") == -1 && url.indexOf("https://") == -1)
            url = "http://" + url;
        if (reg.test(url)) {
            p1Lock();
            $("#addpanel_submit").attr("disabled", "disabled");
			$("#promptInfo img").remove();			
			$("#promptInfo").attr("class", "addpanel_loading").prepend("<img src=\"/Ulowi/Tpl/default/Public/images/loading.gif\" alt=\"请稍候\" />");
			$("#promptInfo p").text("正在抓取商品信息...");
			//提交表单
			$('#frmstep1').submit();
        }else {
            $("#promptInfo").attr("class", "addpanel_wrong");
            $("#promptInfo p").text("输入的网址不正确，请核实后再填写！");
        }
    }
}

$("#addpanel_submit").click(CrawlSubmit);
if ($("#itemUrl").val().length > 0) { CrawlSubmit(); }
$("#itemUrl").keydown(function(e) { if (e.keyCode == 13) { $("#addpanel_submit").click(); return false; } });