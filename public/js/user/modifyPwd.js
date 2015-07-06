/**
 * Created by james on 14-7-19.
 */

var passwordFormValidRules = {
    rules: {
        oripwd: {
            required: true,
            minlength: 6,
            maxlength: 16,
            isValidatePwd: true
        },
        newpwd: {
            required: true,
            minlength: 6,
            maxlength: 16,
            isValidatePwd: true
        },
        confirmPwd: {
            equalTo: "#newpwd"
        }
    },
    messages: {
        oripwd: {
            required: "请输入密码",
            minlength: "密码不得少于6位",
            maxlength: "密码不得长于16位",
            isValidatePwd: "密码只能是字母、数字、符号和中文组成"
        },
        newpwd: {
            required: "请输入密码",
            minlength: "密码不得少于6位",
            maxlength: "密码不得长于16位",
            isValidatePwd: "密码只能是字母、数字、符号和中文组成"
        },
        confirmPwd: {
            equalTo: "请输入正确的确认密码"
        }
    }
}

var updatePassword = function () {
    if (!$(this).valid()) {
        return false;
    }

    var oripwd = $("#oripwd").val();
    var newpwd = $('#newpwd').val();
    var data = {};
    var url = '/user/userinfo/userinfo/updatePassword';
    if(typeof oripwd !=='undefined'){
        data.oripwd = hex_md5(oripwd)
    }
    if(typeof newpwd !=='undefined'){
        data.newpwd = hex_md5(newpwd)
    }

    $.post(url,
        data
        , function (data) {
            if (data.state) {
                //TODO to enable redirect
                alert(data.message.message)
                $('#passwordForm')[0].reset();
                window.location.reload();
            } else {
                alert(data.message.message)
            }
        }, 'json');
    return false;
};

var el = $("#passwordForm");
el.submit(updatePassword);
el.validate(passwordFormValidRules);

$().ready(function () {
    jQuery.validator.addMethod("isValidatePwd", function (value, element) {
        if (value == "") {
            return true;
        }
        return this.optional(element) || checkUserPwd(value);
    }, "密码只能是英文、数字和符号组成");

    jQuery.validator.addMethod("isValidateUsername", function (value, element) {
        return this.optional(element) || checkNickName(value);
    }, "昵称只能是中文、英文、数字和_组成");

});


