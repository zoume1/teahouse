var $partnerContainer = $('.partner-items');
for(var i = 1; i <= 12; i++){
    var $partnerItem = $('.tpl').clone().removeClass('tpl').addClass('partner-item');
    $partnerItem
        .find('img')
            .attr('src', 'static/index/img/p-logo'+i+'.png');
    $partnerContainer.append($partnerItem);
}
// 导航
$('.nav-ul').on('click', '.nav-li', function(){
    var $index = $(this).index();
    switch($index){
        case 0: location.href = './'; break;
        case 1: location.href = 'wisdom'; break;
        case 2: location.href = 'partner'; break;
        case 3: location.href = 'about'; break;
    }
})
// 首页 应用场景
$('.scene-container').on('click', '.scene-item', function(){
    var $index = $(this).index();
    console.log($index)
    switch($index){
        case 0: location.href = 'tea_factory'; break;
        case 1: location.href = 'tea_merchant'; break;
        case 2: location.href = 'tea_moment'; break;
        case 3: location.href = 'consumer'; break;
    }
})
// 应用场景分页
$('.app-scene-nav-container').on('click', '.app-scene-nav-item', function(){
    var $index = $(this).index();
    switch($index){
        case 0: location.href = 'tea_factory'; break;
        case 1: location.href = 'tea_merchant'; break;
        case 2: location.href = 'tea_moment'; break;
        case 3: location.href = 'consumer'; break;
    }
})
// 登录 注册
$('.sign-in').click(function(){
    location.href = 'sign_in';
})
$('.register').click(function(){
    location.href = 'sign_up';
})

// logo返回首页
$('.logo-container').add('.logo span').click(function(){
    location.href = './';
})

// 显示隐藏密码
$('.pw-icon').click(function(){
    $(this).toggleClass('pw-icon-show');
    if($(this).hasClass('pw-icon-show')){
        $(this).siblings().attr('type', 'text');
    }else{
        $(this).siblings().attr('type', 'password');
    }
})