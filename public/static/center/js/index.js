
$(".UCleft-fixed").children().on("click",function(){
	if($(this).hasClass("curr")){
		$(this).removeClass("curr");
	}
	else{
		  	$(this).addClass("curr");
    	$(this).siblings().removeClass("curr");
	}

  });
