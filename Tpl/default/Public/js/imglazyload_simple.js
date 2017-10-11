var ImgLazy = function() {
  var $winH   = $(window).height();
  var $img    = $("img[lazy='y']");
  var $imgH   = parseInt($img.height() / 2); 
  var $srcDef = "../Public/images/grey.gif";
  var runing  = function() {
	  $img.each(function(i) {
		  var $src = $(this).attr("original"); 
		  var $errorImg = $(this).attr('errorimg');
		  if($errorImg != ''){
			  $(this).bind('error',function(){
				  	$(this).attr("src", function() { return $errorImg ;}).fadeIn(300);
				  	$(this).onerror=null;
				  });
		  }
		  
		  var $scroTop = $(this).offset();
		  if ($scroTop.top + $imgH >= $(window).scrollTop() && $(window).scrollTop() + $winH >= ($scroTop.top + $imgH-300)) {
			  if ($(this).attr("src") == $srcDef) { $(this).hide(); }
			  $(this).attr("src", function() { return $src }).fadeIn(300);
		  }
	  })
  }
  runing(); 
  $(window).scroll(function() { runing(); });
};

$(function() { 
	ImgLazy();
});