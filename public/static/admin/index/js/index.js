
$(function(){


//	抽屉效果;
$(".UCleft-fixed").children("dl").on("click","dt",function(){
//	alert(111);
	if($(this).parent("dl").hasClass("curr")){
		$(this).parent("dl").removeClass("curr");
	}
	else{
		$(this).parent("dl").addClass("curr");
    	$(this).parent("dl").siblings().removeClass("curr");
	}

  });
  $(".UCleft-fixed").children("dl").children("dd").on("click","p",function(){
   var url=$(this).data("url");
   var dk = $(window.parent.document).find("#add").attr("src"); 
			$.ajax({
				type : "get",
				url: url,
				cache: true,
				success: function(html){
					$('#add').attr('src',url);
				}
				
			});
  });
  	
    
    
	

});