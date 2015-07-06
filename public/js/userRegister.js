jQuery(function($){


    $('#captchaImg').on('click',getCaptcha);
    $('#captchaButton').on('click',getCaptcha);

    function getCaptcha(){
        $.get("authenticate/Authenticate/getCaptcha",
            function(data){
                if(data.state){
                    $('#captchaImg').attr('src',data.captcha) ;
                }else{
                    //TODO Add otification
                }
            },'json');
    }


    $("#registerform").submit(function() {
        if(!$(this).valid()){
            return false;
        }
        var url = "authenticate/Register/register"; // the script where you handle the form input.
        var un = $('#regUserName').val();
        var email = $('#regEmail').val();
        var pwd1 = $('#regPWD1').val();
        var pwd2 = $('#regPWD2').val();
        var cap = $('#regCaptcha').val();
        //TODO check the arguments
        $.post(url,
            {regUserName:un,
                regEmail:email,
                regPWD1:hex_md5(pwd1),
                regCaptcha:cap
            }
            ,function(data){
                if(data.state){
                    //TODO to enable redirect
                    alert(data.message.message);
                    setTimeout(function(){
                        window.location.href = "/userlogin";
                    },2000);
                }else{
                    alert(data.message.message);
                    getCaptcha();
                }
            }, 'json');
        return false;
    });

    getCaptcha();
});

$().ready(function() {

    $('#registerform')[0].reset();
    //自定义验证方法
    jQuery.validator.addMethod("isValidatePwd", function(value, element) {
        return this.optional(element) || checkUserPwd(value);
    }, "密码只能是英文、数字和符号组成");
    jQuery.validator.addMethod("isValidateUsername", function(value, element) {
        return this.optional(element) || checkUsername(value);
    }, "用户名只能是中文、英文、数字和_组成");
//    jQuery.validator.addMethod("isExistUser", function(value, element) {
//        var a = checkUserExist(value);
//        return this.optional(element) || a;
//    }, "用户名已存在");

    $("#registerform").validate({
        rules: {
            regUserName: {
                required: true,
                minlength: 6,
                maxlength: 32,
                isValidateUsername: true,
                remote: { //验证用户名是否存在
                    url: "authenticate/Register/checkUser", //后台处理程序
                    type: "post",  //数据发送方式
                    dataType: "json"       //接受数据格式
                }
            },
            regEmail:{
                required:true,
                email:true
            },
            regPWD1:{
                required:true,
                minlength:6,
                maxlength:16,
                isValidatePwd:true
            },
            regPWD2:{
                required:true,
                equalTo:"#regPWD1"
            },
            regCaptcha:{
                required:true,
                minlength:4,
                maxlength:4,
                remote: { //验证用户名是否存在
                    url: "authenticate/Register/checkCaptcha", //后台处理程序
                    type: "post",  //数据发送方式
                    dataType: "json"       //接受数据格式
                }
            }

        },
        messages:{
            regUserName:{
                required: "请输入用户名",
                minlength: "用户名不得少于6位",
                maxlength: "用户名不得长于32位",
                isValidateUsername:"用户名只能是字母、数字、_和中文组成",
                remote:"用户名已存在"
            },
            regEmail:{
                required:"请输入Email地址",
                email:"请输入正确的Email地址"
            },
            regPWD1:{
                required:"请输入密码",
                minlength:"密码不得少于6位",
                maxlength:"密码不得长于16位",
                isValidatePwd:"密码只能是字母、数字、符号和中文组成"
            },
            regPWD2:{
                required:"请输入确认密码",
                equalTo:"请输入正确的确认密码"
            },
            regCaptcha:{
                required:"请输入验证码",
                minlength:"验证为4位",
                maxlength:"验证为4位",
                remote: "验证码错误"
            }
        },
        errorPlacement: function(error, element) {
                error.addClass('errBox');
                error.appendTo( element.parent().find('label') );
        },
        errorElement:"span"
    });


    $("#registerform")[0].reset();
});
