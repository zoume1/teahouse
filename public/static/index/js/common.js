
var phoneReg = /^1[34578]\d{9}$/,
    pwdReg = /^[a-zA-Z0-9]{8,20}$/;

var $partnerContainer = $('.partner-items');
for (var i = 1; i <= 12; i++) {
    var $partnerItem = $('.tpl').clone().removeClass('tpl').addClass('partner-item');
    $partnerItem
        .find('img')
        .attr('src', 'static/index/img/p-logo' + i + '.png');
    $partnerContainer.append($partnerItem);
}
// 导航
$('.nav-ul').on('click', '.nav-li', function () {
    var $index = $(this).index();
    switch ($index) {
        case 0: location.href = './'; break;
        case 1: location.href = 'wisdom'; break;
        case 2: location.href = 'partner'; break;
        case 3: location.href = 'about'; break;
    }
})
// 首页 应用场景
$('.scene-container').on('click', '.scene-item', function () {
    var $index = $(this).index();
    console.log($index)
    switch ($index) {
        case 0: location.href = 'tea_factory'; break;
        case 1: location.href = 'tea_merchant'; break;
        case 2: location.href = 'tea_moment'; break;
        case 3: location.href = 'consumer'; break;
    }
})
// 应用场景分页
$('.app-scene-nav-container').on('click', '.app-scene-nav-item', function () {
    var $index = $(this).index();
    switch ($index) {
        case 0: location.href = 'tea_factory'; break;
        case 1: location.href = 'tea_merchant'; break;
        case 2: location.href = 'tea_moment'; break;
        case 3: location.href = 'consumer'; break;
    }
})
// 登录 注册
$('.sign-in').click(function () {
    location.href = 'sign_in';
})
$('.register').click(function () {
    location.href = 'sign_up';
})

// logo返回首页
$('.logo img').add('.logo span').click(function () {
    location.href = './';
})
// 显示隐藏密码
$('.pw-icon').click(function () {
    $(this).toggleClass('pw-icon-show');
    if ($(this).hasClass('pw-icon-show')) {
        $(this).siblings().attr('type', 'text');
    } else {
        $(this).siblings().attr('type', 'password');
    }
})
// aside 返回顶部
$(window).scroll(function () {
    if ($(document).scrollTop() >= 300) {
        $('.aside-item:eq(3)').css('opacity', '1');
    } else {
        $('.aside-item:eq(3)').css('opacity', '0');
    }
})
$('.aside-item:eq(3)').click(function () {
    $('html').animate({ scrollTop: 0 }, 500);
})
;(function(myEvent){
    // 倒计时
    myEvent.buttonCountdown = function($el, msNum, timeFormat) {
        var text = $el.data("text") || $el.text(),
            timer = 0;
        $el.prop("disabled", true).addClass("disabled")
            .on("bc.clear", function () {
                clearTime();
            });
        ;(function countdown() {
            var time = showTime(msNum)[timeFormat];
            $el.text(time + '后失效');
            if (msNum <= 0) {
                msNum = 0;
                clearTime();
            } else {
                msNum -= 1000;
                timer = setTimeout(arguments.callee, 1000);
            }
        }())
        function clearTime() {
            clearTimeout(timer);
            $el.prop("disabled", false).removeClass("disabled").text(text);
        }
        function showTime(ms) {
            var d = Math.floor(ms / 1000 / 60 / 60 / 24),
                h = Math.floor(ms / 1000 / 60 / 60 % 24),
                m = Math.floor(ms / 1000 / 60 % 60),
                s = Math.floor(ms / 1000 % 60),
                ss = Math.floor(ms / 1000);
            return {
                d: d + "天",
                h: h + "小时",
                m: m + "分",
                ss: ss + "秒",
                "d:h:m:s": d + "天" + h + "小时" + m + "分" + s + "秒",
                "h:m:s": h + "小时" + m + "分" + s + "秒",
                "m:s": m + "分" + s + "秒"
            };
        }
        return this;
    }
    
    //设置cookie
    myEvent.setCookie = function (name, value, day) {
        var date = new Date();
        date.setDate(date.getDate() + day);
        document.cookie = name + '=' + value + ';expires=' + date;
    };
    //获取cookie
    myEvent.getCookie = function (name) {
        var reg = RegExp(name + '=([^;]+)');
        var arr = document.cookie.match(reg);
        if (arr) {
            return arr[1];
        } else {
            return '';
        }
    };
    //删除cookie
    myEvent.delCookie = function (name) {
        myEvent.setCookie(name, null, -1);
    };

    // 上传图片
    window.myEvent = myEvent;
}(window.myEvent || {}))

// 获取验证码
function getIdentifyingCode($el, url, phone) {
    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'JSON',
        data: {
            "mobile": phone
        },
        success: function (res) {
            console.log(res);
            if (res.status == 1) {
                layer.msg(res.info);
                myEvent.buttonCountdown($($el), 1000 * 60 * 1, "ss");
            } else {
                layer.msg(res.info);
            }
        },
        error: function (res) {
            console.log(res.status, res.statusText);
        }
    })
}

// 退出登录
$('.logout').click(function () {
    $.ajax({
        url: 'logout',
        type: 'POST',
        dataType: 'JSON',
        success: function (res) {
            console.log(res);
            if (res.status == 1) {
                myEvent.delCookie(btoa('phone'));
                myEvent.delCookie(btoa('password'));
                location.href = 'sign_in';
            }
        },
        error: function (res) {
            console.log(res.status, res.statusText);
        }
    })
})
// 进入后台
$('.backstage').click(function () {
    location.href = 'my_shop';
})
