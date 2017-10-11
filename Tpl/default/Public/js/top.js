$(document).ready(function(){
			$(".xl").mouseover(function(){
					$(this).find("ul:hidden").slideDown(500);
				});
			$(".xl").mouseleave(function(){
					$(this).find("ul:visible").slideUp(100);
				});
});

var IMYUAN;
IMYUAN || (IMYUAN = {});

(function(a) {

    a.fn.extend({
        returntop: function() {
            if (this[0]) {
                var b = this.click(function() {
                    a("html, body").animate({
                        scrollTop: 0
                    },
                    120)
                }),
                c = null;
                a(window).bind("scroll",
                function() {
                    var d = a(document).scrollTop(),
                    e = a(window).height();
                   
                    a.isIE6() && (b.hide(), clearTimeout(c), c = setTimeout(function() {
                        b.show();
                        clearTimeout(c)
                    },
                    1E3), b.css("top",d + e - 125))
                })
            }
        }

    })
})

(jQuery); (function(a) {

    a("body")('<a class="close" href="javascript:;"></a>');

})
(function() {

    $("#returnTop").returntop()
});
  