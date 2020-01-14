document.write("<script type='text/javascript' src='https://cdn.bootcss.com/jquery/3.4.1/jquery.js'></script>");
document.write("<script type='text/javascript' src='https://res.wx.qq.com/open/js/jweixin-1.3.2.js'></script>");

function toWechat() {
    wx.miniProgram.switchTab({
        url: '/pages/storage/view/view'
    });
}