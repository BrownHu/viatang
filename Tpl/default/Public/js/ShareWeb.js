
var sina_tstr = "http://v.t.sina.com.cn/share/share.php";
var kaixin_tstr = "http://www.kaixin001.com/repaste/share.php";
var renren_tstr = "http://share.renren.com/share/buttonshare.do";
var qqspace_tstr = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey";
var qqtag_tstr = "http://shuqian.qq.com/post";
var douban_tstr = "http://www.douban.com/recommend/";
var cangbaidu_tstr = "http://cang.baidu.com/do/add";
var gbookmark_tstr = "http://www.google.com/bookmarks/mark";
var gbuzz_tstr = "http://www.google.com/buzz/post";
var delicious_tstr = "http://del.icio.us/post";
var qq_tstr = "http://v.t.qq.com/share/share.php";


var target_str = '_blank';
//打开窗口的大小
var window_size = "scrollbars=no,width=600,height=450,"
+ "left=75,top=20,status=no,resizable=yes";

var Invite_Content = "亲，悠乐代购不错，价格便宜服务好，还能免费验货，集中邮寄服务。现在开放注册中。赶快加入吧。";
//分享至新浪
function share_sina() {
    window.open(sina_tstr + '?title=' + getencodewebtitleForSina() + '&url='
	+ getencodelochref() + '&rcontent=', target_str, window_size);
}

//分享至开心
function share_kaixin() {
    window.open(kaixin_tstr + '?rtitle=' + getencodewebtitle() + '&rurl='
	+ InviteURL + '&rcontent=', target_str, window_size);
}

//分享至人人
function share_renren() {
    window.open(renren_tstr + '?title=' + getencodewebtitle() +
	'&link=' + getencodelochref() + '&content=', target_str, window_size);
}

//分享至QQ空间
function share_qqspace() {
    window.open(qqspace_tstr + '?url=' + InviteURL +
	'&rcontent=', target_str, window_size);
}

//分享至qq书签
function share_qqtag() {
    window.open(qqtag_tstr + '?title=' + getencodewebtitle() +
	'&uri=' + getencodelochref() +
	'&jumpback=2&noui=1', 'favit');
}

//分享至豆瓣
function share_douban() {
    window.open(douban_tstr + '?url=' + InviteURL +
	'&title=' + getencodewebtitle(), target_str, window_size);
}

//分享至百度搜藏
function share_cangbaidu() {
    window.open(cangbaidu_tstr + '?it='
	+ getencodewebtitle() + '&iu=' + getencodelochref() +
	'&dc=&fr=js#nw=1', target_str, window_size);
}

//分享至google书签
function share_gbookmark() {
    window.open(gbookmark_tstr + '?op=add&bkmk='
	+ getencodelochref() + '&title=' + getencodewebtitle());
}

//分享至google buzz
function share_gbuzz() {
    window.open(gbuzz_tstr + '?message=' + getencodewebtitle() + '&url='
	+ InviteURL,target_str, window_size);
}

//分享至Delicious书签
function share_delicious() {
    window.open(delicious_tstr + '?url='
	+ getencodelochref() + '&title='
	+ encodeURIComponent(document.title) + '&notes=');
}

function getencodelochref() {  
    return encodeURIComponent(InviteURL);
}

function getencodewebtitle() {
    return encodeURIComponent(Invite_Content.substring(0, 76));
}

String.prototype.Trim = function () {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

String.prototype.HalfWidthLength = function () {
    return this.replace(/[^\x00-\xff]/g, "**").length;
    // return this.length + (escape(this).split("%u").length - 1);  
}

function getencodewebtitleForSina() {
    //var url = document.location.href;

    var shareTitle = Invite_Content; //"#注册邀请# -- VANCL 凡客诚品";
    //+ "<a href='" + url + "'  target=_blank>" + url + "</a>"
    var availableLength = 270 - shareTitle.HalfWidthLength() - InviteURL.length;

    return encodeURIComponent(shareTitle);
}
function share_qq() {
    var _t = encodeURI(Invite_Content); //document.title
    var _url = InviteURL;
    var _appkey = encodeURI("c59181915b944d7abcb5b628a8e89990"); //你从腾讯获得的appkey
    var _pic = encodeURI(''); //（列如：var _pic='图片url1|图片url2|图片url3....）
    var _site = document.location.href; //你的网站地址
    var _u = 'http://v.t.qq.com/share/share.php?title=' + _t + '&url=' + _url + '&appkey=' + _appkey + '&site=' + _site + '&pic=' + _pic;
    window.open(_u, '转发到腾讯微博', 'width=700, height=680, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, location=yes, resizable=no, status=no');
}

