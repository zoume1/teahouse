// 轮播图
var mySwiper = new Swiper ('.swiper-container', {
    direction: 'horizontal',
    loop: true,
    autoplay: true,
    delay: 3000,
    // 如果需要分页器
    pagination: {
        el: '.swiper-pagination',
    },
    // 如果需要滚动条
    scrollbar: {
        el: '.swiper-scrollbar',
    },
}); 
var mySwiper2 = new Swiper ('.swiper-container2', {
    direction: 'horizontal',
    loop: true,
    autoplay: true,
    delay: 3000,
    // 如果需要分页器
    pagination: {
        el: '.swiper-pagination',
    },
    // 如果需要滚动条
    scrollbar: {
        el: '.swiper-scrollbar',
    },
    autoplay: {
        disableOnInteraction: false,
        delay: 3000,
    },
});   

// 意见反馈二维码
$('.handle_logo a').mouseenter(function(){
    var index = $(this).index();
    $(this).addClass('curr').siblings('a').removeClass('curr');
    $($('.code_box div')[index]).show().siblings('div').hide();
})
// 导航栏 下级菜单
$('.first_class_ul li').hover(function(){
    if($(this).index() === 3){
        $(this).find('a').addClass('first_class');
        $(this).find('ul').show();
    }
}, function(){
    $(this).find('a').removeClass('first_class');
    $(this).find('ul').hide();
});

//弹窗插件初始化
(function(){
    layui.use('layer', function(){ //独立版的layer无需执行这一句
        var $ = layui.jquery, layer = layui.layer; //独立版的layer无需执行这一句
    });
})();