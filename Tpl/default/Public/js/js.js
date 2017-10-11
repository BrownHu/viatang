$(function($){
	 function fillZero(v){
            if(v<10){v='0'+v;}
            return v;
    }
	var d=new Date(parseInt($('#Beijing_Time').val()*1000));
	
    function nowTime(){
        var Y,M,D,W,H,I,S;
        d.setSeconds(d.getSeconds() + 1);
        Y=d.getFullYear();
        M=fillZero(d.getMonth()+1);
        D=fillZero(d.getDate());
        H=fillZero(d.getHours());
        I=fillZero(d.getMinutes());
        S=fillZero(d.getSeconds());
		var year = Y+'-'+M+'-'+D;
		var timestr = H+':'+I+':'+S;
        $('#footer_date').html(year);
		$('#footer_time').html(timestr);         
    }
	
	var timer = setInterval( nowTime , 1000); 
});

