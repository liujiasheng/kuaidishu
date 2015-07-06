/**
 * Created by Administrator on 14-8-21.
 */


function confirmBinding(){
    var openid = getURLParameter("oi");
    var username = $('#username').val();
    var password = $('#password').val();
    var pwmd5 = hex_md5(password);

    $.post('/weixin/weixin/usertobind',{
        openid : openid,
        username : username,
        password : pwmd5
    },function(data){
        if(data.state){
            window.location.href = '/weixin/weixin/userbinded?msg=绑定成功!';
        }else{
            alert(data.message);
        }
    },"json");

}

function confirmUnBinding(){
    var openid = getURLParameter("oi");

    $.post('/weixin/weixin/usertounbind',{
        openid : openid
    },function(data){
        if(data.state){
            window.location.href = '/weixin/weixin/userbinded?msg=解除绑定成功!';
        }else{
            alert(data.message);
        }
    },"json");
}

function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}

