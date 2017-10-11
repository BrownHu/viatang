	  function aa()
	  {var today=new Date();
	  var xq=today.getDay();
	  var day=today.getDate();
	  var hh=today.getHours();
	  var mm=today.getMinutes();
	  var mmm=today.getSeconds();
	  var yf=today.getMonth()+1;	  
	  var year=today.getFullYear();
	  if(mmm<10)
	  {
		  mmm="0"+mmm
		  }
	    if(mm<10)
	  {
		  mm="0"+mm
		  }
		  if(hh<10)
	  {
		  hh="0"+hh
		  }
		  switch(xq)
	  {
		          case 1:
		  xq="一";
		  break;
		          case 2:
		  xq="二";
		  break;
		          case 3:
		  xq="三";
		  break;
		  		  case 4:
		  xq="四";
		  break;
		  		  case 5:
		  xq="五";
		  break;
		  		  case 6:
		  xq="六";
		  break;
		  		  case 0:
		  xq="天";
		  break;
	  }
	  document.getElementById("clock").innerHTML=year+"年"+yf+"月"+day+"日"+"&nbsp;"+hh+":"+mm+":"+mmm;
	  
	  }setInterval("aa()",1000);
