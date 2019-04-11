
$(function () {

	height = $(window).height() - 64;
	var width = $(document.body).width();
	$(".recontent").height(height);//直接设置元素的高
	$(".UCleft").height(height);//直接设置元素的高

	$(".recontent").width(width - 140);//直接设置元素的高
	//	抽屉效果;
	$(".UCleft-fixed").children("dl").on("click", "dt", function () {
		// alert(111);
		if ($(this).parent("dl").hasClass("curr")) {
			$(this).parent("dl").removeClass("curr");
		}
		else {
			$(this).parent("dl").addClass("curr");
			$(this).parent("dl").siblings().removeClass("curr");
		}

	});


	function setCookie(name, value, day) {
		var date = new Date();
		date.setDate(date.getDate() + day);
		document.cookie = name + '=' + value + ';expires=' + date+";path=/;";
	
	};



$(".UCleft-fixed").children("dl").children("dd").on("click", "p", function () {

	$(this).css('font-weight', 'bold');
	$(this).siblings().css('font-weight', '500');
	$(this).parent("dd").parent("dl").siblings().children("dd").children("p").css('font-weight', '500');
	var url = $(this).data("url");
	var id = $(this).data("id");
	var value = $(this).data("value")
	// delCookie("item_id");
	setCookie("item_id", value);
	setCookie("page_id", id);
	var dk = $(window.parent.document).find("#add").attr("src");
	$.ajax({
		type: "get",
		url: url,
		cache: true,
		success: function (html) {
			$('#add').attr('src', url);
		}



	});
});
$("body").on("click", "#distribution_list a", function () {
	var id = $(this).data("id");
	setCookie("item_id", id);
})







});