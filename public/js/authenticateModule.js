jQuery(function($) {

    var redirect = getParameterByName('redirect');
    // this is the id of the form
    $("#loginform").submit(function() {
        if(!$(this).valid()){
            return false;
    }
        var url = "authenticate/Authenticate/login"; // the script where you handle the form input.
        var un = $("#loginUserName").val();
        var pwd = $("#loginPWD").val();
        var ut = $("#userType").val();
                $.post(url,
                    {"loginUserName":un,
                        "loginPWD":hex_md5(pwd),
                        "userType":ut}
        ,function(data){
            if(data.state){
                window.location.href = redirect;
//                alert(data.message.message)
            }else{
                alert(data.message.message+" "+data.code)
            }
        }, 'json');
        return false;
    });


    // this is the id of the form
    $("#logout").on('click',function() {
        var url = "authenticate/Authenticate/logout"; // the script where you handle the form input.

        $.post(url
            ,function(data){
                if(data.state){
//                window.location.href = ""
                    alert(data.message.message)
                }else{
                    alert(data.message.message+" "+data.code)
                }
            }, 'json');
        return false;
    });




});


function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

$().ready(function() {

    //自定义验证方法
    jQuery.validator.addMethod("isValidatePwd", function(value, element) {
        return this.optional(element) || checkUserPwd(value);
}, "密码只能是英文、数字和符号组成");
    jQuery.validator.addMethod("isValidateUsername", function(value, element) {
        return this.optional(element) || checkUsername(value);
    }, "用户名只能是中文、英文、数字和_组成");


    $("#loginform").validate({
        rules:{
            loginUserName:{
                required: true,
                minlength: 6,
                maxlength: 32,
                isValidateUsername:true

            },
            loginPWD:{
                required:true,
                minlength:6,
                maxlength:16,
                isValidatePwd:true
            }
        },
        messages:{
            loginUserName:{
                required: "请填写用户账号",
                minlength: "请输入正确的账号",
                maxlength: "请输入正确的账号",
                isValidateUsername:"请输入正确的账号"
            },
            loginPWD:{
                required:"请输入密码",
                minlength:"请输入正确的密码",
                maxlength:"请输入正确的密码",
                isValidatePwd:"请输入正确的密码"
            }
        }
    });

    $('#loginUserName').focus();




});
